# Project-Level Permissions - Quick Start Guide

This guide provides a step-by-step implementation plan for Phase 1 (Foundation) of the project-level permissions refactoring.

**Note:** This guide focuses on implementing **Project-level** permissions as the first priority. The full system supports 7 scope levels:
1. **Task** - Most granular (task-specific assignments)
2. **WBS Element** - Work package/phase level
3. **Project** - Project-wide roles (FOCUS OF THIS GUIDE)
4. **Program** - Program-wide roles
5. **Portfolio** - Portfolio-wide roles
6. **Organization** - Organization-wide roles
7. **Global** - Platform-wide roles

After completing this guide for project-level permissions, you can extend to task and WBS element scopes using the same patterns.

---

## Prerequisites

- [ ] Read `PROJECT_LEVEL_PERMISSIONS_REFACTORING_PLAN.md`
- [ ] Review `PROJECT_LEVEL_PERMISSIONS_ARCHITECTURE_DIAGRAMS.md`
- [ ] Backup production database
- [ ] Create feature branch: `feature/project-level-permissions`
- [ ] Set up development environment

---

## Phase 1 Implementation Checklist

### Step 1: Database Schema (Day 1-2)

#### 1.1 Create `project_teams` Migration

```bash
php artisan make:migration create_project_teams_table
```

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_project_teams_table.php`

<details>
<summary>View Migration Code</summary>

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Project this team member belongs to');

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('User assigned to project team');

            $table->foreignId('role_id')
                ->constrained()
                ->comment('Role assigned to user in this project');

            $table->date('start_date')
                ->nullable()
                ->comment('Team member assignment start date');

            $table->date('end_date')
                ->nullable()
                ->comment('Team member assignment end date');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Whether this team assignment is currently active');

            $table->boolean('is_primary')
                ->default(false)
                ->comment('Primary project manager or coordinator');

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who made this assignment');

            $table->timestamp('assigned_at')
                ->nullable()
                ->comment('When this assignment was made');

            $table->text('notes')
                ->nullable()
                ->comment('Additional notes about this assignment');

            $table->timestamps();

            // Indexes
            $table->unique(['project_id', 'user_id', 'role_id'], 'unique_project_user_role');
            $table->index(['project_id', 'is_active'], 'idx_project_active');
            $table->index(['user_id', 'is_active'], 'idx_user_active');
            $table->index('role_id', 'idx_role');
            $table->index('start_date', 'idx_start_date');
            $table->index('end_date', 'idx_end_date');
        });

        DB::statement('ALTER TABLE project_teams COMMENT = "Project team member assignments with roles"');
    }

    public function down(): void
    {
        Schema::dropIfExists('project_teams');
    }
};
```
</details>

**Run:**
```bash
php artisan migrate
```

#### 1.2 Add Cache Fields to `users` Table

```bash
php artisan make:migration add_permissions_cache_to_users_table
```

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_permissions_cache_to_users_table.php`

<details>
<summary>View Migration Code</summary>

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('cached_permissions')
                ->nullable()
                ->after('remember_token')
                ->comment('Cached permission map for performance optimization');

            $table->timestamp('permissions_cached_at')
                ->nullable()
                ->after('cached_permissions')
                ->comment('Timestamp when permissions were last cached');

            $table->index('permissions_cached_at', 'idx_permissions_cached_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_permissions_cached_at');
            $table->dropColumn(['cached_permissions', 'permissions_cached_at']);
        });
    }
};
```
</details>

**Run:**
```bash
php artisan migrate
```

#### 1.3 Add Scope Context to `role_permission` Table (Optional for Phase 1)

This can be deferred to Phase 2 if needed, as it's for advanced permission inheritance.

---

### Step 2: Create Models (Day 3-4)

#### 2.1 Create `ProjectTeam` Model

```bash
php artisan make:model ProjectTeam
```

**File:** `app/Models/ProjectTeam.php`

