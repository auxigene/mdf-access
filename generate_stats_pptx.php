<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Bar;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Pie;
use PhpOffice\PhpPresentation\Shape\Table;
use PhpOffice\PhpPresentation\Style\Border;

echo "Génération de la présentation PowerPoint..." . PHP_EOL;

// Créer la présentation
$presentation = new PhpPresentation();
$presentation->getDocumentProperties()
    ->setCreator('MDF Access')
    ->setTitle('Statistiques FM Sites INWI')
    ->setSubject('Analyse du parc sites INWI')
    ->setDescription('Statistiques détaillées des 8842 sites FM INWI importés');

$totalSites = DB::table('fm_sites')->count();

// ==========================================
// SLIDE 1: PAGE DE TITRE
// ==========================================
$slide1 = $presentation->getActiveSlide();
$slide1->setName('Titre');

// Titre
$shape = $slide1->createRichTextShape()
    ->setHeight(100)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(150);
$shape->getActiveParagraph()->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
$textRun = $shape->createTextRun('STATISTIQUES FM SITES INWI');
$textRun->getFont()
    ->setBold(true)
    ->setSize(44)
    ->setColor(new Color('FF1F4E78'));

// Sous-titre
$shape2 = $slide1->createRichTextShape()
    ->setHeight(60)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(270);
$shape2->getActiveParagraph()->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
$textRun2 = $shape2->createTextRun('Analyse du parc de sites - Programme FM Sites INWI');
$textRun2->getFont()
    ->setSize(24)
    ->setColor(new Color('FF7F7F7F'));

// Statistique principale
$shape3 = $slide1->createRichTextShape()
    ->setHeight(150)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(380);
$shape3->getActiveParagraph()->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
$textRun3 = $shape3->createTextRun($totalSites . ' SITES');
$textRun3->getFont()
    ->setBold(true)
    ->setSize(72)
    ->setColor(new Color('FF0070C0'));

// Date
$shape4 = $slide1->createRichTextShape()
    ->setHeight(40)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(500);
$shape4->getActiveParagraph()->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
$textRun4 = $shape4->createTextRun(date('d/m/Y'));
$textRun4->getFont()
    ->setSize(16)
    ->setColor(new Color('FF7F7F7F'));

echo "  Slide 1: Titre créé" . PHP_EOL;

// ==========================================
// SLIDE 2: RÉPARTITION PAR RÉGION (NORMALISÉE)
// ==========================================
$slide2 = $presentation->createSlide();
$slide2->setName('Répartition par Région');

// Titre
$shape = $slide2->createRichTextShape()
    ->setHeight(40)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(10);
$textRun = $shape->createTextRun('Répartition par Région');
$textRun->getFont()
    ->setBold(true)
    ->setSize(28)
    ->setColor(new Color('FF1F4E78'));

// Récupérer les données NORMALISÉES depuis la table fm_regions
$sitesByRegion = DB::table('fm_sites')
    ->join('fm_regions', 'fm_sites.fm_region_id', '=', 'fm_regions.id')
    ->select(
        'fm_regions.code',
        'fm_regions.name as region',
        'fm_regions.zone_geographique',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_regions.id', 'fm_regions.code', 'fm_regions.name', 'fm_regions.zone_geographique')
    ->orderByDesc('count')
    ->get();

// Créer le graphique en camembert
$pieChart = new Pie();
$series = new \PhpOffice\PhpPresentation\Shape\Chart\Series('Sites', []);

foreach ($sitesByRegion as $row) {
    $series->addValue($row->region, $row->count);
}

$pieChart->addSeries($series);

$shape = $slide2->createChartShape()
    ->setHeight(280)
    ->setWidth(450)
    ->setOffsetX(50)
    ->setOffsetY(60);
$shape->getPlotArea()->setType($pieChart);
$shape->getLegend()->setVisible(true);

// Créer le tableau de données
$tableShape = $slide2->createTableShape(4);
$tableShape->setHeight(280)
    ->setWidth(420)
    ->setOffsetX(530)
    ->setOffsetY(60);

// En-têtes
$row = $tableShape->createRow();
$row->setHeight(35);
$cell = $row->nextCell();
$cell->createTextRun('Région')->getFont()->setBold(true)->setSize(12);
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('Zone')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('Sites')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('%')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Données
foreach ($sitesByRegion as $index => $data) {
    $row = $tableShape->createRow();
    $row->setHeight(30);
    $percentage = round(($data->count / $totalSites) * 100, 1);

    $bgColor = $index % 2 == 0 ? 'FFF0F0F0' : 'FFFFFFFF';

    $cell = $row->nextCell();
    $cell->createTextRun($data->region)->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));

    $cell = $row->nextCell();
    $cell->createTextRun($data->zone_geographique)->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell = $row->nextCell();
    $cell->createTextRun(number_format($data->count))->getFont()->setSize(11)->setBold(true);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $cell = $row->nextCell();
    $cell->createTextRun($percentage . '%')->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

