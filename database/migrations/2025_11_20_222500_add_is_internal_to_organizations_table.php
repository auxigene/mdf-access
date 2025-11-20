<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajouter flag is_internal aux organizations
 *
 * CONTEXTE :
 * - L'architecture pure contextuelle nécessite toujours un moyen d'identifier
 *   l'organisation interne (SAMSIC) pour le bypass RLS
 * - Au lieu d'avoir une colonne 'type', on utilise un simple flag booléen
 * - Seule SAMSIC MAINTENANCE MAROC aura is_internal = true
 *
 * UTILISATION :
 * - System Admin : bypass complet (via is_system_admin sur users)
 * - Organisation interne : bypass complet (via is_internal sur organizations)
 * - Autres organisations : filtrées sur participations
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Ajouter flag is_internal (par défaut false)
            $table->boolean('is_internal')->default(false)->after('status');
        });

        // Marquer SAMSIC MAINTENANCE MAROC comme organisation interne
        // L'ID 1 correspond à SAMSIC d'après le backup DB
        DB::table('organizations')
            ->where('id', 1)
            ->update(['is_internal' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
    }
};
