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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nom/description de la clé API');
            $table->string('key')->unique()->comment('Clé API (hashée)');
            $table->string('api_type')->index()->comment('Type d\'API (excel_update, reporting, analytics, etc.)');
            $table->enum('access_level', ['read', 'write', 'admin'])->default('read')->comment('Niveau d\'accès');
            $table->json('permissions')->nullable()->comment('Permissions granulaires (JSON)');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->comment('Utilisateur associé');
            $table->timestamp('expires_at')->nullable()->comment('Date d\'expiration');
            $table->timestamp('last_used_at')->nullable()->comment('Dernière utilisation');
            $table->boolean('is_active')->default(true)->comment('Clé active/inactive');
            $table->timestamps();

            // Index composé pour optimiser les requêtes fréquentes
            $table->index(['api_type', 'access_level']);
            $table->index(['is_active', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
