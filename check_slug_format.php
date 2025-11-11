<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sample = DB::table('permissions')->limit(5)->get();

echo "Sample de slugs existants :\n\n";
foreach ($sample as $perm) {
    echo "  - {$perm->slug}\n";
}
