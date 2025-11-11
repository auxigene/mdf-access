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
        Schema::create('project_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            // Rôle de l'organisation dans le projet
            // sponsor : Client qui finance et bénéficie
            // moa : Maître d'Ouvrage qui maîtrise le scope et la qualité
            // moe : Maître d'Œuvre qui exécute/produit les livrables
            // subcontractor : Sous-traitant (MOE partiel)
            $table->enum('role', ['sponsor', 'moa', 'moe', 'subcontractor']);

            // Référence de l'organisation pour ce projet
            // Ex: Pour MOA: "SAMSIC-MAINT-2025-001", Pour sous-traitant: "ST-ELEC-2025-05"
            $table->string('reference')->nullable();

            // Description du scope pour sous-traitance partielle
            // Ex: "Travaux électriques", "Plomberie", etc.
            $table->text('scope_description')->nullable();

            // Marquer le MOE principal vs sous-traitants
            $table->boolean('is_primary')->default(false);

            // Période d'intervention
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Statut de l'intervention
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');

            $table->timestamps();

            // Une organisation ne peut avoir qu'un seul rôle par projet
            $table->unique(['project_id', 'organization_id', 'role'], 'project_org_role_unique');

            // Index pour performance
            $table->index('project_id');
            $table->index('organization_id');
            $table->index('role');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_organizations');
    }
};
