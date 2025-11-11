<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Vérification structure table permissions :\n\n";

if (Schema::hasTable('permissions')) {
    $columns = Schema::getColumnListing('permissions');
    echo "Colonnes trouvées dans 'permissions' :\n";
    foreach ($columns as $col) {
        echo "  - {$col}\n";
    }

    echo "\nNombre d'enregistrements : " . DB::table('permissions')->count() . "\n";
} else {
    echo "❌ La table 'permissions' n'existe pas\n";
}
