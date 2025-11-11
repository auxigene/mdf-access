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
            // Référence du projet côté client (numéro de dossier, bon de commande, etc.)
            $table->string('client_reference')
                  ->nullable()
                  ->after('client_organization_id');

            $table->index('client_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['client_reference']);
            $table->dropColumn('client_reference');
        });
    }
};
