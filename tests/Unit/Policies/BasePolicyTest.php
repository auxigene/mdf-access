<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Classe de policy concrète pour tester BasePolicy
 */
class TestablePolicy extends \App\Policies\BasePolicy
{
    protected function getResourceSlug(): string
    {
        return 'projects';
    }
}

class BasePolicyTest extends TestCase
{
    use RefreshDatabase;

    private TestablePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TestablePolicy();
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
    public function viewAny_returns_false_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);

        $result = $this->policy->viewAny($user);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function viewAny_returns_true_for_system_admin()
    {
        $user = User::factory()->create(['is_system_admin' => true]);

        $result = $this->policy->viewAny($user);

        $this->assertTrue($result);
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
    public function view_returns_false_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
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
    public function restore_returns_true_when_user_has_edit_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'edit_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->restore($user, $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function forceDelete_returns_true_when_user_has_delete_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'delete_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->forceDelete($user, $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function approve_returns_true_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'approve_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->approve($user, $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function export_returns_true_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'export_projects']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->export($user);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function export_returns_false_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);

        $result = $this->policy->export($user);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function system_admin_passes_all_checks()
    {
        $user = User::factory()->create(['is_system_admin' => true]);
        $project = Project::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
        $this->assertTrue($this->policy->view($user, $project));
        $this->assertTrue($this->policy->create($user));
        $this->assertTrue($this->policy->update($user, $project));
        $this->assertTrue($this->policy->delete($user, $project));
        $this->assertTrue($this->policy->restore($user, $project));
        $this->assertTrue($this->policy->forceDelete($user, $project));
        $this->assertTrue($this->policy->approve($user, $project));
        $this->assertTrue($this->policy->export($user));
    }
}
