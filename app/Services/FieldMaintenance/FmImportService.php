<?php

namespace App\Services\FieldMaintenance;

use App\Models\FieldMaintenance\{
    FmSite,
    FmRegion,
    FmSiteClass,
    FmEnergySource,
    FmMaintenanceTypology,
    FmSiteTypeColocation,
    FmTenant,
    FmImportLog,
    FmReferenceMapping
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class FmImportService
{
    protected $importLog;
    protected $errors = [];
    protected $warnings = [];
    protected $successful = 0;
    protected $failed = 0;
    protected $updated = 0;
    protected $created = 0;

    // Cache pour les tables de référence
    protected $regionsCache = [];
    protected $classesCache = [];
    protected $energySourcesCache = [];
    protected $typologiesCache = [];
    protected $tenantsCache = [];
    protected $colocationConfigsCache = [];

    // Cache pour les mappings de références Excel -> Code
    protected $regionMappings = [];
    protected $classMappings = [];
    protected $energyMappings = [];
    protected $typologyMappings = [];
    protected $tenantMappings = [];
    protected $colocationMappings = [];

    /**
     * Import du parc de sites depuis le fichier Excel
     */
    public function importParcFromExcel(string $filePath, ?int $userId = null): FmImportLog
    {
        if (!file_exists($filePath)) {
            throw new Exception("Fichier introuvable: {$filePath}");
        }

        // Créer le log d'import
        $this->importLog = FmImportLog::create([
            'file_name' => basename($filePath),
            'file_path' => $filePath,
            'file_hash' => md5_file($filePath),
            'status' => 'pending',
            'imported_by' => $userId,
        ]);

        try {
            $this->importLog->markAsStarted();

            // Charger les caches
            $this->loadReferenceCaches();

            // Lire et importer les données
            $this->processExcelFile($filePath);

            // Marquer comme terminé
            $this->importLog->update([
                'total_rows' => $this->successful + $this->failed,
                'successful_imports' => $this->successful,
                'failed_imports' => $this->failed,
                'updated_records' => $this->updated,
                'created_records' => $this->created,
                'warnings_count' => count($this->warnings),
                'errors' => $this->errors,
                'warnings' => $this->warnings,
            ]);

            $this->importLog->markAsCompleted();

            return $this->importLog;

        } catch (Exception $e) {
            $this->importLog->markAsFailed($e->getMessage());
            Log::error('FmImport failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Charger les tables de référence en cache
     */
    protected function loadReferenceCaches(): void
    {
        $this->regionsCache = FmRegion::active()->get()->keyBy('code');
        $this->classesCache = FmSiteClass::active()->get()->keyBy('code');
        $this->energySourcesCache = FmEnergySource::active()->get()->keyBy('code');
        $this->typologiesCache = FmMaintenanceTypology::active()->get()->keyBy('code');
        $this->tenantsCache = FmTenant::active()->get()->keyBy('code');
        $this->colocationConfigsCache = FmSiteTypeColocation::active()->get()->keyBy('code');

        // Charger les mappings de références Excel -> Code
        $this->regionMappings = FmReferenceMapping::getAllCodesForTable('fm_regions');
        $this->classMappings = FmReferenceMapping::getAllCodesForTable('fm_site_classes');
        $this->energyMappings = FmReferenceMapping::getAllCodesForTable('fm_energy_sources');
        $this->typologyMappings = FmReferenceMapping::getAllCodesForTable('fm_maintenance_typologies');
        $this->tenantMappings = FmReferenceMapping::getAllCodesForTable('fm_tenants');
        $this->colocationMappings = FmReferenceMapping::getAllCodesForTable('fm_site_type_colocations');
    }

    /**
     * Traiter le fichier Excel
     */
    protected function processExcelFile(string $filePath): void
    {
        ini_set('memory_limit', '1G');

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        // Lire la feuille principale
        $sheet = $spreadsheet->getSheetByName('PARC_SITES_INWI');

        if (!$sheet) {
            throw new Exception('Feuille PARC_SITES_INWI introuvable');
        }

        $highestRow = $sheet->getHighestRow();

        // Commencer à la ligne 2 (ligne 1 = en-têtes)
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $this->processSiteRow($sheet, $row);
            } catch (Exception $e) {
                $this->failed++;
                $this->errors[] = [
                    'row' => $row,
                    'error' => $e->getMessage(),
                ];
            }

            // Libérer la mémoire tous les 100 sites
            if ($row % 100 === 0) {
                gc_collect_cycles();
            }
        }
    }

    /**
     * Lire et nettoyer une valeur de cellule
     */
    protected function getCellValue($sheet, string $cell): ?string
    {
        $value = $sheet->getCell($cell)->getValue();

        if ($value === null) {
            return null;
        }

        // Convertir en string et trimmer
        $value = trim((string)$value);

        // Retourner null si la valeur est vide après trim
        return $value === '' ? null : $value;
    }

    /**
     * Traiter une ligne de site
     */
    protected function processSiteRow($sheet, int $row): void
    {
        // Lire les données du site avec trim automatique
        $siteCode = $this->getCellValue($sheet, "A{$row}");
        $gsmId = $this->getCellValue($sheet, "B{$row}");
        $ziCode = $this->getCellValue($sheet, "C{$row}");
        $classCode = $this->getCellValue($sheet, "D{$row}");
        $energyCode = $this->getCellValue($sheet, "E{$row}");
        $typologyName = $this->getCellValue($sheet, "F{$row}");
        $colocationName = $this->getCellValue($sheet, "G{$row}");

        if (empty($siteCode)) {
            return; // Ligne vide
        }

        // Préparer les données du site
        $siteData = [
            'site_code' => $siteCode,
            'gsm_id' => $gsmId,
            'status' => 'active',
        ];

        // Résoudre les relations
        $siteData['fm_region_id'] = $this->resolveRegion($ziCode, $row);
        $siteData['fm_site_class_id'] = $this->resolveSiteClass($classCode, $row);
        $siteData['fm_energy_source_id'] = $this->resolveEnergySource($energyCode, $row);
        $siteData['fm_maintenance_typology_id'] = $this->resolveMaintenanceTypology($typologyName, $row);

        // Gérer la colocation
        $isColocation = !empty($colocationName);
        $siteData['is_colocation'] = $isColocation;

        if ($isColocation) {
            $colocationConfig = $this->resolveColocationConfig($colocationName, $row);
            $siteData['fm_site_type_colocation_id'] = $colocationConfig?->id;

            if ($colocationConfig) {
                $siteData['colocation_details'] = [
                    'config_name' => $colocationName,
                    'tenant_count' => $colocationConfig->tenant_count,
                    'tenants' => $colocationConfig->tenants,
                ];
            }
        }

        // Créer ou mettre à jour le site
        DB::transaction(function () use ($siteCode, $siteData, $colocationName) {
            $site = FmSite::updateOrCreate(
                ['site_code' => $siteCode],
                $siteData
            );

            if ($site->wasRecentlyCreated) {
                $this->created++;
            } else {
                $this->updated++;
            }

            // Gérer les tenants en colocation
            if ($site->is_colocation && !empty($colocationName)) {
                $this->attachTenants($site, $colocationName);
            }

            $this->successful++;
        });
    }

    /**
     * Résoudre la région
     */
    protected function resolveRegion(?string $ziCode, int $row): ?int
    {
        if (empty($ziCode)) {
            return null;
        }

        // Mapper la référence Excel au code réel
        $actualCode = $this->regionMappings[$ziCode] ?? $ziCode;

        $region = $this->regionsCache->get($actualCode);

        if (!$region) {
            $this->warnings[] = [
                'row' => $row,
                'field' => 'ZI',
                'value' => $ziCode,
                'message' => "Région introuvable: {$ziCode}" . ($actualCode !== $ziCode ? " (mappé à: {$actualCode})" : ""),
            ];
            return null;
        }

        return $region->id;
    }

    /**
     * Résoudre la classe de site
     */
    protected function resolveSiteClass(?string $classCode, int $row): ?int
    {
        if (empty($classCode)) {
            $this->warnings[] = [
                'row' => $row,
                'field' => 'Classification',
                'value' => null,
                'message' => 'Classification vide',
            ];
            return null;
        }

        // Mapper la référence Excel au code réel
        $actualCode = $this->classMappings[$classCode] ?? $classCode;

        $class = $this->classesCache->get($actualCode);

        if (!$class) {
            $this->warnings[] = [
                'row' => $row,
                'field' => 'Classification',
                'value' => $classCode,
                'message' => "Classe introuvable: {$classCode}" . ($actualCode !== $classCode ? " (mappé à: {$actualCode})" : ""),
            ];
            return null;
        }

        return $class->id;
    }

    /**
     * Résoudre la source d'énergie
     */
    protected function resolveEnergySource(?string $energyCode, int $row): ?int
    {
        if (empty($energyCode)) {
            return null;
        }

        // Mapper la référence Excel au code réel
        $actualCode = $this->energyMappings[$energyCode] ?? $energyCode;

        $energy = $this->energySourcesCache->get($actualCode);

        if (!$energy) {
            $this->warnings[] = [
                'row' => $row,
                'field' => 'Source d\'Energie',
                'value' => $energyCode,
                'message' => "Source d'énergie introuvable: {$energyCode}" . ($actualCode !== $energyCode ? " (mappé à: {$actualCode})" : ""),
            ];
            return null;
        }

        return $energy->id;
    }

    /**
     * Résoudre la typologie de maintenance
     */
    protected function resolveMaintenanceTypology(?string $typologyName, int $row): ?int
    {
        if (empty($typologyName)) {
            return null;
        }

        // Mapper la référence Excel au code réel
        $actualCode = $this->typologyMappings[$typologyName] ?? $typologyName;

        $typology = $this->typologiesCache->get($actualCode);

        if (!$typology) {
            $this->warnings[] = [
                'row' => $row,
                'field' => 'Typologie Maintenance',
                'value' => $typologyName,
                'message' => "Typologie introuvable: {$typologyName}" . ($actualCode !== $typologyName ? " (mappé à: {$actualCode})" : ""),
            ];
            return null;
        }

        return $typology->id;
    }

    /**
     * Résoudre la configuration de colocation
     */
    protected function resolveColocationConfig(?string $colocationName, int $row): ?FmSiteTypeColocation
    {
        if (empty($colocationName)) {
            return null;
        }

        // Mapper la référence Excel au code réel
        $actualCode = $this->colocationMappings[$colocationName] ?? $colocationName;

        $config = $this->colocationConfigsCache->get($actualCode);

        if (!$config) {
            $this->warnings[] = [
                'row' => $row,
                'field' => 'Colloc',
                'value' => $colocationName,
                'message' => "Configuration de colocation introuvable: {$colocationName}" . ($actualCode !== $colocationName ? " (mappé à: {$actualCode})" : ""),
            ];
            return null;
        }

        return $config;
    }

    /**
     * Attacher les tenants au site
     */
    protected function attachTenants(FmSite $site, string $colocationName): void
    {
        // Mapper la référence Excel au code réel
        $actualCode = $this->colocationMappings[$colocationName] ?? $colocationName;

        $config = $this->colocationConfigsCache->get($actualCode);

        if (!$config || empty($config->tenants)) {
            return;
        }

        // Détacher les tenants existants
        $site->tenants()->detach();

        // Attacher les nouveaux tenants
        foreach ($config->tenants as $rank => $tenantCode) {
            // Mapper le code du tenant (au cas où il y aurait des variations)
            $actualTenantCode = $this->tenantMappings[$tenantCode] ?? $tenantCode;

            $tenant = $this->tenantsCache->get($actualTenantCode);

            if (!$tenant) {
                continue;
            }

            $site->tenants()->attach($tenant->id, [
                'tenant_rank' => $rank + 1,
                'is_primary' => $rank === 0, // Le premier tenant est primaire
                'status' => 'active',
            ]);
        }
    }

    /**
     * Obtenir les statistiques d'import
     */
    public function getStatistics(): array
    {
        return [
            'total_processed' => $this->successful + $this->failed,
            'successful' => $this->successful,
            'failed' => $this->failed,
            'created' => $this->created,
            'updated' => $this->updated,
            'warnings' => count($this->warnings),
            'errors' => count($this->errors),
        ];
    }
}
