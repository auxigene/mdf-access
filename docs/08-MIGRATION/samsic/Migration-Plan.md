# 📋 Plan de Migration : Organisation SAMSIC MAINTENANCE MAROC

**Date :** 9 novembre 2025
**Objectif :** Créer l'organisation propriétaire "SAMSIC MAINTENANCE MAROC" et migrer les données de l'organisation ID=8

---

## 🎯 Contexte

- **Organisation actuelle (ID=8)** : À identifier (probablement une organisation temporaire ou générique)
- **Nouvelle organisation** : "SAMSIC MAINTENANCE MAROC" (propriétaire de la plateforme)
- **Filiales existantes** : Autres organisations avec "SAMSIC" dans le nom (filiales du groupe)

**Raison :** Établir clairement que "SAMSIC MAINTENANCE MAROC" est le propriétaire initial de la plateforme, même si d'autres filiales SAMSIC utilisent le système.

---

## 📊 PHASE 1 : ANALYSE DE L'IMPACT

### Étape 1.1 : Identifier l'organisation ID=8

```sql
SELECT id, name, type, registration_number, city, country, is_active
FROM organizations
WHERE id = 8;
```

### Étape 1.2 : Comptabiliser les données liées

```sql
-- Utilisateurs
SELECT COUNT(*) as count, 'Users' as entity
FROM users
WHERE organization_id = 8

UNION ALL

-- Projets (en tant que client)
SELECT COUNT(*), 'Projects (client)'
FROM projects
WHERE client_organization_id = 8

UNION ALL

-- Participations projets
SELECT COUNT(*), 'Project Organizations'
FROM project_organizations
WHERE organization_id = 8

UNION ALL

-- Ressources
SELECT COUNT(*), 'Resources'
FROM resources
WHERE organization_id = 8;
```

### Étape 1.3 : Script d'analyse automatique

Créer et exécuter ce script pour analyser l'impact :

```bash
php artisan tinker --execute="
echo '╔═══════════════════════════════════════════════════════════════╗' . PHP_EOL;
echo '║  📊 ANALYSE DE L\'IMPACT - MIGRATION ORGANISATION ID=8       ║' . PHP_EOL;
echo '╚═══════════════════════════════════════════════════════════════╝' . PHP_EOL . PHP_EOL;

\$org = App\Models\Organization::find(8);
if (\$org) {
    echo '🏢 Organisation ID=8:' . PHP_EOL;
    echo '  Nom: ' . \$org->name . PHP_EOL;
    echo '  Type: ' . \$org->type . PHP_EOL;
    echo '  Ville: ' . \$org->city . PHP_EOL;
    echo '  Active: ' . (\$org->is_active ? 'Oui' : 'Non') . PHP_EOL;
    echo PHP_EOL;

    echo '📋 Données liées à migrer:' . PHP_EOL;
    \$users = App\Models\User::where('organization_id', 8)->count();
    \$projectsClient = App\Models\Project::where('client_organization_id', 8)->count();
    \$projectOrgs = App\Models\ProjectOrganization::where('organization_id', 8)->count();
    \$resources = App\Models\Resource::where('organization_id', 8)->count();

    echo '  Utilisateurs: ' . \$users . PHP_EOL;
    echo '  Projets (client): ' . \$projectsClient . PHP_EOL;
    echo '  Participations projets: ' . \$projectOrgs . PHP_EOL;
    echo '  Ressources: ' . \$resources . PHP_EOL;
    echo PHP_EOL;

    \$total = \$users + \$projectsClient + \$projectOrgs + \$resources;
    echo '📊 TOTAL ENREGISTREMENTS À MIGRER: ' . \$total . PHP_EOL;
} else {
    echo '❌ Organisation ID=8 non trouvée!' . PHP_EOL;
}

echo PHP_EOL . '🏢 Organisations SAMSIC existantes:' . PHP_EOL;
\$samsic = App\Models\Organization::where('name', 'LIKE', '%SAMSIC%')->get();
foreach(\$samsic as \$s) {
    echo '  [' . \$s->id . '] ' . \$s->name . ' (' . \$s->type . ')' . PHP_EOL;
}
"
```

