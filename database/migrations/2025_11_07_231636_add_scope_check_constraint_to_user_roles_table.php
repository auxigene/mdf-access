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
        // Ajouter une contrainte CHECK pour garantir qu'un seul scope est actif à la fois
        // Soit global (tous NULL), soit portfolio, soit program, soit project
        DB::statement('
            ALTER TABLE user_roles
            ADD CONSTRAINT user_roles_single_scope_check
            CHECK (
                (portfolio_id IS NOT NULL AND program_id IS NULL AND project_id IS NULL) OR
                (portfolio_id IS NULL AND program_id IS NOT NULL AND project_id IS NULL) OR
                (portfolio_id IS NULL AND program_id IS NULL AND project_id IS NOT NULL) OR
                (portfolio_id IS NULL AND program_id IS NULL AND project_id IS NULL)
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            ALTER TABLE user_roles
            DROP CONSTRAINT IF EXISTS user_roles_single_scope_check
        ');
    }
};
