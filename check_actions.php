<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$actions = DB::table('actions')->get();

echo "Actions créées dans la base de données :\n\n";
foreach ($actions as $action) {
    echo "  - {$action->slug}\n";
}