---

## 🔨 PHASE 2 : CRÉATION DE LA NOUVELLE ORGANISATION

### Étape 2.1 : Créer "SAMSIC MAINTENANCE MAROC"

**Option A - Via Tinker (recommandé) :**

```php
php artisan tinker

$newOrg = \App\Models\Organization::create([
    'name' => 'SAMSIC MAINTENANCE MAROC',
    'type' => 'vendor',  // ou 'client' selon votre besoin
    'registration_number' => '',  // À renseigner si disponible
    'address_line1' => '',
    'address_line2' => '',
    'postal_code' => '',
    'city' => 'Casablanca',  // Ou autre ville
    'country' => 'Maroc',
    'phone' => '',
    'email' => 'contact@samsic-maintenance.ma',  // À adapter
    'website' => 'https://www.samsic-maintenance.ma',  // À adapter
    'is_active' => true,
]);

echo "✅ Organisation créée avec ID: " . $newOrg->id . PHP_EOL;
```

**Option B - Via SQL :**

```sql
INSERT INTO organizations (
    name, type, registration_number, address_line1, address_line2,
    postal_code, city, country, phone, email, website, is_active,
    created_at, updated_at
) VALUES (
    'SAMSIC MAINTENANCE MAROC',
    'vendor',
    '',
    '',
    '',
    '',
    'Casablanca',
    'Maroc',
    '',
    'contact@samsic-maintenance.ma',
    'https://www.samsic-maintenance.ma',
    true,
    NOW(),
    NOW()
) RETURNING id;
```

**⚠️ IMPORTANT :** Notez le nouvel ID généré (ex: ID=27 ou 29 selon votre base)

---

## 🔄 PHASE 3 : MIGRATION DES DONNÉES

### Étape 3.1 : Backup de sécurité (OBLIGATOIRE)

```bash
# Backup PostgreSQL complet
pg_dump -h localhost -U postgres -d mdf_access > backup_pre_migration_$(date +%Y%m%d_%H%M%S).sql

# Ou via Laravel
php artisan db:backup
```

### Étape 3.2 : Migration des utilisateurs

```sql
-- Vérifier d'abord
SELECT id, name, email, organization_id
FROM users
WHERE organization_id = 8;

-- Migrer
UPDATE users
SET organization_id = [NOUVEAU_ID]  -- Remplacer par le nouvel ID
WHERE organization_id = 8;

-- Vérifier après
SELECT COUNT(*) as migrated_users
FROM users
WHERE organization_id = [NOUVEAU_ID];
```

**Via Artisan :**

```php
php artisan tinker

$oldOrgId = 8;
$newOrgId = 29;  // Remplacer par le vrai ID

$users = \App\Models\User::where('organization_id', $oldOrgId)->get();
echo "Utilisateurs à migrer: " . $users->count() . PHP_EOL;

foreach ($users as $user) {
    $user->organization_id = $newOrgId;
    $user->save();
    echo "✅ Migré: " . $user->name . " (" . $user->email . ")" . PHP_EOL;
}

echo "\n✅ Migration des utilisateurs terminée!" . PHP_EOL;
```

### Étape 3.3 : Migration des projets (client_organization_id)

```sql
-- Vérifier d'abord
SELECT id, code, name, client_organization_id
FROM projects
WHERE client_organization_id = 8;

-- Migrer
UPDATE projects
SET client_organization_id = [NOUVEAU_ID]
WHERE client_organization_id = 8;

-- Vérifier après
SELECT COUNT(*) as migrated_projects
FROM projects
WHERE client_organization_id = [NOUVEAU_ID];
```

**Via Artisan :**

