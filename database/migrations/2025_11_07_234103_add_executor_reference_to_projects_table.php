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
        Schema::table('projects', function (Blueprint $table) {
            // Référence du projet côté exécutant (code interne SAMSIC ou partenaire)
            $table->string('executor_reference')
                  ->nullable()
                  ->after('executor_organization_id');

            $table->index('executor_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['executor_reference']);
            $table->dropColumn('executor_reference');
        });
    }
};
