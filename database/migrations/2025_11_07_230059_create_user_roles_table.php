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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');

            // Un utilisateur peut avoir un rôle global OU scopé sur portfolio/program/project
            // Seulement UN de ces champs doit être rempli (NULL = rôle global)
            $table->foreignId('portfolio_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');

            $table->timestamps();

            // Un utilisateur peut avoir plusieurs rôles, mais pas le même rôle deux fois sur le même scope
            $table->unique(['user_id', 'role_id', 'portfolio_id', 'program_id', 'project_id'], 'user_role_scope_unique');

            $table->index('user_id');
            $table->index('role_id');
            $table->index('portfolio_id');
            $table->index('program_id');
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