echo "  Slide 2: Répartition par région créée" . PHP_EOL;

// ==========================================
// SLIDE 3: RÉPARTITION PAR CLASSIFICATION (NORMALISÉE)
// ==========================================
$slide3 = $presentation->createSlide();
$slide3->setName('Répartition par Classification');

// Titre
$shape = $slide3->createRichTextShape()
    ->setHeight(40)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(10);
$textRun = $shape->createTextRun('Répartition par Classification');
$textRun->getFont()
    ->setBold(true)
    ->setSize(28)
    ->setColor(new Color('FF1F4E78'));

// Récupérer les données NORMALISÉES depuis la table fm_site_classes
$sitesByClass = DB::table('fm_sites')
    ->join('fm_site_classes', 'fm_sites.fm_site_class_id', '=', 'fm_site_classes.id')
    ->select(
        'fm_site_classes.code',
        'fm_site_classes.name',
        'fm_site_classes.priority',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_site_classes.id', 'fm_site_classes.code', 'fm_site_classes.name', 'fm_site_classes.priority')
    ->orderByDesc('fm_site_classes.priority')
    ->get();

// Créer le graphique en barres
$barChart = new Bar();
$series = new \PhpOffice\PhpPresentation\Shape\Chart\Series('Sites', []);

foreach ($sitesByClass as $row) {
    $series->addValue($row->name, $row->count);
}

$barChart->addSeries($series);

$shape = $slide3->createChartShape()
    ->setHeight(280)
    ->setWidth(450)
    ->setOffsetX(50)
    ->setOffsetY(60);
$shape->getPlotArea()->setType($barChart);

// Créer le tableau de données
$tableShape = $slide3->createTableShape(4);
$tableShape->setHeight(280)
    ->setWidth(420)
    ->setOffsetX(530)
    ->setOffsetY(60);

// En-têtes
$row = $tableShape->createRow();
$row->setHeight(35);
$cell = $row->nextCell();
$cell->createTextRun('Classification')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));

$cell = $row->nextCell();
$cell->createTextRun('Priorité')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('Sites')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('%')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Données
foreach ($sitesByClass as $index => $data) {
    $row = $tableShape->createRow();
    $row->setHeight(30);
    $percentage = round(($data->count / $totalSites) * 100, 1);

    $bgColor = $index % 2 == 0 ? 'FFF0F0F0' : 'FFFFFFFF';

    $cell = $row->nextCell();
    $cell->createTextRun($data->name)->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));

    $cell = $row->nextCell();
    $cell->createTextRun((string)$data->priority)->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell = $row->nextCell();
    $cell->createTextRun(number_format($data->count))->getFont()->setSize(11)->setBold(true);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $cell = $row->nextCell();
    $cell->createTextRun($percentage . '%')->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

echo "  Slide 3: Répartition par classification créée" . PHP_EOL;

// ==========================================
// SLIDE 4: SOURCES D'ÉNERGIE (NORMALISÉE)
// ==========================================
$slide4 = $presentation->createSlide();
$slide4->setName('Sources d\'Énergie');

// Titre
$shape = $slide4->createRichTextShape()
    ->setHeight(40)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(10);
$textRun = $shape->createTextRun('Répartition par Source d\'Énergie');
$textRun->getFont()
    ->setBold(true)
    ->setSize(28)
    ->setColor(new Color('FF1F4E78'));

