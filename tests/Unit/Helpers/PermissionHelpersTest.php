<?php

namespace Tests\Unit\Helpers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionHelpersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_can_returns_true_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $result = user_can('view_projects');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_can_returns_false_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $this->actingAs($user);

        $result = user_can('view_projects');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_can_returns_false_when_user_is_not_authenticated()
    {
        $result = user_can('view_projects');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_can_returns_true_for_system_admin()
    {
        $user = User::factory()->create(['is_system_admin' => true]);
        $this->actingAs($user);

        $result = user_can('view_projects');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_can_accepts_scope_parameter()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role, ['project_id' => $project->id]);

        $this->actingAs($user);

        $result = user_can('view_projects', $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_can_any_returns_true_when_user_has_at_least_one_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $result = user_can_any(['view_projects', 'create_projects', 'edit_projects']);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_can_any_returns_false_when_user_has_none_of_the_permissions()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $this->actingAs($user);

        $result = user_can_any(['view_projects', 'create_projects', 'edit_projects']);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_can_any_returns_false_when_user_is_not_authenticated()
    {
        $result = user_can_any(['view_projects', 'create_projects']);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_can_any_accepts_scope_parameter()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role, ['project_id' => $project->id]);

        $this->actingAs($user);

        $result = user_can_any(['view_projects', 'edit_projects'], $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_can_all_returns_true_when_user_has_all_permissions()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();

        $permission1 = Permission::factory()->create(['slug' => 'view_projects']);
        $permission2 = Permission::factory()->create(['slug' => 'create_projects']);
        $permission3 = Permission::factory()->create(['slug' => 'edit_projects']);

        $role->permissions()->attach([$permission1->id, $permission2->id, $permission3->id]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $result = user_can_all(['view_projects', 'create_projects', 'edit_projects']);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_can_all_returns_false_when_user_is_missing_one_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();

        $permission1 = Permission::factory()->create(['slug' => 'view_projects']);
        $permission2 = Permission::factory()->create(['slug' => 'create_projects']);

        $role->permissions()->attach([$permission1->id, $permission2->id]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $result = user_can_all(['view_projects', 'create_projects', 'edit_projects']);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_can_all_returns_false_when_user_is_not_authenticated()
    {
        $result = user_can_all(['view_projects', 'create_projects']);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_can_all_accepts_scope_parameter()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $project = Project::factory()->create();

        $permission1 = Permission::factory()->create(['slug' => 'view_projects']);
        $permission2 = Permission::factory()->create(['slug' => 'edit_projects']);

        $role->permissions()->attach([$permission1->id, $permission2->id]);
        $user->roles()->attach($role, ['project_id' => $project->id]);

        $this->actingAs($user);

        $result = user_can_all(['view_projects', 'edit_projects'], $project);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_has_role_returns_true_when_user_has_role()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create(['slug' => 'project_manager']);

        $user->roles()->attach($role);

        $this->actingAs($user);

        $result = user_has_role('project_manager');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_has_role_returns_false_when_user_does_not_have_role()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $this->actingAs($user);

        $result = user_has_role('project_manager');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_has_role_returns_false_when_user_is_not_authenticated()
    {
        $result = user_has_role('project_manager');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_is_admin_returns_true_when_user_is_system_admin()
    {
        $user = User::factory()->create(['is_system_admin' => true]);
        $this->actingAs($user);

        $result = user_is_admin();

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function user_is_admin_returns_false_when_user_is_not_system_admin()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $this->actingAs($user);

        $result = user_is_admin();

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function user_is_admin_returns_false_when_user_is_not_authenticated()
    {
        $result = user_is_admin();

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function abort_unless_can_does_not_abort_when_user_has_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user);

        // Ne devrait pas lancer d'exception
        abort_unless_can('view_projects');

        // Si on arrive ici, le test passe
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function abort_unless_can_aborts_with_403_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $this->actingAs($user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionCode(403);

        abort_unless_can('view_projects');
    }

    /**
     * @test
     */
    public function abort_unless_can_accepts_scope_parameter()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);
        $project = Project::factory()->create();

        $role->permissions()->attach($permission);
        $user->roles()->attach($role, ['project_id' => $project->id]);

        $this->actingAs($user);

        // Ne devrait pas lancer d'exception
        abort_unless_can('view_projects', $project);

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function abort_unless_can_accepts_custom_message()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $this->actingAs($user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Custom error message');

        abort_unless_can('view_projects', null, 'Custom error message');
    }

    /**
     * @test
     */
    public function permission_slug_constructs_correct_slug()
    {
        $result = permission_slug('view', 'projects');

        $this->assertEquals('view_projects', $result);
    }

    /**
     * @test
     */
    public function permission_slug_works_with_different_actions()
    {
        $this->assertEquals('create_tasks', permission_slug('create', 'tasks'));
        $this->assertEquals('edit_budgets', permission_slug('edit', 'budgets'));
        $this->assertEquals('delete_users', permission_slug('delete', 'users'));
        $this->assertEquals('approve_documents', permission_slug('approve', 'documents'));
    }
}
