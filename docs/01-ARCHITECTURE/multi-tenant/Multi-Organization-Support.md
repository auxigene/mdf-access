# Architecture Multi-Tenant avec Multi-Organisations par Projet

**Date de création** : 2025-11-08
**Version** : 2.0
**Statut** : Structure DB complète ✅ | Migration en attente 🚧

---

## Vue d'ensemble

### Évolution de l'architecture

Cette version étend l'architecture multi-tenant initiale pour supporter **plusieurs organisations par projet** avec des **rôles distincts** (Sponsor, MOA, MOE, Sous-traitants).

### Problématique résolue

L'architecture initiale ne supportait que 2 organisations par projet :
- Client (sponsor)
- Exécutant (SAMSIC ou partenaire)

La nouvelle architecture supporte les cas complexes :
- **Projet simple** : Client + SAMSIC exécutant
- **SAMSIC MOA + MOE** : Client + SAMSIC cumule les deux rôles (même organisation)
- **Sous-traitance totale** : Client + SAMSIC MOA + Sous-traitant MOE
- **Sous-traitance partielle** : Client + SAMSIC MOA/MOE + Plusieurs sous-traitants

---

## Terminologie française PMBOK

### Rôles des organisations

| Rôle | Acronyme | Définition | Exemple |
|------|----------|------------|---------|
| **Sponsor/Client** | - | Celui qui finance le projet et bénéficie des livrables | Client ABC |
| **Maître d'Ouvrage** | MOA | Celui qui maîtrise le scope, définit les contours des livrables avec le client, s'assure de la qualité attendue | SAMSIC MAINTENANCE |
| **Maître d'Œuvre** | MOE | Celui qui exécute/produit techniquement les livrables sous supervision du MOA | Entreprise XYZ |
| **Sous-traitant** | - | MOE partiel pour une portion du scope | Électricien, Plombier, etc. |

### Hiérarchie des rôles

```
┌──────────────────────────────────────────┐
│           SPONSOR (Client)                │
│  Finance et bénéficie des résultats       │
└──────────────────┬───────────────────────┘
                   │
         ┌─────────▼──────────┐
         │   MOA (SAMSIC)     │
         │  Maîtrise le scope │
         │  Définit qualité   │
         └─────────┬──────────┘
                   │
         ┌─────────▼──────────┐
         │  MOE (Primary)      │
         │  Exécute/Produit    │
         └─────────┬──────────┘
                   │
    ┌──────────────┼──────────────┐
    │              │              │
┌───▼───┐    ┌────▼────┐   ┌─────▼────┐
│ Sous- │    │ Sous-   │   │ Sous-    │
│traitant│   │traitant │   │traitant  │
│  A    │    │   B     │   │    C     │
└───────┘    └─────────┘   └──────────┘
```

---

## Structure de base de données

### Changements par rapport à v1.0

#### Table `projects` (MODIFIÉE)

**Colonnes SUPPRIMÉES** :
```sql
- executor_organization_id  ❌ (maintenant dans project_organizations)
- executor_reference        ❌ (maintenant dans project_organizations)
```

**Colonnes CONSERVÉES** (pour performance RLS) :
```sql
- client_organization_id    ✅ (dénormalisé pour RLS rapide)
- client_reference          ✅ (référence client)
```

#### Table `project_organizations` (NOUVELLE)

Table pivot avec rôles pour gérer toutes les organisations impliquées dans un projet.

```sql
project_organizations:
  - id
  - project_id (FK → projects)
  - organization_id (FK → organizations)

  - role ENUM('sponsor', 'moa', 'moe', 'subcontractor')
    → sponsor : Client qui finance
    → moa : Maître d'Ouvrage
    → moe : Maître d'Œuvre principal
    → subcontractor : Sous-traitant (MOE partiel)

  - reference VARCHAR
    → Référence de l'organisation pour ce projet
    → Ex: "SAMSIC-MAINT-2025-001", "ST-ELEC-2025-05"

  - scope_description TEXT NULLABLE
    → Description du scope (uniquement pour MOE/subcontractors)
    → Ex: "Travaux électriques", "Plomberie"

  - is_primary BOOLEAN DEFAULT false
    → true pour le MOE principal, false pour les sous-traitants
    → Pas utilisé pour sponsor/moa (uniques par nature)

  - start_date DATE NULLABLE
  - end_date DATE NULLABLE
    → Période d'intervention (surtout pour sous-traitants)

  - status ENUM('active', 'inactive', 'completed')
    → Statut de l'intervention

  - created_at
  - updated_at

  UNIQUE (project_id, organization_id, role)
```

