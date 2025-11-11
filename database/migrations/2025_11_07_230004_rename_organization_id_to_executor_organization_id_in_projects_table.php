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
            // Renommer organization_id en executor_organization_id pour clarifier le rôle
            // executor_organization_id = qui EXÉCUTE le projet (SAMSIC ou partenaire)
            // client_organization_id = qui SPONSORISE le projet (le client)
            $table->renameColumn('organization_id', 'executor_organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('executor_organization_id', 'organization_id');
        });
    }
};
