<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckPermission;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CheckPermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_aborts_with_401_when_user_is_not_authenticated()
    {
        $middleware = new CheckPermission();
        $request = Request::create('/test', 'GET');

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Non authentifié');

        $middleware->handle($request, function ($req) {
            return response('Success');
        }, 'view_projects');
    }

    /**
     * @test
     */
    public function it_aborts_with_403_when_user_does_not_have_permission()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $this->actingAs($user);

        $middleware = new CheckPermission();
        $request = Request::create('/test', 'GET');

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage("Vous n'avez pas la permission 'view_projects' requise pour accéder à cette ressource.");

        $middleware->handle($request, function ($req) {
            return response('Success');
        }, 'view_projects');
    }

    /**
     * @test
     */
    public function it_allows_request_when_user_has_permission()
    {
        // Créer utilisateur avec permission
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'view_projects']);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $middleware = new CheckPermission();
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('Success', 200);
        }, 'view_projects');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    /**
     * @test
     */
    public function it_allows_request_when_user_is_system_admin()
    {
        // Les admins système ont toutes les permissions
        $user = User::factory()->create(['is_system_admin' => true]);
        $this->actingAs($user);

        $middleware = new CheckPermission();
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('Success', 200);
        }, 'view_projects');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    /**
     * @test
     */
    public function it_checks_correct_permission_slug()
    {
        $user = User::factory()->create(['is_system_admin' => false]);
        $role = Role::factory()->create();

        // Utilisateur a seulement 'view_projects', pas 'create_projects'
        $permission = Permission::factory()->create(['slug' => 'view_projects']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $middleware = new CheckPermission();
        $request = Request::create('/test', 'GET');

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        // Devrait échouer car l'utilisateur n'a pas 'create_projects'
        $middleware->handle($request, function ($req) {
            return response('Success');
        }, 'create_projects');
    }

    /**
     * @test
     */
    public function it_passes_request_through_middleware_chain()
    {
        $user = User::factory()->create(['is_system_admin' => true]);
        $this->actingAs($user);

        $middleware = new CheckPermission();
        $request = Request::create('/test', 'GET');

        $nextCalled = false;

        $middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;
            return response('Next middleware called', 200);
        }, 'view_projects');

        $this->assertTrue($nextCalled);
    }
}