#### Tables `deliverables`, `tasks`, `wbs_elements` (MODIFIÉES)

Ajout de `assigned_organization_id` pour affectation granulaire.

```sql
deliverables:
  + assigned_organization_id (FK → organizations) NULLABLE
    → Organisation assignée pour produire ce livrable

tasks:
  + assigned_organization_id (FK → organizations) NULLABLE
    → Organisation assignée pour exécuter cette tâche

wbs_elements:
  + assigned_organization_id (FK → organizations) NULLABLE
    → Organisation assignée pour cet élément WBS
```

---

## Contraintes métier (DB Level)

### Contraintes CHECK

#### 1. `is_primary` uniquement pour MOE/Subcontractor
```sql
CHECK (
  (role IN ('moe', 'subcontractor')) OR
  (role IN ('sponsor', 'moa') AND is_primary = false)
)
```

#### 2. Sponsor sans scope_description
```sql
CHECK (
  role != 'sponsor' OR
  (role = 'sponsor' AND scope_description IS NULL)
)
```

#### 3. MOA sans scope_description
```sql
CHECK (
  role != 'moa' OR
  (role = 'moa' AND scope_description IS NULL)
)
```

### Index uniques partiels (Partial Unique Indexes)

#### 1. Un seul sponsor actif par projet
```sql
CREATE UNIQUE INDEX project_org_unique_active_sponsor
ON project_organizations (project_id)
WHERE role = 'sponsor' AND status = 'active'
```

#### 2. Un seul MOA actif par projet
```sql
CREATE UNIQUE INDEX project_org_unique_active_moa
ON project_organizations (project_id)
WHERE role = 'moa' AND status = 'active'
```

#### 3. Un seul MOE primary actif par projet
```sql
CREATE UNIQUE INDEX project_org_unique_primary_moe
ON project_organizations (project_id)
WHERE role IN ('moe', 'subcontractor') AND is_primary = true AND status = 'active'
```

---

## Règles métier (Application Level)

Ces règles doivent être implémentées dans Laravel (Models, Services, Requests).

### Règles obligatoires

1. ✅ Un projet DOIT avoir **exactement UN sponsor actif**
2. ✅ Un projet DOIT avoir **exactement UN MOA actif**
3. ✅ Un projet DOIT avoir **AU MOINS UN MOE actif** (primary ou subcontractor)
4. ✅ Si plusieurs MOE/subcontractors, **UN SEUL** doit être `is_primary = true`
5. ✅ Les dates start_date/end_date des sous-traitants doivent être **dans les bornes du projet**
6. ✅ Un sponsor/MOA ne peut pas avoir de `scope_description`
7. ✅ Un subcontractor DOIT avoir un `scope_description` non null

### Fichiers à créer

- `app/Models/ProjectOrganization.php`
- `app/Services/ProjectOrganizationService.php`
- `app/Http/Requests/StoreProjectOrganizationRequest.php`
- `app/Http/Requests/UpdateProjectOrganizationRequest.php`

---

## Row-Level Security (RLS) - Nouvelle conception

### Principe

Le RLS utilise **toujours** `projects.client_organization_id` comme colonne dénormalisée pour la performance, mais la table `project_organizations` contient les détails complets.

### Arbre de décision (INCHANGÉ)

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
└─────────────────────────────────────────────────┘
                    ↓ sinon
┌─────────────────────────────────────────────────┐
│ Si user.organization.type = 'Client'            │
│   → Filtre: WHERE client_organization_id =      │
│             user.organization_id                │
└─────────────────────────────────────────────────┘
                    ↓ sinon
┌─────────────────────────────────────────────────┐
│ Si user.organization.type = 'Partner'           │
│   → Filtre: WHERE EXISTS (                      │
│       SELECT 1 FROM project_organizations       │
│       WHERE project_id = projects.id            │
│       AND organization_id = user.organization_id│
│       AND role IN ('moa', 'moe', 'subcontractor')│
│     )                                            │
└─────────────────────────────────────────────────┘
```

### Accès granulaire aux livrables/tâches

Pour les organisations MOE/subcontractors, l'accès peut être filtré au niveau des livrables/tâches :

```sql
-- Livrables assignés à l'organisation
SELECT * FROM deliverables
WHERE assigned_organization_id = user.organization_id

-- Tâches assignées à l'organisation
SELECT * FROM tasks
WHERE assigned_organization_id = user.organization_id
```

---

## Exemples de cas d'usage

### Cas 1 : Projet simple (Client + SAMSIC exécutant)

```sql
-- Projet
projects:
  id: 1
  code: "PRJ-2025-001"
  client_organization_id: 25  -- Client ABC
  client_reference: "BC-2025-456"

