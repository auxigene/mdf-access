# Système de Rôles et Permissions (RBAC)

**Date de création** : 2025-11-08
**Version** : 1.0
**Statut** : Complété ✅

---

## Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Structure de base de données](#structure-de-base-de-données)
3. [Permissions](#permissions)
4. [Rôles](#rôles)
5. [Attribution des rôles](#attribution-des-rôles)
6. [Scopes et hiérarchies](#scopes-et-hiérarchies)
7. [Exemples d'utilisation](#exemples-dutilisation)
8. [Intégration avec le multi-tenant](#intégration-avec-le-multi-tenant)

---

## Vue d'ensemble

### Principe général

Le système MDF Access utilise un modèle **RBAC** (Role-Based Access Control) sophistiqué qui combine :

- **174 permissions granulaires** organisées par ressources PMBOK
- **29 rôles prédéfinis** couvrant tous les acteurs d'un projet
- **3 niveaux de scope** (global, organization, project)
- **Intégration multi-tenant** avec Row-Level Security

### Caractéristiques clés

✅ **Granularité fine** : Permissions par ressource et action (view, create, edit, delete, approve)
✅ **Hiérarchie de rôles** : Du Super Admin au Membre d'Équipe
✅ **Scopes contextuels** : Permissions limitées à un périmètre (global, org, projet)
✅ **Multi-tenant natif** : Isolation des données par organisation
✅ **Conformité PMBOK** : Rôles et permissions alignés sur les standards

---

## Structure de base de données

### Tables principales

```
┌──────────────┐     ┌──────────────────┐     ┌──────────────┐
│ users        │────→│ user_roles       │←────│ roles        │
│              │     │                  │     │              │
│ - id         │     │ - user_id        │     │ - id         │
│ - name       │     │ - role_id        │     │ - name       │
│ - email      │     │ - portfolio_id   │     │ - slug       │
│ - org_id     │     │ - program_id     │     │ - scope      │
│ - is_sys_adm │     │ - project_id     │     │ - org_id     │
└──────────────┘     └──────────────────┘     └──────────────┘
                              │                       │
                              │                       │
                              │                       ▼
                              │              ┌──────────────────┐
                              │              │ role_permission  │
                              │              │                  │
                              │              │ - role_id        │
                              │              │ - permission_id  │
                              │              └──────────────────┘
                              │                       │
                              │                       │
                              │                       ▼
                              │              ┌──────────────────┐
                              └─────────────→│ permissions      │
                                             │                  │
                                             │ - id             │
                                             │ - name           │
                                             │ - slug           │
                                             │ - resource       │
                                             │ - action         │
                                             └──────────────────┘
```

### Table `permissions`

Stocke les 174 permissions granulaires du système.

```sql
permissions:
  - id BIGINT PRIMARY KEY
  - name VARCHAR(255)           -- "Voir les projets"
  - slug VARCHAR(255) UNIQUE    -- "view_projects"
  - description TEXT            -- Description détaillée
  - resource VARCHAR(100)       -- "projects", "tasks", etc.
  - action VARCHAR(50)          -- "view", "create", "edit", "delete", "approve"
  - created_at TIMESTAMP
  - updated_at TIMESTAMP
```

### Table `roles`

Stocke les 25 rôles prédéfinis.

```sql
roles:
  - id BIGINT PRIMARY KEY
  - name VARCHAR(255)           -- "Chef de Projet"
  - slug VARCHAR(255) UNIQUE    -- "project_manager"
  - description TEXT            -- Description du rôle
  - scope ENUM('global', 'organization', 'project')
  - organization_id BIGINT NULL -- Pour rôles spécifiques à une org
  - created_at TIMESTAMP
  - updated_at TIMESTAMP

  FOREIGN KEY organization_id → organizations(id)
```

### Table `role_permission` (pivot)

Associe les permissions aux rôles.

```sql
role_permission:
  - id BIGINT PRIMARY KEY
  - role_id BIGINT
  - permission_id BIGINT
  - created_at TIMESTAMP
  - updated_at TIMESTAMP

  FOREIGN KEY role_id → roles(id) ON DELETE CASCADE
  FOREIGN KEY permission_id → permissions(id) ON DELETE CASCADE

  UNIQUE (role_id, permission_id)
```

### Table `user_roles` (attribution scopée)

Attribue des rôles aux utilisateurs avec un scope optionnel.

```sql
user_roles:
  - id BIGINT PRIMARY KEY
  - user_id BIGINT
  - role_id BIGINT
  - portfolio_id BIGINT NULL    -- Scope portfolio
  - program_id BIGINT NULL      -- Scope programme
  - project_id BIGINT NULL      -- Scope projet
  - created_at TIMESTAMP
  - updated_at TIMESTAMP

  FOREIGN KEY user_id → users(id) ON DELETE CASCADE
  FOREIGN KEY role_id → roles(id) ON DELETE CASCADE
  FOREIGN KEY portfolio_id → portfolios(id) ON DELETE CASCADE
  FOREIGN KEY program_id → programs(id) ON DELETE CASCADE
  FOREIGN KEY project_id → projects(id) ON DELETE CASCADE

  CONSTRAINT scope_check CHECK (
    (portfolio_id IS NOT NULL AND program_id IS NULL AND project_id IS NULL) OR
    (portfolio_id IS NULL AND program_id IS NOT NULL AND project_id IS NULL) OR
    (portfolio_id IS NULL AND program_id IS NULL AND project_id IS NOT NULL) OR
    (portfolio_id IS NULL AND program_id IS NULL AND project_id IS NULL)
  )
```

---

## Permissions

### Organisation par ressource

**Total : 174 permissions** réparties sur **44 ressources PMBOK**

| Ressource | Actions | Count |
|-----------|---------|-------|
| **Gestion de portefeuille** | | |
| portfolios | view, create, edit, delete | 4 |
| programs | view, create, edit, delete | 4 |
| projects | view, create, edit, delete, approve | 5 |
| **Planification et exécution** | | |
| tasks | view, create, edit, delete | 4 |
| project_phases | view, create, edit, delete | 4 |
| wbs_elements | view, create, edit, delete | 4 |
| deliverables | view, create, edit, delete, approve | 5 |
| milestones | view, create, edit, delete | 4 |
| schedules | view, create, edit, delete | 4 |
| **Ressources et équipes** | | |
| resources | view, create, edit, delete | 4 |
| resource_allocations | view, create, edit, delete | 4 |
| teams | view, create, edit, delete | 4 |
| team_members | view, create, edit, delete | 4 |
| **Finances** | | |
| budgets | view, create, edit, delete, approve | 5 |
| expenses | view, create, edit, delete | 4 |
| earned_value_metrics | view, create, edit, delete | 4 |
| kpis | view, create, edit, delete | 4 |
| **Risques et problèmes** | | |
| risks | view, create, edit, delete | 4 |
| risk_responses | view, create, edit, delete | 4 |
| issues | view, create, edit, delete | 4 |
| **Qualité** | | |
| quality_audits | view, create, edit, delete | 4 |
| quality_metrics | view, create, edit, delete | 4 |
| lessons_learned | view, create, edit, delete | 4 |
| **Parties prenantes** | | |
| stakeholders | view, create, edit, delete | 4 |
| stakeholder_engagement | view, create, edit, delete | 4 |
| **Communication** | | |
| communications | view, create, edit, delete | 4 |
| meetings | view, create, edit, delete | 4 |
| meeting_attendees | view, create, edit, delete | 4 |
| **Documentation** | | |
| documents | view, create, edit, delete, approve | 5 |
| document_approvals | view, create, edit, delete, approve | 5 |
| **Achats** | | |
| vendors | view, create, edit, delete | 4 |
| procurements | view, create, edit, delete, approve | 5 |
| **Changements** | | |
| change_requests | view, create, edit, delete, approve | 5 |
| **Reporting** | | |
| reports | view, create, export | 3 |
| project_status_reports | view, create, edit, delete | 4 |
| **Système** | | |
| users | view, create, edit, delete | 4 |
| organizations | view, create, edit, delete | 4 |
| roles | view, create, edit, delete | 4 |
| permissions | view, create, edit, delete | 4 |
| user_roles | view, create, edit, delete | 4 |
| api_keys | view, create, edit, delete | 4 |
| **Multi-organisations** | | |
| project_organizations | view, create, edit, delete | 4 |

### Actions standard

| Action | Description | Exemple |
|--------|-------------|---------|
| **view** | Consulter/lire | Voir la liste des projets |
| **create** | Créer/ajouter | Créer un nouveau projet |
| **edit** | Modifier | Modifier les détails d'un projet |
| **delete** | Supprimer | Supprimer un projet |
| **approve** | Approuver | Approuver un budget, un livrable |
| **export** | Exporter | Exporter des rapports en PDF/Excel |

---

## Rôles

### Organisation par catégorie

**Total : 29 rôles** répartis en 11 catégories

### 1. Rôles administratifs SAMSIC (3 rôles)

#### Super Administrateur
- **Slug** : `super_admin`
- **Scope** : `global`
- **Permissions** : **TOUTES** (174)
- **Description** : Accès complet au système
- **Usage** : Administrateurs systèmes SAMSIC

#### Directeur PMO
- **Slug** : `pmo_director`
- **Scope** : `global`
- **Permissions** : 18 permissions (view/approve)
- **Description** : Vision transversale de tous les projets
- **Usage** : Direction du PMO SAMSIC

#### Manager PMO
- **Slug** : `pmo_manager`
- **Scope** : `global`
- **Permissions** : 22 permissions (view/create/edit)
- **Description** : Gestion opérationnelle du PMO
- **Usage** : Managers PMO SAMSIC

### 2. Rôles de gestion de portefeuille (1 rôle)

#### Directeur de Portfolio
- **Slug** : `portfolio_director`
- **Scope** : `organization`
- **Permissions** : 16 permissions
- **Description** : Responsable d'un portfolio de projets
- **Clés** : view/edit portfolios, approve projects/budgets

### 3. Rôles de gestion de programme (1 rôle)

#### Manager de Programme
- **Slug** : `program_manager`
- **Scope** : `project`
- **Permissions** : 22 permissions
- **Description** : Responsable d'un programme
- **Clés** : create/edit projects, view/create/edit tasks/risks/issues

### 4. Rôles de gestion de projet (2 rôles)

#### Chef de Projet
- **Slug** : `project_manager`
- **Scope** : `project`
- **Permissions** : 28 permissions
- **Description** : Responsable d'un projet spécifique
- **Clés** : Gestion complète d'un projet (tasks, budgets, risks, resources, documents)

#### Coordinateur de Projet
- **Slug** : `project_coordinator`
- **Scope** : `project`
- **Permissions** : 15 permissions
- **Description** : Assistance au chef de projet
- **Clés** : view/create tasks, risks, issues, documents (pas de delete)

### 5. Rôles métiers SAMSIC (3 rôles)

#### Responsable Achats
- **Slug** : `procurement_manager`
- **Scope** : `global`
- **Permissions** : 6 permissions
- **Clés** : view projects/budgets, create/edit expenses

#### Responsable Facturation
- **Slug** : `billing_manager`
- **Scope** : `global`
- **Permissions** : 6 permissions
- **Clés** : view projects/budgets/expenses, view/export reports

#### Responsable Méthodes
- **Slug** : `methods_manager`
- **Scope** : `global`
- **Permissions** : 14 permissions
- **Clés** : quality metrics/audits, lessons learned, processes, reports

### 6. Rôles clients (3 rôles)

#### Client Sponsor
- **Slug** : `client_sponsor`
- **Scope** : `organization`
- **Permissions** : 19 permissions (avec approbations)
- **Description** : Sponsor côté client avec pouvoirs d'approbation stratégique
- **Clés** : approve projects/budgets/deliverables/change_requests, view all project data
- **🆕 NOUVEAU** : Ajouté pour approbations côté client

#### Client Administrateur
- **Slug** : `client_admin`
- **Scope** : `organization`
- **Permissions** : 12 permissions
- **Description** : Administrateur côté client
- **Clés** : view projects/tasks/budgets/risks/issues/documents, create issues, export reports

#### Client Lecteur
- **Slug** : `client_viewer`
- **Scope** : `organization`
- **Permissions** : 9 permissions (view uniquement)
- **Description** : Visualisation uniquement pour les clients

### 7. Rôles MOA - Maître d'Ouvrage (3 rôles)

#### Responsable MOA
- **Slug** : `moa_manager`
- **Scope** : `project`
- **Permissions** : 46 permissions (maîtrise complète)
- **Description** : Responsable Maître d'Ouvrage - Maîtrise du scope, validation qualité, approbation livrables
- **Clés** : approve deliverables/change_requests/documents, CRUD quality, edit WBS/scope
- **🆕 NOUVEAU** : Rôle critique pour validation qualité et approbation

#### Contrôleur Qualité MOA
- **Slug** : `moa_quality_controller`
- **Scope** : `project`
- **Permissions** : 23 permissions (focus qualité)
- **Description** : Contrôleur qualité côté MOA - Focus validation et conformité des livrables
- **Clés** : approve deliverables/documents, CRUD quality metrics/audits
- **🆕 NOUVEAU** : Spécialiste validation qualité MOA

#### Assistant MOA
- **Slug** : `moa_assistant`
- **Scope** : `project`
- **Permissions** : 26 permissions (support MOA)
- **Description** : Assistant Maître d'Ouvrage - Support à la maîtrise du scope et suivi qualité
- **Clés** : create/edit deliverables/WBS/change_requests (pas d'approbation)
- **🆕 NOUVEAU** : Support opérationnel au Responsable MOA

### 8. Rôles techniques (3 rôles)

#### Membre d'Équipe
- **Slug** : `team_member`
- **Scope** : `project`
- **Permissions** : 6 permissions
- **Clés** : view projects/tasks/documents, edit tasks, create issues

#### Chef d'Équipe
- **Slug** : `team_lead`
- **Scope** : `project`
- **Permissions** : 16 permissions
- **Clés** : CRUD tasks, teams, resource allocations, issues

#### Gestionnaire Stock
- **Slug** : `stock_manager`
- **Scope** : `global`
- **Permissions** : 6 permissions
- **Clés** : view/create/edit resources, view procurements/vendors/expenses

### 9. Rôles PMBOK spécialisés (7 rôles)

#### Sponsor de Projet
- **Slug** : `project_sponsor`
- **Scope** : `project`
- **Permissions** : 10 permissions (approve)
- **Clés** : approve projects/budgets/change_requests/deliverables

#### Analyste d'Affaires
- **Slug** : `business_analyst`
- **Scope** : `project`
- **Permissions** : 15 permissions
- **Clés** : WBS, deliverables, stakeholders, change requests

#### Responsable Qualité
- **Slug** : `quality_manager`
- **Scope** : `project`
- **Permissions** : 14 permissions
- **Clés** : quality metrics/audits, approve deliverables/documents

#### Gestionnaire des Risques
- **Slug** : `risk_manager`
- **Scope** : `project`
- **Permissions** : 11 permissions
- **Clés** : CRUD risks/risk_responses, issues, documents

#### Gestionnaire des Ressources
- **Slug** : `resource_manager`
- **Scope** : `global`
- **Permissions** : 16 permissions
- **Clés** : CRUD resources/allocations/teams/team_members

#### Planificateur
- **Slug** : `planner`
- **Scope** : `project`
- **Permissions** : 16 permissions
- **Clés** : phases, WBS, tasks, milestones, schedules, allocations

#### Contrôleur de Gestion
- **Slug** : `controller`
- **Scope** : `global`
- **Permissions** : 14 permissions
- **Clés** : budgets, expenses, earned value, KPIs, reports

### 10. Rôles de gouvernance (2 rôles)

#### Membre CCB
- **Slug** : `ccb_member`
- **Scope** : `project`
- **Permissions** : 7 permissions
- **Clés** : view change_requests, approve change_requests

#### Responsable Communication
- **Slug** : `communications_manager`
- **Scope** : `project`
- **Permissions** : 11 permissions
- **Clés** : communications, meetings, stakeholder engagement

### 11. Rôle expertise (1 rôle)

#### Expert Métier
- **Slug** : `subject_matter_expert`
- **Scope** : `project`
- **Permissions** : 11 permissions
- **Clés** : view/edit deliverables, quality metrics/audits, lessons learned

---

## Attribution des rôles

### Logique d'attribution

Les rôles peuvent être attribués avec ou sans scope :

```php
// 1. Rôle global (pas de scope)
user_roles:
  user_id: 5
  role_id: 1  // super_admin
  portfolio_id: NULL
  program_id: NULL
  project_id: NULL

// 2. Rôle scopé sur un portfolio
user_roles:
  user_id: 12
  role_id: 4  // portfolio_director
  portfolio_id: 3
  program_id: NULL
  project_id: NULL

// 3. Rôle scopé sur un programme
user_roles:
  user_id: 18
  role_id: 5  // program_manager
  portfolio_id: NULL
  program_id: 7
  project_id: NULL

// 4. Rôle scopé sur un projet
user_roles:
  user_id: 25
  role_id: 6  // project_manager
  portfolio_id: NULL
  program_id: NULL
  project_id: 42
```

### Règles de scope

| Scope du rôle | Peut être scopé sur | Exemples |
|---------------|---------------------|----------|
| **global** | ∅ (aucun scope) | Super Admin, PMO Director, Resource Manager |
| **organization** | ∅ ou organization | Portfolio Director, Client Admin |
| **project** | portfolio, program OU project | Project Manager, Team Lead, Planner |

### Contrainte CHECK

La table `user_roles` applique une contrainte CHECK PostgreSQL :

```sql
CONSTRAINT scope_check CHECK (
  -- Soit aucun scope
  (portfolio_id IS NULL AND program_id IS NULL AND project_id IS NULL) OR
  -- Soit scope portfolio uniquement
  (portfolio_id IS NOT NULL AND program_id IS NULL AND project_id IS NULL) OR
  -- Soit scope programme uniquement
  (portfolio_id IS NULL AND program_id IS NOT NULL AND project_id IS NULL) OR
  -- Soit scope projet uniquement
  (portfolio_id IS NULL AND program_id IS NULL AND project_id IS NOT NULL)
)
```

**Important** : Un utilisateur ne peut pas avoir un rôle scopé sur plusieurs niveaux simultanément (ex: portfolio + project).

### Multiples rôles par utilisateur

Un utilisateur peut avoir **plusieurs rôles** :

```php
// Utilisateur avec 3 rôles différents
user_roles:
  [
    { user_id: 10, role_id: 6, project_id: 42 },  // Chef de Projet sur PRJ-42
    { user_id: 10, role_id: 6, project_id: 58 },  // Chef de Projet sur PRJ-58
    { user_id: 10, role_id: 13, project_id: NULL } // Membre d'Équipe global
  ]
```

---

## Scopes et hiérarchies

### Niveaux de scope

```
┌─────────────────────────────────────────┐
│         GLOBAL (Système)                │
│  Super Admin, PMO Director, Controller  │
└───────────────┬─────────────────────────┘
                │
        ┌───────▼────────┐
        │  ORGANIZATION  │
        │  (Multi-Tenant)│
        └───────┬────────┘
                │
        ┌───────▼────────┐
        │   PORTFOLIO    │
        └───────┬────────┘
                │
        ┌───────▼────────┐
        │    PROGRAM     │
        └───────┬────────┘
                │
        ┌───────▼────────┐
        │    PROJECT     │
        └────────────────┘
```

### Arbre de décision des permissions

```
┌──────────────────────────────────────────────┐
│ 1. user.is_system_admin = true ?            │
│    → OUI : Bypass tout, accès TOTAL          │
└──────────────────┬───────────────────────────┘
                   │ NON
                   ▼
┌──────────────────────────────────────────────┐
│ 2. user.organization.type = 'Internal' ?    │
│    → OUI : Vérifier PERMISSIONS (rôles)     │
│    → Accès selon rôles attribués             │
└──────────────────┬───────────────────────────┘
                   │ NON
                   ▼
┌──────────────────────────────────────────────┐
│ 3. user.organization.type = 'Client' ?      │
│    → OUI : Filtre RLS sur client_org_id     │
│    → Permissions selon rôles client          │
└──────────────────┬───────────────────────────┘
                   │ NON
                   ▼
┌──────────────────────────────────────────────┐
│ 4. user.organization.type = 'Partner' ?     │
│    → OUI : Filtre RLS sur project_orgs      │
│    → Permissions selon rôles attribués       │
└──────────────────────────────────────────────┘
```

### Vérification des permissions (pseudo-code)

```php
function hasPermission(User $user, string $permission, ?Model $scope = null): bool
{
    // 1. System Admin bypass
    if ($user->is_system_admin) {
        return true;
    }

    // 2. Récupérer les rôles de l'utilisateur
    $userRoles = $user->userRoles;

    // 3. Filtrer par scope si fourni
    if ($scope !== null) {
        if ($scope instanceof Project) {
            $userRoles = $userRoles->where('project_id', $scope->id)
                                   ->orWhereNull('project_id');
        } elseif ($scope instanceof Program) {
            $userRoles = $userRoles->where('program_id', $scope->id)
                                   ->orWhereNull('program_id');
        } elseif ($scope instanceof Portfolio) {
            $userRoles = $userRoles->where('portfolio_id', $scope->id)
                                   ->orWhereNull('portfolio_id');
        }
    }

    // 4. Vérifier si un des rôles a la permission
    foreach ($userRoles as $userRole) {
        $role = $userRole->role;
        if ($role->permissions->contains('slug', $permission)) {
            return true;
        }
    }

    return false;
}
```

---

## Exemples d'utilisation

### Exemple 1 : Chef de Projet sur 2 projets

**Utilisateur** : Marie DUBOIS (ID: 25)
**Organisation** : SAMSIC (Internal)

```php
user_roles:
  [
    {
      user_id: 25,
      role_id: 6,  // project_manager
      project_id: 42,  // Projet Maintenance Usine A
    },
    {
      user_id: 25,
      role_id: 6,  // project_manager
      project_id: 58,  // Projet Rénovation Site B
    }
  ]

// Marie peut :
// ✅ Gérer complètement le projet 42 (edit, create tasks, budgets, etc.)
// ✅ Gérer complètement le projet 58
// ❌ Voir ou modifier le projet 73 (pas de rôle assigné)
```

### Exemple 2 : Client avec accès limité

**Utilisateur** : Ahmed KARIMI (ID: 35)
**Organisation** : Client ABC (Client)

```php
user_roles:
  [
    {
      user_id: 35,
      role_id: 10,  // client_admin
      project_id: NULL,  // Scope organization (RLS)
    }
  ]

// Ahmed peut :
// ✅ Voir tous les projets de son organisation (Client ABC)
// ✅ Voir tasks, budgets, risks, issues, documents
// ✅ Créer des issues
// ✅ Exporter des rapports
// ❌ Modifier des projets
// ❌ Créer ou modifier des tâches
// ❌ Voir les projets d'autres clients (RLS)
```

### Exemple 3 : Sous-traitant MOE partiel

**Utilisateur** : Jean MARTIN (ID: 50)
**Organisation** : Électricité Pro (Partner)

```php
user_roles:
  [
    {
      user_id: 50,
      role_id: 13,  // team_member
      project_id: 42,
    }
  ]

// Jean peut :
// ✅ Voir le projet 42
// ✅ Voir et modifier les tâches assignées à son organisation
// ✅ Créer des issues
// ❌ Voir les tâches assignées à d'autres sous-traitants
// ❌ Voir les autres projets (RLS via project_organizations)
```

### Exemple 4 : Cumul de rôles

**Utilisateur** : Sophie BERNARD (ID: 18)
**Organisation** : SAMSIC (Internal)

```php
user_roles:
  [
    {
      user_id: 18,
      role_id: 5,  // program_manager
      program_id: 7,  // Programme Maintenance Industrielle
    },
    {
      user_id: 18,
      role_id: 15,  // quality_manager
      project_id: NULL,  // Global
    }
  ]

// Sophie peut :
// ✅ Gérer le programme 7 (create/edit projects, tasks, risks, etc.)
// ✅ Gérer la qualité sur TOUS les projets (quality_manager global)
// ✅ create/edit quality_metrics, quality_audits
// ✅ approve deliverables, documents
```

---

## Intégration avec le multi-tenant

### Interaction RBAC ↔ RLS

Le système RBAC fonctionne **en complément** du Row-Level Security (RLS) multi-tenant :

```
┌────────────────────────────────────────────┐
│  1. FILTRAGE RLS (données visibles)       │
│     ↓                                      │
│  Selon user.organization.type :            │
│  - Internal : Tous les projets             │
│  - Client : projects.client_org_id = X     │
│  - Partner : via project_organizations     │
└────────────────┬───────────────────────────┘
                 │
                 ▼
┌────────────────────────────────────────────┐
│  2. VÉRIFICATION RBAC (actions permises)  │
│     ↓                                      │
│  Pour chaque ressource visible :           │
│  - Vérifier permissions via rôles          │
│  - Appliquer scope (project/program)       │
└────────────────────────────────────────────┘
```

### Exemple concret

**Contexte** : Projet 42 avec 3 organisations
- Client ABC (sponsor)
- SAMSIC (MOA + MOE primary)
- Électricité Pro (subcontractor)

**Utilisateur 1** : Marie (SAMSIC, project_manager sur projet 42)
```
RLS : ✅ Voit projet 42 (Internal → voit tout)
RBAC : ✅ Peut edit project 42 (project_manager)
→ Résultat : Accès complet au projet 42
```

**Utilisateur 2** : Ahmed (Client ABC, client_admin)
```
RLS : ✅ Voit projet 42 (client_organization_id = ABC)
RBAC : ❌ Ne peut PAS edit project 42 (client_admin → view only)
→ Résultat : Lecture seule sur projet 42
```

**Utilisateur 3** : Jean (Électricité Pro, team_member sur projet 42)
```
RLS : ✅ Voit projet 42 (project_organizations.organization_id = Électricité Pro)
RBAC : ✅ Peut view/edit tasks (team_member)
→ Résultat : Voir projet, modifier tâches assignées
```

### Règles d'accès combinées

| Type Org | RLS Filtre | RBAC Vérifie | Résultat |
|----------|------------|--------------|----------|
| **Internal** | Aucun (voit tout) | Permissions selon rôles | Accès contrôlé par RBAC uniquement |
| **Client** | `client_organization_id = X` | Permissions client_admin/viewer | RLS + RBAC (double filtre) |
| **Partner** | `project_organizations` | Permissions selon rôles | RLS + RBAC (double filtre) |

---

## Seeders et données

### Seeders disponibles

1. **PermissionsSeeder** : Crée les 170 permissions de base
2. **RolesSeeder** : Crée les 25 rôles de base avec leurs permissions
3. **ProjectOrganizationsPermissionsSeeder** : Ajoute 4 permissions project_organizations
4. **ProjectOrganizationsRolesSeeder** : Attribue les nouvelles permissions aux rôles
5. **ClientMoaRolesSeeder** 🆕 : Ajoute 4 rôles d'approbation (Client Sponsor + 3 rôles MOA)

### Ordre d'exécution

```bash
# 1. Créer les permissions (170)
php artisan db:seed --class=PermissionsSeeder

# 2. Créer les rôles de base avec permissions (25 rôles)
php artisan db:seed --class=RolesSeeder

# 3. Ajouter permissions project_organizations (4)
php artisan db:seed --class=ProjectOrganizationsPermissionsSeeder

# 4. Attribuer nouvelles permissions aux rôles existants
php artisan db:seed --class=ProjectOrganizationsRolesSeeder

# 5. Ajouter rôles d'approbation Client et MOA (4 rôles) 🆕
php artisan db:seed --class=ClientMoaRolesSeeder
```

### Données actuelles

✅ **174 permissions** créées
✅ **25 rôles de base** créés avec permissions
✅ **4 rôles Client/MOA** à créer (seeder prêt) 🆕
✅ **Associations role_permission** créées
❌ **user_roles** à créer manuellement ou via interface

**Total après exécution complète** : **29 rôles**

---

## Prochaines étapes

### Phase 1 : Middleware et Gates (priorité haute)

- [ ] Middleware `CheckPermission`
- [ ] Laravel Gates pour chaque permission
- [ ] Policy classes pour les modèles
- [ ] Blade directives (@can, @cannot)

### Phase 2 : API et interface (priorité haute)

- [ ] API endpoints pour attribution de rôles
- [ ] Interface d'administration des rôles
- [ ] Gestion des permissions utilisateur
- [ ] Logs d'audit des changements

### Phase 3 : Tests et documentation (priorité moyenne)

- [ ] Tests unitaires permissions
- [ ] Tests d'intégration RBAC + RLS
- [ ] Guide utilisateur attribution rôles
- [ ] Exemples de code pour développeurs

---

**Dernière mise à jour** : 2025-11-08
**Auteur** : Système MDF Access
**Version** : 1.0