```php
php artisan tinker

$oldOrgId = 8;
$newOrgId = 29;  // Remplacer par le vrai ID

$projects = \App\Models\Project::where('client_organization_id', $oldOrgId)->get();
echo "Projets à migrer: " . $projects->count() . PHP_EOL;

foreach ($projects as $project) {
    $project->client_organization_id = $newOrgId;
    $project->save();
    echo "✅ Migré: " . $project->code . " - " . $project->name . PHP_EOL;
}

echo "\n✅ Migration des projets terminée!" . PHP_EOL;
```

### Étape 3.4 : Migration des participations projets

```sql
-- Vérifier d'abord
SELECT po.id, p.code, p.name, po.role, o.name as org_name
FROM project_organizations po
JOIN projects p ON po.project_id = p.id
JOIN organizations o ON po.organization_id = o.id
WHERE po.organization_id = 8;

-- Migrer
UPDATE project_organizations
SET organization_id = [NOUVEAU_ID]
WHERE organization_id = 8;

-- Vérifier après
SELECT COUNT(*) as migrated_project_orgs
FROM project_organizations
WHERE organization_id = [NOUVEAU_ID];
```

**Via Artisan :**

```php
php artisan tinker

$oldOrgId = 8;
$newOrgId = 29;  // Remplacer par le vrai ID

$projectOrgs = \App\Models\ProjectOrganization::where('organization_id', $oldOrgId)->get();
echo "Participations projets à migrer: " . $projectOrgs->count() . PHP_EOL;

foreach ($projectOrgs as $po) {
    $po->organization_id = $newOrgId;
    $po->save();
    $project = $po->project;
    echo "✅ Migré: Projet " . $project->code . " - Rôle: " . $po->role . PHP_EOL;
}

echo "\n✅ Migration des participations projets terminée!" . PHP_EOL;
```

### Étape 3.5 : Migration des ressources

```sql
-- Vérifier d'abord
SELECT id, name, type, organization_id
FROM resources
WHERE organization_id = 8;

-- Migrer
UPDATE resources
SET organization_id = [NOUVEAU_ID]
WHERE organization_id = 8;

-- Vérifier après
SELECT COUNT(*) as migrated_resources
FROM resources
WHERE organization_id = [NOUVEAU_ID];
```

**Via Artisan :**

```php
php artisan tinker

$oldOrgId = 8;
$newOrgId = 29;  // Remplacer par le vrai ID

$resources = \App\Models\Resource::where('organization_id', $oldOrgId)->get();
echo "Ressources à migrer: " . $resources->count() . PHP_EOL;

foreach ($resources as $resource) {
    $resource->organization_id = $newOrgId;
    $resource->save();
    echo "✅ Migré: " . $resource->name . " (" . $resource->type . ")" . PHP_EOL;
}

echo "\n✅ Migration des ressources terminée!" . PHP_EOL;
```

---

## 🔍 PHASE 4 : VÉRIFICATION POST-MIGRATION

### Étape 4.1 : Vérifier qu'aucune donnée ne reste avec l'ancien ID

```sql
-- Vérifier users
SELECT COUNT(*) FROM users WHERE organization_id = 8;
-- Doit retourner 0

-- Vérifier projects
SELECT COUNT(*) FROM projects WHERE client_organization_id = 8;
-- Doit retourner 0

-- Vérifier project_organizations
SELECT COUNT(*) FROM project_organizations WHERE organization_id = 8;
-- Doit retourner 0

-- Vérifier resources
SELECT COUNT(*) FROM resources WHERE organization_id = 8;
-- Doit retourner 0
```

### Étape 4.2 : Script de vérification complet

