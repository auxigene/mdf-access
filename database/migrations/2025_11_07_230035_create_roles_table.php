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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Super Admin, PMO, Chef de Projet, Client Admin, etc.
            $table->string('slug')->unique(); // super_admin, pmo, project_manager, client_admin, etc.
            $table->text('description')->nullable();
            $table->enum('scope', ['global', 'organization', 'project'])->default('project');
            // Si le rôle est spécifique à une organisation (ex: Client Admin pour l'org X)
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index('scope');
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
