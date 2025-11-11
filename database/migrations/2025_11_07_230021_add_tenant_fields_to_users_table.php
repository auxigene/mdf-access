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
            // À quelle organisation appartient l'utilisateur
            $table->foreignId('organization_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('organizations')
                  ->onDelete('cascade');

            // Type d'utilisateur pour le multi-tenant
            $table->enum('user_type', ['internal', 'client', 'partner'])
                  ->default('internal')
                  ->after('organization_id');

            $table->index('organization_id');
            $table->index('user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['user_type']);
            $table->dropColumn(['organization_id', 'user_type']);
        });
    }
};
