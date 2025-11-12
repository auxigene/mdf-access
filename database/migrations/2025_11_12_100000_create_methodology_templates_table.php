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
        Schema::create('methodology_templates', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('name');
            $table->string('name_fr')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category', ['pmbok', 'agile', 'hybrid', 'custom'])->default('custom');

            // Multi-tenant: NULL = template système (disponible pour tous)
            // NOT NULL = template spécifique à une organisation
            $table->foreignId('organization_id')
                  ->nullable()
                  ->constrained('organizations')
                  ->onDelete('cascade');

            // Héritage de méthodologie
            // Permet à une méthodologie d'hériter des phases d'une autre
            $table->foreignId('parent_methodology_id')
                  ->nullable()
                  ->constrained('methodology_templates')
                  ->onDelete('restrict');

            // Template système (non modifiable/supprimable)
            $table->boolean('is_system')->default(false);

            // Statut
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index
            $table->index('organization_id');
            $table->index('category');
            $table->index('parent_methodology_id');
            $table->index('is_system');
            $table->index(['organization_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('methodology_templates');
    }
};
