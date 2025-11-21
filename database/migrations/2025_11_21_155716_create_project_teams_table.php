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
        Schema::create('project_teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Project this team member belongs to');

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('User assigned to project team');

            $table->foreignId('role_id')
                ->constrained()
                ->comment('Role assigned to user in this project');

            $table->date('start_date')
                ->nullable()
                ->comment('Team member assignment start date');

            $table->date('end_date')
                ->nullable()
                ->comment('Team member assignment end date');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Whether this team assignment is currently active');

            $table->boolean('is_primary')
                ->default(false)
                ->comment('Primary project manager or coordinator');

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who made this assignment');

            $table->timestamp('assigned_at')
                ->nullable()
                ->comment('When this assignment was made');

            $table->text('notes')
                ->nullable()
                ->comment('Additional notes about this assignment');

            $table->timestamps();

            // Indexes for performance
            $table->unique(['project_id', 'user_id', 'role_id'], 'unique_project_user_role');
            $table->index(['project_id', 'is_active'], 'idx_project_active');
            $table->index(['user_id', 'is_active'], 'idx_user_active');
            $table->index('role_id', 'idx_role');
            $table->index('start_date', 'idx_start_date');
            $table->index('end_date', 'idx_end_date');
        });

        DB::statement('ALTER TABLE project_teams COMMENT = "Project team member assignments with roles"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_teams');
    }
};