<details>
<summary>View Model Code</summary>

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'role_id',
        'start_date',
        'end_date',
        'is_active',
        'is_primary',
        'assigned_by',
        'assigned_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    protected $with = ['user', 'role', 'project'];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeForProject(Builder $query, int|Project $project): Builder
    {
        $projectId = $project instanceof Project ? $project->id : $project;
        return $query->where('project_id', $projectId);
    }

    public function scopeForUser(Builder $query, int|User $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $query->where('user_id', $userId);
    }

    public function scopeWithRole(Builder $query, string $roleSlug): Builder
    {
        return $query->whereHas('role', function (Builder $q) use ($roleSlug) {
            $q->where('slug', $roleSlug);
        });
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $today = Carbon::today();
        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            });
    }

    // ============================================================
    // VALIDATION METHODS
    // ============================================================

    /**
     * Validate that the user's organization participates in the project
     */
    public function validateOrganizationParticipation(): bool
    {
        if (!$this->user || !$this->project) {
            return false;
        }

        $participates = DB::table('project_organizations')
            ->where('project_id', $this->project_id)
            ->where('organization_id', $this->user->organization_id)
            ->where('status', 'active')
            ->exists();

        return $participates;
    }

    /**
     * Validate that the role scope is appropriate for project assignment
     */
    public function validateRoleScope(): bool
    {
        if (!$this->role) {
            return false;
        }

        // Only project and organization scoped roles can be assigned to projects
        return in_array($this->role->scope, ['project', 'organization']);
    }

    /**
     * Validate that the role's organization matches user's organization (if role is org-specific)
     */
    public function validateRoleOrganization(): bool
    {
        if (!$this->role || !$this->user) {
            return false;
        }

        // If role has no organization_id, it's available to all orgs
        if (is_null($this->role->organization_id)) {
            return true;
        }

        // Otherwise, role's org must match user's org
        return $this->role->organization_id === $this->user->organization_id;
    }

    /**
     * Validate that dates are within project bounds
     */
    public function validateDates(): bool
    {
        if (!$this->project) {
            return true; // Can't validate without project
        }

        // Start date must be after project start
        if ($this->start_date && $this->project->start_date) {
            if ($this->start_date->lt($this->project->start_date)) {
                return false;
            }
        }

        // End date must be before project end
        if ($this->end_date && $this->project->end_date) {
            if ($this->end_date->gt($this->project->end_date)) {
                return false;
            }
        }

        // End date must be after start date
        if ($this->start_date && $this->end_date) {
            if ($this->end_date->lt($this->start_date)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that there's only one primary PM per project
     */
    public function validatePrimaryUniqueness(): bool
    {
        if (!$this->is_primary) {
            return true; // Not primary, no constraint
        }

        $existingPrimary = static::query()
            ->where('project_id', $this->project_id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->when($this->exists, function (Builder $query) {
                // Exclude self if updating
                return $query->where('id', '!=', $this->id);
            })
            ->exists();

        return !$existingPrimary;
    }

    /**
     * Run all validations
     */
    public function validateTeamMembership(): bool
    {
        return $this->validateOrganizationParticipation()
            && $this->validateRoleScope()
            && $this->validateRoleOrganization()
            && $this->validateDates()
            && $this->validatePrimaryUniqueness();
    }

    // ============================================================
    // BUSINESS LOGIC METHODS
    // ============================================================

    /**
     * Assign a user to a project with a specific role
     */
    public static function assignUserToProject(
        Project $project,
        User $user,
        Role $role,
        array $options = []
    ): self {
        $teamMember = new static([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'start_date' => $options['start_date'] ?? null,
            'end_date' => $options['end_date'] ?? null,
            'is_active' => $options['is_active'] ?? true,
            'is_primary' => $options['is_primary'] ?? false,
            'assigned_by' => $options['assigned_by'] ?? auth()->id(),
            'assigned_at' => now(),
            'notes' => $options['notes'] ?? null,
        ]);

        // Validate before saving
        if (!$teamMember->validateTeamMembership()) {
            throw new \InvalidArgumentException('Invalid team membership configuration');
        }

        $teamMember->save();

        // Clear user's permission cache
        $user->clearPermissionsCache();

        return $teamMember;
    }

    /**
     * Remove a user from a project (optionally for a specific role)
     */
    public static function removeUserFromProject(
        Project $project,
        User $user,
        ?Role $role = null
    ): bool {
        $query = static::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id);

        if ($role) {
            $query->where('role_id', $role->id);
        }

        $deleted = $query->delete();

        if ($deleted) {
            // Clear user's permission cache
            $user->clearPermissionsCache();
        }

        return $deleted > 0;
    }

    /**
     * Activate this team membership
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
        $this->user->clearPermissionsCache();
    }

    /**
     * Deactivate this team membership
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
        $this->user->clearPermissionsCache();
    }

    /**
     * Check if this team member is currently active (considering dates)
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->start_date && $this->start_date->gt($today)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($today)) {
            return false;
        }

        return true;
    }

    /**
     * Check if this is a primary assignment
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    // ============================================================
    // QUERY HELPERS
    // ============================================================

    /**
     * Get all active team members for a project
     */
    public static function getActiveTeamMembers(Project $project): \Illuminate\Support\Collection
    {
        return static::query()
            ->forProject($project)
            ->currentlyActive()
            ->with(['user', 'role'])
            ->get();
    }

    /**
     * Get all projects a user is assigned to (optionally filtered by role)
     */
    public static function getUserProjects(User $user, ?string $roleSlug = null): \Illuminate\Support\Collection
    {
        $query = static::query()
            ->forUser($user)
            ->currentlyActive()
            ->with(['project', 'role']);

        if ($roleSlug) {
            $query->withRole($roleSlug);
        }

        return $query->get()->pluck('project');
    }

    /**
     * Check if a user has a specific role in a project
     */
    public static function userHasRoleInProject(User $user, Project $project, string $roleSlug): bool
    {
        return static::query()
            ->forUser($user)
            ->forProject($project)
            ->withRole($roleSlug)
            ->currentlyActive()
            ->exists();
    }
}
```
</details>

**Test the model:**
```bash
php artisan tinker
>>> $project = App\Models\Project::first();
>>> $user = App\Models\User::first();
>>> $role = App\Models\Role::where('slug', 'project_manager')->first();
>>> App\Models\ProjectTeam::assignUserToProject($project, $user, $role);
```

---

### Step 3: Enhance Existing Models (Day 5-6)

#### 3.1 Update `User` Model

**File:** `app/Models/User.php`

Add these methods to the existing User model:

<details>
<summary>View Code to Add</summary>

```php
// Add to the top of the class, in the $casts array
protected $casts = [
    // ... existing casts
    'cached_permissions' => 'array',
    'permissions_cached_at' => 'datetime',
];

// ============================================================
// PROJECT TEAM METHODS (Add these to existing User model)
// ============================================================

/**
 * Get user's project team memberships
 */
public function projectTeams(): HasMany
{
    return $this->hasMany(ProjectTeam::class);
}

/**
 * Get user's active project team memberships
 */
public function activeProjectTeams(): HasMany
{
    return $this->projectTeams()->currentlyActive();
}

/**
 * Check if user is a member of a project team
 */
public function isProjectTeamMember(Project $project): bool
{
    return $this->projectTeams()
        ->forProject($project)
        ->currentlyActive()
        ->exists();
}

/**
 * Get user's role in a project team (returns the highest privilege role if multiple)
 */
public function getProjectTeamRole(Project $project): ?Role
{
    $teamMember = $this->projectTeams()
        ->forProject($project)
        ->currentlyActive()
        ->with('role')
        ->first();

    return $teamMember?->role;
}

/**
 * Get all projects user is a team member of
 */
public function getTeamProjects(): \Illuminate\Support\Collection
{
    return ProjectTeam::getUserProjects($this);
}

/**
 * Cache user permissions for performance
 */
public function cachePermissions(): void
{
    $permissions = [
        'global' => [],
        'computed_at' => now()->toIso8601String(),
        'roles' => [
            'global' => [],
            'organization' => [],
            'projects' => [],
        ],
    ];

    // Get all user roles (global, organization, project-scoped)
    $userRoles = $this->roles()->with('permissions')->get();

    foreach ($userRoles as $userRole) {
        $rolePermissions = $userRole->role->permissions->pluck('slug')->toArray();

        // Determine scope
        $pivot = $userRole->pivot;
        if ($pivot->project_id) {
            $key = "project_{$pivot->project_id}";
            $permissions[$key] = array_merge($permissions[$key] ?? [], $rolePermissions);
            $permissions['roles']['projects'][$pivot->project_id] = $userRole->role->slug;
        } elseif ($pivot->program_id) {
            $key = "program_{$pivot->program_id}";
            $permissions[$key] = array_merge($permissions[$key] ?? [], $rolePermissions);
        } elseif ($pivot->portfolio_id) {
            $key = "portfolio_{$pivot->portfolio_id}";
            $permissions[$key] = array_merge($permissions[$key] ?? [], $rolePermissions);
        } elseif ($this->organization_id) {
            $key = "organization_{$this->organization_id}";
            $permissions[$key] = array_merge($permissions[$key] ?? [], $rolePermissions);
            $permissions['roles']['organization'][] = $userRole->role->slug;
        } else {
            $permissions['global'] = array_merge($permissions['global'], $rolePermissions);
            $permissions['roles']['global'][] = $userRole->role->slug;
        }
    }

    // Deduplicate permissions
    foreach ($permissions as $key => $value) {
        if (is_array($value) && $key !== 'roles') {
            $permissions[$key] = array_unique($value);
        }
    }

    $this->update([
        'cached_permissions' => $permissions,
        'permissions_cached_at' => now(),
    ]);
}

/**
 * Clear user's permission cache
 */
public function clearPermissionsCache(): void
{
    $this->update([
        'cached_permissions' => null,
        'permissions_cached_at' => null,
    ]);
}

/**
 * Get cached permissions (returns null if stale or missing)
 */
public function getCachedPermissions(string $context = 'global'): ?array
{
    // Check if cache exists and is fresh (15 minutes TTL)
    if (!$this->cached_permissions || !$this->permissions_cached_at) {
        return null;
    }

    if ($this->permissions_cached_at->lt(now()->subMinutes(15))) {
        return null; // Cache is stale
    }

    return $this->cached_permissions[$context] ?? null;
}

/**
 * Check if user has permission in context (with caching)
 */
public function hasPermissionInContext(
    string $permissionSlug,
    ?Model $context = null,
    bool $checkHierarchy = true
): bool {
    // System admin bypass
    if ($this->is_system_admin) {
        return true;
    }

    // Try cache first
    if ($context instanceof Project) {
        $cached = $this->getCachedPermissions("project_{$context->id}");
        if ($cached !== null) {
            return in_array($permissionSlug, $cached);
        }
    }

    // Fallback to existing hasPermission method
    return $this->hasPermission($permissionSlug, $context);
}
```
</details>

#### 3.2 Update `Project` Model

**File:** `app/Models/Project.php`

Add these methods:

<details>
<summary>View Code to Add</summary>

```php
// ============================================================
// PROJECT TEAM RELATIONSHIPS (Add to existing Project model)
// ============================================================

/**
 * Get project team memberships
 */
public function projectTeams(): HasMany
{
    return $this->hasMany(ProjectTeam::class);
}

/**
 * Get active project team memberships
 */
public function activeProjectTeams(): HasMany
{
    return $this->projectTeams()->currentlyActive();
}

/**
 * Get team members (users) through project_teams
 */
public function teamMembers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'project_teams')
        ->withPivot(['role_id', 'start_date', 'end_date', 'is_active', 'is_primary', 'notes'])
        ->withTimestamps();
}

