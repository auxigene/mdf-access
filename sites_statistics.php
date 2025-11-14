<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
echo "  STATISTIQUES DES SITES FM INWI IMPORTÉS" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
echo PHP_EOL;

// Total des sites
$totalSites = DB::table('fm_sites')->count();
echo "📊 TOTAL DES SITES: {$totalSites}" . PHP_EOL;
echo PHP_EOL;

// ==========================================
// 1. RÉPARTITION PAR RÉGION
// ==========================================
echo "🌍 RÉPARTITION PAR RÉGION:" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

$sitesByRegion = DB::table('fm_sites')
    ->leftJoin('fm_regions', 'fm_sites.fm_region_id', '=', 'fm_regions.id')
    ->select(
        'fm_regions.name as region',
        'fm_regions.zone_geographique',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_regions.id', 'fm_regions.name', 'fm_regions.zone_geographique')
    ->orderByDesc('count')
    ->get();

$sitesWithoutRegion = DB::table('fm_sites')
    ->whereNull('fm_region_id')
    ->count();

foreach ($sitesByRegion as $row) {
    $percentage = round(($row->count / $totalSites) * 100, 1);
    $bar = str_repeat('█', (int)($percentage / 2));
    printf("  %-15s (Zone: %-4s) : %5d sites (%5.1f%%) %s\n",
        $row->region,
        $row->zone_geographique,
        $row->count,
        $percentage,
        $bar
    );
}

if ($sitesWithoutRegion > 0) {
    $percentage = round(($sitesWithoutRegion / $totalSites) * 100, 1);
    echo "  Sans région                : {$sitesWithoutRegion} sites ({$percentage}%)" . PHP_EOL;
}

echo PHP_EOL;

// ==========================================
// 2. RÉPARTITION PAR CLASSIFICATION (CLASSE)
// ==========================================
echo "🏷️  RÉPARTITION PAR CLASSIFICATION:" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

$sitesByClass = DB::table('fm_sites')
    ->leftJoin('fm_site_classes', 'fm_sites.fm_site_class_id', '=', 'fm_site_classes.id')
    ->select(
        'fm_site_classes.code',
        'fm_site_classes.name',
        'fm_site_classes.priority',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_site_classes.id', 'fm_site_classes.code', 'fm_site_classes.name', 'fm_site_classes.priority')
    ->orderByDesc('fm_site_classes.priority')
    ->get();

$sitesWithoutClass = DB::table('fm_sites')
    ->whereNull('fm_site_class_id')
    ->count();

foreach ($sitesByClass as $row) {
    $percentage = round(($row->count / $totalSites) * 100, 1);
    $bar = str_repeat('█', (int)($percentage / 2));
    printf("  %-12s (Priorité: %d) : %5d sites (%5.1f%%) %s\n",
        $row->name,
        $row->priority,
        $row->count,
        $percentage,
        $bar
    );
}

if ($sitesWithoutClass > 0) {
    $percentage = round(($sitesWithoutClass / $totalSites) * 100, 1);
    echo "  Sans classe             : {$sitesWithoutClass} sites ({$percentage}%)" . PHP_EOL;
}

echo PHP_EOL;

// ==========================================
// 3. RÉPARTITION PAR SOURCE D'ÉNERGIE
// ==========================================
echo "⚡ RÉPARTITION PAR SOURCE D'ÉNERGIE:" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

$sitesByEnergy = DB::table('fm_sites')
    ->leftJoin('fm_energy_sources', 'fm_sites.fm_energy_source_id', '=', 'fm_energy_sources.id')
    ->select(
        'fm_energy_sources.code',
        'fm_energy_sources.name',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_energy_sources.id', 'fm_energy_sources.code', 'fm_energy_sources.name')
    ->orderByDesc('count')
    ->get();

$sitesWithoutEnergy = DB::table('fm_sites')
    ->whereNull('fm_energy_source_id')
    ->count();

foreach ($sitesByEnergy as $row) {
    $percentage = round(($row->count / $totalSites) * 100, 1);
    $bar = str_repeat('█', (int)($percentage / 2));
    printf("  %-30s : %5d sites (%5.1f%%) %s\n",
        $row->name,
        $row->count,
        $percentage,
        $bar
    );
}

if ($sitesWithoutEnergy > 0) {
    $percentage = round(($sitesWithoutEnergy / $totalSites) * 100, 1);
    echo "  Sans source d'énergie   : {$sitesWithoutEnergy} sites ({$percentage}%)" . PHP_EOL;
}

echo PHP_EOL;

// ==========================================
// 4. RÉPARTITION PAR TYPOLOGIE DE MAINTENANCE
// ==========================================
echo "🔧 RÉPARTITION PAR TYPOLOGIE DE MAINTENANCE:" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

$sitesByTypology = DB::table('fm_sites')
    ->leftJoin('fm_maintenance_typologies', 'fm_sites.fm_maintenance_typology_id', '=', 'fm_maintenance_typologies.id')
    ->select(
        'fm_maintenance_typologies.code',
        'fm_maintenance_typologies.name',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_maintenance_typologies.id', 'fm_maintenance_typologies.code', 'fm_maintenance_typologies.name')
    ->orderByDesc('count')
    ->get();

$sitesWithoutTypology = DB::table('fm_sites')
    ->whereNull('fm_maintenance_typology_id')
    ->count();

foreach ($sitesByTypology as $row) {
    $percentage = round(($row->count / $totalSites) * 100, 1);
    $bar = str_repeat('█', (int)($percentage / 2));
    printf("  %-30s : %5d sites (%5.1f%%) %s\n",
        $row->name,
        $row->count,
        $percentage,
        $bar
    );
}

if ($sitesWithoutTypology > 0) {
    $percentage = round(($sitesWithoutTypology / $totalSites) * 100, 1);
    echo "  Sans typologie          : {$sitesWithoutTypology} sites ({$percentage}%)" . PHP_EOL;
}

echo PHP_EOL;

// ==========================================
// 5. COLOCATION
// ==========================================
echo "🔗 SITES EN COLOCATION:" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

$colocationStats = DB::table('fm_sites')
    ->select(
        'is_colocation',
        DB::raw('count(*) as count')
    )
    ->groupBy('is_colocation')
    ->get();

foreach ($colocationStats as $row) {
    $percentage = round(($row->count / $totalSites) * 100, 1);
    $label = $row->is_colocation ? 'En colocation' : 'Non colocation';
    $bar = str_repeat('█', (int)($percentage / 2));
    printf("  %-20s : %5d sites (%5.1f%%) %s\n",
        $label,
        $row->count,
        $percentage,
        $bar
    );
}

echo PHP_EOL;

// Détail des configurations de colocation
$colocationByConfig = DB::table('fm_sites')
    ->leftJoin('fm_site_type_colocations', 'fm_sites.fm_site_type_colocation_id', '=', 'fm_site_type_colocations.id')
    ->where('fm_sites.is_colocation', true)
    ->select(
        'fm_site_type_colocations.name as config_name',
        'fm_site_type_colocations.tenant_count',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_site_type_colocations.id', 'fm_site_type_colocations.name', 'fm_site_type_colocations.tenant_count')
    ->orderByDesc('count')
    ->limit(10)
    ->get();

if ($colocationByConfig->count() > 0) {
    echo "  Top 10 des configurations de colocation:" . PHP_EOL;
    foreach ($colocationByConfig as $row) {
        printf("    %-40s (%d tenants) : %4d sites\n",
            $row->config_name,
            $row->tenant_count,
            $row->count
        );
    }
}

echo PHP_EOL;

// ==========================================
// 6. STATISTIQUES COMBINÉES
// ==========================================
echo "📈 STATISTIQUES COMBINÉES:" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

// Top 5 combinaisons Région + Classe
echo "  Top 5 combinaisons Région + Classe:" . PHP_EOL;
$topCombinations = DB::table('fm_sites')
    ->leftJoin('fm_regions', 'fm_sites.fm_region_id', '=', 'fm_regions.id')
    ->leftJoin('fm_site_classes', 'fm_sites.fm_site_class_id', '=', 'fm_site_classes.id')
    ->select(
        'fm_regions.name as region',
        'fm_site_classes.name as classe',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_regions.name', 'fm_site_classes.name')
    ->orderByDesc('count')
    ->limit(5)
    ->get();

foreach ($topCombinations as $row) {
    printf("    %-15s + %-15s : %4d sites\n",
        $row->region,
        $row->classe,
        $row->count
    );
}

echo PHP_EOL;
echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
echo "  FIN DES STATISTIQUES" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