// Récupérer les données NORMALISÉES depuis la table fm_energy_sources
$sitesByEnergy = DB::table('fm_sites')
    ->join('fm_energy_sources', 'fm_sites.fm_energy_source_id', '=', 'fm_energy_sources.id')
    ->select(
        'fm_energy_sources.code',
        'fm_energy_sources.name',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_energy_sources.id', 'fm_energy_sources.code', 'fm_energy_sources.name')
    ->orderByDesc('count')
    ->get();

// Créer le graphique en camembert
$pieChart = new Pie();
$series = new \PhpOffice\PhpPresentation\Shape\Chart\Series('Sites', []);

foreach ($sitesByEnergy as $row) {
    $series->addValue($row->name, $row->count);
}

$pieChart->addSeries($series);

$shape = $slide4->createChartShape()
    ->setHeight(280)
    ->setWidth(450)
    ->setOffsetX(50)
    ->setOffsetY(60);
$shape->getPlotArea()->setType($pieChart);

// Créer le tableau de données
$tableShape = $slide4->createTableShape(3);
$tableShape->setHeight(280)
    ->setWidth(420)
    ->setOffsetX(530)
    ->setOffsetY(60);

// En-têtes
$row = $tableShape->createRow();
$row->setHeight(35);
$cell = $row->nextCell();
$cell->createTextRun('Source d\'Énergie')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));

$cell = $row->nextCell();
$cell->createTextRun('Sites')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('%')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Données
foreach ($sitesByEnergy as $index => $data) {
    $row = $tableShape->createRow();
    $row->setHeight(30);
    $percentage = round(($data->count / $totalSites) * 100, 1);

    $bgColor = $index % 2 == 0 ? 'FFF0F0F0' : 'FFFFFFFF';

    $cell = $row->nextCell();
    $cell->createTextRun($data->name)->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));

    $cell = $row->nextCell();
    $cell->createTextRun(number_format($data->count))->getFont()->setSize(11)->setBold(true);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $cell = $row->nextCell();
    $cell->createTextRun($percentage . '%')->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

echo "  Slide 4: Sources d'énergie créée" . PHP_EOL;

// ==========================================
// SLIDE 5: TYPOLOGIE DE MAINTENANCE (NORMALISÉE)
// ==========================================
$slide5 = $presentation->createSlide();
$slide5->setName('Typologie de Maintenance');

// Titre
$shape = $slide5->createRichTextShape()
    ->setHeight(40)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(10);
$textRun = $shape->createTextRun('Répartition par Typologie de Maintenance');
$textRun->getFont()
    ->setBold(true)
    ->setSize(28)
    ->setColor(new Color('FF1F4E78'));

// Récupérer les données NORMALISÉES depuis la table fm_maintenance_typologies
$sitesByTypology = DB::table('fm_sites')
    ->join('fm_maintenance_typologies', 'fm_sites.fm_maintenance_typology_id', '=', 'fm_maintenance_typologies.id')
    ->select(
        'fm_maintenance_typologies.code',
        'fm_maintenance_typologies.name',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_maintenance_typologies.id', 'fm_maintenance_typologies.code', 'fm_maintenance_typologies.name')
    ->orderByDesc('count')
    ->get();

// Créer le graphique en barres
$barChart = new Bar();
$series = new \PhpOffice\PhpPresentation\Shape\Chart\Series('Sites', []);

foreach ($sitesByTypology as $row) {
    $series->addValue($row->name, $row->count);
}

$barChart->addSeries($series);

$shape = $slide5->createChartShape()
    ->setHeight(280)
    ->setWidth(450)
    ->setOffsetX(50)
    ->setOffsetY(60);
$shape->getPlotArea()->setType($barChart);

// Créer le tableau de données
$tableShape = $slide5->createTableShape(3);
$tableShape->setHeight(280)
    ->setWidth(420)
    ->setOffsetX(530)
    ->setOffsetY(60);

// En-têtes
$row = $tableShape->createRow();
$row->setHeight(35);
$cell = $row->nextCell();
$cell->createTextRun('Typologie')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));

$cell = $row->nextCell();
$cell->createTextRun('Sites')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('%')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Données
foreach ($sitesByTypology as $index => $data) {
    $row = $tableShape->createRow();
    $row->setHeight(30);
    $percentage = round(($data->count / $totalSites) * 100, 1);

    $bgColor = $index % 2 == 0 ? 'FFF0F0F0' : 'FFFFFFFF';

    $cell = $row->nextCell();
    $cell->createTextRun($data->name)->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));

    $cell = $row->nextCell();
    $cell->createTextRun(number_format($data->count))->getFont()->setSize(11)->setBold(true);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $cell = $row->nextCell();
    $cell->createTextRun($percentage . '%')->getFont()->setSize(11);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

echo "  Slide 5: Typologie de maintenance créée" . PHP_EOL;