/**
 * Get active team members only
 */
public function activeTeamMembers(): BelongsToMany
{
    return $this->teamMembers()
        ->wherePivot('is_active', true)
        ->wherePivot(function ($query) {
            $today = now()->toDateString();
            $query->where(function ($q) use ($today) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            });
        });
}

// ============================================================
// PROJECT TEAM METHODS
// ============================================================

/**
 * Get team members filtered by role
 */
public function getTeamMembersByRole(string $roleSlug): \Illuminate\Support\Collection
{
    return $this->activeProjectTeams()
        ->withRole($roleSlug)
        ->with('user')
        ->get()
        ->pluck('user');
}

/**
 * Get the primary project manager
 */
public function getPrimaryProjectManager(): ?User
{
    $teamMember = $this->activeProjectTeams()
        ->primary()
        ->with('user')
        ->first();

    return $teamMember?->user;
}

/**
 * Add a team member to the project
 */
public function addTeamMember(User $user, Role $role, array $options = []): ProjectTeam
{
    return ProjectTeam::assignUserToProject($this, $user, $role, $options);
}

/**
 * Remove a team member from the project
 */
public function removeTeamMember(User $user, ?Role $role = null): bool
{
    return ProjectTeam::removeUserFromProject($this, $user, $role);
}

/**
 * Check if a user is a team member
 */
