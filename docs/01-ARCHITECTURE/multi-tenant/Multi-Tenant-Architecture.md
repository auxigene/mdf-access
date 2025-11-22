# Architecture Multi-Tenant - MDF Access

**Date de dernière mise à jour** : 2025-11-07
**Version** : 1.0
**Statut** : Structure de base de données complète ✅ | Implémentation applicative en cours 🚧

---

## Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Structure de base de données](#structure-de-base-de-données)
3. [Logique Row-Level Security](#logique-row-level-security)
4. [Migrations exécutées](#migrations-exécutées)
5. [Système de permissions](#système-de-permissions)
6. [Système de rôles](#système-de-rôles)
7. [État d'implémentation](#état-dimplémentation)
8. [Prochaines étapes](#prochaines-étapes)

---

## Vue d'ensemble

### Principe

L'application MDF Access utilise une architecture **multi-tenant avec Row-Level Security (RLS)** pour :
- Isoler les données des clients
- Permettre aux utilisateurs internes SAMSIC d'avoir une vue transversale
- Gérer les permissions granulaires par rôle et scope

### Approche choisie

**Single Database, Shared Schema avec RLS** :
- Une seule base de données PostgreSQL
- Schéma partagé entre tous les tenants
- Filtrage des données au niveau applicatif via `organization_id`
- Type d'utilisateur dérivé de son organisation

---

## Structure de base de données

### Table `users`

Gestion des utilisateurs avec appartenance organisationnelle.

```sql
users:
  - id
  - name
  - email
  - password
  - organization_id (FK → organizations) NULLABLE
    → À quelle organisation appartient l'utilisateur
    → NULL uniquement pour les super-admins système

  - is_system_admin BOOLEAN DEFAULT false
    → Super-admin système (bypass toutes les restrictions)
    → Utilisateurs sans organisation (organization_id = NULL)

  - created_at
  - updated_at
```

**Règles** :
- Si `is_system_admin = true` → Accès total, `organization_id` peut être NULL
- Si `is_system_admin = false` → `organization_id` doit être renseigné
- Le type d'utilisateur est dérivé de `organization.type` (pas de redondance)

### Table `organizations`

```sql
organizations:
  - id
  - name
  - type → 'Internal' | 'Client' | 'Partner'
    → Internal : SAMSIC et ses départements
    → Client : Organisations clientes
    → Partner : Partenaires stratégiques

  - address
  - ville
  - contact_info (JSON)
  - logo
  - status → 'active' | 'inactive' | 'archived'
  - created_at
  - updated_at
  - deleted_at (soft delete)
```

### Table `projects`

Projets avec distinction exécutant/client.

```sql
projects:
  - id
  - program_id (FK → programs) NULLABLE

  - executor_organization_id (FK → organizations)
    → Organisation qui EXÉCUTE le projet (SAMSIC ou partenaire)

  - executor_reference
    → Référence interne de l'exécutant (ex: "SAMSIC-2025-001")

  - client_organization_id (FK → organizations)
    → Organisation qui SPONSORISE/POSSÈDE le projet (le client)

  - client_reference
    → Référence côté client (ex: "BC-2025-456")

  - code (UNIQUE)
    → Code unique système du projet

  - name
  - description
  - project_manager_id (FK → users)
  - project_type
  - methodology → 'waterfall' | 'agile' | 'hybrid'
  - start_date, end_date
  - baseline_start, baseline_end
  - budget, actual_cost
  - status → 'initiation' | 'planning' | 'execution' | 'monitoring' | 'closure' | 'on_hold' | 'cancelled'
  - priority → 'low' | 'medium' | 'high' | 'critical'
  - health_status → 'green' | 'yellow' | 'red'
  - charter_approved_at
  - charter_approved_by (FK → users)
  - completion_percentage
  - created_at
  - updated_at
  - deleted_at
```

**Index** :
- `executor_organization_id`
- `client_organization_id`
- `executor_reference`
- `client_reference`

### Table `roles`

Rôles avec scopes hiérarchiques.

```sql
roles:
  - id
  - name → "Chef de Projet", "Client Administrateur", etc.
  - slug → "project_manager", "client_admin", etc.
  - description
  - scope → 'global' | 'organization' | 'project'
    → global : Accès transversal (ex: PMO, Super Admin)
    → organization : Limité à une organisation
    → project : Limité à un projet spécifique

  - organization_id (FK → organizations) NULLABLE
    → Pour les rôles spécifiques à une organisation

  - created_at
  - updated_at
```

### Table `permissions`

Permissions granulaires par ressource et action.

```sql
permissions:
  - id
  - name → "Voir les projets", "Modifier des budgets", etc.
  - slug → "view_projects", "edit_budgets", etc.
  - description
  - resource → 'projects' | 'tasks' | 'budgets' | 'risks' | etc.
  - action → 'view' | 'create' | 'edit' | 'delete' | 'approve' | 'export'
  - created_at
  - updated_at

  UNIQUE(resource, action)
```

**Total** : 170 permissions couvrant toutes les ressources PMBOK

### Table `role_permission`

Table pivot rôles ↔ permissions.

```sql
role_permission:
  - role_id (FK → roles)
  - permission_id (FK → permissions)
  - created_at
  - updated_at

  PRIMARY KEY (role_id, permission_id)
```

### Table `user_roles`

Attribution des rôles aux utilisateurs avec scope hiérarchique.

```sql
user_roles:
  - user_id (FK → users)
  - role_id (FK → roles)

  - portfolio_id (FK → portfolios) NULLABLE
    → Scope au niveau portfolio

  - program_id (FK → programs) NULLABLE
    → Scope au niveau programme

  - project_id (FK → projects) NULLABLE
    → Scope au niveau projet

  - created_at
  - updated_at

  UNIQUE(user_id, role_id, portfolio_id, program_id, project_id)

  CONSTRAINT: Un seul scope actif à la fois
    (portfolio_id IS NOT NULL AND program_id IS NULL AND project_id IS NULL) OR
    (portfolio_id IS NULL AND program_id IS NOT NULL AND project_id IS NULL) OR
    (portfolio_id IS NULL AND program_id IS NULL AND project_id IS NOT NULL) OR
    (portfolio_id IS NULL AND program_id IS NULL AND project_id IS NULL)
```

**Exemples** :
```sql
-- Rôle global (Super Admin)
user_id: 1, role_id: 1, portfolio_id: NULL, program_id: NULL, project_id: NULL

-- Rôle sur portfolio
user_id: 2, role_id: 4, portfolio_id: 5, program_id: NULL, project_id: NULL

-- Rôle sur projet
user_id: 3, role_id: 6, portfolio_id: NULL, program_id: NULL, project_id: 10
```

---

## Logique Row-Level Security

### Principe

Le filtrage des données se fait selon le type d'organisation de l'utilisateur.

### Arbre de décision

```
┌─────────────────────────────────────────────────┐
│ Si user.is_system_admin = true                  │
│   → Accès TOTAL (bypass tout)                   │
└─────────────────────────────────────────────────┘
                    ↓ sinon
┌─────────────────────────────────────────────────┐
│ Si user.organization.type = 'Internal'          │
│   → Accès selon PERMISSIONS (rôles)             │
│   → Peut voir tous les projets (selon rôle)     │
│   → Filtre basé sur les permissions, pas l'org  │
└─────────────────────────────────────────────────┘
                    ↓ sinon
┌─────────────────────────────────────────────────┐
│ Si user.organization.type = 'Client'            │
│   → Filtre automatique:                         │
│     WHERE client_organization_id =              │
│           user.organization_id                  │
│   → Voit uniquement SES projets                 │
└─────────────────────────────────────────────────┘
                    ↓ sinon
┌─────────────────────────────────────────────────┐
│ Si user.organization.type = 'Partner'           │
│   → Filtre automatique:                         │
│     WHERE executor_organization_id =            │
│           user.organization_id                  │
│   → Voit uniquement les projets qu'il exécute   │
└─────────────────────────────────────────────────┘
```

### Cas d'usage typiques

#### 1. Super-admin système
```php
User {
  id: 1,
  organization_id: NULL,
  is_system_admin: true
}

// Voit TOUT, aucun filtre appliqué
Project::all(); // Tous les projets
```

#### 2. Utilisateur SAMSIC (Internal)
```php
User {
  id: 2,
  organization_id: 1,  // SAMSIC (type: Internal)
  is_system_admin: false
}
Organization { id: 1, type: 'Internal' }

// Voit selon PERMISSIONS (rôle PMO, Chef de projet, etc.)
// Pas de filtre organisation, mais vérification permissions
Project::all(); // Tous les projets SI permission view_projects
```

#### 3. Utilisateur client
```php
User {
  id: 3,
  organization_id: 25,  // Client ABC (type: Client)
  is_system_admin: false
}
Organization { id: 25, type: 'Client' }

// Filtre automatique sur client_organization_id
Project::all();
// SQL: SELECT * FROM projects WHERE client_organization_id = 25
```

#### 4. Utilisateur partenaire
```php
User {
  id: 4,
  organization_id: 50,  // Partner XYZ (type: Partner)
  is_system_admin: false
}
Organization { id: 50, type: 'Partner' }

// Filtre automatique sur executor_organization_id
Project::all();
// SQL: SELECT * FROM projects WHERE executor_organization_id = 50
```

### Exemple de projet

```php
Project {
  id: 100,
  code: "PRJ-2025-001",
  executor_organization_id: 1,      // SAMSIC
  executor_reference: "SAMSIC-MAINT-2025-001",
  client_organization_id: 25,       // Client ABC
  client_reference: "BC-2025-456",
}

// Qui peut voir ce projet ?
// ✅ Super-admin (is_system_admin = true)
// ✅ Users SAMSIC avec permission view_projects
// ✅ Users de l'org 25 (Client ABC)
// ❌ Users d'autres clients
// ❌ Users de partenaires (sauf si executor_organization_id = leur org)
```

---

## Migrations exécutées

### Liste des 11 migrations multi-tenant

#### Modifications table `projects` (4 migrations)

1. **`2025_11_07_230004_rename_organization_id_to_executor_organization_id_in_projects_table.php`**
   - Renomme `organization_id` → `executor_organization_id`
   - Clarifie le rôle : qui EXÉCUTE le projet

2. **`2025_11_07_230005_add_client_organization_id_to_projects_table.php`**
   - Ajoute `client_organization_id`
   - Identifie qui SPONSORISE le projet
   - Index pour performance

3. **`2025_11_07_233812_add_client_reference_to_projects_table.php`**
   - Ajoute `client_reference`
   - Référence du projet côté client (BC, dossier, etc.)
   - Index pour recherche

4. **`2025_11_07_234103_add_executor_reference_to_projects_table.php`**
   - Ajoute `executor_reference`
   - Référence du projet côté exécutant (code SAMSIC)
   - Index pour recherche

#### Modifications table `users` (2 migrations)

5. **`2025_11_07_230021_add_tenant_fields_to_users_table.php`**
   - Ajoute `organization_id` (FK → organizations)
   - Ajoute `user_type` ENUM('internal', 'client', 'partner')
   - Index sur les deux colonnes

6. **`2025_11_07_235248_replace_user_type_with_is_system_admin_in_users_table.php`**
   - Supprime `user_type` (redondant avec organization.type)
   - Ajoute `is_system_admin` BOOLEAN
   - Élimine la redondance de données

#### Système de rôles et permissions (5 migrations)

7. **`2025_11_07_230035_create_roles_table.php`**
   - Crée table `roles`
   - Champs : name, slug, description, scope, organization_id
   - Scopes : global, organization, project

8. **`2025_11_07_230045_create_permissions_table.php`**
   - Crée table `permissions`
   - Champs : name, slug, resource, action
   - Actions : view, create, edit, delete, approve, export
   - Contrainte unique sur (resource, action)

9. **`2025_11_07_230052_create_role_permission_table.php`**
   - Table pivot rôles ↔ permissions
   - Clé primaire composite (role_id, permission_id)

10. **`2025_11_07_230059_create_user_roles_table.php`**
    - Attribution rôles aux utilisateurs
    - Scope hiérarchique : portfolio_id, program_id, project_id
    - Contrainte unique (user_id, role_id, portfolio_id, program_id, project_id)

11. **`2025_11_07_231636_add_scope_check_constraint_to_user_roles_table.php`**
    - Contrainte CHECK sur user_roles
    - Garantit qu'un seul scope est actif à la fois
    - Empêche les scopes ambigus

---

## Système de permissions

### Statistiques

- **Total** : 170 permissions
- **Ressources** : 44 ressources PMBOK
- **Actions** : view, create, edit, delete, approve, export

### Ressources couvertes

#### Gestion organisationnelle
- portfolios, programs, projects, organizations

#### Gestion de scope
- project_phases, wbs_elements, deliverables, tasks

#### Gestion de ressources
- resources, resource_allocations, teams, team_members

#### Gestion de temps
- milestones, schedules

#### Gestion de coûts
- budgets, expenses, earned_value_metrics

#### Gestion des parties prenantes
- stakeholders, stakeholder_engagement

#### Gestion des risques
- risks, risk_responses

#### Gestion des changements
- issues, change_requests

#### Gestion de la qualité
- quality_metrics, quality_audits, lessons_learned

#### Gestion des communications
- communications, meetings, meeting_attendees

#### Gestion documentaire
- documents, document_approvals

#### Gestion des achats
- vendors, procurements

#### Reporting et métriques
- project_status_reports, kpis, reports

#### Système
- roles, permissions, user_roles, api_keys, users

### Format des permissions

```sql
{
  resource: 'projects',
  action: 'view',
  name: 'Voir les projets',
  slug: 'view_projects'
}
```

---

## Système de rôles

### Statistiques

- **Total** : 25 rôles
- **Scope global** : 8 rôles
- **Scope organization** : 3 rôles
- **Scope project** : 14 rôles

### Liste des rôles

#### Rôles SAMSIC internes (scope: global)

| Rôle | Slug | Permissions | Description |
|------|------|-------------|-------------|
| Super Administrateur | `super_admin` | 170 (toutes) | Accès complet système |
| Directeur PMO | `pmo_director` | 12 | Vision transversale, approbations |
| Manager PMO | `pmo_manager` | 15 | Gestion étendue projets |
| Responsable Achats | `procurement_manager` | 5 | Gestion achats/approvisionnements |
| Responsable Facturation | `billing_manager` | 5 | Gestion facturation |
| Gestionnaire des Ressources | `resource_manager` | 20 | Allocation ressources, équipes |
| Contrôleur de Gestion | `controller` | 17 | Budgets, EVM, KPIs |
| Responsable Méthodes | `methods_manager` | 20 | Méthodes, processus, qualité |
| Gestionnaire Stock | `stock_manager` | 7 | Gestion stocks, inventaires |

#### Rôles de gestion (scope: organization/project)

| Rôle | Slug | Scope | Permissions |
|------|------|-------|-------------|
| Directeur de Portfolio | `portfolio_director` | organization | 13 |
| Manager de Programme | `program_manager` | project | 18 |
| Chef de Projet | `project_manager` | project | 25 |
| Coordinateur de Projet | `project_coordinator` | project | 13 |

#### Rôles PMBOK spécialisés (scope: project)

| Rôle | Slug | Permissions | Spécialité |
|------|------|-------------|------------|
| Sponsor de Projet | `project_sponsor` | 12 | Décisions stratégiques, approbations |
| Analyste d'Affaires | `business_analyst` | 20 | Besoins, WBS, parties prenantes |
| Responsable Qualité | `quality_manager` | 19 | Qualité, audits, approbations |
| Gestionnaire des Risques | `risk_manager` | 16 | Risques et réponses |
| Planificateur | `planner` | 20 | Planning, jalons, schedules |
| Membre CCB | `ccb_member` | 8 | Change Control Board |

#### Rôles clients (scope: organization)

| Rôle | Slug | Permissions | Description |
|------|------|-------------|-------------|
| Client Administrateur | `client_admin` | 10 | Accès étendu client |
| Client Lecteur | `client_viewer` | 7 | Visualisation uniquement |

#### Rôles techniques (scope: project)

| Rôle | Slug | Permissions | Description |
|------|------|-------------|-------------|
| Membre d'Équipe | `team_member` | 6 | Tâches, documents |
| Chef d'Équipe | `team_lead` | 18 | Gestion équipe technique |
| Expert Métier | `subject_matter_expert` | 13 | SME domaine spécifique |

#### Rôles communication (scope: project)

| Rôle | Slug | Permissions | Description |
|------|------|-------------|-------------|
| Responsable Communication | `communications_manager` | 19 | Communication, réunions |

---

## État d'implémentation

### ✅ Complété

#### Base de données
- [x] Structure tenant-aware (organization_id, client_organization_id)
- [x] 11 migrations multi-tenant exécutées
- [x] Contraintes et index en place
- [x] Soft deletes configurés

#### Permissions et rôles
- [x] 170 permissions PMBOK créées
- [x] 25 rôles préconfigurés
- [x] Attributions permissions → rôles
- [x] Système de scope hiérarchique

#### Seeders
- [x] PermissionsSeeder opérationnel
- [x] RolesSeeder opérationnel
- [x] Vérification des doublons
- [x] Transactions DB avec rollback

### 🚧 En cours / À faire

#### Models Laravel
- [ ] Model User avec relations
- [ ] Model Role avec relations
- [ ] Model Permission avec relations
- [ ] Model UserRole avec relations
- [ ] Model Organization avec relations
- [ ] Model Project avec relations

#### RLS applicatif
- [ ] Trait `TenantScoped`
- [ ] Global Scope `TenantScope`
- [ ] Middleware `CheckTenantAccess`
- [ ] Application aux models concernés

#### Policies Laravel
- [ ] ProjectPolicy
- [ ] BudgetPolicy
- [ ] DocumentPolicy
- [ ] Etc.

#### Helpers et services
- [ ] Helper `hasPermission($permission)`
- [ ] Helper `hasRole($role)`
- [ ] Service PermissionChecker
- [ ] Service RoleManager

#### Tests
- [ ] Tests unitaires RLS
- [ ] Tests d'intégration permissions
- [ ] Tests isolation tenants
- [ ] Tests scopes hiérarchiques

---

## Prochaines étapes

### Phase 1 : Models et relations (priorité haute)

1. Créer les Models Eloquent
2. Définir les relations entre models
3. Ajouter les accesseurs/mutateurs

### Phase 2 : RLS applicatif (priorité haute)

1. Créer Trait `TenantScoped`
2. Créer Global Scope `TenantScope`
3. Créer Middleware `CheckTenantAccess`
4. Appliquer aux models concernés

### Phase 3 : Policies et autorisations (priorité moyenne)

1. Créer Policies pour chaque ressource
2. Implémenter vérifications permissions
3. Intégrer dans les controllers

### Phase 4 : Tests (priorité moyenne)

1. Tests unitaires RLS
2. Tests d'intégration
3. Tests de sécurité

### Phase 5 : UI et UX (priorité basse)

1. Interface gestion rôles
2. Interface attribution permissions
3. Dashboard admin

---

## Notes techniques

### Performances

- Tous les champs tenant-aware ont des index
- Contraintes DB pour intégrité
- Soft deletes pour historique
- Transactions pour cohérence

### Sécurité

- RLS au niveau DB (structure)
- Filtrage automatique prévu (Global Scopes)
- Vérification middleware
- Policies pour autorisations fines

### Évolutivité

- Système de permissions extensible
- Ajout de nouveaux rôles facile
- Scopes hiérarchiques flexibles
- Support multi-organisation natif

---

**Dernière mise à jour** : 2025-11-07
**Auteur** : Système MDF Access
**Version** : 1.0
