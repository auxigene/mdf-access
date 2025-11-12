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
        Schema::table('project_phases', function (Blueprint $table) {
            // Référence au template utilisé (nullable car phases custom possibles)
            $table->foreignId('phase_template_id')
                  ->nullable()
                  ->after('project_id')
                  ->constrained('phase_templates')
                  ->onDelete('set null');

            // Hiérarchie de phases réelles (permet sous-phases dans les projets)
            // NULL = phase racine, NOT NULL = sous-phase
            $table->foreignId('parent_phase_id')
                  ->nullable()
                  ->after('phase_template_id')
                  ->constrained('project_phases')
                  ->onDelete('cascade');

            // Niveau hiérarchique (facilite les requêtes)
            $table->integer('level')
                  ->default(1)
                  ->after('parent_phase_id');

            // Index pour performance
            $table->index('phase_template_id');
            $table->index('parent_phase_id');
            $table->index(['project_id', 'parent_phase_id']);
            $table->index(['project_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_phases', function (Blueprint $table) {
            // Supprimer les foreign keys d'abord
            $table->dropForeign(['phase_template_id']);
            $table->dropForeign(['parent_phase_id']);

            // Supprimer les index
            $table->dropIndex(['phase_template_id']);
            $table->dropIndex(['parent_phase_id']);
            $table->dropIndex(['project_id', 'parent_phase_id']);
            $table->dropIndex(['project_id', 'level']);

            // Supprimer les colonnes
            $table->dropColumn(['phase_template_id', 'parent_phase_id', 'level']);
        });
    }
};