// ==========================================
// SLIDE 6: COLOCATION (NORMALISÉE)
// ==========================================
$slide6 = $presentation->createSlide();
$slide6->setName('Colocation');

// Titre
$shape = $slide6->createRichTextShape()
    ->setHeight(40)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(10);
$textRun = $shape->createTextRun('Sites en Colocation');
$textRun->getFont()
    ->setBold(true)
    ->setSize(28)
    ->setColor(new Color('FF1F4E78'));

// Statistiques globales colocation
$colocationCount = DB::table('fm_sites')->where('is_colocation', true)->count();
$nonColocationCount = DB::table('fm_sites')->where('is_colocation', false)->count();
$colocationPercentage = round(($colocationCount / $totalSites) * 100, 1);

// KPI Box gauche
$shape = $slide6->createRichTextShape()
    ->setHeight(100)
    ->setWidth(200)
    ->setOffsetX(50)
    ->setOffsetY(60);
$shape->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FFE7F3FF'));

$p1 = $shape->createParagraph();
$p1->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr1 = $p1->createTextRun('En Colocation');
$tr1->getFont()->setSize(14)->setBold(true);

$p2 = $shape->createParagraph();
$p2->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr2 = $p2->createTextRun(number_format($colocationCount));
$tr2->getFont()->setSize(32)->setBold(true)->setColor(new Color('FF0070C0'));

$p3 = $shape->createParagraph();
$p3->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr3 = $p3->createTextRun($colocationPercentage . '%');
$tr3->getFont()->setSize(16);

// KPI Box droite
$shape = $slide6->createRichTextShape()
    ->setHeight(100)
    ->setWidth(200)
    ->setOffsetX(270)
    ->setOffsetY(60);
$shape->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FFF0F0F0'));

$p1 = $shape->createParagraph();
$p1->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr1 = $p1->createTextRun('Non Colocation');
$tr1->getFont()->setSize(14)->setBold(true);

$p2 = $shape->createParagraph();
$p2->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr2 = $p2->createTextRun(number_format($nonColocationCount));
$tr2->getFont()->setSize(32)->setBold(true)->setColor(new Color('FF7F7F7F'));

$p3 = $shape->createParagraph();
$p3->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr3 = $p3->createTextRun(round(100 - $colocationPercentage, 1) . '%');
$tr3->getFont()->setSize(16);

// Top configurations de colocation (NORMALISÉES)
$topColocation = DB::table('fm_sites')
    ->join('fm_site_type_colocations', 'fm_sites.fm_site_type_colocation_id', '=', 'fm_site_type_colocations.id')
    ->where('fm_sites.is_colocation', true)
    ->select(
        'fm_site_type_colocations.code',
        'fm_site_type_colocations.name',
        'fm_site_type_colocations.tenant_count',
        DB::raw('count(*) as count')
    )
    ->groupBy('fm_site_type_colocations.id', 'fm_site_type_colocations.code', 'fm_site_type_colocations.name', 'fm_site_type_colocations.tenant_count')
    ->orderByDesc('count')
    ->limit(10)
    ->get();

// Créer le tableau Top 10
$tableShape = $slide6->createTableShape(4);
$tableShape->setHeight(210)
    ->setWidth(470)
    ->setOffsetX(480)
    ->setOffsetY(60);

// En-têtes
$row = $tableShape->createRow();
$row->setHeight(30);
$cell = $row->nextCell();
$cell->createTextRun('Configuration')->getFont()->setBold(true)->setSize(11)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));

$cell = $row->nextCell();
$cell->createTextRun('Tenants')->getFont()->setBold(true)->setSize(11)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('Sites')->getFont()->setBold(true)->setSize(11)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cell = $row->nextCell();
$cell->createTextRun('%')->getFont()->setBold(true)->setSize(11)->setColor(new Color('FFFFFFFF'));
$cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));
$cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Données
foreach ($topColocation as $index => $data) {
    $row = $tableShape->createRow();
    $row->setHeight(20);
    $percentage = round(($data->count / $colocationCount) * 100, 1);

    $bgColor = $index % 2 == 0 ? 'FFF0F0F0' : 'FFFFFFFF';

    $cell = $row->nextCell();
    $cell->createTextRun($data->name)->getFont()->setSize(9);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));

    $cell = $row->nextCell();
    $cell->createTextRun((string)$data->tenant_count)->getFont()->setSize(9);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell = $row->nextCell();
    $cell->createTextRun(number_format($data->count))->getFont()->setSize(9)->setBold(true);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $cell = $row->nextCell();
    $cell->createTextRun($percentage . '%')->getFont()->setSize(9);
    $cell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor(new Color($bgColor));
    $cell->createParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
}

