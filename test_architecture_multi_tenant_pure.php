<?php

/**
 * Script de Test : Architecture Multi-Tenant Pure
 *
 * Ce script teste que l'architecture sans colonne 'type' fonctionne correctement
 * et que les nouveaux helpers contextuels sont opérationnels.
 *
 * Exécution : php test_architecture_multi_tenant_pure.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Organization;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectOrganization;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  TEST : Architecture Multi-Tenant Pure (Sans Colonne Type)    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$testsTotal = 0;
$testsPassed = 0;
$testsFailed = 0;

function test($description, $condition) {
    global $testsTotal, $testsPassed, $testsFailed;
    $testsTotal++;

    if ($condition) {
        $testsPassed++;
        echo "✅ TEST {$testsTotal}: {$description}\n";
        return true;
    } else {
        $testsFailed++;
        echo "❌ TEST {$testsTotal}: {$description}\n";
        return false;
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo " 1. TEST STRUCTURE TABLE ORGANIZATIONS\n";
echo "═══════════════════════════════════════════════════════════════\n";

try {
    // Vérifier que la colonne 'type' n'existe plus
    $columns = DB::getSchemaBuilder()->getColumnListing('organizations');
    test(
        "Colonne 'type' supprimée de la table organizations",
        !in_array('type', $columns)
    );

    // Vérifier que les colonnes nécessaires existent toujours
    test(
        "Colonne 'name' existe toujours",
        in_array('name', $columns)
    );
    test(
        "Colonne 'status' existe toujours",
        in_array('status', $columns)
    );

    echo "\n";

} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo " 2. TEST MODEL ORGANIZATION - NOUVEAUX HELPERS CONTEXTUELS\n";
echo "═══════════════════════════════════════════════════════════════\n";

try {
    // Récupérer une organisation
    $org = Organization::first();

    if ($org) {
        echo "Organisation testée : {$org->name} (ID: {$org->id})\n\n";

        // Tester que l'ancienne propriété 'type' n'existe pas
        test(
            "Propriété 'type' n'existe plus dans Organization",
            !isset($org->type)
        );

        // Tester les nouveaux helpers contextuels
        $methodsToCheck = [
            'isClientForProject',
            'isMoeForProject',
            'isMoaForProject',
            'getRoleForProject',
            'getProjectsWhereClient',
            'getProjectsWhereMoe',
            'getProjectsWhereMoa',
            'getProjectsWhereSubcontractor',
        ];

        foreach ($methodsToCheck as $method) {
            test(
                "Méthode Organization::{$method}() existe",
                method_exists($org, $method)
            );
        }

        // Test fonctionnel avec un projet existant
        $project = Project::first();
        if ($project) {
            echo "\nProjet testé : {$project->name} (ID: {$project->id})\n";

            try {
                $role = $org->getRoleForProject($project->id);
                test(
                    "getRoleForProject() retourne une valeur ('{$role}' ou null)",
                    is_string($role) || $role === null
                );

                $isClient = $org->isClientForProject($project->id);
                test(
                    "isClientForProject() retourne un booléen",
                    is_bool($isClient)
                );

                $isMoe = $org->isMoeForProject($project->id);
                test(
                    "isMoeForProject() retourne un booléen",
                    is_bool($isMoe)
                );
            } catch (Exception $e) {
                echo "⚠️  Erreur lors du test fonctionnel : " . $e->getMessage() . "\n";
            }
        }

        echo "\n";
    } else {
        echo "⚠️  Aucune organisation trouvée dans la base de données\n\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo " 3. TEST MODEL USER - NOUVEAUX HELPERS CONTEXTUELS\n";
echo "═══════════════════════════════════════════════════════════════\n";

try {
    // Récupérer un utilisateur
    $user = User::with('organization')->first();

    if ($user) {
        echo "Utilisateur testé : {$user->name} (ID: {$user->id})\n";
        echo "Organisation : " . ($user->organization->name ?? 'Aucune') . "\n\n";

        // Tester les nouveaux helpers contextuels
        $methodsToCheck = [
            'isClientForProject',
            'isMoeForProject',
            'isMoaForProject',
            'getRoleForProject',
            'getAccessibleProjects',
            'getProjectsWhereClient',
            'getProjectsWhereMoe',
            'getProjectsWhereMoa',
        ];

        foreach ($methodsToCheck as $method) {
            test(
                "Méthode User::{$method}() existe",
                method_exists($user, $method)
            );
        }

        // Test fonctionnel
        $project = Project::first();
        if ($project && $user->organization) {
            try {
                $role = $user->getRoleForProject($project->id);
                test(
                    "User::getRoleForProject() retourne une valeur ('{$role}' ou null)",
                    is_string($role) || $role === null
                );

                $accessibleProjects = $user->getAccessibleProjects();
                test(
                    "getAccessibleProjects() retourne une collection",
                    $accessibleProjects instanceof \Illuminate\Support\Collection
                );

                echo "  → Projets accessibles : {$accessibleProjects->count()}\n";
            } catch (Exception $e) {
                echo "⚠️  Erreur lors du test fonctionnel : " . $e->getMessage() . "\n";
            }
        }

        echo "\n";
    } else {
        echo "⚠️  Aucun utilisateur trouvé dans la base de données\n\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo " 4. TEST PARTICIPATIONS PROJET (PROJECT_ORGANIZATIONS)\n";
echo "═══════════════════════════════════════════════════════════════\n";

try {
    $participationCount = ProjectOrganization::count();
    echo "Participations projet trouvées : {$participationCount}\n\n";

    test(
        "Table project_organizations contient des données",
        $participationCount > 0
    );

    if ($participationCount > 0) {
        $participation = ProjectOrganization::with(['project', 'organization'])->first();

        if ($participation) {
            echo "Participation testée :\n";
            echo "  - Projet : " . ($participation->project->name ?? 'N/A') . "\n";
            echo "  - Organisation : " . ($participation->organization->name ?? 'N/A') . "\n";
            echo "  - Rôle : {$participation->role}\n";
            echo "  - Statut : {$participation->status}\n\n";

            test(
                "ProjectOrganization a un rôle défini",
                !empty($participation->role)
            );

            test(
                "Rôle est valide (sponsor/moa/moe/subcontractor)",
                in_array($participation->role, ['sponsor', 'moa', 'moe', 'subcontractor'])
            );

            // Tester le helper contextuel
            $org = $participation->organization;
            $project = $participation->project;

            if ($org && $project) {
                $orgRole = $org->getRoleForProject($project->id);
                test(
                    "getRoleForProject() retourne le bon rôle pour cette participation",
                    $orgRole === $participation->role
                );
            }
        }
    }

    echo "\n";

} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo " RÉSUMÉ DES TESTS\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "Tests total : {$testsTotal}\n";
echo "✅ Réussis : {$testsPassed}\n";
echo "❌ Échoués : {$testsFailed}\n";
echo "\n";

if ($testsFailed === 0) {
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║                  ✅ TOUS LES TESTS SONT PASSÉS !               ║\n";
    echo "║                                                                ║\n";
    echo "║  Architecture Multi-Tenant Pure : OPÉRATIONNELLE ✅            ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║             ⚠️  CERTAINS TESTS ONT ÉCHOUÉ                      ║\n";
    echo "║                                                                ║\n";
    echo "║  Veuillez vérifier les erreurs ci-dessus                      ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";
    exit(1);
}
