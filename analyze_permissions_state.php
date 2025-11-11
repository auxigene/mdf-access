<?php

/**
 * Script d'analyse de l'état actuel des permissions
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  ANALYSE : État Actuel du Système de Permissions             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// 1. Compter permissions totales
$totalPermissions = DB::table('permissions')->count();
echo "📊 Permissions totales : {$totalPermissions}\n\n";

if ($totalPermissions > 0) {
    // 2. Ressources uniques dans permissions
    $uniqueResources = DB::table('permissions')
        ->select('resource')
        ->distinct()
        ->whereNotNull('resource')
        ->orderBy('resource')
        ->pluck('resource');

    echo "📦 Ressources uniques trouvées : " . $uniqueResources->count() . "\n";
    foreach ($uniqueResources as $resource) {
        $count = DB::table('permissions')->where('resource', $resource)->count();
        echo "   - {$resource} : {$count} permissions\n";
    }
    echo "\n";

    // 3. Actions uniques dans permissions
    $uniqueActions = DB::table('permissions')
        ->select('action')
        ->distinct()
        ->whereNotNull('action')
        ->orderBy('action')
        ->pluck('action');

    echo "⚡ Actions uniques trouvées : " . $uniqueActions->count() . "\n";
    foreach ($uniqueActions as $action) {
        $count = DB::table('permissions')->where('action', $action)->count();
        echo "   - {$action} : {$count} permissions\n";
    }
    echo "\n";

    // 4. Vérifier si resource_id et action_id sont remplis
    $withResourceId = DB::table('permissions')->whereNotNull('resource_id')->count();
    $withActionId = DB::table('permissions')->whereNotNull('action_id')->count();

    echo "🔗 État des nouvelles colonnes :\n";
    echo "   - resource_id remplis : {$withResourceId}/{$totalPermissions}\n";
    echo "   - action_id remplis : {$withActionId}/{$totalPermissions}\n\n";
}

// 5. État des nouvelles tables
$resourcesCount = DB::table('resources')->count();
$actionsCount = DB::table('actions')->count();
$resourceActionsCount = DB::table('resource_actions')->count();

echo "🗄️  État des nouvelles tables :\n";
echo "   - resources : {$resourcesCount} enregistrements\n";
echo "   - actions : {$actionsCount} enregistrements\n";
echo "   - resource_actions : {$resourceActionsCount} enregistrements\n\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  CONCLUSION                                                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

if ($totalPermissions === 0) {
    echo "❌ La table permissions est VIDE\n";
    echo "   → Action requise : Exécuter PermissionsSeeder\n\n";
} elseif ($resourcesCount === 0 || $actionsCount === 0) {
    echo "⚠️  Permissions existent mais tables resources/actions vides\n";
    echo "   → Action requise : Migration des données\n\n";
} elseif ($withResourceId < $totalPermissions || $withActionId < $totalPermissions) {
    echo "⚠️  Tables créées mais permissions pas liées aux IDs\n";
    echo "   → Action requise : Mise à jour des IDs\n\n";
} else {
    echo "✅ Système de permissions flexibles OPÉRATIONNEL\n\n";
}
