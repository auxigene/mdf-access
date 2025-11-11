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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // view_projects, edit_projects, delete_projects, etc.
            $table->string('slug')->unique(); // view_projects, edit_projects, etc.
            $table->text('description')->nullable();
            $table->string('resource'); // projects, tasks, budgets, risks, etc.
            $table->enum('action', ['view', 'create', 'edit', 'delete', 'export', 'approve']); // CRUD operations
            $table->timestamps();

            $table->index('resource');
            $table->index('action');
            $table->unique(['resource', 'action']); // Une seule permission par resource/action
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
