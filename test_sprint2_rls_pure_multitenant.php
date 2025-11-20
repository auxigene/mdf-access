<?php

/**
 * Script de test pour le système RLS (Row-Level Security)
 * Architecture Multi-Tenant Pure
 *
 * Ce script teste que le filtrage multi-tenant fonctionne correctement
 * avec une architecture PURE où seul System Admin a un bypass
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
echo "║      🧪 TESTS SPRINT 2 - RLS Multi-Tenant PUR              ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "🎯 Architecture Multi-Tenant Pure :\n";
echo "   - System Admin : SEUL bypass (voit tout)\n";
echo "   - Toutes les organisations : filtrées sur participations\n";
echo "   - Pas d'exception pour SAMSIC ou autre\n\n";

// ===================================
// TEST 1 : System Admin (SEUL BYPASS)
// ===================================
echo "📋 TEST 1 : SYSTEM ADMIN (seul bypass - doit voir tous les projets)\n";
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
// TEST 2 : SAMSIC (Filtrée comme les autres)
// ===================================
echo "📋 TEST 2 : SAMSIC (filtrée comme toute organisation)\n";
echo str_repeat("-", 65) . "\n";

$samsic = Organization::find(1);
if (!$samsic) {
    echo "⚠️  Organisation SAMSIC (ID=1) non trouvée\n\n";
} else {
    echo "Organisation : {$samsic->name} (ID={$samsic->id})\n";

    // Compter les participations SAMSIC
    $samsicParticipations = DB::table('project_organizations')
        ->where('organization_id', $samsic->id)
        ->where('status', 'active')
        ->distinct()
        ->count('project_id');

    echo "Participations actives : $samsicParticipations projets\n";

    // Créer ou récupérer un user SAMSIC
    $samsicUser = User::where('organization_id', $samsic->id)
                      ->where('is_system_admin', false)
                      ->first();

    if (!$samsicUser) {
        $samsicUser = User::create([
            'name' => 'User SAMSIC Test',
            'email' => 'samsic.user@test.local',
            'password' => bcrypt('password'),
            'is_system_admin' => false,
            'organization_id' => $samsic->id,
        ]);
    }

    Auth::login($samsicUser);
    $projectCount = Project::count();

    echo "User : {$samsicUser->name} (ID={$samsicUser->id})\n";
    echo "Projects visibles : $projectCount\n";
    echo "Projects attendus : $samsicParticipations\n";
    echo "isSystemAdmin() : " . ($samsicUser->isSystemAdmin() ? "✅ TRUE" : "❌ FALSE") . "\n";

    if ($projectCount === $samsicParticipations) {
        echo "Résultat : ✅ PASS - SAMSIC est filtrée (multi-tenant pur)\n\n";
    } else {
        echo "Résultat : ❌ FAIL - SAMSIC ne devrait voir que ses participations\n\n";
    }

    Auth::logout();
}

// ===================================
// TEST 3 : Organisation avec Participations
// ===================================
echo "📋 TEST 3 : ORGANISATION AVEC PARTICIPATIONS\n";
echo str_repeat("-", 65) . "\n";

// Trouver une organisation (hors SAMSIC) qui participe à des projets
$orgWithParticipations = DB::table('project_organizations')
    ->where('status', 'active')
    ->where('organization_id', '!=', 1) // Pas SAMSIC (déjà testée)
    ->select('organization_id')
    ->distinct()
    ->first();

if ($orgWithParticipations) {
    $org = Organization::find($orgWithParticipations->organization_id);

    // Compter les participations attendues
    $expectedProjectCount = DB::table('project_organizations')
        ->where('organization_id', $org->id)
        ->where('status', 'active')
        ->distinct()
        ->count('project_id');

    // Créer ou récupérer un user
    $user = User::where('organization_id', $org->id)
                ->where('is_system_admin', false)
                ->first();

    if (!$user) {
        $user = User::create([
            'name' => "User {$org->name}",
            'email' => "user.org{$org->id}@test.local",
            'password' => bcrypt('password'),
            'is_system_admin' => false,
            'organization_id' => $org->id,
        ]);
    }

    Auth::login($user);
    $projectCount = Project::count();

    echo "User : {$user->name} (ID={$user->id})\n";
    echo "Organization : {$org->name} (ID={$org->id})\n";
    echo "Participations actives : $expectedProjectCount projets\n";
    echo "Projects visibles : $projectCount\n";
    echo "isSystemAdmin() : " . ($user->isSystemAdmin() ? "✅ TRUE" : "❌ FALSE") . "\n";
    echo "Résultat : " . ($projectCount === $expectedProjectCount ? '✅ PASS' : '❌ FAIL') . "\n\n";

    Auth::logout();
} else {
    echo "⚠️  Aucune organisation avec participations trouvée (hors SAMSIC)\n\n";
}

// ===================================
// TEST 4 : Organisation SANS Participations
// ===================================
echo "📋 TEST 4 : ORGANISATION SANS PARTICIPATIONS (doit voir 0 projets)\n";
echo str_repeat("-", 65) . "\n";

// Créer une organisation test sans participations
$orgWithoutParticipations = Organization::firstOrCreate(
    ['name' => 'Org Test Sans Participations'],
    [
        'status' => 'active',
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
echo "isSystemAdmin() : " . ($userWithoutParticipations->isSystemAdmin() ? "✅ TRUE" : "❌ FALSE") . "\n";
echo "Résultat : " . ($projectCount === 0 ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 5 : withoutTenantScope()
// ===================================
echo "📋 TEST 5 : withoutTenantScope() (bypass manuel du scope)\n";
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
echo "║        ✅ TESTS TERMINÉS - RLS Multi-Tenant PUR            ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Tous les tests RLS ont été exécutés!\n";
echo "✅ Sprint 2 - Architecture Multi-Tenant PURE\n";
echo "✅ Aucune exception organisationnelle\n\n";

echo "💡 Principes de l'Architecture Multi-Tenant Pure :\n\n";

echo "1. 🔐 System Admin (is_system_admin)\n";
echo "   → SEUL cas de bypass total\n";
echo "   → Voit TOUS les projets sans restriction\n";
echo "   → Pas lié à une organisation spécifique\n\n";

echo "2. 🏢 Toutes les Organisations (y compris SAMSIC)\n";
echo "   → Filtrées sur leurs participations dans project_organizations\n";
echo "   → Ne voient QUE les projets où elles participent activement\n";
echo "   → Pas d'exception, pas de privilège spécial\n\n";

echo "3. 📊 Isolation des Données\n";
echo "   → Chaque organisation voit uniquement ses données\n";
echo "   → Les rôles (sponsor, moa, moe, subcontractor) sont contextuels\n";
echo "   → Une organisation peut avoir différents rôles sur différents projets\n\n";

echo "4. 🎯 Cas d'Usage SAMSIC\n";
echo "   → Si SAMSIC doit tout voir : attribuer is_system_admin aux users\n";
echo "   → OU : ajouter SAMSIC dans project_organizations de tous les projets\n";
echo "   → Pas de logique spéciale dans le code (multi-tenant pur)\n\n";
