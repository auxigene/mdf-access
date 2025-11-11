<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Budget;
use App\Models\Project;
use App\Models\Organization;
use App\Models\ProjectOrganization;
use App\Policies\BudgetPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetPolicyTest extends TestCase
{
    use RefreshDatabase;

    private BudgetPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new BudgetPolicy();
    }

    /**
     * @test
     */
    public function view_returns_true_when_user_has_permission_and_access_to_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_budgets']);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Associer l'organisation au projet
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->view($user, $budget);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function view_returns_false_when_user_has_permission_but_no_access_to_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_budgets']);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Ne PAS associer l'organisation au projet

        $result = $this->policy->view($user, $budget);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function update_returns_true_when_user_has_permission_and_access_to_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'edit_budgets']);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Associer l'organisation au projet
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->update($user, $budget);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function update_returns_false_when_user_has_permission_but_no_access_to_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'edit_budgets']);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Ne PAS associer l'organisation au projet

        $result = $this->policy->update($user, $budget);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function approve_returns_true_when_user_is_moa_for_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'approve_budgets']);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Associer l'organisation au projet comme MOA
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moa'
        ]);

        $result = $this->policy->approve($user, $budget);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function approve_returns_true_when_user_is_client_for_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'approve_budgets']);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Associer l'organisation au projet comme client
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'client'
        ]);

        $result = $this->policy->approve($user, $budget);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function approve_returns_false_when_user_is_moe_for_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'approve_budgets']);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Associer l'organisation au projet comme MOE (ne peut pas approuver)
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->approve($user, $budget);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function approve_returns_false_when_user_does_not_have_permission()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        // Associer l'organisation au projet comme MOA
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moa'
        ]);

        $result = $this->policy->approve($user, $budget);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function approve_returns_false_when_budget_has_no_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'approve_budgets']);

        $budget = Budget::factory()->create(['project_id' => null]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->approve($user, $budget);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function system_admin_can_perform_all_actions()
    {
        $user = User::factory()->create(['is_system_admin' => true]);
        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        $this->assertTrue($this->policy->view($user, $budget));
        $this->assertTrue($this->policy->update($user, $budget));
        $this->assertTrue($this->policy->approve($user, $budget));
    }
}
