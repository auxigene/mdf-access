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
        // Contrainte 1: Le champ is_primary ne peut être true que pour moe/subcontractor
        // sponsor et moa sont uniques par définition donc is_primary n'a pas de sens
        DB::statement("
            ALTER TABLE project_organizations
            ADD CONSTRAINT project_org_primary_only_for_moe_check
            CHECK (
                (role IN ('moe', 'subcontractor')) OR
                (role IN ('sponsor', 'moa') AND is_primary = false)
            )
        ");

        // Contrainte 2: Un sponsor ne doit pas avoir de scope_description (c'est tout le projet)
        DB::statement("
            ALTER TABLE project_organizations
            ADD CONSTRAINT project_org_sponsor_no_scope_check
            CHECK (
                role != 'sponsor' OR
                (role = 'sponsor' AND scope_description IS NULL)
            )
        ");

        // Contrainte 3: Un MOA ne doit pas avoir de scope_description (il gère tout)
        DB::statement("
            ALTER TABLE project_organizations
            ADD CONSTRAINT project_org_moa_no_scope_check
            CHECK (
                role != 'moa' OR
                (role = 'moa' AND scope_description IS NULL)
            )
        ");

        // Index unique partiel : Un seul sponsor actif par projet
        DB::statement("
            CREATE UNIQUE INDEX project_org_unique_active_sponsor
            ON project_organizations (project_id)
            WHERE role = 'sponsor' AND status = 'active'
        ");

        // Index unique partiel : Un seul MOA actif par projet
        DB::statement("
            CREATE UNIQUE INDEX project_org_unique_active_moa
            ON project_organizations (project_id)
            WHERE role = 'moa' AND status = 'active'
        ");

        // Index unique partiel : Un seul MOE primary actif par projet
        DB::statement("
            CREATE UNIQUE INDEX project_org_unique_primary_moe
            ON project_organizations (project_id)
            WHERE role IN ('moe', 'subcontractor') AND is_primary = true AND status = 'active'
        ");

        /*
         * RÈGLES MÉTIER À APPLIQUER AU NIVEAU APPLICATIF (Validations Laravel):
         *
         * 1. Un projet DOIT avoir exactement UN sponsor actif
         * 2. Un projet DOIT avoir exactement UN MOA actif
         * 3. Un projet DOIT avoir AU MOINS UN MOE actif (primary ou subcontractor)
         * 4. Si plusieurs MOE/subcontractors, UN SEUL doit être marqué is_primary = true
         * 5. Les dates start_date/end_date des sous-traitants doivent être dans les bornes du projet
         *
         * Ces règles seront implémentées dans:
         * - app/Models/ProjectOrganization.php (validation)
         * - app/Services/ProjectOrganizationService.php (logique métier)
         * - app/Http/Requests/StoreProjectOrganizationRequest.php (validation requête)
         */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les index uniques partiels
        DB::statement("DROP INDEX IF EXISTS project_org_unique_active_sponsor");
        DB::statement("DROP INDEX IF EXISTS project_org_unique_active_moa");
        DB::statement("DROP INDEX IF EXISTS project_org_unique_primary_moe");

        // Supprimer les contraintes CHECK
        DB::statement("
            ALTER TABLE project_organizations
            DROP CONSTRAINT IF EXISTS project_org_primary_only_for_moe_check
        ");

        DB::statement("
            ALTER TABLE project_organizations
            DROP CONSTRAINT IF EXISTS project_org_sponsor_no_scope_check
        ");

        DB::statement("
            ALTER TABLE project_organizations
            DROP CONSTRAINT IF EXISTS project_org_moa_no_scope_check
        ");
    }
};