```php
php artisan tinker

$oldOrgId = 8;
$newOrgId = 29;  // Remplacer par le vrai ID

echo "╔═══════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║  ✅ VÉRIFICATION POST-MIGRATION                              ║" . PHP_EOL;
echo "╚═══════════════════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

echo "🔍 Données restantes avec ancien ID ($oldOrgId):" . PHP_EOL;
$oldUsers = \App\Models\User::where('organization_id', $oldOrgId)->count();
$oldProjects = \App\Models\Project::where('client_organization_id', $oldOrgId)->count();
$oldProjectOrgs = \App\Models\ProjectOrganization::where('organization_id', $oldOrgId)->count();
$oldResources = \App\Models\Resource::where('organization_id', $oldOrgId)->count();

echo "  Utilisateurs: $oldUsers" . ($oldUsers == 0 ? ' ✅' : ' ❌') . PHP_EOL;
echo "  Projets: $oldProjects" . ($oldProjects == 0 ? ' ✅' : ' ❌') . PHP_EOL;
echo "  Participations: $oldProjectOrgs" . ($oldProjectOrgs == 0 ? ' ✅' : ' ❌') . PHP_EOL;
echo "  Ressources: $oldResources" . ($oldResources == 0 ? ' ✅' : ' ❌') . PHP_EOL;
echo PHP_EOL;

echo "📊 Données migrées vers nouveau ID ($newOrgId):" . PHP_EOL;
$newUsers = \App\Models\User::where('organization_id', $newOrgId)->count();
$newProjects = \App\Models\Project::where('client_organization_id', $newOrgId)->count();
$newProjectOrgs = \App\Models\ProjectOrganization::where('organization_id', $newOrgId)->count();
$newResources = \App\Models\Resource::where('organization_id', $newOrgId)->count();

echo "  Utilisateurs: $newUsers" . PHP_EOL;
echo "  Projets: $newProjects" . PHP_EOL;
echo "  Participations: $newProjectOrgs" . PHP_EOL;
echo "  Ressources: $newResources" . PHP_EOL;
echo PHP_EOL;

$total = $oldUsers + $oldProjects + $oldProjectOrgs + $oldResources;
if ($total == 0) {
    echo "✅ MIGRATION RÉUSSIE - Aucune donnée résiduelle!" . PHP_EOL;
} else {
    echo "❌ ATTENTION - $total enregistrement(s) non migré(s)!" . PHP_EOL;
}
```

### Étape 4.3 : Vérifier l'intégrité des relations

```php
php artisan tinker

$newOrgId = 29;  // Remplacer par le vrai ID
$newOrg = \App\Models\Organization::find($newOrgId);

echo "Organisation: " . $newOrg->name . PHP_EOL;
echo "Utilisateurs: " . $newOrg->users()->count() . PHP_EOL;
echo "Projets (client): " . $newOrg->clientProjects()->count() . PHP_EOL;
echo "Participations: " . $newOrg->projectOrganizations()->count() . PHP_EOL;
echo "Ressources: " . $newOrg->resources()->count() . PHP_EOL;
```

---

## 🗑️ PHASE 5 : NETTOYAGE (OPTIONNEL)

### Étape 5.1 : Décision sur l'ancienne organisation ID=8

**Option A - Supprimer l'organisation (si plus utilisée) :**

⚠️ **ATTENTION :** Ne supprimer que si toutes les vérifications sont OK !

```sql
-- Soft delete (recommandé)
UPDATE organizations
SET deleted_at = NOW()
WHERE id = 8;

-- Ou hard delete (définitif)
DELETE FROM organizations WHERE id = 8;
```

**Option B - Désactiver l'organisation (recommandé) :**

```sql
UPDATE organizations
SET is_active = false,
    name = name || ' (MIGRÉ vers SAMSIC MAINTENANCE MAROC)'
WHERE id = 8;
```

**Option C - Conserver pour historique (le plus sûr) :**

```sql
UPDATE organizations
SET name = name || ' (ANCIEN - Migré)'
WHERE id = 8;
```

---

## 📝 PHASE 6 : DOCUMENTATION

### Étape 6.1 : Documenter la migration

