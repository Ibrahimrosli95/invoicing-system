<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ProofSecurityService;
use App\Models\Proof;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProofSecurityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProofSecurityService $securityService;
    protected User $user;
    protected User $manager;
    protected User $executive;
    protected Company $company;
    protected Proof $proof;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->securityService = new ProofSecurityService();
        
        // Create test data
        $this->company = Company::factory()->create();
        
        // Create users with different roles
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->user->assignRole('company_manager');
        
        $this->manager = User::factory()->create(['company_id' => $this->company->id]);
        $this->manager->assignRole('sales_manager');
        
        $this->executive = User::factory()->create(['company_id' => $this->company->id]);
        $this->executive->assignRole('sales_executive');
        
        $this->proof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'testimonial',
            'created_by' => $this->user->id,
        ]);
        
        $this->actingAs($this->user);
    }

    public function test_can_access_sensitive_content_allows_sufficient_clearance()
    {
        $this->proof->update([
            'metadata' => ['security_level' => 'confidential']
        ]);

        $canAccess = $this->securityService->canAccessSensitiveContent($this->user, $this->proof);

        $this->assertTrue($canAccess);
    }

    public function test_can_access_sensitive_content_denies_insufficient_clearance()
    {
        $this->proof->update([
            'metadata' => ['security_level' => 'highly_confidential']
        ]);

        $canAccess = $this->securityService->canAccessSensitiveContent($this->executive, $this->proof);

        $this->assertFalse($canAccess);
    }

    public function test_can_access_sensitive_content_denies_different_company()
    {
        $otherCompany = Company::factory()->create();
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherUser->assignRole('company_manager');

        $canAccess = $this->securityService->canAccessSensitiveContent($otherUser, $this->proof);

        $this->assertFalse($canAccess);
    }

    public function test_get_security_level_returns_explicit_level()
    {
        $this->proof->update([
            'metadata' => ['security_level' => 'restricted']
        ]);

        $level = $this->securityService->getSecurityLevel($this->proof);

        $this->assertEquals(ProofSecurityService::SECURITY_LEVELS['restricted'], $level);
    }

    public function test_get_security_level_auto_classifies_sensitive_proof()
    {
        $this->proof->update([
            'type' => 'testimonial',
            'metadata' => ['contains_pii' => true]
        ]);

        $level = $this->securityService->getSecurityLevel($this->proof);

        $this->assertGreaterThanOrEqual(ProofSecurityService::SECURITY_LEVELS['confidential'], $level);
    }

    public function test_set_security_level_updates_metadata()
    {
        $result = $this->securityService->setSecurityLevel($this->proof, 'restricted', 'Contains sensitive customer data');

        $this->assertTrue($result);
        
        $this->proof->refresh();
        $metadata = $this->proof->metadata;
        
        $this->assertEquals('restricted', $metadata['security_level']);
        $this->assertEquals('restricted', $metadata['security_classification']['level']);
        $this->assertEquals('Contains sensitive customer data', $metadata['security_classification']['reason']);
        $this->assertFalse($metadata['security_classification']['auto_classified']);
        $this->assertEquals($this->user->id, $metadata['security_classification']['classified_by']);
    }

    public function test_set_security_level_rejects_invalid_level()
    {
        $result = $this->securityService->setSecurityLevel($this->proof, 'invalid_level');

        $this->assertFalse($result);
    }

    public function test_get_user_clearance_level_by_role()
    {
        $superadmin = User::factory()->create(['company_id' => $this->company->id]);
        $superadmin->assignRole('superadmin');
        
        $companyManager = User::factory()->create(['company_id' => $this->company->id]);
        $companyManager->assignRole('company_manager');
        
        $salesExecutive = User::factory()->create(['company_id' => $this->company->id]);
        $salesExecutive->assignRole('sales_executive');

        $this->assertEquals(ProofSecurityService::SECURITY_LEVELS['highly_confidential'], 
                          $this->securityService->getUserClearanceLevel($superadmin));
        $this->assertEquals(ProofSecurityService::SECURITY_LEVELS['restricted'], 
                          $this->securityService->getUserClearanceLevel($companyManager));
        $this->assertEquals(ProofSecurityService::SECURITY_LEVELS['internal'], 
                          $this->securityService->getUserClearanceLevel($salesExecutive));
    }

    public function test_scan_for_sensitive_data_detects_patterns()
    {
        $this->proof->update([
            'title' => 'Credit card 4532-1234-5678-9012 found',
            'description' => 'Contact john.doe@example.com or +60123456789',
            'metadata' => ['notes' => 'Amount: $15,000.00 USD']
        ]);

        $findings = $this->securityService->scanForSensitiveData($this->proof);

        $this->assertNotEmpty($findings);
        
        $patternTypes = array_column($findings, 'pattern');
        $this->assertContains('credit_card', $patternTypes);
        $this->assertContains('email_address', $patternTypes);
        $this->assertContains('phone_number', $patternTypes);
        $this->assertContains('currency_amount', $patternTypes);
    }

    public function test_apply_access_restrictions_updates_metadata()
    {
        $restrictions = [
            'ip_whitelist' => ['192.168.1.1', '10.0.0.1'],
            'time_restrictions' => ['start_hour' => 9, 'end_hour' => 17],
            'watermarking_required' => true,
            'view_limit' => 10,
        ];

        $result = $this->securityService->applyAccessRestrictions($this->proof, $restrictions);

        $this->assertTrue($result);
        
        $this->proof->refresh();
        $metadata = $this->proof->metadata;
        
        $this->assertEquals($restrictions['ip_whitelist'], $metadata['access_restrictions']['ip_whitelist']);
        $this->assertEquals($restrictions['time_restrictions'], $metadata['access_restrictions']['time_restrictions']);
        $this->assertTrue($metadata['access_restrictions']['watermarking_required']);
        $this->assertEquals(10, $metadata['access_restrictions']['view_limit']);
        $this->assertEquals($this->user->id, $metadata['access_restrictions']['applied_by']);
    }

    public function test_check_access_restrictions_allows_unrestricted_access()
    {
        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn() => $this->user);

        $result = $this->securityService->checkAccessRestrictions($this->proof, $request);

        $this->assertTrue($result['allowed']);
        $this->assertEmpty($result['violations']);
    }

    public function test_check_access_restrictions_blocks_invalid_ip()
    {
        $this->securityService->applyAccessRestrictions($this->proof, [
            'ip_whitelist' => ['192.168.1.1']
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn() => $this->user);

        $result = $this->securityService->checkAccessRestrictions($this->proof, $request);

        $this->assertFalse($result['allowed']);
        $this->assertNotEmpty($result['violations']);
        $this->assertStringContainsString('not in whitelist', $result['violations'][0]);
    }

    public function test_check_access_restrictions_enforces_time_limits()
    {
        $this->securityService->applyAccessRestrictions($this->proof, [
            'time_restrictions' => ['start_hour' => 9, 'end_hour' => 17]
        ]);

        // Mock current time to be outside allowed hours (e.g., 22:00)
        Carbon::setTestNow(Carbon::today()->setHour(22));

        $request = Request::create('/', 'GET');
        $result = $this->securityService->checkAccessRestrictions($this->proof, $request);

        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('outside allowed hours', $result['violations'][0]);

        Carbon::setTestNow(); // Reset
    }

    public function test_check_access_restrictions_enforces_view_limits()
    {
        $this->securityService->applyAccessRestrictions($this->proof, [
            'view_limit' => 2
        ]);

        // Mock that user has already viewed 2 times
        $this->proof->views()->createMany([
            ['viewer_type' => 'user', 'viewer_id' => $this->user->id],
            ['viewer_type' => 'user', 'viewer_id' => $this->user->id],
        ]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn() => $this->user);

        $result = $this->securityService->checkAccessRestrictions($this->proof, $request);

        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('View limit (2) exceeded', $result['violations'][0]);
    }

    public function test_generate_secure_access_token_creates_valid_token()
    {
        $token = $this->securityService->generateSecureAccessToken($this->proof, $this->user->id, 24);

        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token);
        
        // Check that token was cached
        $cacheKey = "proof_access_token:{$this->user->id}:{$this->proof->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_validate_secure_access_token_accepts_valid_token()
    {
        $token = $this->securityService->generateSecureAccessToken($this->proof, $this->user->id, 24);

        $isValid = $this->securityService->validateSecureAccessToken($token, $this->proof, $this->user->id);

        $this->assertTrue($isValid);
    }

    public function test_validate_secure_access_token_rejects_invalid_token()
    {
        $isValid = $this->securityService->validateSecureAccessToken('invalid.token', $this->proof, $this->user->id);

        $this->assertFalse($isValid);
    }

    public function test_validate_secure_access_token_rejects_wrong_user()
    {
        $token = $this->securityService->generateSecureAccessToken($this->proof, $this->user->id, 24);

        $isValid = $this->securityService->validateSecureAccessToken($token, $this->proof, $this->manager->id);

        $this->assertFalse($isValid);
    }

    public function test_revoke_access_token_removes_from_cache()
    {
        $token = $this->securityService->generateSecureAccessToken($this->proof, $this->user->id, 24);
        
        // Verify token exists
        $this->assertTrue($this->securityService->validateSecureAccessToken($token, $this->proof, $this->user->id));

        $result = $this->securityService->revokeAccessToken($this->proof, $this->user->id);

        $this->assertTrue($result);
        $this->assertFalse($this->securityService->validateSecureAccessToken($token, $this->proof, $this->user->id));
    }

    public function test_log_security_event_stores_in_metadata()
    {
        Log::shouldReceive('warning')->once();

        $this->securityService->logSecurityEvent('unauthorized_access', $this->proof, [
            'attempted_action' => 'download',
            'user_agent' => 'Test Browser',
        ]);

        $this->proof->refresh();
        $metadata = $this->proof->metadata;
        
        $this->assertArrayHasKey('security_events', $metadata);
        $this->assertCount(1, $metadata['security_events']);
        
        $event = $metadata['security_events'][0];
        $this->assertEquals('unauthorized_access', $event['event_type']);
        $this->assertEquals($this->proof->id, $event['proof_id']);
        $this->assertEquals($this->user->id, $event['user_id']);
        $this->assertEquals('download', $event['details']['attempted_action']);
    }

    public function test_get_security_violations_filters_by_date()
    {
        // Add old event
        $oldEvent = [
            'event_type' => 'old_violation',
            'timestamp' => now()->subDays(45)->toISOString(),
            'user_id' => $this->user->id,
        ];

        // Add recent event
        $recentEvent = [
            'event_type' => 'recent_violation',
            'timestamp' => now()->subDays(15)->toISOString(),
            'user_id' => $this->user->id,
        ];

        $this->proof->update([
            'metadata' => [
                'security_events' => [$oldEvent, $recentEvent]
            ]
        ]);

        $violations = $this->securityService->getSecurityViolations($this->proof, 30);

        $this->assertCount(1, $violations);
        $this->assertEquals('recent_violation', $violations[0]['event_type']);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        Cache::flush();
        parent::tearDown();
    }
}