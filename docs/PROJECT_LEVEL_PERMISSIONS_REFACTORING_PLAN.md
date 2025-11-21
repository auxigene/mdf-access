# Project-Level Permissions Refactoring Plan

## Executive Summary

This document outlines a comprehensive plan to refactor the MDF Access platform's permission system to support **hierarchical, project-aware role-based access control (RBAC)**. The goal is to enable users to have different roles and permissions across different projects (e.g., Project Manager on Project A, Technician on Project B).

**Key Finding:** The database infrastructure for project-level permissions **already exists** but is not fully implemented. This refactoring focuses on activating and integrating the existing schema.

---

## Current State Analysis

### ✅ What Already Exists

1. **`user_roles` table with scope fields** (`project_id`, `program_id`, `portfolio_id`)
   - Location: `database/migrations/2025_11_07_230059_create_user_roles_table.php`
   - Supports assigning roles at project, program, or portfolio level

2. **`roles.scope` enum** ('global', 'organization', 'project')
   - Location: `database/migrations/2025_11_07_230035_create_roles_table.php`
   - Defines role hierarchy level

3. **`User::hasPermission()` with scope support**
   - Location: `app/Models/User.php:221-263`
   - Accepts optional `$scope` parameter (Model instance)
   - Not consistently used across the application

4. **Contextual organization roles per project**
   - `project_organizations` table defines MOA, MOE, sponsor, subcontractor roles
   - Location: `database/migrations/2025_11_08_090816_create_project_organizations_table.php`

5. **Tenant scoping via RLS**
   - `TenantScope` filters projects by organization participation
   - Location: `app/Scopes/TenantScope.php`

### ❌ What's Missing