public function hasTeamMember(User $user): bool
{
    return $user->isProjectTeamMember($this);
}

/**
 * Check if user can access this project (considering team membership and org participation)
 */
public function canUserAccessProject(User $user): bool
{
    // System admin can access everything
    if ($user->is_system_admin) {
        return true;
    }

    // Check if user is a team member
    if ($this->hasTeamMember($user)) {
        return true;
    }

    // Check if user's organization participates in the project
    $participates = $this->organizations()
        ->where('organization_id', $user->organization_id)
        ->where('status', 'active')
        ->exists();

    return $participates;
}
```
</details>

---

### Step 4: Write Unit Tests (Day 7-8)

#### 4.1 Create ProjectTeam Test

```bash
php artisan make:test ProjectTeamTest --unit
```

**File:** `tests/Unit/ProjectTeamTest.php`

<details>
<summary>View Test Code</summary>

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Role;
use App\Models\Organization;
use App\Models\ProjectTeam;
use App\Models\ProjectOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTeamTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;
    private Role $projectRole;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organization
        $this->organization = Organization::factory()->create();

        // Create user
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create project
        $this->project = Project::factory()->create([
            'client_organization_id' => $this->organization->id,
        ]);

        // Create project organization participation
        ProjectOrganization::create([
            'project_id' => $this->project->id,
            'organization_id' => $this->organization->id,
            'role' => 'moe',
            'status' => 'active',
        ]);

        // Create project-scoped role
        $this->projectRole = Role::factory()->create([
            'slug' => 'project_manager',
            'scope' => 'project',
        ]);
    }

    /** @test */
    public function it_can_assign_user_to_project()
    {
        $teamMember = ProjectTeam::assignUserToProject(
            $this->project,
            $this->user,
            $this->projectRole
        );

        $this->assertDatabaseHas('project_teams', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'role_id' => $this->projectRole->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_validates_organization_participation()
    {
        $otherOrg = Organization::factory()->create();
        $otherUser = User::factory()->create(['organization_id' => $otherOrg->id]);

        $this->expectException(\InvalidArgumentException::class);

        ProjectTeam::assignUserToProject(
            $this->project,
            $otherUser,
            $this->projectRole
        );
    }

    /** @test */
    public function it_validates_role_scope()
    {
        $globalRole = Role::factory()->create(['scope' => 'global']);

        $teamMember = new ProjectTeam([
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'role_id' => $globalRole->id,
        ]);

        $this->assertFalse($teamMember->validateRoleScope());
    }

    /** @test */
    public function it_enforces_unique_primary_project_manager()
    {
        // First PM
        ProjectTeam::assignUserToProject(
            $this->project,
            $this->user,
            $this->projectRole,
            ['is_primary' => true]
        );

        // Second PM attempt
        $anotherUser = User::factory()->create(['organization_id' => $this->organization->id]);

        $secondPM = new ProjectTeam([
            'project_id' => $this->project->id,
            'user_id' => $anotherUser->id,
            'role_id' => $this->projectRole->id,
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->assertFalse($secondPM->validatePrimaryUniqueness());
    }

    /** @test */
    public function it_clears_user_cache_on_assignment()
    {
        // Cache permissions first
        $this->user->cachePermissions();
        $this->assertNotNull($this->user->fresh()->cached_permissions);

        // Assign to team
        ProjectTeam::assignUserToProject(
            $this->project,
            $this->user,
            $this->projectRole
        );

        // Cache should be cleared
        $this->assertNull($this->user->fresh()->cached_permissions);
    }

    /** @test */
    public function it_can_remove_user_from_project()
    {
        // Assign
        ProjectTeam::assignUserToProject(
            $this->project,
            $this->user,
            $this->projectRole
        );

        // Remove
        $removed = ProjectTeam::removeUserFromProject($this->project, $this->user);

        $this->assertTrue($removed);
        $this->assertDatabaseMissing('project_teams', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_get_active_team_members()
    {
        ProjectTeam::assignUserToProject($this->project, $this->user, $this->projectRole);

        $members = ProjectTeam::getActiveTeamMembers($this->project);

        $this->assertCount(1, $members);
        $this->assertTrue($members->first()->user->is($this->user));
    }

    /** @test */
    public function it_can_get_user_projects()
    {
        ProjectTeam::assignUserToProject($this->project, $this->user, $this->projectRole);

        $projects = ProjectTeam::getUserProjects($this->user);

        $this->assertCount(1, $projects);
        $this->assertTrue($projects->first()->is($this->project));
    }
}
```
</details>

