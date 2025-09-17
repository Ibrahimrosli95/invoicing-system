<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Proof;
use App\Models\ProofAsset;
use App\Models\User;
use App\Models\Company;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ProofAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Company $otherCompany;
    protected User $superadmin;
    protected User $companyManager;
    protected User $financeManager;
    protected User $salesManager;
    protected User $salesCoordinator;
    protected User $salesExecutive;
    protected User $otherCompanyUser;
    protected Team $team;
    protected Proof $ownProof;
    protected Proof $teamProof;
    protected Proof $companyProof;
    protected Proof $otherCompanyProof;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRolesAndPermissions();
        $this->createCompaniesAndUsers();
        $this->createTeamStructure();
        $this->createTestProofs();
    }

    public function test_superadmin_can_access_all_proofs()
    {
        $this->actingAs($this->superadmin);

        // Can view any proof
        $response = $this->get(route('proofs.show', $this->ownProof->uuid));
        $response->assertStatus(200);

        $response = $this->get(route('proofs.show', $this->otherCompanyProof->uuid));
        $response->assertStatus(200);

        // Can edit any proof
        $response = $this->get(route('proofs.edit', $this->otherCompanyProof->uuid));
        $response->assertStatus(200);

        // Can delete any proof
        $response = $this->delete(route('proofs.destroy', $this->otherCompanyProof->uuid));
        $response->assertRedirect();
    }

    public function test_company_manager_can_manage_company_proofs()
    {
        $this->actingAs($this->companyManager);

        // Can view company proofs
        $response = $this->get(route('proofs.show', $this->ownProof->uuid));
        $response->assertStatus(200);

        $response = $this->get(route('proofs.show', $this->teamProof->uuid));
        $response->assertStatus(200);

        $response = $this->get(route('proofs.show', $this->companyProof->uuid));
        $response->assertStatus(200);

        // Cannot view other company proofs
        $response = $this->get(route('proofs.show', $this->otherCompanyProof->uuid));
        $response->assertStatus(403);

        // Can edit company proofs
        $response = $this->get(route('proofs.edit', $this->companyProof->uuid));
        $response->assertStatus(200);

        // Can delete company proofs
        $response = $this->delete(route('proofs.destroy', $this->companyProof->uuid));
        $response->assertRedirect();
    }

    public function test_sales_manager_can_manage_team_proofs()
    {
        $this->actingAs($this->salesManager);

        // Can view own proofs
        $response = $this->get(route('proofs.show', $this->ownProof->uuid));
        $response->assertStatus(200);

        // Can view team proofs
        $response = $this->get(route('proofs.show', $this->teamProof->uuid));
        $response->assertStatus(200);

        // Can edit own proofs
        $response = $this->get(route('proofs.edit', $this->ownProof->uuid));
        $response->assertStatus(200);

        // Can edit team member proofs
        $response = $this->get(route('proofs.edit', $this->teamProof->uuid));
        $response->assertStatus(200);

        // Cannot edit proofs from other teams/users not in team
        $response = $this->get(route('proofs.edit', $this->companyProof->uuid));
        $response->assertStatus(403);

        // Cannot access other company proofs
        $response = $this->get(route('proofs.show', $this->otherCompanyProof->uuid));
        $response->assertStatus(403);
    }

    public function test_sales_coordinator_has_limited_permissions()
    {
        $this->actingAs($this->salesCoordinator);

        // Can create proofs
        $response = $this->get(route('proofs.create'));
        $response->assertStatus(200);

        // Can view company proofs
        $response = $this->get(route('proofs.show', $this->companyProof->uuid));
        $response->assertStatus(200);

        // Can edit own proofs only
        $response = $this->get(route('proofs.edit', $this->ownProof->uuid));
        $response->assertStatus(200);

        // Cannot edit other users' proofs
        $response = $this->get(route('proofs.edit', $this->teamProof->uuid));
        $response->assertStatus(403);

        // Cannot delete proofs (except drafts)
        $draftProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->salesCoordinator->id,
            'status' => 'draft',
        ]);

        $response = $this->delete(route('proofs.destroy', $draftProof->uuid));
        $response->assertRedirect();

        // Cannot delete published proofs
        $publishedProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->salesCoordinator->id,
            'status' => 'active',
        ]);

        $response = $this->delete(route('proofs.destroy', $publishedProof->uuid));
        $response->assertStatus(403);
    }

    public function test_sales_executive_has_minimal_permissions()
    {
        $this->actingAs($this->salesExecutive);

        // Can create proofs
        $response = $this->get(route('proofs.create'));
        $response->assertStatus(200);

        // Can view own proofs
        $response = $this->get(route('proofs.show', $this->ownProof->uuid));
        $response->assertStatus(200);

        // Can view published company proofs
        $publishedProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'active',
            'created_by' => $this->salesManager->id,
        ]);

        $response = $this->get(route('proofs.show', $publishedProof->uuid));
        $response->assertStatus(200);

        // Cannot view draft proofs of others
        $draftProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'draft',
            'created_by' => $this->salesManager->id,
        ]);

        $response = $this->get(route('proofs.show', $draftProof->uuid));
        $response->assertStatus(403);

        // Can edit own proofs only
        $response = $this->get(route('proofs.edit', $this->ownProof->uuid));
        $response->assertStatus(200);

        // Cannot edit other users' proofs
        $response = $this->get(route('proofs.edit', $this->teamProof->uuid));
        $response->assertStatus(403);

        // Cannot delete any proofs (except own drafts)
        $ownDraft = Proof::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->salesExecutive->id,
            'status' => 'draft',
        ]);

        $response = $this->delete(route('proofs.destroy', $ownDraft->uuid));
        $response->assertRedirect();
    }

    public function test_proof_duplication_permissions()
    {
        // Sales coordinator can duplicate any company proof
        $this->actingAs($this->salesCoordinator);
        $response = $this->post(route('proofs.duplicate', $this->companyProof->uuid));
        $response->assertRedirect();

        // Sales executive can only duplicate published proofs
        $this->actingAs($this->salesExecutive);
        
        $publishedProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'active',
            'created_by' => $this->salesManager->id,
        ]);

        $response = $this->post(route('proofs.duplicate', $publishedProof->uuid));
        $response->assertRedirect();

        // Cannot duplicate draft proofs
        $draftProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'draft',
            'created_by' => $this->salesManager->id,
        ]);

        $response = $this->post(route('proofs.duplicate', $draftProof->uuid));
        $response->assertStatus(403);
    }

    public function test_asset_upload_permissions()
    {
        $this->actingAs($this->salesExecutive);

        // Can upload assets to own proofs
        $response = $this->post(route('proofs.upload-assets', $this->ownProof->uuid), [
            'assets' => [
                new \Illuminate\Http\UploadedFile(
                    __DIR__ . '/../../fixtures/test-image.jpg',
                    'test-image.jpg',
                    'image/jpeg',
                    null,
                    true
                )
            ]
        ]);
        $response->assertRedirect();

        // Cannot upload assets to other users' proofs
        $response = $this->post(route('proofs.upload-assets', $this->teamProof->uuid), [
            'assets' => [
                new \Illuminate\Http\UploadedFile(
                    __DIR__ . '/../../fixtures/test-image.jpg',
                    'test-image.jpg',
                    'image/jpeg',
                    null,
                    true
                )
            ]
        ]);
        $response->assertStatus(403);
    }

    public function test_featured_proof_management_permissions()
    {
        // Only company managers can manage featured proofs
        $this->actingAs($this->companyManager);

        $response = $this->patch(route('proofs.toggle-featured', $this->companyProof->uuid));
        $response->assertRedirect();

        // Sales managers cannot manage featured proofs
        $this->actingAs($this->salesManager);

        $response = $this->patch(route('proofs.toggle-featured', $this->ownProof->uuid));
        $response->assertStatus(403);
    }

    public function test_proof_approval_permissions()
    {
        // Sales managers and above can approve proofs
        $this->actingAs($this->salesManager);

        $approvalProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'draft',
            'metadata' => [
                'approval' => [
                    'workflow_state' => 'pending_review',
                    'current_approver' => $this->salesManager->id,
                ]
            ],
        ]);

        $response = $this->post(route('proofs.approve', $approvalProof->uuid), [
            'comments' => 'Approved for publication',
        ]);
        $response->assertRedirect();

        // Sales executives cannot approve proofs
        $this->actingAs($this->salesExecutive);

        $response = $this->post(route('proofs.approve', $approvalProof->uuid));
        $response->assertStatus(403);
    }

    public function test_sensitive_data_access_permissions()
    {
        // Create proof with sensitive data
        $sensitiveProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'metadata' => [
                'contains_pii' => true,
                'security_level' => 'confidential',
            ],
        ]);

        // Finance managers can access sensitive data
        $this->actingAs($this->financeManager);
        $response = $this->get(route('proofs.show', $sensitiveProof->uuid));
        $response->assertStatus(200);

        // Sales executives cannot access sensitive data
        $this->actingAs($this->salesExecutive);
        $response = $this->get(route('proofs.show', $sensitiveProof->uuid));
        $response->assertStatus(403);
    }

    public function test_analytics_access_permissions()
    {
        // Sales coordinators and above can view analytics
        $this->actingAs($this->salesCoordinator);
        $response = $this->get(route('proofs.analytics.data'));
        $response->assertStatus(200);

        // Create a user without coordinator role
        $basicUser = User::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($basicUser);
        $response = $this->get(route('proofs.analytics.data'));
        $response->assertStatus(403);
    }

    public function test_bulk_operations_permissions()
    {
        // Sales coordinators and above can perform bulk operations
        $this->actingAs($this->salesCoordinator);
        
        $proof1 = Proof::factory()->create(['company_id' => $this->company->id]);
        $proof2 = Proof::factory()->create(['company_id' => $this->company->id]);

        $response = $this->post(route('proofs.bulk-action'), [
            'action' => 'activate',
            'proof_ids' => [$proof1->id, $proof2->id],
        ]);
        $response->assertRedirect();

        // Sales executives cannot perform bulk operations
        $this->actingAs($this->salesExecutive);

        $response = $this->post(route('proofs.bulk-action'), [
            'action' => 'activate',
            'proof_ids' => [$proof1->id, $proof2->id],
        ]);
        $response->assertStatus(403);
    }

    public function test_cross_company_access_prevention()
    {
        $this->actingAs($this->otherCompanyUser);

        // Cannot access proofs from different company
        $response = $this->get(route('proofs.show', $this->ownProof->uuid));
        $response->assertStatus(403);

        $response = $this->get(route('proofs.edit', $this->companyProof->uuid));
        $response->assertStatus(403);

        $response = $this->delete(route('proofs.destroy', $this->teamProof->uuid));
        $response->assertStatus(403);

        // Cannot see other company's proofs in listings
        $response = $this->get(route('proofs.index'));
        $response->assertStatus(200);
        $response->assertDontSee($this->ownProof->title);
        $response->assertDontSee($this->companyProof->title);
    }

    public function test_archived_proof_access()
    {
        $archivedProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'archived',
            'created_by' => $this->salesExecutive->id,
        ]);

        // Only managers can view archived proofs
        $this->actingAs($this->salesManager);
        $response = $this->get(route('proofs.show', $archivedProof->uuid));
        $response->assertStatus(200);

        // Sales executives cannot view archived proofs (even their own)
        $this->actingAs($this->salesExecutive);
        $response = $this->get(route('proofs.show', $archivedProof->uuid));
        $response->assertStatus(403);
    }

    public function test_proof_restore_permissions()
    {
        // Only superadmin can restore soft-deleted proofs
        $deletedProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->salesManager->id,
        ]);
        $deletedProof->delete();

        $this->actingAs($this->superadmin);
        $response = $this->post(route('proofs.restore', $deletedProof->id));
        $response->assertRedirect();

        // Company managers cannot restore
        $this->actingAs($this->companyManager);
        $response = $this->post(route('proofs.restore', $deletedProof->id));
        $response->assertStatus(403);
    }

    protected function createRolesAndPermissions(): void
    {
        $roles = [
            'superadmin' => ['*'],
            'company_manager' => [
                'proof.viewAny', 'proof.view', 'proof.create', 'proof.update', 'proof.delete',
                'proof.publish', 'proof.manageFeatured', 'proof.viewAnalytics'
            ],
            'finance_manager' => [
                'proof.viewAny', 'proof.view', 'proof.viewAnalytics', 'proof.viewSensitiveData'
            ],
            'sales_manager' => [
                'proof.viewAny', 'proof.view', 'proof.create', 'proof.update', 'proof.delete',
                'proof.publish', 'proof.viewAnalytics', 'proof.approveContent'
            ],
            'sales_coordinator' => [
                'proof.viewAny', 'proof.view', 'proof.create', 'proof.update',
                'proof.viewAnalytics', 'proof.bulkOperations'
            ],
            'sales_executive' => [
                'proof.viewAny', 'proof.view', 'proof.create', 'proof.update'
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
            
            if ($permissions !== ['*']) {
                foreach ($permissions as $permissionName) {
                    $permission = Permission::firstOrCreate([
                        'name' => $permissionName,
                        'guard_name' => 'web'
                    ]);
                    $role->givePermissionTo($permission);
                }
            }
        }
    }

    protected function createCompaniesAndUsers(): void
    {
        $this->company = Company::factory()->create(['name' => 'Test Company']);
        $this->otherCompany = Company::factory()->create(['name' => 'Other Company']);

        $this->superadmin = User::factory()->create(['company_id' => $this->company->id]);
        $this->superadmin->assignRole('superadmin');

        $this->companyManager = User::factory()->create(['company_id' => $this->company->id]);
        $this->companyManager->assignRole('company_manager');

        $this->financeManager = User::factory()->create(['company_id' => $this->company->id]);
        $this->financeManager->assignRole('finance_manager');

        $this->salesManager = User::factory()->create(['company_id' => $this->company->id]);
        $this->salesManager->assignRole('sales_manager');

        $this->salesCoordinator = User::factory()->create(['company_id' => $this->company->id]);
        $this->salesCoordinator->assignRole('sales_coordinator');

        $this->salesExecutive = User::factory()->create(['company_id' => $this->company->id]);
        $this->salesExecutive->assignRole('sales_executive');

        $this->otherCompanyUser = User::factory()->create(['company_id' => $this->otherCompany->id]);
        $this->otherCompanyUser->assignRole('company_manager');
    }

    protected function createTeamStructure(): void
    {
        $this->team = Team::factory()->create(['company_id' => $this->company->id]);
        
        $this->team->users()->attach([
            $this->salesManager->id,
            $this->salesCoordinator->id,
            $this->salesExecutive->id,
        ]);
    }

    protected function createTestProofs(): void
    {
        // Proof owned by the sales executive (used in tests as "own proof")
        $this->ownProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->salesExecutive->id,
            'status' => 'active',
            'title' => 'Own Proof',
        ]);

        // Proof from team member
        $this->teamProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->salesCoordinator->id,
            'status' => 'active',
            'title' => 'Team Proof',
        ]);

        // Proof from company (not in team)
        $this->companyProof = Proof::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->companyManager->id,
            'status' => 'active',
            'title' => 'Company Proof',
        ]);

        // Proof from other company
        $this->otherCompanyProof = Proof::factory()->create([
            'company_id' => $this->otherCompany->id,
            'created_by' => $this->otherCompanyUser->id,
            'status' => 'active',
            'title' => 'Other Company Proof',
        ]);
    }
}