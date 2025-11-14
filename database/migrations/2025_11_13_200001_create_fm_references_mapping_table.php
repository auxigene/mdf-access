<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fm_references_mapping', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 100)->comment('Nom de la table concernée (fm_regions, fm_site_classes, etc.)');
            $table->string('excel_reference', 255)->comment('Référence telle qu\'elle apparaît dans Excel (peut contenir des espaces, erreurs de saisie, etc.)');
            $table->string('code', 100)->comment('Code unique dans la base de données');
            $table->timestamps();

            // Index pour améliorer les performances de recherche
            $table->index(['table_name', 'excel_reference'], 'idx_fm_ref_mapping_lookup');
            $table->index('code', 'idx_fm_ref_mapping_code');

            // Contrainte unique sur la combinaison table_name + excel_reference
            // Car une même référence Excel ne peut pointer que vers un seul code
            $table->unique(['table_name', 'excel_reference'], 'uq_fm_ref_mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_references_mapping');
    }
};
