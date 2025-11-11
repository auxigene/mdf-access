<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganisationsInternesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisations = [
            ['name' => 'Stock', 'description' => 'Gestion des stocks et inventaires'],
            ['name' => 'Achats', 'description' => 'Service des achats et approvisionnements'],
            ['name' => 'Facturation', 'description' => 'Service de facturation et comptabilité'],
            ['name' => 'PMO', 'description' => 'Project Management Office - Bureau de gestion de projets'],
            ['name' => 'Méthodes', 'description' => 'Service des méthodes et processus'],
        ];

        $this->command->info("Création des organisations internes...");

        foreach ($organisations as $org) {
            // Vérifier si l'organisation existe déjà
            $exists = DB::table('organizations')
                ->where('name', $org['name'])
                // 'type' supprimé : Plus de type fixe, rôle défini par projet
                ->exists();

            if ($exists) {
                $this->command->warn("⊘ {$org['name']} existe déjà, ignorée.");
                continue;
            }

            DB::table('organizations')->insert([
                'name' => $org['name'],
                // 'type' supprimé : Architecture multi-tenant pure
                'address' => "Siege SAMSIC MAINTENANCE Maroc",
                'ville' => "Casablanca",
                'contact_info' => json_encode(['description' => $org['description']]),
                'logo' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("✓ {$org['name']} créée avec succès.");
        }

        $this->command->info("========================================");
        $this->command->info("Organisations internes créées avec succès!");
        $this->command->info("========================================");
    }
}
