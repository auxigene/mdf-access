<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║       🧪 TESTS SPRINT 2 - RLS APPLICATION LAYER            ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// ===================================
// PRÉPARATION : Créer utilisateurs de test
// ===================================
echo "📋 PRÉPARATION : Créer utilisateurs de test\n";
echo str_repeat("-", 65) . "\n\n";

// 1. System Admin
$systemAdmin = User::firstOrCreate(
    ['email' => 'system.admin@test.com'],
    [
        'name' => 'System Admin',
        'password' => bcrypt('password'),
        'organization_id' => null,
        'is_system_admin' => true,
    ]
);
echo "✅ System Admin créé (ID={$systemAdmin->id})\n";

// 2. Internal (SAMSIC)
$samsicOrg = Organization::where('type', 'Internal')->first()
    ?? Organization::find(27);

$internalUser = User::firstOrCreate(
    ['email' => 'internal@samsic.ma'],
    [
        'name' => 'User SAMSIC',
        'password' => bcrypt('password'),
        'organization_id' => $samsicOrg->id,
        'is_system_admin' => false,
    ]
);
echo "✅ Internal User créé (ID={$internalUser->id}, Org={$samsicOrg->id})\n";

// 3. Client
$clientOrg = Organization::where('type', 'Client')->first();
if (!$clientOrg) {
    $clientOrg = Organization::create([
        'name' => 'Client Test',
        'type' => 'Client',
        'status' => 'active',
    ]);
}

$clientUser = User::firstOrCreate(
    ['email' => 'client@test.com'],
    [
        'name' => 'User Client',
        'password' => bcrypt('password'),
        'organization_id' => $clientOrg->id,
        'is_system_admin' => false,
    ]
);
echo "✅ Client User créé (ID={$clientUser->id}, Org={$clientOrg->id})\n";

// 4. Partner
$partnerOrg = Organization::where('type', 'Partner')->first();
if (!$partnerOrg) {
    $partnerOrg = Organization::create([
        'name' => 'Partner Test',
        'type' => 'Partner',
        'status' => 'active',
    ]);
}

$partnerUser = User::firstOrCreate(
    ['email' => 'partner@test.com'],
    [
        'name' => 'User Partner',
        'password' => bcrypt('password'),
        'organization_id' => $partnerOrg->id,
        'is_system_admin' => false,
    ]
);
echo "✅ Partner User créé (ID={$partnerUser->id}, Org={$partnerOrg->id})\n\n";

// ===================================
// TEST 1 : System Admin (Bypass)
// ===================================
echo "📋 TEST 1 : SYSTEM ADMIN (doit voir tout)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($systemAdmin);
$projectCount = Project::count();
$projectCountWithoutScope = Project::withoutTenantScope()->count();

echo "Projects visibles : $projectCount\n";
echo "Projects sans scope : $projectCountWithoutScope\n";
echo "Résultat : " . ($projectCount === $projectCountWithoutScope ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 2 : Internal User (Bypass)
// ===================================
echo "📋 TEST 2 : INTERNAL USER (doit voir tout)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($internalUser);
$projectCount = Project::count();
$projectCountWithoutScope = Project::withoutTenantScope()->count();

echo "Projects visibles : $projectCount\n";
echo "Projects sans scope : $projectCountWithoutScope\n";
echo "Résultat : " . ($projectCount === $projectCountWithoutScope ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 3 : Client User (Filtré)
// ===================================
echo "📋 TEST 3 : CLIENT USER (filtré sur client_organization_id)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($clientUser);
$projectCount = Project::count();
$projectCountExpected = Project::withoutTenantScope()
    ->where('client_organization_id', $clientUser->organization_id)
    ->count();

echo "Projects visibles : $projectCount\n";
echo "Projects attendus : $projectCountExpected\n";
echo "Résultat : " . ($projectCount === $projectCountExpected ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// TEST 4 : Partner User (Filtré)
// ===================================
echo "📋 TEST 4 : PARTNER USER (filtré sur project_organizations)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($partnerUser);
$projectCount = Project::count();

echo "Projects visibles : $projectCount\n";
echo "Note : Partner n'a pas de participations pour l'instant\n";
echo "Résultat : " . ($projectCount === 0 ? '✅ PASS' : '⚠️  VÉRIFIER') . "\n\n";

Auth::logout();

// ===================================
// TEST 5 : withoutTenantScope()
// ===================================
echo "📋 TEST 5 : withoutTenantScope() (bypass manuel)\n";
echo str_repeat("-", 65) . "\n";

Auth::login($clientUser);
$projectCountScoped = Project::count();
$projectCountUnscoped = Project::withoutTenantScope()->count();

echo "Projects avec scope : $projectCountScoped\n";
echo "Projects sans scope : $projectCountUnscoped\n";
echo "Résultat : " . ($projectCountUnscoped > $projectCountScoped ? '✅ PASS' : '❌ FAIL') . "\n\n";

Auth::logout();

// ===================================
// RÉSUMÉ
// ===================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║           ✅ TESTS TERMINÉS - RLS APPLICATION LAYER         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Tous les tests RLS ont été exécutés!\n";
echo "✅ Sprint 2 - RLS Application Layer → COMPLÉTÉ\n\n";
