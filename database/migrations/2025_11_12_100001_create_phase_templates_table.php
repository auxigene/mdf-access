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
        Schema::create('phase_templates', function (Blueprint $table) {
            $table->id();

            // Méthodologie parente
            $table->foreignId('methodology_template_id')
                  ->constrained('methodology_templates')
                  ->onDelete('cascade');

            // Hiérarchie de phases (permet sous-phases)
            // NULL = phase racine, NOT NULL = sous-phase
            $table->foreignId('parent_phase_id')
                  ->nullable()
                  ->constrained('phase_templates')
                  ->onDelete('cascade');

            // Identification
            $table->string('name');
            $table->string('name_fr')->nullable();
            $table->text('description')->nullable();
            $table->enum('phase_type', [
                'initiation',
                'planning',
                'execution',
                'monitoring',
                'closure',
                'custom'
            ])->nullable();

            // Séquençage
            $table->integer('sequence');
            $table->integer('level')->default(1); // Niveau hiérarchique (1=racine, 2=sous-phase, etc.)

            // Planification (durée typique)
            $table->integer('typical_duration_days')->nullable();
            $table->decimal('typical_duration_percent', 5, 2)->nullable(); // % de la durée totale du projet

            // Contenu de la phase (JSON pour flexibilité)
            $table->json('key_activities')->nullable();      // Activités clés à réaliser
            $table->json('key_deliverables')->nullable();    // Livrables attendus
            $table->json('entry_criteria')->nullable();      // Critères d'entrée
            $table->json('exit_criteria')->nullable();       // Critères de sortie

            $table->timestamps();

            // Index
            $table->index('methodology_template_id');
            $table->index('parent_phase_id');
            $table->index('phase_type');
            $table->index(['methodology_template_id', 'sequence']);
            $table->index(['methodology_template_id', 'parent_phase_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phase_templates');
    }
};
