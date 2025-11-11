<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Organization;
use App\Models\ProjectOrganization;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ProjectPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ProjectPolicy();
    }

    /**
     * @test
     */
    public function viewAny_returns_true_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->viewAny($user);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function viewAny_returns_true_when_user_organization_participates_in_projects()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $project = Project::factory()->create();

        // Associer l'organisation au projet
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->viewAny($user);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function viewAny_returns_false_when_user_has_no_permission_and_no_project_participation()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);

        $result = $this->policy->viewAny($user);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function view_returns_true_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->view($user, $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function view_returns_true_when_user_organization_participates_in_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $project = Project::factory()->create();

        // Associer l'organisation au projet
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->view($user, $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function view_returns_false_when_user_has_no_permission_and_organization_does_not_participate()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $project = Project::factory()->create();

        $result = $this->policy->view($user, $project);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function create_returns_true_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'create_projects']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function create_returns_false_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);

        $result = $this->policy->create($user);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function update_returns_true_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'edit_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->update($user, $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function update_returns_false_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $project = Project::factory()->create();

        $result = $this->policy->update($user, $project);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function delete_returns_true_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'delete_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->delete($user, $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function delete_returns_false_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $project = Project::factory()->create();

        $result = $this->policy->delete($user, $project);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function system_admin_can_perform_all_actions()
    {
        $user = User::factory()->create(['is_system_admin' => true]);
        $project = Project::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
        $this->assertTrue($this->policy->view($user, $project));
        $this->assertTrue($this->policy->create($user));
        $this->assertTrue($this->policy->update($user, $project));
        $this->assertTrue($this->policy->delete($user, $project));
    }
}