1. **No hierarchical permission resolution**
   - Current: Permission check is binary (has or doesn't have)
   - Needed: Check global → organization → project hierarchy

2. **No project team management**
   - No API/UI for assigning users to projects with specific roles
   - No concept of "project team members"

3. **Policies don't leverage project-scoped roles**
   - `ProjectPolicy`, `BudgetPolicy`, etc. don't check project-specific user roles
   - Location: `app/Policies/`

4. **Middleware doesn't support context**
   - `CheckPermission` middleware checks global permissions only
   - No route-level project context

5. **No permission inheritance rules**
   - Unclear how project permissions inherit from program/portfolio
   - No defined precedence rules

6. **Missing role assignment workflows**
   - No validation for role scope compatibility
   - No automatic role suggestions based on organization context

---

## Architecture Design

### Permission Resolution Hierarchy

```
SYSTEM ADMIN (bypass all)
    ↓
GLOBAL ROLE (platform-wide)
    ↓
ORGANIZATION ROLE (within tenant)
    ↓
PORTFOLIO ROLE (portfolio-specific)
    ↓
PROGRAM ROLE (program-specific)
    ↓
PROJECT ROLE (project-specific)
    ↓
WBS_ELEMENT ROLE (WBS element-specific)
    ↓
TASK ROLE (most granular - task-specific)
```

**Scope Levels (in order of precedence - most specific to least specific):**
1. **Task** - Assignment to specific task (e.g., task owner, task reviewer)
2. **WBS Element** - Assignment to work breakdown structure element (e.g., work package manager)
3. **Project** - Assignment to project (e.g., project manager, technical lead)
4. **Program** - Assignment to program (e.g., program manager)
5. **Portfolio** - Assignment to portfolio (e.g., portfolio director)
6. **Organization** - Organization-wide role (e.g., department head)
7. **Global** - Platform-wide role (e.g., PMO director, system architect)

**Resolution Logic:**
1. If user is system admin → **GRANT**
2. Check task-level role permissions (if context is a task) → **GRANT if found**
3. Check WBS element-level role permissions (if context is WBS or task has WBS parent) → **GRANT if found**
4. Check project-level role permissions → **GRANT if found**
5. Check program-level role permissions (if project has program) → **GRANT if found**
6. Check portfolio-level role permissions (if project has portfolio via program) → **GRANT if found**
7. Check organization-level role permissions → **GRANT if found**
8. Check global role permissions → **GRANT if found**
9. Otherwise → **DENY**

**Why Task and WBS Element Scopes?**
- **Task-level permissions** enable fine-grained control for task assignment (e.g., Alice owns Task #123, Bob reviews Task #456)
- **WBS element permissions** support work package management where a user manages a specific deliverable or phase
- **Supports PMBOK best practices** where work is hierarchically decomposed and assigned at different levels

### Project Team Membership

New concept: **Project Team** = Users assigned to a project with specific roles

```
project_teams (NEW TABLE)
├── id
├── project_id (FK → projects)
├── user_id (FK → users)
├── role_id (FK → roles WHERE scope IN ['project', 'organization'])
├── start_date
├── end_date
├── is_active
├── assigned_by (FK → users)
├── assigned_at
└── notes
```

**Business Rules:**
- Users can only be assigned to projects their organization participates in
- Role scope must be 'project' or 'organization' (not 'global')
- Role organization_id must match user's organization or be NULL
- Dates must be within project duration
- One primary Project Manager per project (enforced)

---

## Refactoring Plan: 8 Phases

### Phase 1: Database Schema Enhancements

#### 1.1 Create `project_teams` table
**File:** `database/migrations/YYYY_MM_DD_create_project_teams_table.php`

```php
Schema::create('project_teams', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('role_id')->constrained();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('is_primary')->default(false); // Primary PM/Coordinator
    $table->foreignId('assigned_by')->nullable()->constrained('users');
    $table->timestamp('assigned_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    // Indexes
    $table->unique(['project_id', 'user_id', 'role_id'], 'unique_project_user_role');
    $table->index(['project_id', 'is_active']);
    $table->index(['user_id', 'is_active']);
});
```

#### 1.2 Update `user_roles` table to support WBS and Task scopes
**File:** `database/migrations/YYYY_MM_DD_add_wbs_task_scope_to_user_roles.php`

```php
Schema::table('user_roles', function (Blueprint $table) {
    $table->foreignId('wbs_element_id')
        ->nullable()
        ->after('project_id')
        ->constrained('wbs_elements')
        ->onDelete('cascade')
        ->comment('Optional WBS element scope for this role assignment');

    $table->foreignId('task_id')
        ->nullable()
        ->after('wbs_element_id')
        ->constrained('tasks')
        ->onDelete('cascade')
        ->comment('Optional task scope for this role assignment');

    // Update unique constraint to include new scope fields
    $table->dropUnique('user_roles_unique');
    $table->unique(
        ['user_id', 'role_id', 'portfolio_id', 'program_id', 'project_id', 'wbs_element_id', 'task_id'],
        'user_roles_unique_extended'
    );

    // Add check constraint to ensure only ONE scope is set
    DB::statement('ALTER TABLE user_roles ADD CONSTRAINT check_single_scope
        CHECK (
            (portfolio_id IS NOT NULL)::integer +
            (program_id IS NOT NULL)::integer +
            (project_id IS NOT NULL)::integer +
            (wbs_element_id IS NOT NULL)::integer +
            (task_id IS NOT NULL)::integer <= 1
        )');

    $table->index('wbs_element_id');
    $table->index('task_id');
});
```

#### 1.3 Update `roles` table scope enum
**File:** `database/migrations/YYYY_MM_DD_expand_role_scope_enum.php`

```php
DB::statement("ALTER TABLE roles MODIFY scope ENUM(
    'global',
    'organization',
    'portfolio',
    'program',
    'project',
    'wbs_element',
    'task'
) NOT NULL DEFAULT 'global'");
```

**Note:** For PostgreSQL, use:
```php
DB::statement("ALTER TABLE roles ALTER COLUMN scope TYPE VARCHAR(20)");
// Then add a check constraint
DB::statement("ALTER TABLE roles ADD CONSTRAINT roles_scope_check
    CHECK (scope IN ('global', 'organization', 'portfolio', 'program', 'project', 'wbs_element', 'task'))");
```

#### 1.4 Add scope context to `role_permission`
**File:** `database/migrations/YYYY_MM_DD_add_scope_to_role_permission.php`

```php
Schema::table('role_permission', function (Blueprint $table) {
    $table->enum('scope_level', [
        'global',
        'organization',
        'portfolio',
        'program',
        'project',
        'wbs_element',
        'task'
    ])
        ->default('global')
        ->after('permission_id');
    $table->text('scope_description')->nullable()->after('scope_level');

    $table->index('scope_level');
});
```

#### 1.5 Add cached permissions to `users` table (performance)
**File:** `database/migrations/YYYY_MM_DD_add_permissions_cache_to_users.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->json('cached_permissions')->nullable()->after('remember_token');
    $table->timestamp('permissions_cached_at')->nullable()->after('cached_permissions');
});
```

**Deliverables:**
- [ ] Migration files created
- [ ] Run migrations
- [ ] Update database diagram documentation

---

### Phase 2: Model Layer Refactoring

#### 2.1 Create `ProjectTeam` model
**File:** `app/Models/ProjectTeam.php`

**Key Methods:**
```php
// Validation
public function validateTeamMembership(): bool
public function validateOrganizationParticipation(): bool
public function validateRoleScope(): bool
public function validateDates(): bool

// Business logic
public static function assignUserToProject(Project $project, User $user, Role $role, array $options = []): self
public static function removeUserFromProject(Project $project, User $user, ?Role $role = null): bool
public function activate(): void
public function deactivate(): void

// Queries
public static function getActiveTeamMembers(Project $project): Collection
public static function getUserProjects(User $user, ?string $roleSlug = null): Collection
public function isPrimary(): bool
```

**Scopes:**
```php
public function scopeActive(Builder $query): Builder
public function scopeForProject(Builder $query, int $projectId): Builder
public function scopeForUser(Builder $query, int $userId): Builder
public function scopeWithRole(Builder $query, string $roleSlug): Builder
public function scopePrimary(Builder $query): Builder
```

#### 2.2 Enhance `User` model
**File:** `app/Models/User.php`

**New Methods:**
```php
/**
 * Hierarchical permission check with project context
 */
public function hasPermissionInContext(
    string $permissionSlug,
    ?Model $context = null,
    bool $checkHierarchy = true
): bool

/**
 * Get user's effective role for a project (highest privilege)
 */
public function getEffectiveRoleForProject(Project $project): ?Role

/**
 * Get all permissions for user in project context
 */
public function getProjectPermissions(Project $project): Collection

/**
 * Check if user is in project team
 */
public function isProjectTeamMember(Project $project): bool

/**
 * Get user's role in project team
 */
public function getProjectTeamRole(Project $project): ?Role

/**
 * Cache user permissions (performance optimization)
 */
public function cachePermissions(): void
public function clearPermissionsCache(): void
public function getCachedPermissions(string $context = 'global'): ?Collection
```

**Refactor Existing:**
```php
// BEFORE
public function hasPermission(string $permissionSlug, ?Model $scope = null): bool
{
    // Simple check without hierarchy
}

// AFTER
public function hasPermission(string $permissionSlug, ?Model $scope = null): bool
{
    return $this->hasPermissionInContext($permissionSlug, $scope, true);
}
```

#### 2.3 Enhance `Project` model
**File:** `app/Models/Project.php`

**New Relationships:**
```php
public function teamMembers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'project_teams')
        ->withPivot(['role_id', 'start_date', 'end_date', 'is_active', 'is_primary'])
        ->withTimestamps();
}

public function activeTeamMembers(): BelongsToMany
{
    return $this->teamMembers()->wherePivot('is_active', true);
}

public function projectTeams(): HasMany
{
    return $this->hasMany(ProjectTeam::class);
}
```

**New Methods:**
```php
public function getTeamMembersByRole(string $roleSlug): Collection
public function getPrimaryProjectManager(): ?User
public function addTeamMember(User $user, Role $role, array $options = []): ProjectTeam
public function removeTeamMember(User $user, ?Role $role = null): bool
public function hasTeamMember(User $user): bool
public function canUserAccessProject(User $user): bool
```

#### 2.4 Enhance `Role` model
**File:** `app/Models/Role.php`

**New Methods:**
```php
// Scope checking methods
public function isTaskScoped(): bool
public function isWbsElementScoped(): bool
public function isProjectScoped(): bool
public function isProgramScoped(): bool
public function isPortfolioScoped(): bool
public function isOrganizationScoped(): bool
public function isGlobalScoped(): bool

// Assignment validation
public function canBeAssignedToTask(): bool
public function canBeAssignedToWbsElement(): bool
public function canBeAssignedToProject(): bool
public function canBeAssignedToUser(User $user): bool

// Query helpers
public static function getTaskRoles(): Collection
public static function getWbsElementRoles(): Collection
public static function getProjectRoles(?int $organizationId = null): Collection
public static function getScopedRoles(string $scope, ?int $organizationId = null): Collection

// Scope level comparison
public function getScopeLevel(): int // Returns numeric level (1=task, 2=wbs, ..., 7=global)
public function isMoreSpecificThan(Role $other): bool
public function isLessSpecificThan(Role $other): bool
```

**Deliverables:**
- [ ] `ProjectTeam` model created with full validation
- [ ] `User` model enhanced with hierarchical permission methods
- [ ] `Project` model updated with team management
- [ ] `Role` model enhanced with scope helpers
- [ ] Unit tests for all new methods

---

### Phase 3: Permission Service Layer

#### 3.1 Create `PermissionResolver` service
**File:** `app/Services/Permissions/PermissionResolver.php`

**Purpose:** Centralize all permission resolution logic

```php
class PermissionResolver
{
    /**
     * Resolve permission with hierarchical context
     */
    public function resolve(
        User $user,
        string $permissionSlug,
        ?Model $context = null
    ): PermissionResolution

    /**
     * Resolve permission at specific scope level
     */
    public function resolveAtScope(
        User $user,
        string $permissionSlug,
        string $scopeLevel,
        ?Model $context = null
    ): bool

    /**
     * Get all effective permissions for user in context
     */
    public function getEffectivePermissions(
        User $user,
        ?Model $context = null
    ): Collection

    /**
     * Check permission with detailed result
     */
    public function check(
        User $user,
        string $permissionSlug,
        ?Model $context = null
    ): PermissionCheckResult
}
```

**PermissionResolution DTO:**
```php
class PermissionResolution
{
    public bool $granted;
    public ?string $grantedBy; // 'system_admin', 'global_role', 'project_role', etc.
    public ?Role $effectiveRole;
    public ?string $scopeLevel;
    public array $checkPath; // Audit trail
}
```

#### 3.2 Create `ProjectAccessManager` service
**File:** `app/Services/Permissions/ProjectAccessManager.php`

**Purpose:** Manage project-level access control

```php
class ProjectAccessManager
{
    public function canAccessProject(User $user, Project $project): bool
    public function canViewProject(User $user, Project $project): bool
    public function canEditProject(User $user, Project $project): bool
    public function canManageTeam(User $user, Project $project): bool

    public function getAccessLevel(User $user, Project $project): string // 'none', 'view', 'edit', 'manage'
    public function getUserProjectRole(User $user, Project $project): ?Role

    // Team management
    public function canAssignRole(User $assigner, User $assignee, Role $role, Project $project): bool
    public function canRemoveFromTeam(User $user, ProjectTeam $teamMember): bool
}
```

#### 3.3 Create `RoleHierarchyResolver` service
**File:** `app/Services/Permissions/RoleHierarchyResolver.php`

**Purpose:** Resolve role precedence and inheritance

```php
class RoleHierarchyResolver
{
    /**
     * Get effective role for user in context (most specific scope)
     */
    public function getEffectiveRole(User $user, Model $context): ?Role

    /**
     * Get all user roles sorted by precedence (most specific first)
     */
    public function getRolesByPrecedence(User $user, ?Model $context = null): Collection

    /**
     * Get scope precedence order
     * Returns: ['task', 'wbs_element', 'project', 'program', 'portfolio', 'organization', 'global']
     */
    public function getRoleScopePrecedence(): array

    /**
     * Resolve context hierarchy (e.g., Task -> WBS Element -> Project -> Program -> Portfolio)
     */
    public function resolveContextHierarchy(Model $context): Collection

    /**
     * Check if a role at one scope can inherit permissions from another scope
     */
    public function canInheritFrom(string $childScope, string $parentScope): bool

    /**
     * Get inherited permissions for a role at target scope
     */
    public function getInheritedPermissions(Role $role, string $targetScope): Collection

    /**
     * Get numeric scope level (1 = most specific, 7 = least specific)
     */
    public function getScopeLevel(string $scope): int
}
```

**Example Context Resolution:**
```php
// Given a Task, resolve all parent contexts
$task = Task::find(123);
$hierarchy = $resolver->resolveContextHierarchy($task);

// Returns:
// [
//     Task #123,
//     WbsElement #45 (parent of task),
//     Project #12 (parent of WBS element),
//     Program #3 (parent of project),
//     Portfolio #1 (parent of program)
// ]
```

**Deliverables:**
- [ ] `PermissionResolver` service created
- [ ] `ProjectAccessManager` service created
- [ ] `RoleHierarchyResolver` service created
- [ ] Service provider registration
- [ ] Integration tests for permission resolution
- [ ] Context hierarchy resolution tests for Task → WBS → Project → Program → Portfolio

---

### Practical Use Cases for Granular Scopes

#### Task-Level Permissions

**Scenario 1: Task Ownership**
- User "Alice" is assigned as **Task Owner** for Task #456 "Implement Authentication API"
- Alice gets `edit_tasks`, `complete_tasks`, `comment_tasks` permissions ONLY for Task #456
- Alice does NOT have these permissions for other tasks in the project
- Useful for: Assigning specific work items without granting project-wide task access

**Scenario 2: Task Review**
- User "Bob" is assigned as **Task Reviewer** for Task #789 "Code Review"
- Bob gets `view_tasks`, `comment_tasks`, `approve_tasks` permissions ONLY for Task #789
- Useful for: Quality control workflows where external reviewers need limited access

**Implementation:**
```php
// Assign Alice as task owner
$task = Task::find(456);
$ownerRole = Role::where('slug', 'task_owner')->first();
$alice->assignRole($ownerRole, $task);

// Check permission
$alice->hasPermissionInContext('edit_tasks', $task); // true for Task #456
$alice->hasPermissionInContext('edit_tasks', $otherTask); // false for other tasks
```

#### WBS Element-Level Permissions

**Scenario 1: Work Package Manager**
- User "Carol" manages WBS Element "Phase 2: Development"
- Carol gets `view_wbs`, `edit_wbs`, `view_tasks`, `create_tasks`, `edit_tasks` for ALL tasks under "Phase 2"
- Carol does NOT have access to tasks in "Phase 1" or "Phase 3"
- Useful for: Delegating management of specific project phases or deliverables

**Scenario 2: Subcontractor Scope**
- External subcontractor "DevCorp" is assigned to WBS Element "Mobile App Module"
- DevCorp users can only see/edit tasks within their assigned module
- Enforces scope boundaries for multi-vendor projects
- Useful for: Limiting subcontractor access to their contracted scope

**Implementation:**
```php
// Assign Carol as work package manager
$wbsElement = WbsElement::find(45); // "Phase 2: Development"
$wpmRole = Role::where('slug', 'work_package_manager')->first();
$carol->assignRole($wpmRole, $wbsElement);

// Check permission for task within WBS element
$task = Task::where('wbs_element_id', 45)->first();
$carol->hasPermissionInContext('edit_tasks', $task); // true (task is in Carol's WBS)

// Check permission for task outside WBS element
$otherTask = Task::where('wbs_element_id', 67)->first();
$carol->hasPermissionInContext('edit_tasks', $otherTask); // false
```

#### Hierarchy Example: Real-World Project

```
Project: "Website Redesign" (Project Manager: Alice)
├── WBS: "Frontend Development" (Work Package Manager: Bob)
│   ├── Task: "Homepage UI" (Task Owner: Carol)
│   ├── Task: "Product Pages" (Task Owner: Dave)
│   └── Task: "Checkout Flow" (Task Owner: Eve)
├── WBS: "Backend API" (Work Package Manager: Frank)
│   ├── Task: "Authentication API" (Task Owner: Grace)
│   └── Task: "Payment Integration" (Task Owner: Henry)
└── WBS: "Testing & QA" (Work Package Manager: Ivan)
    └── Task: "Security Audit" (Task Reviewer: External Auditor)

Permissions:
- Alice: Full project access (project manager)
- Bob: Can manage all tasks under "Frontend Development" WBS
- Carol: Can only edit "Homepage UI" task
- External Auditor: Can only view/comment on "Security Audit" task
```

**Benefits:**
1. **Principle of Least Privilege** - Users get only the access they need
2. **Flexible Delegation** - Project managers can delegate without over-permissioning
3. **Multi-Vendor Support** - Cleanly isolate subcontractor access
4. **Compliance** - Audit trail shows exactly who had access to what
5. **Scalability** - Large projects can have hundreds of tasks with granular ownership

---

### Phase 4: Policy Layer Enhancement

#### 4.1 Create `BaseProjectAwarePolicy`
**File:** `app/Policies/BaseProjectAwarePolicy.php`

```php
abstract class BaseProjectAwarePolicy
{
    protected PermissionResolver $permissionResolver;
    protected ProjectAccessManager $accessManager;

    /**
     * Check permission with project context
     */
    protected function checkProjectPermission(
        User $user,
        string $permissionSlug,
        Model $model
    ): bool {
        $project = $this->resolveProject($model);

        if (!$project) {
            // Fallback to organization-level permission
            return $user->hasPermission($permissionSlug);
        }

        return $this->permissionResolver->resolve(
            $user,
            $permissionSlug,
            $project
        )->granted;
    }

    /**
     * Resolve project from model (handles Project, Task, Budget, etc.)
     */
    abstract protected function resolveProject(Model $model): ?Project;

    /**
     * Check if user can access project context
     */
    protected function canAccessProjectContext(User $user, Model $model): bool
    {
        $project = $this->resolveProject($model);
        return $project && $this->accessManager->canAccessProject($user, $project);
    }
}
```

#### 4.2 Refactor `ProjectPolicy`
**File:** `app/Policies/ProjectPolicy.php`

```php
class ProjectPolicy extends BaseProjectAwarePolicy
{
    protected function resolveProject(Model $model): ?Project
    {
        return $model instanceof Project ? $model : null;
    }

    public function view(User $user, Project $project): bool
    {
        return $this->checkProjectPermission($user, 'view_projects', $project);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->checkProjectPermission($user, 'edit_projects', $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->checkProjectPermission($user, 'delete_projects', $project);
    }

    public function manageTeam(User $user, Project $project): bool
    {
        return $this->checkProjectPermission($user, 'manage_team_projects', $project);
    }

    public function assignRole(User $user, Project $project, User $assignee, Role $role): bool
    {
        return $this->accessManager->canAssignRole($user, $assignee, $role, $project);
    }
}
```

#### 4.3 Refactor Resource Policies
**Files:** `app/Policies/{Task,Budget,Deliverable,Risk,Issue}Policy.php`

**Pattern:**
```php
class TaskPolicy extends BaseProjectAwarePolicy
{
    protected function resolveProject(Model $model): ?Project
    {
        return $model->project; // Assumes Task belongs to Project
    }

    public function view(User $user, Task $task): bool
    {
        return $this->canAccessProjectContext($user, $task) &&
               $this->checkProjectPermission($user, 'view_tasks', $task);
    }

    // ... other methods
}
```

**Deliverables:**
- [ ] `BaseProjectAwarePolicy` created
- [ ] `ProjectPolicy` refactored
- [ ] All resource policies refactored (Task, Budget, Deliverable, Risk, Issue, etc.)
- [ ] Policy tests updated

---

### Phase 5: Middleware Enhancements

#### 5.1 Create `CheckProjectPermission` middleware
**File:** `app/Http/Middleware/CheckProjectPermission.php`

**Purpose:** Check permissions with project context from route

```php
class CheckProjectPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        $project = $this->resolveProjectFromRoute($request);

        $resolver = app(PermissionResolver::class);
        $result = $resolver->resolve($user, $permission, $project);

        if (!$result->granted) {
            abort(403, "You don't have permission to {$permission} in this project.");
        }

        // Attach permission info to request for debugging
        $request->attributes->set('permission_check', $result);

        return $next($request);
    }

    private function resolveProjectFromRoute(Request $request): ?Project
    {
        // Check route parameters
        if ($projectId = $request->route('project')) {
            return Project::find($projectId);
        }

        // Check for resources that belong to projects
        if ($taskId = $request->route('task')) {
            return Task::find($taskId)?->project;
        }

        // ... similar for other resources

        return null;
    }
}
```

**Registration in `app/Http/Kernel.php`:**
```php
protected $middlewareAliases = [
    // ...
    'project.permission' => \App\Http\Middleware\CheckProjectPermission::class,
];
```

**Usage:**
```php
Route::get('/projects/{project}', [ProjectController::class, 'show'])
    ->middleware('project.permission:view_projects');

Route::put('/projects/{project}/tasks/{task}', [TaskController::class, 'update'])
    ->middleware('project.permission:edit_tasks');
```

#### 5.2 Enhance `CheckPermission` middleware
**File:** `app/Http/Middleware/CheckPermission.php`

**Add context support:**
```php
public function handle(Request $request, Closure $next, string $permission): Response
{
    $user = $request->user();

    // Try to resolve context from route
    $context = $this->resolveContextFromRoute($request);

    $resolver = app(PermissionResolver::class);
    $result = $resolver->resolve($user, $permission, $context);

    if (!$result->granted) {
        abort(403, "Insufficient permissions: {$permission}");
    }

    return $next($request);
}
```

**Deliverables:**
- [ ] `CheckProjectPermission` middleware created
- [ ] Middleware registered in Kernel
- [ ] Existing `CheckPermission` enhanced
- [ ] Route protection updated

---

### Phase 6: API Layer (Controllers & Resources)

#### 6.1 Create `ProjectTeamController`
**File:** `app/Http/Controllers/Api/ProjectTeamController.php`

**Endpoints:**
```php
// List team members
GET /api/projects/{project}/team

// Add team member
POST /api/projects/{project}/team
Body: {
    "user_id": 123,
    "role_id": 45,
    "start_date": "2025-01-01",
    "end_date": null,
    "notes": "Assigned as technical lead"
}

// Update team member
PUT /api/projects/{project}/team/{team}
Body: {
    "role_id": 46,
    "end_date": "2025-12-31"
}

// Remove team member
DELETE /api/projects/{project}/team/{team}

// Get user's projects
GET /api/users/{user}/projects?role=project_manager

// Get project roles (available for assignment)
GET /api/projects/{project}/available-roles
```

**Controller Methods:**
```php
public function index(Project $project): JsonResponse
public function store(StoreProjectTeamRequest $request, Project $project): JsonResponse
public function update(UpdateProjectTeamRequest $request, Project $project, ProjectTeam $team): JsonResponse
public function destroy(Project $project, ProjectTeam $team): JsonResponse
public function availableRoles(Project $project): JsonResponse
public function userProjects(User $user): JsonResponse
```

#### 6.2 Create Form Requests
**Files:** `app/Http/Requests/ProjectTeam/{Store,Update}ProjectTeamRequest.php`

**StoreProjectTeamRequest:**
```php
public function rules(): array
{
    return [
        'user_id' => ['required', 'exists:users,id'],
        'role_id' => ['required', 'exists:roles,id'],
        'start_date' => ['nullable', 'date'],
        'end_date' => ['nullable', 'date', 'after:start_date'],
        'is_primary' => ['boolean'],
        'notes' => ['nullable', 'string', 'max:1000'],
    ];
}

public function withValidator(Validator $validator): void
{
    $validator->after(function ($validator) {
        // Validate user's organization participates in project
        // Validate role scope is appropriate
        // Validate dates within project bounds
        // Validate only one primary PM
    });
}
```

#### 6.3 Create API Resources
**File:** `app/Http/Resources/ProjectTeamResource.php`

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'user' => new UserResource($this->whenLoaded('user')),
        'role' => new RoleResource($this->whenLoaded('role')),
        'project' => new ProjectResource($this->whenLoaded('project')),
        'start_date' => $this->start_date,
        'end_date' => $this->end_date,
        'is_active' => $this->is_active,
        'is_primary' => $this->is_primary,
        'assigned_by' => new UserResource($this->whenLoaded('assignedBy')),
        'assigned_at' => $this->assigned_at,
        'notes' => $this->notes,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
```

#### 6.4 Create `PermissionsController`
**File:** `app/Http/Controllers/Api/PermissionsController.php`

**Purpose:** Debug and inspect user permissions

```php
// Get user's permissions in project context
GET /api/projects/{project}/permissions/user/{user}

// Get current user's permissions in project
GET /api/projects/{project}/permissions/me

// Check specific permission
GET /api/permissions/check?permission=edit_tasks&project_id=123
```

**Deliverables:**
- [ ] `ProjectTeamController` created with all CRUD operations
- [ ] Form requests created with validation
- [ ] API resources created
- [ ] `PermissionsController` created for debugging
- [ ] Routes registered in `routes/api.php`
- [ ] API tests written

---

### Phase 7: Frontend Integration (Vue.js)

#### 7.1 Create Composables
**File:** `resources/js/composables/usePermissions.js`

```javascript
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useProjectStore } from '@/stores/project'

export function usePermissions() {
    const authStore = useAuthStore()
    const projectStore = useProjectStore()

    const can = (permission, context = null) => {
        const user = authStore.user
        if (!user) return false

        // If context is a project, check project-level permissions
        if (context?.type === 'project') {
            return checkProjectPermission(user, permission, context.id)
        }

        // Otherwise check global permissions
        return user.permissions?.includes(permission) ?? false
    }

    const canInCurrentProject = (permission) => {
        const currentProject = projectStore.currentProject
        return currentProject ? can(permission, { type: 'project', id: currentProject.id }) : false
    }

    const hasRole = (roleSlug, projectId = null) => {
        // Check if user has role (globally or in specific project)
        // Implementation uses API call or cached data
    }

    const getProjectRole = (projectId) => {
        // Get user's role in specific project
        // Returns role object or null
    }

    return {
        can,
        canInCurrentProject,
        hasRole,
        getProjectRole
    }
}
```

**File:** `resources/js/composables/useProjectTeam.js`

```javascript
export function useProjectTeam(projectId) {
    const teamMembers = ref([])
    const loading = ref(false)

    const fetchTeamMembers = async () => {
        loading.value = true
        try {
            const response = await axios.get(`/api/projects/${projectId}/team`)
            teamMembers.value = response.data.data
        } finally {
            loading.value = false
        }
    }

    const addTeamMember = async (userId, roleId, options = {}) => {
        const response = await axios.post(`/api/projects/${projectId}/team`, {
            user_id: userId,
            role_id: roleId,
            ...options
        })
        await fetchTeamMembers()
        return response.data.data
    }

    const removeTeamMember = async (teamId) => {
        await axios.delete(`/api/projects/${projectId}/team/${teamId}`)
        await fetchTeamMembers()
    }

    const updateTeamMember = async (teamId, updates) => {
        const response = await axios.put(`/api/projects/${projectId}/team/${teamId}`, updates)
        await fetchTeamMembers()
        return response.data.data
    }

    return {
        teamMembers,
        loading,
        fetchTeamMembers,
        addTeamMember,
        removeTeamMember,
        updateTeamMember
    }
}
```

#### 7.2 Create Project Team Management Component
**File:** `resources/js/components/Projects/TeamManagement.vue`

**Features:**
- List team members with roles
- Add/remove team members
- Change member roles
- Set primary project manager
- Filter by role
- Search by name

**UI Elements:**
```vue
<template>
    <div class="project-team-management">
        <div class="team-header">
            <h2>Project Team</h2>
            <button v-if="can('manage_team_projects')" @click="showAddMemberModal = true">
                Add Member
            </button>
        </div>

        <div class="team-filters">
            <input v-model="searchQuery" placeholder="Search members..." />
            <select v-model="roleFilter">
                <option value="">All Roles</option>
                <option v-for="role in availableRoles" :key="role.id" :value="role.slug">
                    {{ role.name }}
                </option>
            </select>
        </div>

        <table class="team-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Role</th>
                    <th>Organization</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th v-if="can('manage_team_projects')">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="member in filteredTeamMembers" :key="member.id">
                    <td>
                        <div class="member-info">
                            <avatar :user="member.user" />
                            <span>{{ member.user.name }}</span>
                            <badge v-if="member.is_primary" variant="primary">Primary</badge>
                        </div>
                    </td>
                    <td>{{ member.role.name }}</td>
                    <td>{{ member.user.organization.name }}</td>
                    <td>{{ formatDateRange(member.start_date, member.end_date) }}</td>
                    <td>
                        <badge :variant="member.is_active ? 'success' : 'secondary'">
                            {{ member.is_active ? 'Active' : 'Inactive' }}
                        </badge>
                    </td>
                    <td v-if="can('manage_team_projects')">
                        <button @click="editMember(member)">Edit</button>
                        <button @click="removeMember(member)">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <add-team-member-modal
            v-if="showAddMemberModal"
            :project="project"
            :available-roles="availableRoles"
            @close="showAddMemberModal = false"
            @added="onMemberAdded"
        />
    </div>
</template>
```

#### 7.3 Create Permission-Aware UI Directives
**File:** `resources/js/directives/permission.js`

```javascript
export const vPermission = {
    mounted(el, binding) {
        const { value, modifiers } = binding
        const { can } = usePermissions()

        const hasPermission = can(value, modifiers.project ? { type: 'project', id: modifiers.project } : null)

        if (!hasPermission) {
            if (modifiers.hide) {
                el.style.display = 'none'
            } else if (modifiers.disable) {
                el.setAttribute('disabled', 'disabled')
                el.classList.add('disabled')
            } else {
                el.remove() // Default: remove from DOM
            }
        }
    }
}

// Usage in components:
// <button v-permission="'edit_projects'">Edit</button>
// <button v-permission.hide="'delete_projects'">Delete</button>
// <button v-permission.project.123="'approve_budgets'">Approve</button>
```

#### 7.4 Update Navigation Guards
**File:** `resources/js/router/guards/permission.js`

```javascript
export async function checkProjectPermission(to, from, next) {
    const { can } = usePermissions()
    const requiredPermission = to.meta.permission
    const projectId = to.params.project

    if (!requiredPermission) {
        return next()
    }

    const hasPermission = await can(requiredPermission, { type: 'project', id: projectId })

    if (hasPermission) {
        next()
    } else {
        next({
            name: 'forbidden',
            query: { message: `You don't have permission to ${requiredPermission}` }
        })
    }
}

// Route definition:
{
    path: '/projects/:project/budget',
    component: ProjectBudget,
    meta: { permission: 'view_budgets' },
    beforeEnter: checkProjectPermission
}
```

**Deliverables:**
- [ ] `usePermissions` composable created
- [ ] `useProjectTeam` composable created
- [ ] Team management component created
- [ ] Permission directive created
- [ ] Navigation guards updated
- [ ] Integration tests for components

---

### Phase 8: Data Migration & Seeding

#### 8.1 Create Migration Script for Existing Data
**File:** `database/migrations/YYYY_MM_DD_migrate_existing_roles_to_project_teams.php`

**Purpose:** Convert any existing implicit project assignments to explicit `project_teams` records

```php
public function up()
{
    // Example: If you have project managers assigned via a different mechanism
    // Migrate them to the new project_teams table

    $projects = DB::table('projects')->get();

    foreach ($projects as $project) {
        // If project has a project_manager_id field (legacy)
        if ($project->project_manager_id) {
            DB::table('project_teams')->insert([
                'project_id' => $project->id,
                'user_id' => $project->project_manager_id,
                'role_id' => DB::table('roles')->where('slug', 'project_manager')->value('id'),
                'is_active' => true,
                'is_primary' => true,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
```

#### 8.2 Seed Project-Scoped Roles
**File:** `database/seeders/ProjectRolesSeeder.php`

```php
public function run()
{
    $projectRoles = [
        [
            'name' => 'Project Manager',
            'slug' => 'project_manager',
            'scope' => 'project',
            'description' => 'Manages project execution, team, and deliverables',
            'permissions' => [
                'view_projects', 'edit_projects',
                'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
                'view_budgets', 'edit_budgets',
                'manage_team_projects',
                'approve_deliverables',
                // ... all project management permissions
            ]
        ],
        [
            'name' => 'Technical Lead',
            'slug' => 'technical_lead',
            'scope' => 'project',
            'description' => 'Technical oversight and architecture decisions',
            'permissions' => [
                'view_projects', 'view_tasks', 'edit_tasks',
                'view_deliverables', 'edit_deliverables',
                'view_technical_docs', 'edit_technical_docs',
            ]
        ],
        [
            'name' => 'Project Technician',
            'slug' => 'project_technician',
            'scope' => 'project',
            'description' => 'Executes tasks and creates deliverables',
            'permissions' => [
                'view_projects',
                'view_tasks', 'edit_tasks',
                'create_deliverables', 'view_deliverables',
                'create_time_entries',
            ]
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
            ]
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
                'export_financial_reports',
            ]
        ],
        [
            'name' => 'Quality Manager',
            'slug' => 'quality_manager',
            'scope' => 'project',
            'description' => 'Quality assurance and approval authority',
            'permissions' => [
                'view_projects',
                'view_deliverables', 'approve_deliverables',
                'view_quality_controls', 'create_quality_controls',
                'view_risks', 'create_risks',
                'view_issues', 'create_issues',
            ]
        ],
    ];

    foreach ($projectRoles as $roleData) {
        $permissions = $roleData['permissions'];
        unset($roleData['permissions']);

        $role = Role::create($roleData);

        // Attach permissions
        $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
        $role->permissions()->attach($permissionIds);
    }
}
```

#### 8.3 Create Sample Project Teams
**File:** `database/seeders/ProjectTeamsSeeder.php`

```php
public function run()
{
    // For development/testing only
    if (!app()->environment('production')) {
        $projects = Project::limit(5)->get();

        foreach ($projects as $project) {
            // Assign project manager
            $pm = User::where('organization_id', $project->client_organization_id)
                ->inRandomOrder()
                ->first();

            if ($pm) {
                ProjectTeam::create([
                    'project_id' => $project->id,
                    'user_id' => $pm->id,
                    'role_id' => Role::where('slug', 'project_manager')->value('id'),
                    'is_active' => true,
                    'is_primary' => true,
                    'assigned_at' => now(),
                ]);
            }

            // Assign 2-5 team members with various roles
            // ...
        }
    }
}
```

**Deliverables:**
- [ ] Migration script created and tested
- [ ] Project role seeder created
- [ ] Sample team seeder created (dev only)
- [ ] Rollback plan documented

---

## Additional Components

### Documentation Updates

**Files to Update:**
1. `docs/ROLES_AND_PERMISSIONS.md` - Add project-level RBAC documentation
2. `docs/MULTI_TENANT_ARCHITECTURE.md` - Update with project team concept
3. `PERMISSIONS_USAGE_EXAMPLES.md` - Add project context examples
4. Create `docs/PROJECT_TEAM_MANAGEMENT.md` - Complete guide for project teams
5. Create `docs/PERMISSION_RESOLUTION_ALGORITHM.md` - Document hierarchy resolution

### Testing Strategy

**Unit Tests:**
- `User::hasPermissionInContext()` with all scope levels
- `PermissionResolver::resolve()` hierarchical logic
- `ProjectTeam` validation rules
- `RoleHierarchyResolver` precedence logic

**Feature Tests:**
- Project team CRUD operations
- Permission checks at different scope levels
- Policy authorization with project context
- Middleware permission checking

**Integration Tests:**
- User assigned to project can edit tasks
- User removed from project loses access
- Role change updates permissions immediately
- Permission cache invalidation

**E2E Tests (Cypress/Dusk):**
- Project manager can add team members
- Team member can view but not edit based on role
- Permission-based UI element visibility
- Navigation restrictions based on project access

### Performance Optimization

1. **Permission Caching:**
   - Cache user permissions in `users.cached_permissions`
   - Invalidate cache on role changes
   - Use Redis for permission lookups

2. **Eager Loading:**
   - Always eager load `project.teamMembers.role`
   - Preload permissions when loading users

3. **Database Indexes:**
   - Index `project_teams(project_id, is_active)`
   - Index `project_teams(user_id, is_active)`
   - Index `user_roles(user_id, project_id)`

4. **Query Optimization:**
   - Use `whereHas` efficiently for team member queries
   - Batch permission checks when possible

### Security Considerations

1. **Validation:**
   - Always validate user's organization participates in project before role assignment
   - Prevent privilege escalation (can't assign higher role than you have)
   - Validate date ranges within project bounds

2. **Audit Logging:**
   - Log all role assignments/removals
   - Track permission changes
   - Monitor failed authorization attempts

3. **Rate Limiting:**
   - Limit permission check API calls
   - Rate limit team management operations

---

## Implementation Timeline

### Sprint 1 (2 weeks): Foundation
- Phase 1: Database schema
- Phase 2: Model layer
- Initial tests

### Sprint 2 (2 weeks): Business Logic
- Phase 3: Service layer
- Phase 4: Policy layer
- Integration tests

### Sprint 3 (2 weeks): API & Middleware
- Phase 5: Middleware
- Phase 6: API controllers
- API tests

### Sprint 4 (2 weeks): Frontend
- Phase 7: Vue components
- E2E tests
- UI/UX refinements

### Sprint 5 (1 week): Migration & Polish
- Phase 8: Data migration
- Documentation
- Performance optimization
- Final testing

**Total: 9 weeks**

---

## Success Metrics

1. **Functional:**
   - ✅ User can have different roles in different projects
   - ✅ Permissions correctly resolved with project context
   - ✅ Project managers can manage their project teams
   - ✅ All policies respect project-level permissions

2. **Performance:**
   - Permission check < 50ms (90th percentile)
   - Team member list load < 200ms
   - No N+1 queries in permission resolution

3. **Security:**
   - Zero privilege escalation vulnerabilities
   - 100% audit trail coverage for role changes
   - All authorization checks covered by tests

4. **Usability:**
   - Project team management UI intuitive
   - Clear permission denied messages
   - Easy role assignment workflow

---

## Rollback Plan

If issues arise during rollout:

1. **Phase 1-2 (Database/Models):** Drop new tables, restore from backup
2. **Phase 3-4 (Services/Policies):** Feature flag to disable new permission logic
3. **Phase 5-6 (API):** Disable new routes, use legacy endpoints
4. **Phase 7 (Frontend):** Hide team management UI, use old permission checks
5. **Phase 8 (Migration):** Restore database from pre-migration backup

**Feature Flag:**
```php
// config/features.php
'project_level_permissions' => env('FEATURE_PROJECT_PERMISSIONS', false),

// In code
if (config('features.project_level_permissions')) {
    // Use new hierarchical permission check
} else {
    // Use legacy permission check
}
```

---

## Questions for Stakeholders

Before starting implementation, please confirm:

1. **Role Naming:** Are the proposed project roles (PM, Technical Lead, Technician, Observer, etc.) aligned with your organization's terminology?

2. **Permission Inheritance:** Should program-level roles automatically grant project-level access, or should each project require explicit team assignment?

3. **Primary PM Requirement:** Should every active project REQUIRE a primary project manager, or is it optional?

4. **Organization Constraints:** Can users be assigned to projects where their organization is NOT a participant (e.g., external consultants)?

5. **Historical Data:** Do we need to preserve historical team assignments after project completion, or can they be archived?

6. **Notification Requirements:** Should users be notified when added/removed from project teams?

---

## Next Steps

1. **Review this plan** with technical and business stakeholders
2. **Prioritize phases** if full implementation timeline too long
3. **Create detailed tickets** for each phase in your project management tool
4. **Set up feature flag** in configuration
5. **Begin Phase 1** after approval

---

**Document Version:** 1.0
**Author:** Claude
**Date:** 2025-11-21
**Status:** Proposed
