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
        Schema::table('users', function (Blueprint $table) {
            // Supprimer l'index sur user_type
            $table->dropIndex(['user_type']);

            // Supprimer la colonne user_type (redondante avec organization->type)
            $table->dropColumn('user_type');

            // Ajouter is_system_admin pour les super-admins sans organisation
            $table->boolean('is_system_admin')
                  ->default(false)
                  ->after('organization_id')
                  ->comment('Super-admin système (organization_id peut être NULL)');

            $table->index('is_system_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer is_system_admin
            $table->dropIndex(['is_system_admin']);
            $table->dropColumn('is_system_admin');

            // Restaurer user_type
            $table->enum('user_type', ['internal', 'client', 'partner'])
                  ->default('internal')
                  ->after('organization_id');

            $table->index('user_type');
        });
    }
};
