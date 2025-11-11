<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Utiliser SQL brut pour supprimer les contraintes/index s'ils existent
        DB::statement('ALTER TABLE projects DROP CONSTRAINT IF EXISTS projects_executor_organization_id_foreign');
        DB::statement('DROP INDEX IF EXISTS projects_executor_organization_id_index');
        DB::statement('DROP INDEX IF EXISTS projects_executor_reference_index');

        // Supprimer les colonnes si elles existent
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'executor_organization_id')) {
                $table->dropColumn('executor_organization_id');
            }

            if (Schema::hasColumn('projects', 'executor_reference')) {
                $table->dropColumn('executor_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Restaurer les colonnes si rollback
            $table->foreignId('executor_organization_id')
                  ->nullable()
                  ->after('program_id')
                  ->constrained('organizations')
                  ->onDelete('cascade');

            $table->string('executor_reference')
                  ->nullable()
                  ->after('executor_organization_id');

            $table->index('executor_organization_id');
            $table->index('executor_reference');
        });
    }
};