echo "  Slide 6: Colocation créée" . PHP_EOL;

// ==========================================
// SLIDE 7: VUE D'ENSEMBLE (AVEC DONNÉES NORMALISÉES)
// ==========================================
$slide7 = $presentation->createSlide();
$slide7->setName('Vue d\'Ensemble');

// Titre
$shape = $slide7->createRichTextShape()
    ->setHeight(50)
    ->setWidth(900)
    ->setOffsetX(50)
    ->setOffsetY(20);
$textRun = $shape->createTextRun('Vue d\'Ensemble - Indicateurs Clés');
$textRun->getFont()
    ->setBold(true)
    ->setSize(32)
    ->setColor(new Color('FF1F4E78'));

// Récupérer les données normalisées pour les KPIs
$topRegion = DB::table('fm_sites')
    ->join('fm_regions', 'fm_sites.fm_region_id', '=', 'fm_regions.id')
    ->select('fm_regions.name', DB::raw('count(*) as count'))
    ->groupBy('fm_regions.id', 'fm_regions.name')
    ->orderByDesc('count')
    ->first();

$topClass = DB::table('fm_sites')
    ->join('fm_site_classes', 'fm_sites.fm_site_class_id', '=', 'fm_site_classes.id')
    ->select('fm_site_classes.name', DB::raw('count(*) as count'))
    ->groupBy('fm_site_classes.id', 'fm_site_classes.name')
    ->orderByDesc('count')
    ->first();

$topEnergy = DB::table('fm_sites')
    ->join('fm_energy_sources', 'fm_sites.fm_energy_source_id', '=', 'fm_energy_sources.id')
    ->select('fm_energy_sources.name', 'fm_energy_sources.code', DB::raw('count(*) as count'))
    ->groupBy('fm_energy_sources.id', 'fm_energy_sources.name', 'fm_energy_sources.code')
    ->orderByDesc('count')
    ->first();

$topTypology = DB::table('fm_sites')
    ->join('fm_maintenance_typologies', 'fm_sites.fm_maintenance_typology_id', '=', 'fm_maintenance_typologies.id')
    ->select('fm_maintenance_typologies.name', DB::raw('count(*) as count'))
    ->groupBy('fm_maintenance_typologies.id', 'fm_maintenance_typologies.name')
    ->orderByDesc('count')
    ->first();

// KPI 1: Région dominante
$kpi1 = $slide7->createRichTextShape()
    ->setHeight(120)
    ->setWidth(280)
    ->setOffsetX(50)
    ->setOffsetY(100);
$kpi1->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FFE7F3FF'));

$p = $kpi1->createParagraph();
$p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr = $p->createTextRun('Région #1');
$tr->getFont()->setSize(14)->setColor(new Color('FF7F7F7F'));

$p2 = $kpi1->createParagraph();
$p2->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr2 = $p2->createTextRun($topRegion->name);
$tr2->getFont()->setSize(24)->setBold(true)->setColor(new Color('FF0070C0'));

$p3 = $kpi1->createParagraph();
$p3->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr3 = $p3->createTextRun(number_format($topRegion->count) . ' sites');
$tr3->getFont()->setSize(18)->setColor(new Color('FF1F4E78'));

// KPI 2: Classe dominante
$kpi2 = $slide7->createRichTextShape()
    ->setHeight(120)
    ->setWidth(280)
    ->setOffsetX(360)
    ->setOffsetY(100);
$kpi2->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FFFFF4E7'));

$p = $kpi2->createParagraph();
$p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr = $p->createTextRun('Classe #1');
$tr->getFont()->setSize(14)->setColor(new Color('FF7F7F7F'));

$p2 = $kpi2->createParagraph();
$p2->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr2 = $p2->createTextRun($topClass->name);
$tr2->getFont()->setSize(24)->setBold(true)->setColor(new Color('FFFF6B00'));

$p3 = $kpi2->createParagraph();
$p3->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr3 = $p3->createTextRun(number_format($topClass->count) . ' sites');
$tr3->getFont()->setSize(18)->setColor(new Color('FF1F4E78'));

