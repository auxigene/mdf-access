<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajouter colonnes resource_id et action_id aux permissions
 *
 * CONTEXTE :
 * - Les colonnes ont été supprimées par erreur par la migration cleanup
 * - Cette migration les recrée et repopule les données
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Étape 1 : Ajouter les colonnes
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('resource_id')->nullable()->after('slug')
                  ->constrained('acl_resources')->onDelete('cascade');
            $table->foreignId('action_id')->nullable()->after('resource_id')
                  ->constrained('actions')->onDelete('cascade');
            $table->boolean('is_active')->default(true)->after('action_id');
        });

        echo "\n✅ Colonnes ajoutées : resource_id, action_id, is_active\n";

        // Étape 2 : Mapper les permissions existantes
        $permissions = DB::table('permissions')->get();
        $mapped = 0;

        echo "🔄 Mapping des permissions...\n";

        foreach ($permissions as $permission) {
            // Parser le slug (format: "action_resource", ex: "view_portfolios")
            $parts = explode('_', $permission->slug, 2);

            if (count($parts) !== 2) {
                echo "   ⚠️  Skip {$permission->slug} (format invalide)\n";
                continue;
            }

            // IMPORTANT: Le format est "action_resource", pas "resource_action"
            [$actionSlug, $resourceSlug] = $parts;

            // Trouver resource_id et action_id
            $resource = DB::table('acl_resources')->where('slug', $resourceSlug)->first();
            $action = DB::table('actions')->where('slug', $actionSlug)->first();

            if (!$resource) {
                echo "   ⚠️  Resource '{$resourceSlug}' introuvable pour {$permission->slug}\n";
                continue;
            }

            if (!$action) {
                echo "   ⚠️  Action '{$actionSlug}' introuvable pour {$permission->slug}\n";
                continue;
            }

            // Mettre à jour la permission
            DB::table('permissions')
                ->where('id', $permission->id)
                ->update([
                    'resource_id' => $resource->id,
                    'action_id' => $action->id,
                    'is_active' => true,
                ]);

            $mapped++;
        }

        echo "   ✓ {$mapped} permissions mappées\n";
        echo "\n✅ Migration terminée avec succès\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['resource_id']);
            $table->dropForeign(['action_id']);
            $table->dropColumn(['resource_id', 'action_id', 'is_active']);
        });
    }
};