Créer un fichier `migration_log_YYYYMMDD.md` avec :

- Date et heure de la migration
- ID de l'ancienne organisation
- ID de la nouvelle organisation
- Nombre d'enregistrements migrés par entité
- Problèmes rencontrés (si applicable)
- Actions correctives prises

### Étape 6.2 : Mettre à jour la documentation

Ajouter une note dans :
- `docs/ODOO_IMPORT_SUMMARY.md`
- `README.md` (si applicable)

---

## 🚀 SCRIPT DE MIGRATION COMPLET (Tout-en-un)

Créer le fichier `migrate_org8_to_samsic.php` :

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Organization;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectOrganization;
use App\Models\Resource;
use Illuminate\Support\Facades\DB;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  🔄 MIGRATION ORGANISATION ID=8 → SAMSIC MAINTENANCE MAROC  ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$oldOrgId = 8;

// PHASE 1 : ANALYSE
echo "📊 PHASE 1 : ANALYSE\n";
echo str_repeat("-", 65) . "\n\n";

$oldOrg = Organization::find($oldOrgId);
if (!$oldOrg) {
    die("❌ Organisation ID=$oldOrgId non trouvée!\n");
}

echo "🏢 Organisation actuelle ID=$oldOrgId:\n";
echo "  Nom: {$oldOrg->name}\n";
echo "  Type: {$oldOrg->type}\n\n";

$stats = [
    'users' => User::where('organization_id', $oldOrgId)->count(),
    'projects' => Project::where('client_organization_id', $oldOrgId)->count(),
    'project_orgs' => ProjectOrganization::where('organization_id', $oldOrgId)->count(),
    'resources' => Resource::where('organization_id', $oldOrgId)->count(),
];

echo "📋 Données à migrer:\n";
echo "  Utilisateurs: {$stats['users']}\n";
echo "  Projets: {$stats['projects']}\n";
echo "  Participations: {$stats['project_orgs']}\n";
echo "  Ressources: {$stats['resources']}\n\n";

$total = array_sum($stats);
echo "📊 TOTAL: $total enregistrement(s)\n\n";

if ($total == 0) {
    die("✅ Aucune donnée à migrer. Migration annulée.\n");
}

// Confirmation
echo "⚠️  Cette opération va migrer $total enregistrement(s).\n";
echo "Voulez-vous continuer? (y/n): ";
$confirm = trim(fgets(STDIN));
if (strtolower($confirm) !== 'y') {
    die("Migration annulée par l'utilisateur.\n");
}

// PHASE 2 : CRÉATION
echo "\n🔨 PHASE 2 : CRÉATION DE LA NOUVELLE ORGANISATION\n";
echo str_repeat("-", 65) . "\n\n";

$newOrg = Organization::where('name', 'SAMSIC MAINTENANCE MAROC')->first();
if ($newOrg) {
    echo "✅ Organisation déjà existante (ID={$newOrg->id})\n";
} else {
    $newOrg = Organization::create([
        'name' => 'SAMSIC MAINTENANCE MAROC',
        'type' => 'vendor',
        'city' => 'Casablanca',
        'country' => 'Maroc',
        'is_active' => true,
    ]);
    echo "✅ Organisation créée (ID={$newOrg->id})\n";
}

$newOrgId = $newOrg->id;
echo "\n";

// PHASE 3 : MIGRATION
echo "🔄 PHASE 3 : MIGRATION DES DONNÉES\n";
echo str_repeat("-", 65) . "\n\n";

DB::beginTransaction();

