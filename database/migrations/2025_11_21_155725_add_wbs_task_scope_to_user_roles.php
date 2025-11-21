<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->foreignId('wbs_element_id')
                ->nullable()
                ->after('project_id')
                ->constrained('wbs_elements')
                ->onDelete('cascade')
                ->comment('Optional WBS element scope for this role assignment');

            $table->foreignId('task_id')
                ->nullable()
                ->after('wbs_element_id')
                ->constrained('tasks')
                ->onDelete('cascade')
                ->comment('Optional task scope for this role assignment');

            // Add indexes for the new columns
            $table->index('wbs_element_id');
            $table->index('task_id');
        });

        // Drop the old unique constraint
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropUnique('user_role_scope_unique');
        });

        // Add new unique constraint that includes WBS and task
        Schema::table('user_roles', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'role_id', 'portfolio_id', 'program_id', 'project_id', 'wbs_element_id', 'task_id'],
                'user_roles_unique_extended'
            );
        });

        // Add check constraint to ensure only ONE scope is set at a time
        // This prevents having both project_id AND task_id set simultaneously
        DB::statement('ALTER TABLE user_roles ADD CONSTRAINT check_single_scope
            CHECK (
                (portfolio_id IS NOT NULL)::integer +
                (program_id IS NOT NULL)::integer +
                (project_id IS NOT NULL)::integer +
                (wbs_element_id IS NOT NULL)::integer +
                (task_id IS NOT NULL)::integer <= 1
            )');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraint
        DB::statement('ALTER TABLE user_roles DROP CONSTRAINT IF EXISTS check_single_scope');

        // Drop the extended unique constraint
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropUnique('user_roles_unique_extended');
        });

        // Re-create the old unique constraint
        Schema::table('user_roles', function (Blueprint $table) {
            $table->unique(['user_id', 'role_id', 'portfolio_id', 'program_id', 'project_id'], 'user_role_scope_unique');
        });

        // Drop the new columns
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropIndex(['wbs_element_id']);
            $table->dropIndex(['task_id']);
            $table->dropForeign(['wbs_element_id']);
            $table->dropForeign(['task_id']);
            $table->dropColumn(['wbs_element_id', 'task_id']);
        });
    }
};
