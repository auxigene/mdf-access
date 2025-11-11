<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Task;
use App\Models\Project;
use App\Models\Organization;
use App\Models\ProjectOrganization;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TaskPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TaskPolicy();
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
        $permission = Permission::factory()->create(['slug' => 'view_tasks']);

        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Associer l'organisation au projet
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->view($user, $task);

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
        $permission = Permission::factory()->create(['slug' => 'view_tasks']);

        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Ne PAS associer l'organisation au projet

        $result = $this->policy->view($user, $task);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function view_returns_false_when_user_does_not_have_permission()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);

        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Associer l'organisation au projet
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->view($user, $task);

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
        $permission = Permission::factory()->create(['slug' => 'edit_tasks']);

        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Associer l'organisation au projet
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->update($user, $task);

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
        $permission = Permission::factory()->create(['slug' => 'edit_tasks']);

        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Ne PAS associer l'organisation au projet

        $result = $this->policy->update($user, $task);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function delete_returns_true_when_user_has_permission_and_access_to_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'delete_tasks']);

        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Associer l'organisation au projet
        ProjectOrganization::factory()->create([
            'project_id' => $project->id,
            'organization_id' => $organization->id,
            'role' => 'moe'
        ]);

        $result = $this->policy->delete($user, $task);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function delete_returns_false_when_user_has_permission_but_no_access_to_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'delete_tasks']);

        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        // Ne PAS associer l'organisation au projet

        $result = $this->policy->delete($user, $task);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function system_admin_can_access_all_tasks_regardless_of_project()
    {
        $user = User::factory()->create(['is_system_admin' => true]);
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->assertTrue($this->policy->view($user, $task));
        $this->assertTrue($this->policy->update($user, $task));
        $this->assertTrue($this->policy->delete($user, $task));
    }

    /**
     * @test
     */
    public function view_returns_false_when_task_has_no_project()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'is_system_admin' => false,
            'organization_id' => $organization->id
        ]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_tasks']);

        $task = Task::factory()->create(['project_id' => null]);

        // Donner la permission
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $result = $this->policy->view($user, $task);

        $this->assertFalse($result);
    }
}
