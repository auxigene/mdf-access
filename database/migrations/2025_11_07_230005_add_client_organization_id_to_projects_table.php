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
            // Le client propriétaire du projet (pour le multi-tenant)
            // Vient après executor_organization_id (qui a été renommé dans la migration précédente)
            $table->foreignId('client_organization_id')
                  ->after('executor_organization_id')
                  ->constrained('organizations')
                  ->onDelete('cascade');

            $table->index('client_organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_organization_id']);
            $table->dropIndex(['client_organization_id']);
            $table->dropColumn('client_organization_id');
        });
    }
};
