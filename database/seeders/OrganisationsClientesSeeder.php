<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OrganisationsClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Chemin vers le fichier Excel
        $filePath = base_path('ClientsSamsic.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Le fichier ClientsSamsic.xlsx n'existe pas à la racine du projet.");
            return;
        }

        $this->command->info("Chargement du fichier Excel...");

        try {
            // Charger le fichier Excel
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Supprimer la première ligne (en-têtes)
            $headers = array_shift($rows);

            $this->command->info("En-têtes détectés: " . implode(', ', array_filter($headers)));
            $this->command->info("Nombre de lignes à importer: " . count($rows));

            $imported = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                // Ignorer les lignes vides
                if (empty(array_filter($row))) {
                    $skipped++;
                    continue;
                }

                // Mapper les colonnes selon la structure du fichier Excel
                // Ajustez les index selon votre fichier
                $name = $row[1] ?? null; // Première colonne = nom du client
                $address = $row[4] ?? null; // Deuxième colonne = adresse
                $ville = $row[6] ?? null; // Troisième colonne = ville
                
                $phone = $row[7] ?? null; // Quatrième colonne = téléphone
                $fax = $row[8] ?? null; // Cinquième colonne = téléphone
                $gsm = $row[9] ?? null; // Sixième colonne = téléphone
                $email = $row[10] ?? null; // Septème colonne = email

                if (empty($name)) {
                    $this->command->warn("Ligne " . ($index + 2) . ": Nom vide, ignorée.");
                    $skipped++;
                    continue;
                }

                // Préparer les informations de contact
                $contactInfo = [];
                if ($phone) $contactInfo['phone'] = $phone;
                if ($fax) $contactInfo['fax'] = $fax;
                if ($gsm) $contactInfo['gsm'] = $gsm;
                if ($email) $contactInfo['email'] = $email;

                // Créer l'organisation
                DB::table('organizations')->insert([
                    'name' => $name,
                    // 'type' supprimé : Architecture multi-tenant pure - rôle défini par projet
                    'address' => $address,
                    'ville' => $ville,
                    'contact_info' => !empty($contactInfo) ? json_encode($contactInfo) : null,
                    'logo' => null,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $imported++;
                $this->command->info("✓ Importé: {$name}");
            }

            DB::commit();

            $this->command->info("========================================");
            $this->command->info("Importation terminée!");
            $this->command->info("✓ Clients importés: {$imported}");
            $this->command->warn("⊘ Lignes ignorées: {$skipped}");
            $this->command->info("========================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Erreur lors de l'importation: " . $e->getMessage());
            $this->command->error("Trace: " . $e->getTraceAsString());
        }
    }
}