**Run tests:**
```bash
php artisan test --filter=ProjectTeamTest
```

---

### Step 5: Seed Project Roles (Day 9)

#### 5.1 Create Project Roles Seeder

```bash
php artisan make:seeder ProjectRolesSeeder
```

**File:** `database/seeders/ProjectRolesSeeder.php`

<details>
<summary>View Seeder Code</summary>

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class ProjectRolesSeeder extends Seeder
{
    public function run(): void
    {
        $projectRoles = [
            [
                'name' => 'Project Manager',
                'slug' => 'project_manager',
                'scope' => 'project',
                'description' => 'Manages all aspects of project execution, team, and deliverables',
                'permissions' => [
                    'view_projects', 'edit_projects',
                    'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
                    'view_deliverables', 'create_deliverables', 'edit_deliverables', 'approve_deliverables',
                    'view_budgets', 'edit_budgets',
                    'view_risks', 'create_risks', 'edit_risks',
                    'view_issues', 'create_issues', 'edit_issues',
                    'view_reports', 'export_reports',
                ],
            ],
            [
                'name' => 'Technical Lead',
                'slug' => 'technical_lead',
                'scope' => 'project',
                'description' => 'Provides technical oversight and makes architecture decisions',
                'permissions' => [
                    'view_projects',
                    'view_tasks', 'edit_tasks',
                    'view_deliverables', 'edit_deliverables',
                    'view_risks', 'create_risks',
                    'view_issues', 'create_issues',
                ],
            ],
            [
                'name' => 'Project Technician',
                'slug' => 'project_technician',
                'scope' => 'project',
                'description' => 'Executes assigned tasks and creates deliverables',
                'permissions' => [
                    'view_projects',
                    'view_tasks', 'edit_tasks',
                    'create_deliverables', 'view_deliverables',
                    'create_issues', 'view_issues',
                ],
            ],
            [
                'name' => 'Project Observer',
                'slug' => 'project_observer',
                'scope' => 'project',
                'description' => 'Read-only access to project information',
                'permissions' => [
                    'view_projects',
                    'view_tasks',
                    'view_deliverables',
                    'view_budgets',
                    'view_reports',
                ],
            ],
            [
                'name' => 'Budget Controller',
                'slug' => 'budget_controller',
                'scope' => 'project',
                'description' => 'Manages project budget and financial tracking',
                'permissions' => [
                    'view_projects',
                    'view_budgets', 'edit_budgets',
                    'view_expenses', 'approve_expenses',
                    'view_invoices',
                    'export_reports',
                ],
            ],
            [
                'name' => 'Quality Manager',
                'slug' => 'quality_manager',
                'scope' => 'project',
                'description' => 'Ensures quality standards and provides approval authority',
                'permissions' => [
                    'view_projects',
                    'view_deliverables', 'approve_deliverables',
                    'view_risks', 'create_risks',
                    'view_issues', 'create_issues',
                    'view_reports',
                ],
            ],
        ];

        foreach ($projectRoles as $roleData) {
            $permissionSlugs = $roleData['permissions'];
            unset($roleData['permissions']);

            // Create role
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Attach permissions
            $permissions = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
            $role->permissions()->sync($permissions);

            $this->command->info("Created/updated project role: {$role->name}");
        }
    }
}
```
</details>

**Run seeder:**
```bash
php artisan db:seed --class=ProjectRolesSeeder
```

---

### Step 6: Manual Testing (Day 10)

Create a test script to verify everything works:

**File:** `tests/manual/test_project_teams.php`

```bash
php artisan tinker
```

```php
// 1. Find a project
$project = App\Models\Project::first();

