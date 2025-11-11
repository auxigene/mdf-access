<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Nettoyage Permissions FK Avant ACL
 *
 * PROBLÈME :
 * - La migration 2025_11_09_212445 a été partiellement exécutée
 * - Les colonnes resource_id/action_id existent avec FK vers mauvaises tables
 * - permissions.resource_id → resources (PMBOK) au lieu de acl_resources
 *
 * SOLUTION :
 * - Supprimer les FK constraints existantes
 * - Supprimer les colonnes resource_id, action_id, is_active
 * - Permettre à populate_acl_permissions_system de les recréer correctement
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si les colonnes existent avant de les supprimer
        if (Schema::hasColumn('permissions', 'resource_id')) {
            Schema::table('permissions', function (Blueprint $table) {
                // Supprimer les FK constraints
                $table->dropForeign(['resource_id']);
                $table->dropForeign(['action_id']);

                // Supprimer les colonnes
                $table->dropColumn(['resource_id', 'action_id', 'is_active']);
            });

            echo "\n✅ Nettoyage permissions table effectué\n";
            echo "   - FK constraints supprimées (resource_id, action_id)\n";
            echo "   - Colonnes supprimées (resource_id, action_id, is_active)\n\n";
        } else {
            echo "\n✅ Aucun nettoyage nécessaire - colonnes n'existent pas\n\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne peut pas vraiment revenir en arrière car on ne sait pas
        // vers quelle table pointaient les FK originales
        echo "\n⚠️  Rollback non implémenté - migration de nettoyage\n\n";
    }
};
