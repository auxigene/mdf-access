<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Créer ACL Resources et Renommer Tables
 *
 * PROBLÈME RÉSOLU :
 * - La table 'resources' existe déjà pour les ressources humaines PMBOK
 * - Conflit de nom avec notre système de permissions flexibles
 *
 * SOLUTION :
 * - Créer 'acl_resources' pour le système de permissions ACL
 * - Renommer 'resource_actions' en 'acl_resource_actions'
 * - Mettre à jour les contraintes de clés étrangères
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Créer la table acl_resources (système de permissions)
        Schema::create('acl_resources', function (Blueprint $table) {
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

        // 2. Supprimer la contrainte FK de resource_actions vers resources (PMBOK)
        Schema::table('resource_actions', function (Blueprint $table) {
            $table->dropForeign(['resource_id']);
        });

        // 3. Renommer resource_actions en acl_resource_actions
        Schema::rename('resource_actions', 'acl_resource_actions');

        // 4. Ajouter nouvelle contrainte FK vers acl_resources
        Schema::table('acl_resource_actions', function (Blueprint $table) {
            $table->foreign('resource_id')
                  ->references('id')
                  ->on('acl_resources')
                  ->onDelete('cascade');
        });

        echo "\n✅ Tables ACL créées et renommées avec succès\n";
        echo "   - acl_resources créée\n";
        echo "   - resource_actions → acl_resource_actions\n";
        echo "   - FK mise à jour vers acl_resources\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Supprimer FK vers acl_resources
        Schema::table('acl_resource_actions', function (Blueprint $table) {
            $table->dropForeign(['resource_id']);
        });

        // 2. Renommer acl_resource_actions en resource_actions
        Schema::rename('acl_resource_actions', 'resource_actions');

        // 3. Recréer FK vers resources (PMBOK)
        Schema::table('resource_actions', function (Blueprint $table) {
            $table->foreign('resource_id')
                  ->references('id')
                  ->on('resources')
                  ->onDelete('cascade');
        });

        // 4. Supprimer acl_resources
        Schema::dropIfExists('acl_resources');
    }
};