// 2. Find a user in a participating organization
$org = $project->organizations->first();
$user = App\Models\User::where('organization_id', $org->id)->first();

// 3. Find project manager role
$pmRole = App\Models\Role::where('slug', 'project_manager')->first();

// 4. Assign user to project
$team = App\Models\ProjectTeam::assignUserToProject($project, $user, $pmRole, [
    'is_primary' => true,
    'notes' => 'Initial project manager assignment'
]);

// 5. Verify assignment
dd([
    'team_member' => $team,
    'is_active' => $team->isCurrentlyActive(),
    'user_is_member' => $user->isProjectTeamMember($project),
    'project_has_member' => $project->hasTeamMember($user),
    'user_role' => $user->getProjectTeamRole($project)->name,
]);

// 6. List all team members
$project->activeProjectTeams->each(function ($team) {
    echo "{$team->user->name} - {$team->role->name}\n";
});
```

---

## Verification Checklist

- [ ] Migrations run successfully without errors
- [ ] `project_teams` table created with correct schema
- [ ] `users` table has cache columns
- [ ] `ProjectTeam` model exists and has all methods
- [ ] `User` model enhanced with team methods
- [ ] `Project` model enhanced with team methods
- [ ] All unit tests pass
- [ ] Project roles seeded correctly
- [ ] Can assign users to projects via Tinker
- [ ] Can query team members successfully
- [ ] Permission cache invalidates on role changes

---

## Next Steps (Phase 2)

Once Phase 1 is complete and stable:

1. **Create Service Layer** (`PermissionResolver`, `ProjectAccessManager`)
2. **Enhance Policies** to use project context
3. **Create API endpoints** for team management
4. **Build Vue.js UI** for project team management

---

## Troubleshooting

### Common Issues

**Issue:** Migration fails with foreign key constraint error
- **Solution:** Ensure parent tables (`projects`, `users`, `roles`) exist and have data

**Issue:** `validateOrganizationParticipation()` always returns false
- **Solution:** Check that `project_organizations` table has active records for the project

**Issue:** Tests fail with "Class ProjectTeam not found"
- **Solution:** Run `composer dump-autoload`

**Issue:** Permission cache not clearing
- **Solution:** Verify `clearPermissionsCache()` method is called after team changes

---

## Extending to Task and WBS Element Scopes

After successfully implementing project-level permissions, you can extend the system to support task and WBS element scopes using the same patterns.

### Task-Level Permissions

**When to implement:** When you need fine-grained control over individual task assignments.

**Use cases:**
- Assign specific tasks to external contractors without giving project-wide access
- Implement task ownership where only the owner can edit/complete their task
- Enable task-specific reviewers for quality control workflows

**Implementation steps:**
1. Add `task_id` foreign key to `user_roles` table (already in main plan)
2. Update `roles.scope` enum to include 'task' (already in main plan)
3. Create task-scoped roles: `task_owner`, `task_assignee`, `task_reviewer`
4. Update `PermissionResolver` to check task-level roles first
5. Create `TaskTeam` model (similar to `ProjectTeam`) or extend `user_roles` pivot directly

**Example:**
```php
// Assign user as task owner
$task = Task::find(456);
$taskOwnerRole = Role::where('slug', 'task_owner')->where('scope', 'task')->first();

