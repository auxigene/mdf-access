<?php

namespace App\Models\FieldMaintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FmReferenceMapping extends Model
{
    protected $table = 'fm_references_mapping';

    protected $fillable = [
        'table_name',
        'excel_reference',
        'code',
    ];

    /**
     * Trouver le code correspondant à une référence Excel
     *
     * @param string $tableName Nom de la table concernée (ex: 'fm_regions')
     * @param string $excelReference Référence telle qu'elle apparaît dans Excel
     * @return string|null Le code correspondant ou null si non trouvé
     */
    public static function findCode(string $tableName, string $excelReference): ?string
    {
        // Trim la référence pour gérer les espaces en début/fin
        $excelReference = trim($excelReference);

        $cacheKey = "fm_ref_mapping:{$tableName}:{$excelReference}";

        return Cache::remember($cacheKey, 3600, function () use ($tableName, $excelReference) {
            return self::where('table_name', $tableName)
                ->where('excel_reference', $excelReference)
                ->value('code');
        });
    }

    /**
     * Trouver tous les codes pour une table donnée (retourne un tableau associatif référence => code)
     *
     * @param string $tableName Nom de la table concernée
     * @return array
     */
    public static function getAllCodesForTable(string $tableName): array
    {
        $cacheKey = "fm_ref_mapping_all:{$tableName}";

        return Cache::remember($cacheKey, 3600, function () use ($tableName) {
            return self::where('table_name', $tableName)
                ->pluck('code', 'excel_reference')
                ->toArray();
        });
    }

    /**
     * Vider le cache pour une table donnée
     *
     * @param string $tableName
     * @return void
     */
    public static function clearCacheForTable(string $tableName): void
    {
        Cache::forget("fm_ref_mapping_all:{$tableName}");

        // Note: Les caches individuels (findCode) expireront automatiquement après 1h
        // ou peuvent être vidés manuellement si nécessaire
    }
}
