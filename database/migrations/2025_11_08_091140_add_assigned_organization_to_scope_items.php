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
        // Ajouter assigned_organization_id aux deliverables
        Schema::table('deliverables', function (Blueprint $table) {
            $table->foreignId('assigned_organization_id')
                  ->nullable()
                  ->after('wbs_element_id')
                  ->constrained('organizations')
                  ->onDelete('set null')
                  ->comment('Organisation assignée pour produire ce livrable (MOE ou sous-traitant)');

            $table->index('assigned_organization_id');
        });

        // Ajouter assigned_organization_id aux tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_organization_id')
                  ->nullable()
                  ->after('deliverable_id')
                  ->constrained('organizations')
                  ->onDelete('set null')
                  ->comment('Organisation assignée pour exécuter cette tâche');

            $table->index('assigned_organization_id');
        });

        // Ajouter assigned_organization_id aux wbs_elements
        Schema::table('wbs_elements', function (Blueprint $table) {
            $table->foreignId('assigned_organization_id')
                  ->nullable()
                  ->after('parent_id')
                  ->constrained('organizations')
                  ->onDelete('set null')
                  ->comment('Organisation assignée pour cet élément WBS');

            $table->index('assigned_organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->dropForeign(['assigned_organization_id']);
            $table->dropIndex(['assigned_organization_id']);
            $table->dropColumn('assigned_organization_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_organization_id']);
            $table->dropIndex(['assigned_organization_id']);
            $table->dropColumn('assigned_organization_id');
        });

        Schema::table('wbs_elements', function (Blueprint $table) {
            $table->dropForeign(['assigned_organization_id']);
            $table->dropIndex(['assigned_organization_id']);
            $table->dropColumn('assigned_organization_id');
        });
    }
};
