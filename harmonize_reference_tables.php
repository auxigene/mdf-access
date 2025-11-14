<?php
   require 'vendor/autoload.php';

   $app = require_once 'bootstrap/app.php';
   $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

   use Illuminate\Support\Facades\DB;

   echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
   echo "  HARMONISATION DES TABLES DE RÉFÉRENCE FM" . PHP_EOL;
   echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
   echo PHP_EOL;

   // ==========================================
   // 1. HARMONISER fm_site_classes
   // ==========================================
   echo "1. HARMONISATION DES CLASSES DE SITES" . PHP_EOL;
   echo str_repeat("─", 60) . PHP_EOL;

   // Identifier les doublons
   $classes = DB::table('fm_site_classes')
      ->select('id', 'code', 'name', 'priority')
      ->orderBy('code')
      ->get();

   echo "Classes actuelles:" . PHP_EOL;

   foreach ($classes as $class) {
      $siteCount = DB::table('fm_sites')->where('fm_site_class_id', $class->id)->count();
      echo "  ID {$class->id}: {$class->code} - {$class->name} (Priorité: {$class->priority}) - {$siteCount} sites" . PHP_EOL;
   }

   echo PHP_EOL . "Fusion des doublons 'En cours' et 'Classe EN COURS'..." . PHP_EOL;

   // Trouver les deux enregistrements
   $enCours = DB::table('fm_site_classes')->where('code', 'EC')->where('name', 'En cours')->first();
   $classeEnCours = DB::table('fm_site_classes')->where('name', 'Classe EN COURS')->first();

   if ($enCours && $classeEnCours) {
      echo "  Trouvé: 'En cours' (ID: {$enCours->id}) et 'Classe EN COURS' (ID: {$classeEnCours->id})" . PHP_EOL;
      // Compter les sites pour chaque classe
      $enCoursCount = DB::table('fm_sites')->where('fm_site_class_id', $enCours->id)->count();
      $classeEnCoursCount = DB::table('fm_sites')->where('fm_site_class_id', $classeEnCours->id)->count();
      echo "  Sites 'En cours': {$enCoursCount}" . PHP_EOL;
      echo "  Sites 'Classe EN COURS': {$classeEnCoursCount}" . PHP_EOL;
      // Migrer tous les sites de 'Classe EN COURS' vers 'En cours'
      DB::table('fm_sites')
          ->where('fm_site_class_id', $classeEnCours->id)
          ->update(['fm_site_class_id' => $enCours->id]);
      echo "  ✅ {$classeEnCoursCount} sites migrés de 'Classe EN COURS' vers 'En cours'" . PHP_EOL;
      // Mettre à jour les mappings de références
      DB::table('fm_references_mapping')
          ->where('table_name', 'fm_site_classes')
          ->where('code', $classeEnCours->code)
          ->update(['code' => $enCours->code]);
      echo "  ✅ Mappings de références mis à jour" . PHP_EOL;
      // Supprimer l'enregistrement doublon
      DB::table('fm_site_classes')->where('id', $classeEnCours->id)->delete();
      echo "  ✅ Enregistrement 'Classe EN COURS' supprimé" . PHP_EOL;
   } else {
      echo "  ⚠️  Doublons non trouvés (peut-être déjà fusionnés)" . PHP_EOL;
   }

   echo PHP_EOL;
   // ==========================================
   // 2. HARMONISER fm_maintenance_typologies
   // ==========================================
   echo "2. HARMONISATION DES TYPOLOGIES DE MAINTENANCE" . PHP_EOL;
   echo str_repeat("─", 60) . PHP_EOL;

   // Identifier les doublons
   $typologies = DB::table('fm_maintenance_typologies')
      ->select('id', 'code', 'name')
      ->orderBy('code')
      ->get();
   echo "Typologies actuelles:" . PHP_EOL;
   foreach ($typologies as $typo) {
      $siteCount = DB::table('fm_sites')->where('fm_maintenance_typology_id', $typo->id)->count();
      echo "  ID {$typo->id}: {$typo->code} - {$typo->name} - {$siteCount} sites" . PHP_EOL;
   }

   echo PHP_EOL . "Fusion des doublons 'ROOFTOP' et 'Rooftop'..." . PHP_EOL;
   // Trouver les deux enregistrements
   $rooftopUpper = DB::table('fm_maintenance_typologies')->where('code', 'ROOFTOP')->first();
   $rooftopMixed = DB::table('fm_maintenance_typologies')->where('name', 'Rooftop')->first();

   if ($rooftopUpper && $rooftopMixed && $rooftopUpper->id !== $rooftopMixed->id) {
      echo "  Trouvé: 'ROOFTOP' (ID: {$rooftopUpper->id}, code: {$rooftopUpper->code}) et 'Rooftop' (ID: {$rooftopMixed->id}, code: {$rooftopMixed->code})" . PHP_EOL;
      // Compter les sites pour chaque typologie
      $rooftopUpperCount = DB::table('fm_sites')->where('fm_maintenance_typology_id', $rooftopUpper->id)->count();
      $rooftopMixedCount = DB::table('fm_sites')->where('fm_maintenance_typology_id', $rooftopMixed->id)->count();
      echo "  Sites 'ROOFTOP': {$rooftopUpperCount}" . PHP_EOL;
      echo "  Sites 'Rooftop': {$rooftopMixedCount}" . PHP_EOL;
      // Déterminer quelle version garder (celle avec le code ROOFTOP)
      $keepId = $rooftopUpper->id;
      $deleteId = $rooftopMixed->id;
      $keepCode = $rooftopUpper->code;
      $deleteCode = $rooftopMixed->code;
      // Migrer tous les sites de 'Rooftop' vers 'ROOFTOP'
      DB::table('fm_sites')
          ->where('fm_maintenance_typology_id', $deleteId)
          ->update(['fm_maintenance_typology_id' => $keepId]);
      echo "  ✅ {$rooftopMixedCount} sites migrés de 'Rooftop' vers 'ROOFTOP'" . PHP_EOL;
      // Mettre à jour les mappings de références
      DB::table('fm_references_mapping')
          ->where('table_name', 'fm_maintenance_typologies')
          ->where('code', $deleteCode)
          ->update(['code' => $keepCode]);
      echo "  ✅ Mappings de références mis à jour" . PHP_EOL;
      // Supprimer l'enregistrement doublon
      DB::table('fm_maintenance_typologies')->where('id', $deleteId)->delete();
      echo "  ✅ Enregistrement 'Rooftop' supprimé" . PHP_EOL;
      // Mettre à jour le nom pour normaliser
      DB::table('fm_maintenance_typologies')
          ->where('id', $keepId)
          ->update(['name' => 'Rooftop']);
      echo "  ✅ Nom normalisé: 'ROOFTOP' → 'Rooftop'" . PHP_EOL;
   } else {
      echo "  ⚠️  Doublons non trouvés ou déjà fusionnés" . PHP_EOL;
   }
   echo PHP_EOL;
   // ==========================================
   // 3. VÉRIFICATION FINALE
   // ==========================================
   echo "3. VÉRIFICATION FINALE" . PHP_EOL;
   echo str_repeat("─", 60) . PHP_EOL;
   echo "Classes de sites après harmonisation:" . PHP_EOL;

   $classes = DB::table('fm_site_classes')
      ->select('id', 'code', 'name', 'priority')
      ->orderBy('priority', 'desc')
      ->get();

   foreach ($classes as $class) {
      $siteCount = DB::table('fm_sites')->where('fm_site_class_id', $class->id)->count();
      echo "  {$class->code} - {$class->name} (Priorité: {$class->priority}): {$siteCount} sites" . PHP_EOL;
   }
   echo PHP_EOL . "Typologies de maintenance après harmonisation:" . PHP_EOL;

   $typologies = DB::table('fm_maintenance_typologies')
      ->select('id', 'code', 'name')
      ->orderBy('name')
      ->get();

   foreach ($typologies as $typo) {
      $siteCount = DB::table('fm_sites')->where('fm_maintenance_typology_id', $typo->id)->count();
      echo "  {$typo->code} - {$typo->name}: {$siteCount} sites" . PHP_EOL;
   }
   echo PHP_EOL;
   echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
   echo "  HARMONISATION TERMINÉE" . PHP_EOL;
   echo "═══════════════════════════════════════════════════════════" . PHP_EOL;