DB::table('user_roles')->insert([
    'user_id' => $user->id,
    'role_id' => $taskOwnerRole->id,
    'task_id' => $task->id,
]);

// Check permission
$user->hasPermissionInContext('edit_tasks', $task); // Checks task-level first
```

### WBS Element-Level Permissions

**When to implement:** When you need to delegate management of specific work packages or project phases.

**Use cases:**
- Assign work package managers who control a group of tasks
- Give subcontractors access only to their contracted scope (WBS element)
- Implement phase-specific permissions (e.g., "Testing Phase Manager")

**Implementation steps:**
1. Add `wbs_element_id` foreign key to `user_roles` table (already in main plan)
2. Update `roles.scope` enum to include 'wbs_element' (already in main plan)
3. Create WBS-scoped roles: `work_package_manager`, `phase_lead`, `deliverable_owner`
4. Update `PermissionResolver` to check WBS-level roles after task-level
5. Implement `WbsTeam` model or extend pivot table

**Example:**
```php
// Assign user as work package manager
$wbsElement = WbsElement::find(45); // "Phase 2: Development"
$wpmRole = Role::where('slug', 'work_package_manager')->where('scope', 'wbs_element')->first();

DB::table('user_roles')->insert([
    'user_id' => $user->id,
    'role_id' => $wpmRole->id,
    'wbs_element_id' => $wbsElement->id,
]);