// KPI 3: Source d'énergie dominante
$kpi3 = $slide7->createRichTextShape()
    ->setHeight(120)
    ->setWidth(280)
    ->setOffsetX(670)
    ->setOffsetY(100);
$kpi3->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FFE7FFE7'));

$p = $kpi3->createParagraph();
$p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr = $p->createTextRun('Énergie #1');
$tr->getFont()->setSize(14)->setColor(new Color('FF7F7F7F'));

$p2 = $kpi3->createParagraph();
$p2->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr2 = $p2->createTextRun($topEnergy->code);
$tr2->getFont()->setSize(24)->setBold(true)->setColor(new Color('FF00B050'));

$energyPercentage = round(($topEnergy->count / $totalSites) * 100, 1);
$p3 = $kpi3->createParagraph();
$p3->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr3 = $p3->createTextRun($energyPercentage . '%');
$tr3->getFont()->setSize(18)->setColor(new Color('FF1F4E78'));

// KPI 4: Typologie dominante
$kpi4 = $slide7->createRichTextShape()
    ->setHeight(120)
    ->setWidth(280)
    ->setOffsetX(50)
    ->setOffsetY(250);
$kpi4->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FFFFE7F3'));

$p = $kpi4->createParagraph();
$p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr = $p->createTextRun('Typologie #1');
$tr->getFont()->setSize(14)->setColor(new Color('FF7F7F7F'));

$p2 = $kpi4->createParagraph();
$p2->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr2 = $p2->createTextRun($topTypology->name);
$tr2->getFont()->setSize(24)->setBold(true)->setColor(new Color('FFC00070'));

$typologyPercentage = round(($topTypology->count / $totalSites) * 100, 1);
$p3 = $kpi4->createParagraph();
$p3->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr3 = $p3->createTextRun($typologyPercentage . '%');
$tr3->getFont()->setSize(18)->setColor(new Color('FF1F4E78'));

// KPI 5: Taux de colocation
$kpi5 = $slide7->createRichTextShape()
    ->setHeight(120)
    ->setWidth(280)
    ->setOffsetX(360)
    ->setOffsetY(250);
$kpi5->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FFF3E7FF'));

$p = $kpi5->createParagraph();
$p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr = $p->createTextRun('Colocation');
$tr->getFont()->setSize(14)->setColor(new Color('FF7F7F7F'));

$p2 = $kpi5->createParagraph();
$p2->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr2 = $p2->createTextRun($colocationPercentage . '%');
$tr2->getFont()->setSize(24)->setBold(true)->setColor(new Color('FF7030A0'));

$p3 = $kpi5->createParagraph();
$p3->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr3 = $p3->createTextRun(number_format($colocationCount) . ' sites');
$tr3->getFont()->setSize(18)->setColor(new Color('FF1F4E78'));

// KPI 6: Total sites
$kpi6 = $slide7->createRichTextShape()
    ->setHeight(120)
    ->setWidth(280)
    ->setOffsetX(670)
    ->setOffsetY(250);
$kpi6->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
    ->setStartColor(new Color('FF1F4E78'));

$p = $kpi6->createParagraph();
$p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr = $p->createTextRun('TOTAL');
$tr->getFont()->setSize(14)->setColor(new Color('FFFFFFFF'));

$p2 = $kpi6->createParagraph();
$p2->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr2 = $p2->createTextRun(number_format($totalSites));
$tr2->getFont()->setSize(36)->setBold(true)->setColor(new Color('FFFFFFFF'));

$p3 = $kpi6->createParagraph();
$p3->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$tr3 = $p3->createTextRun('sites');
$tr3->getFont()->setSize(18)->setColor(new Color('FFFFFFFF'));

echo "  Slide 7: Vue d'ensemble créée" . PHP_EOL;

// Sauvegarder le fichier
$outputPath = storage_path('app/public/FM_Sites_INWI_Statistiques.pptx');
$oWriter = IOFactory::createWriter($presentation, 'PowerPoint2007');
$oWriter->save($outputPath);

echo PHP_EOL . "✅ Présentation PowerPoint créée avec succès!" . PHP_EOL;
echo "📁 Fichier: {$outputPath}" . PHP_EOL;
echo "📊 7 slides générées avec données normalisées" . PHP_EOL;
echo "📋 Tableaux de données ajoutés sous chaque graphique" . PHP_EOL;