-- Organisations du projet
project_organizations:
  [
    {
      project_id: 1,
      organization_id: 25,  -- Client ABC
      role: 'sponsor',
      reference: 'BC-2025-456',
      is_primary: false,
      status: 'active'
    },
    {
      project_id: 1,
      organization_id: 1,  -- SAMSIC
      role: 'moa',
      reference: 'SAMSIC-MAINT-2025-001',
      is_primary: false,
      status: 'active'
    },
    {
      project_id: 1,
      organization_id: 1,  -- SAMSIC
      role: 'moe',
      reference: 'SAMSIC-MAINT-2025-001',
      is_primary: true,
      status: 'active'
    }
  ]
```

### Cas 2 : SAMSIC cumule MOA + MOE (même organisation, rôles multiples)

Exemple réaliste où SAMSIC gère à la fois la maîtrise d'ouvrage ET l'exécution.

```sql
-- Projet
projects:
  id: 2
  code: "PRJ-2025-002"
  client_organization_id: 26  -- Client DEF

-- Organisations du projet
project_organizations:
  [
    {
      project_id: 2,
      organization_id: 26,  -- Client DEF
      role: 'sponsor',
      reference: 'DEF-2025-789',
      is_primary: false,
      status: 'active'
    },
    {
      project_id: 2,
      organization_id: 1,  -- SAMSIC (MOA)
      role: 'moa',
      reference: 'SAMSIC-MOA-2025-002',
      is_primary: false,
      status: 'active'
    },
    {
      project_id: 2,
      organization_id: 1,  -- SAMSIC (MOE) - MÊME ORGANISATION
      role: 'moe',
      reference: 'SAMSIC-MOE-2025-002',
      is_primary: true,
      scope_description: null,
      status: 'active'
    }
  ]

-- Note : La contrainte unique permet une organisation avec des rôles différents
-- UNIQUE (project_id, organization_id, role) ✅
-- Cela PERMET : organization_id=1 avec role='moa' ET role='moe'
-- Cela EMPÊCHE : organization_id=1 avec role='moa' deux fois
```

### Cas 3 : Sous-traitance totale

```sql
-- Projet
projects:
  id: 3
  code: "PRJ-2025-003"
  client_organization_id: 30  -- Client XYZ

-- Organisations du projet
project_organizations:
  [
    {
      project_id: 3,
      organization_id: 30,  -- Client XYZ
      role: 'sponsor',
      status: 'active'
    },
    {
      project_id: 3,
      organization_id: 1,  -- SAMSIC
      role: 'moa',
      reference: 'SAMSIC-MOA-2025-003',
      status: 'active'
    },
    {
      project_id: 3,
      organization_id: 50,  -- Sous-traitant principal
      role: 'moe',
      reference: 'ST-MAIN-2025-05',
      is_primary: true,
      scope_description: 'Travaux complets de maintenance',
      status: 'active'
    }
  ]
```

### Cas 4 : Sous-traitance partielle

```sql
-- Projet
projects:
  id: 4
  code: "PRJ-2025-004"
  client_organization_id: 40  -- Client GHI

-- Organisations du projet
project_organizations:
  [
    {
      project_id: 4,
      organization_id: 40,  -- Client GHI
      role: 'sponsor',
      status: 'active'
    },
    {
      project_id: 4,
      organization_id: 1,  -- SAMSIC
      role: 'moa',
      reference: 'SAMSIC-MOA-2025-004',
      status: 'active'
    },
    {
      project_id: 4,
      organization_id: 1,  -- SAMSIC
      role: 'moe',
      reference: 'SAMSIC-MOE-2025-004',
      is_primary: true,
      scope_description: 'Coordination générale + mécanique',
      status: 'active'
    },
    {
      project_id: 4,
      organization_id: 51,  -- Sous-traitant électricité
      role: 'subcontractor',
      reference: 'ST-ELEC-2025-06',
      is_primary: false,
      scope_description: 'Travaux électriques',
      start_date: '2025-02-01',
      end_date: '2025-04-30',
      status: 'active'
    },
    {
      project_id: 4,
      organization_id: 52,  -- Sous-traitant plomberie
      role: 'subcontractor',
      reference: 'ST-PLOMB-2025-07',
      is_primary: false,
      scope_description: 'Travaux de plomberie',
      start_date: '2025-03-01',
      end_date: '2025-05-31',
      status: 'active'
    }
  ]

