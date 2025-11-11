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
echo "  Type: {$oldOrg->type}\n";
echo "  Ville: " . ($oldOrg->city ?? 'N/A') . "\n";
echo "  Active: " . ($oldOrg->is_active ? 'Oui' : 'Non') . "\n\n";

// Vérifier les données liées (gérer le cas où resources n'a pas organization_id)
$stats = [
    'users' => User::where('organization_id', $oldOrgId)->count(),
    'projects' => Project::where('client_organization_id', $oldOrgId)->count(),
    'project_orgs' => ProjectOrganization::where('organization_id', $oldOrgId)->count(),
];

// Ressources - vérifier si la colonne existe
try {
    $stats['resources'] = Resource::where('organization_id', $oldOrgId)->count();
} catch (\Exception $e) {
    $stats['resources'] = 0;
}

echo "📋 Données à migrer:\n";
echo "  Utilisateurs: {$stats['users']}\n";
echo "  Projets (client): {$stats['projects']}\n";
echo "  Participations projets: {$stats['project_orgs']}\n";
echo "  Ressources: {$stats['resources']}\n\n";

$total = array_sum($stats);
echo "📊 TOTAL: $total enregistrement(s)\n\n";

if ($total == 0) {
    die("✅ Aucune donnée à migrer. Migration annulée.\n");
}

echo "🏢 Organisations SAMSIC existantes:\n";
$samsicOrgs = Organization::where('name', 'LIKE', '%SAMSIC%')->get();
foreach ($samsicOrgs as $org) {
    echo "  [{$org->id}] {$org->name} ({$org->type})\n";
}
echo "\n";

// Confirmation
echo "⚠️  Cette opération va migrer $total enregistrement(s) de '{$oldOrg->name}' vers 'SAMSIC MAINTENANCE MAROC'.\n";
echo "Voulez-vous continuer? (y/n): ";
$confirm = trim(fgets(STDIN));
if (strtolower($confirm) !== 'y') {
    die("\n❌ Migration annulée par l'utilisateur.\n");
}

// PHASE 2 : CRÉATION
echo "\n🔨 PHASE 2 : CRÉATION DE LA NOUVELLE ORGANISATION\n";
echo str_repeat("-", 65) . "\n\n";

$newOrg = Organization::where('name', 'SAMSIC MAINTENANCE MAROC')->first();
if ($newOrg) {
    echo "✅ Organisation 'SAMSIC MAINTENANCE MAROC' déjà existante (ID={$newOrg->id})\n";
    echo "   Type: {$newOrg->type}\n";
    echo "   Ville: " . ($newOrg->city ?? 'N/A') . "\n";
} else {
    echo "Création de 'SAMSIC MAINTENANCE MAROC'...\n";
    $newOrg = Organization::create([
        'name' => 'SAMSIC MAINTENANCE MAROC',
        'type' => 'vendor',
        'registration_number' => '',
        'address_line1' => '',
        'address_line2' => '',
        'postal_code' => '',
        'city' => 'Casablanca',
        'country' => 'Maroc',
        'phone' => '',
        'email' => 'contact@samsic-maintenance.ma',
        'website' => 'https://www.samsic-maintenance.ma',
        'is_active' => true,
    ]);
    echo "✅ Organisation créée avec succès (ID={$newOrg->id})\n";
}

$newOrgId = $newOrg->id;
echo "\n";

// PHASE 3 : MIGRATION
echo "🔄 PHASE 3 : MIGRATION DES DONNÉES\n";
echo str_repeat("-", 65) . "\n\n";

echo "Démarrage de la transaction...\n";
DB::beginTransaction();

try {
    $migrated = [];

    // Utilisateurs
    if ($stats['users'] > 0) {
        echo "Utilisateurs: ";
        $count = User::where('organization_id', $oldOrgId)
            ->update(['organization_id' => $newOrgId]);
        $migrated['users'] = $count;
        echo "$count migré(s) ✅\n";
    } else {
        $migrated['users'] = 0;
    }

    // Projets
    if ($stats['projects'] > 0) {
        echo "Projets: ";
        $count = Project::where('client_organization_id', $oldOrgId)
            ->update(['client_organization_id' => $newOrgId]);
        $migrated['projects'] = $count;
        echo "$count migré(s) ✅\n";
    } else {
        $migrated['projects'] = 0;
    }

    // Participations
    if ($stats['project_orgs'] > 0) {
        echo "Participations: ";
        $count = ProjectOrganization::where('organization_id', $oldOrgId)
            ->update(['organization_id' => $newOrgId]);
        $migrated['project_orgs'] = $count;
        echo "$count migré(s) ✅\n";
    } else {
        $migrated['project_orgs'] = 0;
    }

    // Ressources (si la colonne existe)
    try {
        if ($stats['resources'] > 0) {
            echo "Ressources: ";
            $count = Resource::where('organization_id', $oldOrgId)
                ->update(['organization_id' => $newOrgId]);
            $migrated['resources'] = $count;
            echo "$count migré(s) ✅\n";
        } else {
            $migrated['resources'] = 0;
        }
    } catch (\Exception $e) {
        $migrated['resources'] = 0;
        echo "Ressources: N/A (colonne organization_id n'existe pas)\n";
    }

    DB::commit();
    echo "\n✅ Transaction committée avec succès!\n\n";

} catch (\Exception $e) {
    DB::rollBack();
    die("\n❌ ERREUR: " . $e->getMessage() . "\n\nTransaction annulée. Toutes les modifications ont été annulées.\n");
}

