<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ProofConsentService;
use App\Models\Proof;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProofConsentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProofConsentService $consentService;
    protected User $user;
    protected Company $company;
    protected Proof $proof;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->consentService = new ProofConsentService();
        
        // Create test data
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->proof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'testimonial',
            'created_by' => $this->user->id,
        ]);
        
        $this->actingAs($this->user);
    }

    public function test_generate_consent_token_creates_valid_token()
    {
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+60123456789',
        ];

        $token = $this->consentService->generateConsentToken($this->proof, $customerData);

        $this->assertNotEmpty($token);
        $this->assertEquals(32, strlen($token));
        
        // Check metadata was stored
        $this->proof->refresh();
        $metadata = $this->proof->metadata;
        
        $this->assertArrayHasKey('consent', $metadata);
        $this->assertEquals('pending', $metadata['consent']['status']);
        $this->assertEquals($customerData['name'], $metadata['consent']['customer_name']);
        $this->assertEquals($customerData['email'], $metadata['consent']['customer_email']);
        $this->assertEquals('testimonial_usage', $metadata['consent']['consent_type']);
    }

    public function test_verify_consent_token_validates_correct_token()
    {
        $customerData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);

        $isValid = $this->consentService->verifyConsentToken($this->proof, $token);

        $this->assertTrue($isValid);
    }

    public function test_verify_consent_token_rejects_invalid_token()
    {
        $customerData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $this->consentService->generateConsentToken($this->proof, $customerData);

        $isValid = $this->consentService->verifyConsentToken($this->proof, 'invalid_token');

        $this->assertFalse($isValid);
    }

    public function test_grant_consent_updates_proof_status()
    {
        $customerData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);

        $result = $this->consentService->grantConsent($this->proof, $token, [
            'agreed_terms' => true,
            'marketing_use' => true,
        ]);

        $this->assertTrue($result);
        
        $this->proof->refresh();
        $this->assertEquals('active', $this->proof->status);
        
        $metadata = $this->proof->metadata;
        $this->assertEquals('granted', $metadata['consent']['status']);
        $this->assertArrayHasKey('granted_at', $metadata['consent']);
        $this->assertArrayHasKey('ip_address', $metadata['consent']);
        $this->assertArrayHasKey('details', $metadata['consent']);
    }

    public function test_grant_consent_fails_with_invalid_token()
    {
        $result = $this->consentService->grantConsent($this->proof, 'invalid_token');

        $this->assertFalse($result);
        
        $this->proof->refresh();
        $this->assertEquals('draft', $this->proof->status);
    }

    public function test_revoke_consent_archives_proof()
    {
        // First grant consent
        $customerData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);
        $this->consentService->grantConsent($this->proof, $token);

        // Then revoke it
        $result = $this->consentService->revokeConsent($this->proof, 'Customer request');

        $this->assertTrue($result);
        
        $this->proof->refresh();
        $this->assertEquals('archived', $this->proof->status);
        
        $metadata = $this->proof->metadata;
        $this->assertEquals('revoked', $metadata['consent']['status']);
        $this->assertEquals('Customer request', $metadata['consent']['revocation_reason']);
        $this->assertArrayHasKey('revoked_at', $metadata['consent']);
    }

    public function test_get_consent_status_returns_complete_information()
    {
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);

        $status = $this->consentService->getConsentStatus($this->proof);

        $this->assertTrue($status['has_consent_data']);
        $this->assertEquals('pending', $status['status']);
        $this->assertEquals('john@example.com', $status['customer_email']);
        $this->assertEquals('John Doe', $status['customer_name']);
        $this->assertEquals('testimonial_usage', $status['consent_type']);
        $this->assertNotNull($status['expires_at']);
    }

    public function test_get_consent_status_handles_proof_without_consent()
    {
        $proof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'visual_proof', // Type that doesn't require consent
        ]);

        $status = $this->consentService->getConsentStatus($proof);

        $this->assertFalse($status['has_consent_data']);
        $this->assertEquals('not_required', $status['status']);
    }

    public function test_requires_consent_identifies_consent_requiring_types()
    {
        $testimonialProof = Proof::factory()->create(['type' => 'testimonial']);
        $caseStudyProof = Proof::factory()->create(['type' => 'case_study']);
        $visualProof = Proof::factory()->create(['type' => 'visual_proof']);

        $this->assertTrue($this->consentService->requiresConsent($testimonialProof));
        $this->assertTrue($this->consentService->requiresConsent($caseStudyProof));
        $this->assertFalse($this->consentService->requiresConsent($visualProof));
    }

    public function test_requires_consent_detects_personal_data()
    {
        $proofWithPii = Proof::factory()->create([
            'type' => 'visual_proof',
            'metadata' => ['contains_pii' => true],
        ]);

        $this->assertTrue($this->consentService->requiresConsent($proofWithPii));
    }

    public function test_get_expiring_consents_finds_soon_to_expire()
    {
        // Create proof with consent that expires in 15 days
        $customerData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);
        $this->consentService->grantConsent($this->proof, $token);

        // Manually set granted date to make it expire soon
        $metadata = $this->proof->metadata;
        $metadata['consent']['granted_at'] = now()->subYears(2)->addDays(15)->toISOString();
        $this->proof->update(['metadata' => $metadata]);

        $expiringConsents = $this->consentService->getExpiringConsents(30);

        $this->assertCount(1, $expiringConsents);
        $this->assertEquals($this->proof->id, $expiringConsents[0]['proof']->id);
        $this->assertLessThan(30, $expiringConsents[0]['expires_in_days']);
    }

    public function test_bulk_consent_status_check()
    {
        $proof2 = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'testimonial',
        ]);

        // Grant consent for first proof
        $customerData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);
        $this->consentService->grantConsent($this->proof, $token);

        $bulkStatus = $this->consentService->getBulkConsentStatus([
            $this->proof->id,
            $proof2->id,
        ]);

        $this->assertArrayHasKey($this->proof->id, $bulkStatus);
        $this->assertArrayHasKey($proof2->id, $bulkStatus);
        $this->assertEquals('granted', $bulkStatus[$this->proof->id]['status']);
        $this->assertEquals('not_required', $bulkStatus[$proof2->id]['status']);
    }

    public function test_send_consent_request_logs_request()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Consent request email would be sent', \Mockery::type('array'));

        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);
        
        $result = $this->consentService->sendConsentRequest($this->proof, $token, $customerData);

        $this->assertTrue($result);
        
        // Check that email_sent_at was recorded
        $this->proof->refresh();
        $metadata = $this->proof->metadata;
        $this->assertArrayHasKey('email_sent_at', $metadata['consent']);
    }

    public function test_generate_withdrawal_link_creates_valid_url()
    {
        // Grant consent first
        $customerData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);
        $this->consentService->grantConsent($this->proof, $token);

        $withdrawalLink = $this->consentService->generateWithdrawalLink($this->proof);

        $this->assertNotNull($withdrawalLink);
        $this->assertStringContainsString($this->proof->uuid, $withdrawalLink);
        $this->assertStringContainsString('proof.consent.withdraw', $withdrawalLink);
    }

    public function test_generate_withdrawal_link_returns_null_for_no_consent()
    {
        $withdrawalLink = $this->consentService->generateWithdrawalLink($this->proof);

        $this->assertNull($withdrawalLink);
    }

    public function test_anonymize_proof_data_removes_personal_info()
    {
        // Set up proof with personal data
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+60123456789',
        ];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);
        $this->consentService->grantConsent($this->proof, $token);

        $result = $this->consentService->anonymizeProofData($this->proof);

        $this->assertTrue($result);
        
        $this->proof->refresh();
        $this->assertEquals('Anonymized Proof', $this->proof->title);
        $this->assertStringContainsString('anonymized', $this->proof->description);
        
        $metadata = $this->proof->metadata;
        $this->assertArrayNotHasKey('customer_name', $metadata['consent']);
        $this->assertArrayNotHasKey('customer_email', $metadata['consent']);
        $this->assertArrayNotHasKey('customer_phone', $metadata['consent']);
        $this->assertArrayHasKey('anonymized_at', $metadata['consent']);
    }

    public function test_consent_type_mapping()
    {
        $types = [
            'testimonial' => 'testimonial_usage',
            'case_study' => 'case_study_publication',
            'client_review' => 'review_display',
            'social_proof' => 'social_media_content',
        ];

        foreach ($types as $proofType => $expectedConsentType) {
            $proof = Proof::factory()->create([
                'type' => $proofType,
                'company_id' => $this->company->id,
            ]);

            $customerData = ['name' => 'Test', 'email' => 'test@example.com'];
            $this->consentService->generateConsentToken($proof, $customerData);

            $proof->refresh();
            $this->assertEquals($expectedConsentType, $proof->metadata['consent']['consent_type']);
        }
    }

    public function test_consent_expiration_calculation()
    {
        $customerData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $token = $this->consentService->generateConsentToken($this->proof, $customerData);
        $this->consentService->grantConsent($this->proof, $token);

        $status = $this->consentService->getConsentStatus($this->proof);
        
        $this->assertNotNull($status['expires_at']);
        
        $expiresAt = Carbon::parse($status['expires_at']);
        $grantedAt = Carbon::parse($this->proof->metadata['consent']['granted_at']);
        
        $this->assertEquals(2, $expiresAt->diffInYears($grantedAt));
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}