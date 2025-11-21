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
        // PostgreSQL approach: Alter column type and add check constraint
        DB::statement("ALTER TABLE roles ALTER COLUMN scope TYPE VARCHAR(20)");

        // Add check constraint for allowed scope values
        DB::statement("ALTER TABLE roles ADD CONSTRAINT roles_scope_check
            CHECK (scope IN ('global', 'organization', 'portfolio', 'program', 'project', 'wbs_element', 'task'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the check constraint
        DB::statement("ALTER TABLE roles DROP CONSTRAINT IF EXISTS roles_scope_check");

        // Revert to original enum values (remove any rows with new scopes first)
        DB::statement("DELETE FROM roles WHERE scope IN ('wbs_element', 'task', 'portfolio', 'program')");

        // Set column type back to original enum
        DB::statement("ALTER TABLE roles ALTER COLUMN scope TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE roles ADD CONSTRAINT roles_scope_check_original
            CHECK (scope IN ('global', 'organization', 'project'))");
    }
};
