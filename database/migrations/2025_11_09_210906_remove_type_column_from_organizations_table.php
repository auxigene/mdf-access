<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration vers Architecture Multi-Tenant Pure
 *
 * Cette migration supprime la colonne 'type' de la table organizations.
 *
 * CONTEXTE :
 * - Avant : Une organisation avait un TYPE FIXE ('Internal', 'Client', 'Partner')
 * - Après : Le rôle d'une organisation est CONTEXTUEL, défini par projet via project_organizations
 *
 * IMPACT :
 * - Une organisation peut être Cliente sur Projet A et MOE sur Projet B
 * - Plus de notion artificielle de "propriétaire de plateforme"
 * - Architecture plus flexible et réaliste
 *
 * DOCUMENTATION : docs/ARCHITECTURE_CHANGE_MULTI_TENANT_PURE.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Supprimer la colonne type qui déterminait le rôle fixe
            // Le rôle est maintenant défini par projet dans project_organizations.role
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Restaurer la colonne type si rollback nécessaire
            // Note : Les données type seront perdues, mais elles étaient redondantes
            $table->string('type', 50)->nullable()->after('name');
        });
    }
};