// Check permission - user can edit all tasks under this WBS element
$task = Task::where('wbs_element_id', 45)->first();
$user->hasPermissionInContext('edit_tasks', $task); // Checks WBS-level (task's parent)
```

### Permission Resolution with All Scopes

With all scopes implemented, the resolution order becomes:

```
1. System Admin? → GRANT
2. Task-level role? → GRANT if found
3. WBS Element-level role? → GRANT if found
4. Project-level role? → GRANT if found
5. Program-level role? → GRANT if found
6. Portfolio-level role? → GRANT if found
7. Organization-level role? → GRANT if found
8. Global role? → GRANT if found
9. Otherwise → DENY
```

**Context Resolution:**
```php
// Given a task, resolve hierarchy
$task = Task::find(123);

// Hierarchy: Task → WBS Element → Project → Program → Portfolio
$contexts = [
    $task,                           // Task #123
    $task->wbsElement,              // WBS Element #45 (parent)
    $task->wbsElement->project,     // Project #12 (grandparent)
    $task->project->program,        // Program #3 (great-grandparent)
    $task->project->program->portfolio, // Portfolio #1 (great-great-grandparent)
];

// Check user's roles at each level
foreach ($contexts as $context) {
    if ($user->hasRoleInContext($context)) {
        return GRANT;
    }
}
```

### Recommended Implementation Order

1. **Phase 1:** Project-level permissions (THIS GUIDE) ✅
2. **Phase 2:** Services, Policies, API for project-level
3. **Phase 3:** Frontend UI for project team management
4. **Phase 4:** WBS Element-level permissions (optional, as needed)
5. **Phase 5:** Task-level permissions (optional, for high-security environments)

**Note:** Task and WBS element scopes add complexity. Only implement them if you have clear use cases. Project-level permissions alone cover 80% of typical needs.

---

## Support

For questions or issues during implementation:
- Review `PROJECT_LEVEL_PERMISSIONS_REFACTORING_PLAN.md`
- Check `PROJECT_LEVEL_PERMISSIONS_ARCHITECTURE_DIAGRAMS.md`
- Consult existing documentation in `docs/`

---

**Document Version:** 1.0
**Phase:** 1 (Foundation)
**Estimated Time:** 10 days
**Status:** Ready for Implementation
