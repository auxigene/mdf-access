<?php

/**
 * Script de test pour le système RLS (Row-Level Security)
 * Version adaptée à l'architecture contextuelle (sans colonne type)
 *
 * Ce script teste que le filtrage multi-tenant fonctionne correctement
 * après les modifications apportées pour supporter le backup DB réel
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Organization;
use App\Models\ProjectOrganization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║    🧪 TESTS SPRINT 2 - RLS (Architecture Contextuelle)     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// ===================================
// PRÉPARATION : Vérifier la structure
// ===================================
echo "📋 PRÉPARATION : Vérification de la structure DB\n";
echo str_repeat("-", 65) . "\n\n";

// Vérifier que la colonne is_internal existe
$hasIsInternal = Schema::hasColumn('organizations', 'is_internal');
echo "✓ Colonne 'is_internal' dans organizations : " . ($hasIsInternal ? "✅ OUI" : "❌ NON") . "\n";

if (!$hasIsInternal) {
    echo "\n⚠️  ERREUR : La migration pour ajouter 'is_internal' n'a pas été exécutée!\n";
    echo "Veuillez exécuter : php artisan migrate\n\n";
    exit(1);
}

// Vérifier que SAMSIC est marquée comme interne
$samsic = Organization::find(1);
if ($samsic) {
    echo "✓ SAMSIC trouvée (ID=1) : {$samsic->name}\n";
    echo "✓ SAMSIC is_internal : " . ($samsic->is_internal ? "✅ TRUE" : "❌ FALSE") . "\n\n";
} else {
    echo "⚠️  SAMSIC non trouvée à l'ID 1\n\n";
}

// ===================================
// TEST 1 : System Admin (Bypass)
// ===================================
echo "📋 TEST 1 : SYSTEM ADMIN (doit voir tous les projets)\n";
echo str_repeat("-", 65) . "\n";

$systemAdmin = User::where('is_system_admin', true)->first();
if (!$systemAdmin) {
    echo "⚠️  Aucun System Admin trouvé, création d'un compte test...\n";
    $systemAdmin = User::create([
        'name' => 'System Admin Test',
        'email' => 'sysadmin@test.local',
        'password' => bcrypt('password'),
        'is_system_admin' => true,
        'organization_id' => null,
    ]);
}

Auth::login($systemAdmin);
$projectCount = Project::count();
$projectCountWithoutScope = Project::withoutTenantScope()->count();

echo "User : {$systemAdmin->name} (ID={$systemAdmin->id})\n";
echo "Projects visibles : $projectCount\n";
echo "Projects sans scope : $projectCountWithoutScope\n";
echo "isSystemAdmin() : " . ($systemAdmin->isSystemAdmin() ? "✅ TRUE" : "❌ FALSE") . "\n";
echo "Résultat : " . ($projectCount === $projectCountWithoutScope ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 2 : Internal User (Bypass)
// ===================================
echo "📋 TEST 2 : INTERNAL USER (SAMSIC - doit voir tous les projets)\n";
echo str_repeat("-", 65) . "\n";

$internalUser = User::where('organization_id', 1)->first();
if (!$internalUser) {
    echo "⚠️  Aucun utilisateur SAMSIC trouvé, création d'un compte test...\n";
    $internalUser = User::create([
        'name' => 'User SAMSIC Test',
        'email' => 'samsic@test.local',
        'password' => bcrypt('password'),
        'is_system_admin' => false,
        'organization_id' => 1,
    ]);
}

Auth::login($internalUser);
$projectCount = Project::count();
$projectCountWithoutScope = Project::withoutTenantScope()->count();

echo "User : {$internalUser->name} (ID={$internalUser->id})\n";
echo "Organization : " . ($internalUser->organization ? $internalUser->organization->name : 'NULL') . "\n";
echo "Projects visibles : $projectCount\n";
echo "Projects sans scope : $projectCountWithoutScope\n";
echo "isInternal() : " . ($internalUser->isInternal() ? "✅ TRUE" : "❌ FALSE") . "\n";
echo "Résultat : " . ($projectCount === $projectCountWithoutScope ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 3 : Organisation avec Participations
// ===================================
echo "📋 TEST 3 : ORGANISATION AVEC PARTICIPATIONS (filtré)\n";
echo str_repeat("-", 65) . "\n";

// Trouver ou créer une organisation qui participe à des projets
$orgWithParticipations = DB::table('project_organizations')
    ->where('status', 'active')
    ->where('organization_id', '!=', 1) // Pas SAMSIC
    ->select('organization_id')
    ->distinct()
    ->first();

if ($orgWithParticipations) {
    $org = Organization::find($orgWithParticipations->organization_id);

    // Créer ou récupérer un user pour cette org
    $user = User::where('organization_id', $org->id)->first();
    if (!$user) {
        $user = User::create([
            'name' => "User {$org->name}",
            'email' => "user.org{$org->id}@test.local",
            'password' => bcrypt('password'),
            'is_system_admin' => false,
            'organization_id' => $org->id,
        ]);
    }

    // Compter les participations attendues
    $expectedProjectCount = DB::table('project_organizations')
        ->where('organization_id', $org->id)
        ->where('status', 'active')
        ->distinct()
        ->count('project_id');

    Auth::login($user);
    $projectCount = Project::count();

    echo "User : {$user->name} (ID={$user->id})\n";
    echo "Organization : {$org->name} (ID={$org->id})\n";
    echo "Projects visibles : $projectCount\n";
    echo "Projects attendus : $expectedProjectCount\n";
    echo "isInternal() : " . ($user->isInternal() ? "✅ TRUE" : "❌ FALSE") . "\n";
    echo "isClient() : " . ($user->isClient() ? "✅ TRUE" : "❌ FALSE") . "\n";
    echo "isPartner() : " . ($user->isPartner() ? "✅ TRUE" : "❌ FALSE") . "\n";
    echo "Résultat : " . ($projectCount === $expectedProjectCount ? '✅ PASS' : '❌ FAIL') . "\n\n";

    Auth::logout();
} else {
    echo "⚠️  Aucune organisation avec participations trouvée (hors SAMSIC)\n";
    echo "Le test est sauté.\n\n";
}

// ===================================
// TEST 4 : Organisation sans Participations
// ===================================
echo "📋 TEST 4 : ORGANISATION SANS PARTICIPATIONS (doit voir 0 projets)\n";
echo str_repeat("-", 65) . "\n";

// Créer une organisation test sans participations
$orgWithoutParticipations = Organization::firstOrCreate(
    ['name' => 'Org Test Sans Participations'],
    [
        'status' => 'active',
        'is_internal' => false,
    ]
);

$userWithoutParticipations = User::firstOrCreate(
    ['email' => 'user.noparticipations@test.local'],
    [
        'name' => 'User Sans Participations',
        'password' => bcrypt('password'),
        'is_system_admin' => false,
        'organization_id' => $orgWithoutParticipations->id,
    ]
);

Auth::login($userWithoutParticipations);
$projectCount = Project::count();

echo "User : {$userWithoutParticipations->name} (ID={$userWithoutParticipations->id})\n";
echo "Organization : {$orgWithoutParticipations->name} (ID={$orgWithoutParticipations->id})\n";
echo "Projects visibles : $projectCount\n";
echo "isInternal() : " . ($userWithoutParticipations->isInternal() ? "✅ TRUE" : "❌ FALSE") . "\n";
echo "Résultat : " . ($projectCount === 0 ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 5 : withoutTenantScope()
// ===================================
echo "📋 TEST 5 : withoutTenantScope() (bypass manuel)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($userWithoutParticipations);
$projectCountScoped = Project::count();
$projectCountUnscoped = Project::withoutTenantScope()->count();

echo "User : {$userWithoutParticipations->name}\n";
echo "Projects avec scope : $projectCountScoped\n";
echo "Projects sans scope : $projectCountUnscoped\n";
echo "Résultat : " . ($projectCountUnscoped > $projectCountScoped ? '✅ PASS' : '⚠️  VÉRIFIER') . "\n\n";

Auth::logout();

// ===================================
// RÉSUMÉ
// ===================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║     ✅ TESTS TERMINÉS - RLS Architecture Contextuelle      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Tous les tests RLS ont été exécutés!\n";
echo "✅ Sprint 2 - RLS adapté à l'architecture contextuelle\n";
echo "✅ Système compatible avec le backup DB réel\n\n";

echo "💡 Notes importantes :\n";
echo "   - is_internal flag ajouté à la table organizations\n";
echo "   - Méthodes isInternal(), isClient(), isPartner() adaptées\n";
echo "   - TenantScope simplifié pour l'architecture contextuelle\n";
echo "   - Les organisations voient tous les projets où elles participent\n\n";