try {
    // Utilisateurs
    if ($stats['users'] > 0) {
        echo "Utilisateurs: ";
        $migrated = User::where('organization_id', $oldOrgId)
            ->update(['organization_id' => $newOrgId]);
        echo "$migrated migré(s) ✅\n";
    }

    // Projets
    if ($stats['projects'] > 0) {
        echo "Projets: ";
        $migrated = Project::where('client_organization_id', $oldOrgId)
            ->update(['client_organization_id' => $newOrgId]);
        echo "$migrated migré(s) ✅\n";
    }

    // Participations
    if ($stats['project_orgs'] > 0) {
        echo "Participations: ";
        $migrated = ProjectOrganization::where('organization_id', $oldOrgId)
            ->update(['organization_id' => $newOrgId]);
        echo "$migrated migré(s) ✅\n";
    }

    // Ressources
    if ($stats['resources'] > 0) {
        echo "Ressources: ";
        $migrated = Resource::where('organization_id', $oldOrgId)
            ->update(['organization_id' => $newOrgId]);
        echo "$migrated migré(s) ✅\n";
    }

    DB::commit();
    echo "\n✅ Transaction committée avec succès!\n\n";

} catch (\Exception $e) {
    DB::rollBack();
    die("\n❌ ERREUR: " . $e->getMessage() . "\nTransaction annulée.\n");
}

// PHASE 4 : VÉRIFICATION
echo "🔍 PHASE 4 : VÉRIFICATION\n";
echo str_repeat("-", 65) . "\n\n";

$remaining = [
    'users' => User::where('organization_id', $oldOrgId)->count(),
    'projects' => Project::where('client_organization_id', $oldOrgId)->count(),
    'project_orgs' => ProjectOrganization::where('organization_id', $oldOrgId)->count(),
    'resources' => Resource::where('organization_id', $oldOrgId)->count(),
];

echo "Données restantes avec ID=$oldOrgId:\n";
foreach ($remaining as $entity => $count) {
    $status = $count == 0 ? '✅' : '❌';
    echo "  $entity: $count $status\n";
}

$totalRemaining = array_sum($remaining);
echo "\n";

if ($totalRemaining == 0) {
    echo "✅ MIGRATION RÉUSSIE - Toutes les données ont été migrées!\n\n";

    echo "📊 Nouvelles statistiques pour '{$newOrg->name}' (ID=$newOrgId):\n";
    echo "  Utilisateurs: " . User::where('organization_id', $newOrgId)->count() . "\n";
    echo "  Projets: " . Project::where('client_organization_id', $newOrgId)->count() . "\n";
    echo "  Participations: " . ProjectOrganization::where('organization_id', $newOrgId)->count() . "\n";
    echo "  Ressources: " . Resource::where('organization_id', $newOrgId)->count() . "\n";
} else {
    echo "❌ ATTENTION: $totalRemaining enregistrement(s) n'ont pas été migrés!\n";
}

echo "\n✅ Migration terminée!\n";
```

**Exécuter :**
```bash
php migrate_org8_to_samsic.php
```

---

## ⚠️ POINTS D'ATTENTION

### 1. Backup Obligatoire
- **TOUJOURS** faire un backup complet avant la migration
- Tester la restauration du backup

### 2. Environnement de Test
- Idéalement, tester d'abord sur une copie de la base
- Valider le processus avant de l'appliquer en production

### 3. Permissions et RLS
- Si vous avez déjà implémenté Row-Level Security, vérifiez les policies après migration
- Les utilisateurs doivent pouvoir accéder aux données de la nouvelle organisation

### 4. Audit Trail
- Documenter toutes les étapes
- Garder une trace des IDs avant/après migration

### 5. Communication
- Informer les utilisateurs de la migration
- Prévoir une fenêtre de maintenance si nécessaire

---

## 📞 Support

En cas de problème pendant la migration :

1. **NE PAS PANIQUER** - Les transactions sont protégées
2. Vérifier les logs Laravel : `storage/logs/laravel.log`
3. Restaurer le backup si nécessaire
4. Analyser l'erreur avant de relancer

---

**Date de création :** 9 novembre 2025
**Version :** 1.0
**Auteur :** Plan de migration SAMSIC MAINTENANCE MAROC
