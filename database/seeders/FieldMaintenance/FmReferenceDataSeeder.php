<?php

namespace Database\Seeders\FieldMaintenance;

use Illuminate\Database\Seeder;

class FmReferenceDataSeeder extends Seeder
{
    /**
     * Seed toutes les données de référence pour Field Maintenance
     * dans le bon ordre (en respectant les dépendances)
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('  Import des données de référence FM - Sites INWI');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('');

        // 1. Régions (pas de dépendances)
        $this->call(FmRegionSeeder::class);

        // 2. Classes de sites (pas de dépendances)
        $this->call(FmSiteClassSeeder::class);

        // 3. Sources d'énergie (pas de dépendances)
        $this->call(FmEnergySourceSeeder::class);

        // 4. Typologies de maintenance (pas de dépendances)
        $this->call(FmMaintenanceTypologySeeder::class);

        // 5. Tenants (pas de dépendances)
        $this->call(FmTenantSeeder::class);

        // 6. Configurations de colocation (dépend des tenants)
        $this->call(FmSiteTypeColocationSeeder::class);

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('  ✅ Toutes les données de référence ont été importées');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('');
    }
}