// PHASE 4 : VÉRIFICATION
echo "🔍 PHASE 4 : VÉRIFICATION POST-MIGRATION\n";
echo str_repeat("-", 65) . "\n\n";

// Vérifier les données restantes
$remaining = [
    'users' => User::where('organization_id', $oldOrgId)->count(),
    'projects' => Project::where('client_organization_id', $oldOrgId)->count(),
    'project_orgs' => ProjectOrganization::where('organization_id', $oldOrgId)->count(),
];

// Ressources - vérifier si la colonne existe
try {
    $remaining['resources'] = Resource::where('organization_id', $oldOrgId)->count();
} catch (\Exception $e) {
    $remaining['resources'] = 0;
}

echo "Données restantes avec ancien ID ($oldOrgId):\n";
$allMigrated = true;
foreach ($remaining as $entity => $count) {
    $status = $count == 0 ? '✅' : '❌';
    echo "  " . ucfirst($entity) . ": $count $status\n";
    if ($count > 0) {
        $allMigrated = false;
    }
}

$totalRemaining = array_sum($remaining);
echo "\n";

if ($allMigrated) {
    echo "✅ MIGRATION RÉUSSIE - Toutes les données ont été migrées!\n\n";

    $newStats = [
        'users' => User::where('organization_id', $newOrgId)->count(),
        'projects' => Project::where('client_organization_id', $newOrgId)->count(),
        'project_orgs' => ProjectOrganization::where('organization_id', $newOrgId)->count(),
    ];

    // Ressources - vérifier si la colonne existe
    try {
        $newStats['resources'] = Resource::where('organization_id', $newOrgId)->count();
    } catch (\Exception $e) {
        $newStats['resources'] = 0;
    }

    echo "📊 Nouvelles statistiques pour '{$newOrg->name}' (ID=$newOrgId):\n";
    echo "  Utilisateurs: {$newStats['users']}\n";
    echo "  Projets: {$newStats['projects']}\n";
    echo "  Participations: {$newStats['project_orgs']}\n";
    echo "  Ressources: {$newStats['resources']}\n\n";

    // PHASE 5 : NETTOYAGE
    echo "🗑️  PHASE 5 : NETTOYAGE (OPTIONNEL)\n";
    echo str_repeat("-", 65) . "\n\n";

    echo "Que voulez-vous faire avec l'ancienne organisation '{$oldOrg->name}' (ID=$oldOrgId)?\n";
    echo "  1) Désactiver (recommandé)\n";
    echo "  2) Renommer pour historique\n";
    echo "  3) Supprimer (soft delete)\n";
    echo "  4) Rien faire (conserver telle quelle)\n";
    echo "Votre choix (1-4): ";

    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case '1':
            $oldOrg->is_active = false;
            $oldOrg->name = $oldOrg->name . ' (MIGRÉ vers SAMSIC MAINTENANCE MAROC)';
            $oldOrg->save();
            echo "✅ Organisation désactivée et renommée\n";
            break;

        case '2':
            $oldOrg->name = $oldOrg->name . ' (ANCIEN - Migré le ' . date('Y-m-d') . ')';
            $oldOrg->save();
            echo "✅ Organisation renommée pour historique\n";
            break;

        case '3':
            $oldOrg->delete();
            echo "✅ Organisation supprimée (soft delete)\n";
            break;

        case '4':
        default:
            echo "✅ Organisation conservée telle quelle\n";
            break;
    }

    echo "\n";

} else {
    echo "❌ ATTENTION: $totalRemaining enregistrement(s) n'ont pas été migrés!\n";
    echo "Veuillez vérifier manuellement les données.\n\n";
}

// Résumé final
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ MIGRATION TERMINÉE                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "📄 Rapport de migration:\n";
echo "  Date: " . date('Y-m-d H:i:s') . "\n";
echo "  Ancienne organisation: {$oldOrg->name} (ID=$oldOrgId)\n";
echo "  Nouvelle organisation: {$newOrg->name} (ID=$newOrgId)\n";
echo "  Enregistrements migrés:\n";
foreach ($migrated as $entity => $count) {
    echo "    - " . ucfirst($entity) . ": $count\n";
}
echo "\n✅ Migration terminée avec succès!\n\n";

// Créer un log file
$logContent = "# Rapport de Migration - " . date('Y-m-d H:i:s') . "\n\n";
$logContent .= "## Organisations\n";
$logContent .= "- **Ancienne:** {$oldOrg->name} (ID=$oldOrgId)\n";
$logContent .= "- **Nouvelle:** {$newOrg->name} (ID=$newOrgId)\n\n";
$logContent .= "## Données Migrées\n";
foreach ($migrated as $entity => $count) {
    $logContent .= "- " . ucfirst($entity) . ": $count\n";
}
$logContent .= "\n## Statut\n";
$logContent .= $allMigrated ? "✅ Migration réussie\n" : "⚠️ Migration partielle\n";

$logFile = __DIR__ . '/migration_log_' . date('Ymd_His') . '.md';
file_put_contents($logFile, $logContent);
echo "📄 Rapport sauvegardé: $logFile\n\n";