-- Affectation granulaire
deliverables:
  [
    {
      id: 1,
      name: 'Installation tableau électrique',
      assigned_organization_id: 51  -- Sous-traitant électricité
    },
    {
      id: 2,
      name: 'Rénovation sanitaires',
      assigned_organization_id: 52  -- Sous-traitant plomberie
    },
    {
      id: 3,
      name: 'Coordination chantier',
      assigned_organization_id: 1  -- SAMSIC
    }
  ]
```

---

## Permissions ajoutées

### Nouvelles permissions (4)

| Permission | Slug | Description |
|------------|------|-------------|
| Voir les organisations d'un projet | `view_project_organizations` | Visualiser les organisations impliquées |
| Ajouter des organisations à un projet | `create_project_organizations` | Ajouter un MOE, sous-traitant |
| Modifier les organisations d'un projet | `edit_project_organizations` | Modifier rôles, scopes |
| Retirer des organisations d'un projet | `delete_project_organizations` | Retirer une organisation |

### Total permissions

- **Avant** : 170 permissions
- **Ajoutées** : 4 permissions
- **Total** : **174 permissions**

---

## Migrations créées

### Liste des 3 nouvelles migrations

1. **`2025_11_08_090816_create_project_organizations_table.php`**
   - Crée la table `project_organizations`
   - Colonnes : project_id, organization_id, role, reference, scope_description, is_primary, dates, status
   - Contrainte unique (project_id, organization_id, role)

2. **`2025_11_08_091140_add_assigned_organization_to_scope_items.php`**
   - Ajoute `assigned_organization_id` à `deliverables`
   - Ajoute `assigned_organization_id` à `tasks`
   - Ajoute `assigned_organization_id` à `wbs_elements`

3. **`2025_11_08_092410_remove_executor_columns_from_projects_table.php`**
   - Supprime `executor_organization_id` de `projects`
   - Supprime `executor_reference` de `projects`
   - Évite la redondance

4. **`2025_11_08_092618_add_business_constraints_to_project_organizations_table.php`**
   - Contraintes CHECK (is_primary, scope_description)
   - Index uniques partiels (sponsor, moa, moe unique)

---

## Seeders créés

### ProjectOrganizationsPermissionsSeeder

Ajoute les 4 permissions pour `project_organizations`.

```bash
php artisan db:seed --class=ProjectOrganizationsPermissionsSeeder
```

---

## État d'implémentation

### ✅ Complété

- [x] Table `project_organizations` avec contraintes
- [x] Colonnes `assigned_organization_id` sur deliverables/tasks/wbs_elements
- [x] Contraintes métier DB (CHECK, indexes uniques)
- [x] 4 nouvelles permissions
- [x] Seeder permissions
- [x] Documentation complète

### 🚧 En attente

- [ ] Exécution des migrations
- [ ] Exécution du seeder permissions
- [ ] Model ProjectOrganization
- [ ] Service ProjectOrganizationService
- [ ] Requests de validation
- [ ] Update du RLS pour Partners
- [ ] Mise à jour MULTI_TENANT_ARCHITECTURE.md

---

## Migration de données (N/A)

Comme la table `projects` est actuellement vide, **aucune migration de données** n'est nécessaire.

Pour les futurs projets existants, la migration suivrait ce pattern :

```php
// Pour chaque projet existant
DB::table('project_organizations')->insert([
    // Sponsor (client)
    [
        'project_id' => $project->id,
        'organization_id' => $project->client_organization_id,
        'role' => 'sponsor',
        'reference' => $project->client_reference,
        'is_primary' => false,
        'status' => 'active'
    ],
    // MOA/MOE (à définir selon le contexte métier)
]);
```

---

## Prochaines étapes

### Phase 1 : Exécution (priorité immédiate)

1. Exécuter les 4 migrations multi-organisations
2. Exécuter ProjectOrganizationsPermissionsSeeder
3. Vérifier les contraintes DB

### Phase 2 : Models et services (priorité haute)

1. Model ProjectOrganization avec relations
2. Service ProjectOrganizationService (logique métier)
3. Requests de validation

### Phase 3 : RLS applicatif (priorité haute)

1. Mettre à jour TenantScope pour Partners
2. Filtrer par project_organizations pour Partners
3. Filtrer deliverables/tasks par assigned_organization_id

### Phase 4 : Interface (priorité moyenne)

1. UI gestion organisations d'un projet
2. UI affectation livrables/tâches
3. Validation temps réel contraintes

---

**Dernière mise à jour** : 2025-11-08
**Auteur** : Système MDF Access
**Version** : 2.0
