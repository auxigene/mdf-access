<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Vérification structure table resources :\n\n";

if (Schema::hasTable('resources')) {
    $columns = Schema::getColumnListing('resources');
    echo "Colonnes trouvées dans 'resources' :\n";
    foreach ($columns as $col) {
        echo "  - {$col}\n";
    }

    echo "\nNombre d'enregistrements : " . DB::table('resources')->count() . "\n";
} else {
    echo "❌ La table 'resources' n'existe pas\n";
}

echo "\n";

if (Schema::hasTable('actions')) {
    $columns = Schema::getColumnListing('actions');
    echo "Colonnes trouvées dans 'actions' :\n";
    foreach ($columns as $col) {
        echo "  - {$col}\n";
    }

    echo "\nNombre d'enregistrements : " . DB::table('actions')->count() . "\n";
} else {
    echo "❌ La table 'actions' n'existe pas\n";
}

echo "\n";

if (Schema::hasTable('resource_actions')) {
    $columns = Schema::getColumnListing('resource_actions');
    echo "Colonnes trouvées dans 'resource_actions' :\n";
    foreach ($columns as $col) {
        echo "  - {$col}\n";
    }

    echo "\nNombre d'enregistrements : " . DB::table('resource_actions')->count() . "\n";
} else {
    echo "❌ La table 'resource_actions' n'existe pas\n";
}
