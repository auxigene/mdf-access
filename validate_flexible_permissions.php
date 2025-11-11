<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\AclResource;
use App\Models\Action;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║  VALIDATION : Système de Permissions Flexibles               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$errors = [];

// ===================================
// TEST 1 : Compter les enregistrements
// ===================================
echo "📊 Test 1 : Vérification des données...\n";

$resourcesCount = AclResource::count();
$actionsCount = Action::count();
$permissionsCount = Permission::count();
$applicabilityCount = DB::table('acl_resource_actions')->count();

echo "   ✓ ACL Resources : {$resourcesCount}\n";
echo "   ✓ Actions : {$actionsCount}\n";
echo "   ✓ Permissions : {$permissionsCount}\n";
echo "   ✓ Applicabilités : {$applicabilityCount}\n";

if ($resourcesCount == 0) $errors[] = "Aucune ressource ACL trouvée";
if ($actionsCount == 0) $errors[] = "Aucune action trouvée";
if ($permissionsCount == 0) $errors[] = "Aucune permission trouvée";

// ===================================
// TEST 2 : Vérifier les relations
// ===================================
echo "\n🔗 Test 2 : Vérification des relations...\n";

$permission = Permission::with(['aclResource', 'action'])->first();
if ($permission) {
    echo "   Permission : {$permission->name}\n";
    echo "   - Resource : " . ($permission->aclResource?->name ?? '❌ NULL') . "\n";
    echo "   - Action : " . ($permission->action?->name ?? '❌ NULL') . "\n";

    if (!$permission->aclResource) $errors[] = "Relation aclResource non fonctionnelle";
    if (!$permission->action) $errors[] = "Relation action non fonctionnelle";
} else {
    $errors[] = "Aucune permission disponible pour tester les relations";
}

// ===================================
// TEST 3 : Tester les scopes
// ===================================
echo "\n🔍 Test 3 : Vérification des scopes...\n";

$projectsPerms = Permission::forResource('projects')->count();
echo "   ✓ Permissions 'projects' : {$projectsPerms}\n";

$viewPerms = Permission::forAction('view')->count();
echo "   ✓ Permissions 'view' : {$viewPerms}\n";

$projectViewPerms = Permission::forResourceAction('projects', 'view')->count();
echo "   ✓ Permissions 'projects' + 'view' : {$projectViewPerms}\n";

if ($projectsPerms == 0) $errors[] = "Scope forResource() ne fonctionne pas";
if ($viewPerms == 0) $errors[] = "Scope forAction() ne fonctionne pas";

// ===================================
// TEST 4 : Matrice d'applicabilité
// ===================================
echo "\n⭐ Test 4 : Vérification matrice d'applicabilité...\n";

$projectsResource = AclResource::where('slug', 'projects')->first();
if ($projectsResource) {
    $applicableActions = $projectsResource->applicableActions()->pluck('slug')->toArray();
    echo "   Actions applicables à 'projects' : " . implode(', ', $applicableActions) . "\n";

    // Vérifier que "approve" est applicable à projects
    if (!in_array('approve', $applicableActions)) {
        $errors[] = "Action 'approve' devrait être applicable à 'projects'";
    }

    // Vérifier qu'il y a au moins 5 actions applicables
    if (count($applicableActions) < 5) {
        $errors[] = "Projects devrait avoir au moins 5 actions applicables";
    }
} else {
    $errors[] = "Ressource 'projects' introuvable";
}

$usersResource = AclResource::where('slug', 'users')->first();
if ($usersResource) {
    $applicableActions = $usersResource->applicableActions()->pluck('slug')->toArray();
    echo "   Actions applicables à 'users' : " . implode(', ', $applicableActions) . "\n";

    // Vérifier que les actions de base sont présentes
    $requiredActions = ['view', 'create', 'edit', 'delete'];
    foreach ($requiredActions as $action) {
        if (!in_array($action, $applicableActions)) {
            $errors[] = "Action '{$action}' devrait être applicable à 'users'";
        }
    }
} else {
    $errors[] = "Ressource 'users' introuvable";
}

// ===================================
// TEST 5 : Helpers
// ===================================
echo "\n🛠️  Test 5 : Vérification des helpers...\n";

$permission = Permission::with(['aclResource', 'action'])->first();
if ($permission) {
    echo "   - getResourceLabel() : " . $permission->getResourceLabel() . "\n";
    echo "   - getActionLabel() : " . $permission->getActionLabel() . "\n";
    echo "   - getFullDescription() : " . $permission->getFullDescription() . "\n";
}

// ===================================
// TEST 6 : Vérifier que colonnes VARCHAR sont supprimées
// ===================================
echo "\n🗑️  Test 6 : Vérification suppression colonnes VARCHAR...\n";

use Illuminate\Support\Facades\Schema;

$hasResourceCol = Schema::hasColumn('permissions', 'resource');
$hasActionCol = Schema::hasColumn('permissions', 'action');

if ($hasResourceCol) {
    $errors[] = "Colonne VARCHAR 'resource' existe encore (devrait être supprimée)";
    echo "   ❌ Colonne 'resource' existe encore\n";
} else {
    echo "   ✓ Colonne 'resource' supprimée\n";
}

if ($hasActionCol) {
    $errors[] = "Colonne VARCHAR 'action' existe encore (devrait être supprimée)";
    echo "   ❌ Colonne 'action' existe encore\n";
} else {
    echo "   ✓ Colonne 'action' supprimée\n";
}

// ===================================
// TEST 7 : Vérifier nouvelles colonnes
// ===================================
echo "\n➕ Test 7 : Vérification nouvelles colonnes...\n";

$hasResourceIdCol = Schema::hasColumn('permissions', 'resource_id');
$hasActionIdCol = Schema::hasColumn('permissions', 'action_id');
$hasIsActiveCol = Schema::hasColumn('permissions', 'is_active');

echo "   " . ($hasResourceIdCol ? "✓" : "❌") . " Colonne 'resource_id'\n";
echo "   " . ($hasActionIdCol ? "✓" : "❌") . " Colonne 'action_id'\n";
echo "   " . ($hasIsActiveCol ? "✓" : "❌") . " Colonne 'is_active'\n";

if (!$hasResourceIdCol) $errors[] = "Colonne 'resource_id' manquante";
if (!$hasActionIdCol) $errors[] = "Colonne 'action_id' manquante";
if (!$hasIsActiveCol) $errors[] = "Colonne 'is_active' manquante";

// ===================================
// RÉSUMÉ
// ===================================
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
if (count($errors) === 0) {
    echo "║  ✅ TOUS LES TESTS ONT RÉUSSI                                 ║\n";
    echo "║  Système de Permissions Flexibles opérationnel                ║\n";
} else {
    echo "║  ❌ ERREURS DÉTECTÉES                                          ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    foreach ($errors as $error) {
        echo "   ❌ {$error}\n";
    }
    exit(1);
}
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
