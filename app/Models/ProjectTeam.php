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
