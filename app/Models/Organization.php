<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    // ===================================
    // CONFIGURATION
    // ===================================

    protected $fillable = [
        'name',
        // 'type' - SUPPRIMÉ : Le rôle est contextuel, défini par projet dans project_organizations
        'address',
        'ville',
        'contact_info',
        'logo',
        'status',
    ];

    protected $casts = [
        'contact_info' => 'array',
    ];

    // ===================================
    // ACCESSORS - Contact Info
    // ===================================

    /**
     * Obtenir l'email de contact
     */
    public function getContactEmailAttribute(): ?string
    {
        return $this->contact_info['email'] ?? null;
    }

    /**
     * Obtenir le téléphone de contact
     */
    public function getContactPhoneAttribute(): ?string
    {
        return $this->contact_info['phone'] ?? null;
    }

    /**
     * Obtenir le fax de contact
     */
    public function getContactFaxAttribute(): ?string
    {
        return $this->contact_info['fax'] ?? null;
    }

    /**
     * Obtenir le site web de contact
     */
    public function getContactWebsiteAttribute(): ?string
    {
        return $this->contact_info['website'] ?? null;
    }

    // ===================================
    // MUTATORS - Contact Info
    // ===================================

    /**
     * Définir l'email de contact
     */
    public function setContactEmailAttribute(?string $value): void
    {
        $contactInfo = $this->contact_info ?? [];
        $contactInfo['email'] = $value;
        $this->attributes['contact_info'] = json_encode($contactInfo);
    }

    /**
     * Définir le téléphone de contact
     */
    public function setContactPhoneAttribute(?string $value): void
    {
        $contactInfo = $this->contact_info ?? [];
        $contactInfo['phone'] = $value;
        $this->attributes['contact_info'] = json_encode($contactInfo);
    }

    /**
     * Définir le fax de contact
     */
    public function setContactFaxAttribute(?string $value): void
    {
        $contactInfo = $this->contact_info ?? [];
        $contactInfo['fax'] = $value;
        $this->attributes['contact_info'] = json_encode($contactInfo);
    }

    /**
     * Définir le site web de contact
     */
    public function setContactWebsiteAttribute(?string $value): void
    {
        $contactInfo = $this->contact_info ?? [];
        $contactInfo['website'] = $value;
        $this->attributes['contact_info'] = json_encode($contactInfo);
    }

    // ===================================
    // RELATIONS - Utilisateurs
    // ===================================

    /**
     * Utilisateurs appartenant à cette organisation
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ===================================
    // RELATIONS - Projets (Client Principal)
    // ===================================

    /**
     * Projets où cette organisation est le client/sponsor principal
     * (colonne client_organization_id dans projects)
     */
    public function projectsAsClient()
    {
        return $this->hasMany(Project::class, 'client_organization_id');
    }

    // ===================================
    // RELATIONS - Participations Projets
    // ===================================

    /**
     * Participations de cette organisation dans des projets
     * (enregistrements dans project_organizations avec détails du rôle)
     */
    public function participations()
    {
        return $this->hasMany(ProjectOrganization::class);
    }

    /**
     * Projets auxquels cette organisation participe (tous rôles confondus)
     * Via la table pivot project_organizations
     */
    public function allProjects()
    {
        return $this->belongsToMany(Project::class, 'project_organizations')
                    ->withPivot([
                        'role',
                        'reference',
                        'scope_description',
                        'is_primary',
                        'status',
                        'start_date',
                        'end_date'
                    ])
                    ->withTimestamps();
    }

    // ===================================
    // RELATIONS - Travaux Assignés
    // ===================================

    /**
     * Éléments WBS assignés à cette organisation
     */
    public function assignedWbsElements()
    {
        return $this->hasMany(WbsElement::class, 'assigned_organization_id');
    }

    /**
     * Livrables assignés à cette organisation pour production
     */
    public function assignedDeliverables()
    {
        return $this->hasMany(Deliverable::class, 'assigned_organization_id');
    }

    /**
     * Tâches assignées à cette organisation pour exécution
     */
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_organization_id');
    }

    // ===================================
    // SCOPES - Statut
    // ===================================

    /**
     * Filtrer uniquement les organisations actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Filtrer les organisations inactives
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Filtrer les organisations archivées
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    // ===================================
    // HELPERS - Rôle Contextuel (par Projet)
    // ===================================

    /**
     * Vérifier si l'organisation est le client/sponsor pour un projet donné
     *
     * @param int $projectId ID du projet
     * @return bool True si cette organisation est sponsor du projet
     */
    public function isClientForProject(int $projectId): bool
    {
        return $this->participations()
            ->where('project_id', $projectId)
            ->where('role', 'sponsor')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Vérifier si l'organisation est MOE pour un projet donné
     *
     * @param int $projectId ID du projet
     * @return bool True si cette organisation est MOE du projet
     */
    public function isMoeForProject(int $projectId): bool
    {
        return $this->participations()
            ->where('project_id', $projectId)
            ->whereIn('role', ['moe', 'subcontractor'])
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Vérifier si l'organisation est MOA pour un projet donné
     *
     * @param int $projectId ID du projet
     * @return bool True si cette organisation est MOA du projet
     */
    public function isMoaForProject(int $projectId): bool
    {
        return $this->participations()
            ->where('project_id', $projectId)
            ->where('role', 'moa')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Obtenir le rôle de l'organisation pour un projet donné
     *
     * @param int $projectId ID du projet
     * @return string|null Le rôle ('sponsor', 'moa', 'moe', 'subcontractor') ou null
     */
    public function getRoleForProject(int $projectId): ?string
    {
        $participation = $this->participations()
            ->where('project_id', $projectId)
            ->where('status', 'active')
            ->first();

        return $participation?->role;
    }

    /**
     * Récupérer tous les projets où cette organisation est cliente
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProjectsWhereClient()
    {
        return $this->participations()
            ->where('role', 'sponsor')
            ->where('status', 'active')
            ->with('project')
            ->get()
            ->pluck('project');
    }

    /**
     * Récupérer tous les projets où cette organisation est MOE
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProjectsWhereMoe()
    {
        return $this->participations()
            ->whereIn('role', ['moe', 'subcontractor'])
            ->where('status', 'active')
            ->with('project')
            ->get()
            ->pluck('project');
    }

    /**
     * Récupérer tous les projets où cette organisation est MOA
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProjectsWhereMoa()
    {
        return $this->participations()
            ->where('role', 'moa')
            ->where('status', 'active')
            ->with('project')
            ->get()
            ->pluck('project');
    }

    /**
     * Récupérer tous les projets où cette organisation est sous-traitant
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProjectsWhereSubcontractor()
    {
        return $this->participations()
            ->where('role', 'subcontractor')
            ->where('status', 'active')
            ->with('project')
            ->get()
            ->pluck('project');
    }

    // ===================================
    // HELPERS - Statut
    // ===================================

    /**
     * Vérifier si l'organisation est active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si l'organisation est inactive
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Vérifier si l'organisation est archivée
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    // ===================================
    // HELPERS - Projets par Rôle
    // ===================================

    /**
     * Récupérer les projets où cette organisation est Sponsor
     */
    public function projectsAsSponsor()
    {
        return $this->participations()
                    ->where('role', 'sponsor')
                    ->where('status', 'active')
                    ->with('project')
                    ->get()
                    ->pluck('project');
    }

    /**
     * Récupérer les projets où cette organisation est MOA (Maître d'Ouvrage)
     */
    public function projectsAsMoa()
    {
        return $this->participations()
                    ->where('role', 'moa')
                    ->where('status', 'active')
                    ->with('project')
                    ->get()
                    ->pluck('project');
    }

    /**
     * Récupérer les projets où cette organisation est MOE (Maître d'Œuvre)
     * Inclut les rôles 'moe' et 'subcontractor'
     */
    public function projectsAsMoe()
    {
        return $this->participations()
                    ->whereIn('role', ['moe', 'subcontractor'])
                    ->where('status', 'active')
                    ->with('project')
                    ->get()
                    ->pluck('project');
    }

    /**
     * Récupérer les projets où cette organisation est sous-traitant
     */
    public function projectsAsSubcontractor()
    {
        return $this->participations()
                    ->where('role', 'subcontractor')
                    ->where('status', 'active')
                    ->with('project')
                    ->get()
                    ->pluck('project');
    }

    // ===================================
    // HELPERS - Contact
    // ===================================

    /**
     * Obtenir les informations de contact formatées
     */
    public function getFormattedContact(): array
    {
        return [
            'email' => $this->contact_email,
            'phone' => $this->contact_phone,
            'fax' => $this->contact_fax,
            'website' => $this->contact_website,
        ];
    }

    /**
     * Mettre à jour les informations de contact
     */
    public function updateContact(array $contactInfo): void
    {
        $this->contact_info = array_merge($this->contact_info ?? [], $contactInfo);
        $this->save();
    }
}
