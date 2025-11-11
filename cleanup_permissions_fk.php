<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "🔧 Nettoyage des contraintes FK sur table permissions...\n\n";

try {
    // Vérifier si les colonnes existent
    if (Schema::hasColumn('permissions', 'resource_id')) {
        echo "📝 Suppression des contraintes FK existantes...\n";

        // Supprimer les FK constraints
        DB::statement('ALTER TABLE permissions DROP CONSTRAINT IF EXISTS permissions_resource_id_foreign');
        DB::statement('ALTER TABLE permissions DROP CONSTRAINT IF EXISTS permissions_action_id_foreign');

        echo "   ✓ FK constraint permissions_resource_id_foreign supprimée\n";
        echo "   ✓ FK constraint permissions_action_id_foreign supprimée\n";

        // Supprimer les colonnes
        Schema::table('permissions', function ($table) {
            $table->dropColumn(['resource_id', 'action_id', 'is_active']);
        });

        echo "   ✓ Colonnes resource_id, action_id, is_active supprimées\n";
        echo "\n✅ Nettoyage effectué avec succès!\n";
    } else {
        echo "✅ Aucun nettoyage nécessaire - colonnes n'existent pas\n";
    }

} catch (\Exception $e) {
    echo "\n❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
