<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Vérification des tables existantes :\n\n";

$tablesToCheck = ['resources', 'actions', 'resource_actions', 'permissions'];

foreach ($tablesToCheck as $table) {
    $exists = Schema::hasTable($table);
    echo ($exists ? "✓" : "✗") . " {$table}\n";

    if ($exists && in_array($table, ['resources', 'actions', 'resource_actions'])) {
        $count = DB::table($table)->count();
        echo "  → {$count} enregistrement(s)\n";
    }
}

echo "\nColonnes de la table permissions :\n";
if (Schema::hasTable('permissions')) {
    $columns = Schema::getColumnListing('permissions');
    foreach ($columns as $col) {
        echo "  - {$col}\n";
    }
}
