<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Proof;
use App\Models\User;
use App\Models\Company;
use App\Services\ProofConsentService;
use App\Services\ProofSecurityService;
use App\Services\ProofApprovalService;
use App\Http\Middleware\ProofSecurityMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class ProofSecurityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $companyManager;
    protected User $salesManager;
    protected User $salesExecutive;
    protected Proof $testimonialProof;
    protected Proof $sensitiveProof;

    protected function setUp(): void
    {
        parent::setUp();

        // Create company
        $this->company = Company::factory()->create();

        // Create roles if they don't exist
        $this->createRolesIfNeeded();

        // Create users with different roles
        $this->companyManager = User::factory()->create(['company_id' => $this->company->id]);
        $this->companyManager->assignRole('company_manager');

        $this->salesManager = User::factory()->create(['company_id' => $this->company->id]);
        $this->salesManager->assignRole('sales_manager');

        $this->salesExecutive = User::factory()->create(['company_id' => $this->company->id]);
        $this->salesExecutive->assignRole('sales_executive');

        // Create test proofs
        $this->testimonialProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'testimonial',
            'status' => 'draft',
            'created_by' => $this->salesExecutive->id,
        ]);

        $this->sensitiveProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'case_study',
            'status' => 'draft',
            'created_by' => $this->salesManager->id,
            'metadata' => [
                'security_level' => 'restricted',
                'contains_pii' => true,
            ],
        ]);
    }

    public function test_consent_workflow_for_testimonial_proof()
    {
        $this->actingAs($this->salesExecutive);

        // Step 1: Generate consent token
        $consentService = new ProofConsentService();
        $customerData = [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '+60123456789',
        ];

        $token = $consentService->generateConsentToken($this->testimonialProof, $customerData);

        $this->assertNotEmpty($token);
        $this->testimonialProof->refresh();
        $this->assertEquals('pending', $this->testimonialProof->metadata['consent']['status']);

        // Step 2: Send consent request (mocked)
        $emailSent = $consentService->sendConsentRequest($this->testimonialProof, $token, $customerData);
        $this->assertTrue($emailSent);

        // Step 3: Customer grants consent
        $consentGranted = $consentService->grantConsent($this->testimonialProof, $token, [
            'agreed_terms' => true,
            'marketing_use' => true,
        ]);

        $this->assertTrue($consentGranted);
        $this->testimonialProof->refresh();
        $this->assertEquals('active', $this->testimonialProof->status);
        $this->assertEquals('granted', $this->testimonialProof->metadata['consent']['status']);

        // Step 4: Customer can revoke consent
        $consentRevoked = $consentService->revokeConsent($this->testimonialProof, 'Customer changed mind');

        $this->assertTrue($consentRevoked);
        $this->testimonialProof->refresh();
        $this->assertEquals('archived', $this->testimonialProof->status);
        $this->assertEquals('revoked', $this->testimonialProof->metadata['consent']['status']);
    }

    public function test_approval_workflow_for_sensitive_content()
    {
        $this->actingAs($this->salesManager);

        $approvalService = new ProofApprovalService();

        // Step 1: Submit proof for approval
        $submitted = $approvalService->submitForApproval($this->sensitiveProof);

        $this->assertTrue($submitted);
        $this->sensitiveProof->refresh();
        $metadata = $this->sensitiveProof->metadata;
        $this->assertEquals('pending_review', $metadata['approval']['workflow_state']);
        $this->assertArrayHasKey('approvers', $metadata['approval']);

        // Step 2: Switch to approver and approve the proof
        $this->actingAs($this->companyManager);

        $approved = $approvalService->approveProof($this->sensitiveProof, [
            'comments' => 'Content looks good, approved for publication',
        ]);

        $this->assertTrue($approved);
        $this->sensitiveProof->refresh();
        $this->assertEquals('active', $this->sensitiveProof->status);
        $this->assertEquals('approved', $this->sensitiveProof->metadata['approval']['workflow_state']);
    }

    public function test_approval_workflow_rejection()
    {
        $this->actingAs($this->salesManager);

        $approvalService = new ProofApprovalService();
        $approvalService->submitForApproval($this->sensitiveProof);

        // Switch to approver and reject
        $this->actingAs($this->companyManager);

        $rejected = $approvalService->rejectProof($this->sensitiveProof, [
            'reason' => 'Contains sensitive information that needs redaction',
            'comments' => 'Please remove customer phone numbers before resubmitting',
        ]);

        $this->assertTrue($rejected);
        $this->sensitiveProof->refresh();
        $this->assertEquals('draft', $this->sensitiveProof->status);
        $this->assertEquals('rejected', $this->sensitiveProof->metadata['approval']['workflow_state']);
    }

    public function test_security_clearance_access_control()
    {
        $securityService = new ProofSecurityService();

        // Company manager should access restricted content
        $canAccessAsManager = $securityService->canAccessSensitiveContent($this->companyManager, $this->sensitiveProof);
        $this->assertTrue($canAccessAsManager);

        // Sales executive should NOT access restricted content
        $canAccessAsExecutive = $securityService->canAccessSensitiveContent($this->salesExecutive, $this->sensitiveProof);
        $this->assertFalse($canAccessAsExecutive);

        // Sales manager should access confidential but not restricted
        $this->sensitiveProof->update(['metadata' => ['security_level' => 'confidential']]);
        $canAccessConfidential = $securityService->canAccessSensitiveContent($this->salesManager, $this->sensitiveProof);
        $this->assertTrue($canAccessConfidential);

        $this->sensitiveProof->update(['metadata' => ['security_level' => 'highly_confidential']]);
        $canAccessHighlyConfidential = $securityService->canAccessSensitiveContent($this->salesManager, $this->sensitiveProof);
        $this->assertFalse($canAccessHighlyConfidential);
    }

    public function test_proof_access_middleware_enforcement()
    {
        $this->actingAs($this->salesExecutive);

        // Try to access restricted proof - should be denied
        $response = $this->get(route('proofs.show', $this->sensitiveProof->uuid));

        $this->assertEquals(403, $response->status());
    }

    public function test_proof_access_with_valid_security_token()
    {
        $securityService = new ProofSecurityService();

        // Generate token for company manager
        $token = $securityService->generateSecureAccessToken($this->sensitiveProof, $this->companyManager->id, 24);

        $this->assertNotEmpty($token);

        // Validate token
        $isValid = $securityService->validateSecureAccessToken($token, $this->sensitiveProof, $this->companyManager->id);
        $this->assertTrue($isValid);

        // Token should be invalid for different user
        $isValidForOtherUser = $securityService->validateSecureAccessToken($token, $this->sensitiveProof, $this->salesExecutive->id);
        $this->assertFalse($isValidForOtherUser);
    }

    public function test_access_restrictions_workflow()
    {
        $this->actingAs($this->companyManager);

        $securityService = new ProofSecurityService();

        // Apply IP restrictions
        $restrictions = [
            'ip_whitelist' => ['192.168.1.100', '10.0.0.50'],
            'time_restrictions' => [
                'start_hour' => 9,
                'end_hour' => 17,
            ],
            'view_limit' => 5,
            'watermarking_required' => true,
        ];

        $applied = $securityService->applyAccessRestrictions($this->sensitiveProof, $restrictions);
        $this->assertTrue($applied);

        // Test access with current IP (should fail - not in whitelist)
        $request = $this->createRequest();
        $checkResult = $securityService->checkAccessRestrictions($this->sensitiveProof, $request);
        
        $this->assertFalse($checkResult['allowed']);
        $this->assertStringContainsString('not in whitelist', implode(', ', $checkResult['violations']));
    }

    public function test_time_based_access_restrictions()
    {
        $securityService = new ProofSecurityService();

        $this->actingAs($this->companyManager);
        
        // Apply time restrictions (9 AM to 5 PM)
        $securityService->applyAccessRestrictions($this->sensitiveProof, [
            'time_restrictions' => [
                'start_hour' => 9,
                'end_hour' => 17,
            ],
        ]);

        // Mock time outside allowed hours
        Carbon::setTestNow(Carbon::today()->setHour(20)); // 8 PM

        $request = $this->createRequest();
        $checkResult = $securityService->checkAccessRestrictions($this->sensitiveProof, $request);

        $this->assertFalse($checkResult['allowed']);
        $this->assertStringContainsString('outside allowed hours', implode(', ', $checkResult['violations']));

        // Mock time within allowed hours
        Carbon::setTestNow(Carbon::today()->setHour(14)); // 2 PM

        $checkResult = $securityService->checkAccessRestrictions($this->sensitiveProof, $request);
        $this->assertTrue($checkResult['allowed']);

        Carbon::setTestNow(); // Reset
    }

    public function test_sensitive_data_detection()
    {
        $securityService = new ProofSecurityService();

        // Create proof with sensitive data
        $proofWithSensitiveData = Proof::factory()->create([
            'company_id' => $this->company->id,
            'title' => 'Customer testimonial with card 4532-1234-5678-9012',
            'description' => 'Contact customer at john.doe@company.com or call +60123456789. Project value: $25,000.00',
            'metadata' => [
                'customer_notes' => 'IC: 901234-12-3456, Passport: A12345678'
            ],
        ]);

        $findings = $securityService->scanForSensitiveData($proofWithSensitiveData);

        $this->assertNotEmpty($findings);
        
        $patterns = array_column($findings, 'pattern');
        $this->assertContains('credit_card', $patterns);
        $this->assertContains('email_address', $patterns);
        $this->assertContains('phone_number', $patterns);
        $this->assertContains('currency_amount', $patterns);

        // High confidence findings
        $highConfidenceFindings = array_filter($findings, fn($f) => $f['confidence'] === 'high');
        $this->assertNotEmpty($highConfidenceFindings);
    }

    public function test_complete_gdpr_compliance_workflow()
    {
        $consentService = new ProofConsentService();

        $this->actingAs($this->salesExecutive);

        // 1. Create testimonial requiring consent
        $customerData = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '+60123456789',
        ];

        $token = $consentService->generateConsentToken($this->testimonialProof, $customerData);
        $consentService->grantConsent($this->testimonialProof, $token);

        $this->testimonialProof->refresh();
        $this->assertEquals('granted', $this->testimonialProof->metadata['consent']['status']);

        // 2. Check data can be exported (Right to Access)
        $consentStatus = $consentService->getConsentStatus($this->testimonialProof);
        $this->assertTrue($consentStatus['has_consent_data']);
        $this->assertEquals('granted', $consentStatus['status']);

        // 3. Customer requests data deletion (Right to Erasure)
        $consentService->revokeConsent($this->testimonialProof, 'Customer requested data deletion');

        $this->testimonialProof->refresh();
        $this->assertEquals('revoked', $this->testimonialProof->metadata['consent']['status']);
        $this->assertEquals('archived', $this->testimonialProof->status);

        // 4. Anonymize data for compliance
        $anonymized = $consentService->anonymizeProofData($this->testimonialProof);
        $this->assertTrue($anonymized);

        $this->testimonialProof->refresh();
        $this->assertEquals('Anonymized Proof', $this->testimonialProof->title);
        $this->assertArrayNotHasKey('customer_name', $this->testimonialProof->metadata['consent']);
    }

    public function test_multi_approver_workflow()
    {
        // Create highly sensitive proof requiring multiple approvers
        $highSecurityProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'case_study',
            'metadata' => [
                'security_level' => 'highly_confidential',
                'contains_pii' => true,
                'high_value' => true,
            ],
        ]);

        $approvalService = new ProofApprovalService();

        $this->actingAs($this->salesManager);

        // Submit for approval - should require multiple approvers
        $submitted = $approvalService->submitForApproval($highSecurityProof);
        $this->assertTrue($submitted);

        $highSecurityProof->refresh();
        $metadata = $highSecurityProof->metadata;
        $this->assertTrue($metadata['approval']['requires_all_approvers']);
    }

    public function test_security_violation_logging()
    {
        $securityService = new ProofSecurityService();

        $this->actingAs($this->salesExecutive);

        // Attempt to access restricted content (should log violation)
        $securityService->logSecurityEvent('unauthorized_access_attempt', $this->sensitiveProof, [
            'attempted_action' => 'view',
            'security_level' => 'restricted',
            'user_clearance' => 'internal',
        ]);

        $this->sensitiveProof->refresh();
        $securityEvents = $this->sensitiveProof->metadata['security_events'] ?? [];

        $this->assertNotEmpty($securityEvents);
        $this->assertEquals('unauthorized_access_attempt', $securityEvents[0]['event_type']);
        $this->assertEquals($this->salesExecutive->id, $securityEvents[0]['user_id']);
    }

    public function test_expiring_consent_detection()
    {
        $consentService = new ProofConsentService();

        // Create consent that expires soon
        $customerData = ['name' => 'Test User', 'email' => 'test@example.com'];
        $token = $consentService->generateConsentToken($this->testimonialProof, $customerData);
        $consentService->grantConsent($this->testimonialProof, $token);

        // Manually adjust consent date to expire soon
        $metadata = $this->testimonialProof->metadata;
        $metadata['consent']['granted_at'] = now()->subYears(2)->addDays(15)->toISOString();
        $this->testimonialProof->update(['metadata' => $metadata]);

        // Check for expiring consents
        $expiringConsents = $consentService->getExpiringConsents(30);

        $this->assertCount(1, $expiringConsents);
        $this->assertEquals($this->testimonialProof->id, $expiringConsents[0]['proof']->id);
        $this->assertLessThan(30, $expiringConsents[0]['expires_in_days']);
    }

    protected function createRequest(): \Illuminate\Http\Request
    {
        $request = \Illuminate\Http\Request::create('/', 'GET');
        $request->setUserResolver(fn() => $this->companyManager);
        return $request;
    }

    protected function createRolesIfNeeded(): void
    {
        $roles = ['superadmin', 'company_manager', 'sales_manager', 'sales_coordinator', 'sales_executive', 'finance_manager'];

        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName, 'guard_name' => 'web']);
            }
        }
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}