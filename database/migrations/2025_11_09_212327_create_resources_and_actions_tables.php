<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Permissions Flexibles - Tables Resources, Actions et Applicabilité
 *
 * Cette migration crée 3 tables pour un système de permissions dynamique :
 * 1. resources : Liste des ressources (projects, tasks, users, etc.)
 * 2. actions : Liste des actions (view, create, edit, delete, etc.)
 * 3. resource_actions : Matrice d'applicabilité (quelles actions pour quelles ressources)
 *
 * CONTEXTE :
 * - Avant : permissions.resource et permissions.action étaient des VARCHAR (figés)
 * - Après : Ressources et actions deviennent dynamiques et configurables via UI admin
 *
 * AVANTAGE :
 * - Évite création de ~150 permissions absurdes (ex: "archive_users", "duplicate_organizations")
 * - Réduction ~54% du nombre de permissions (390 → ~180)
 * - Interface admin claire avec matrice visuelle ✅/⬜
 *
 * DOCUMENTATION : docs/ARCHITECTURE_EVOLUTION_PERMISSIONS_FLEXIBLES.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table resources - Vérifier existence avant création
        if (!Schema::hasTable('resources')) {
            Schema::create('resources', function (Blueprint $table) {
                $table->id();
                $table->string('name');  // "Projets"
                $table->string('slug')->unique();  // "projects"
                $table->text('description')->nullable();
                $table->string('model_class')->nullable();  // "App\Models\Project"
                $table->string('icon', 50)->nullable();  // "folder" (pour UI)
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Index pour performances
                $table->index('slug');
                $table->index('is_active');
            });
        }

        // 2. Table actions - Vérifier existence avant création
        if (!Schema::hasTable('actions')) {
            Schema::create('actions', function (Blueprint $table) {
                $table->id();
                $table->string('name');  // "Voir"
                $table->string('slug')->unique();  // "view"
                $table->text('description')->nullable();
                $table->string('verb', 50)->nullable();  // "read", "write", "delete" (pour API)
                $table->string('color', 20)->nullable();  // "#4CAF50" (pour UI)
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Index pour performances
                $table->index('slug');
                $table->index('is_active');
            });
        }

        // 3. Table pivot resource_actions (applicabilité) - Vérifier existence avant création
        if (!Schema::hasTable('resource_actions')) {
            Schema::create('resource_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('resource_id')->constrained()->onDelete('cascade');
                $table->foreignId('action_id')->constrained()->onDelete('cascade');
                $table->boolean('is_default_enabled')->default(true);
                $table->timestamps();

                // Contrainte d'unicité : une action ne peut être applicable qu'une fois par ressource
                $table->unique(['resource_id', 'action_id']);

                // Index pour performances
                $table->index('resource_id');
                $table->index('action_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer dans l'ordre inverse (pour contraintes FK)
        Schema::dropIfExists('resource_actions');
        Schema::dropIfExists('actions');
        Schema::dropIfExists('resources');
    }
};
