<?php

namespace App\Console\Commands\FieldMaintenance;

use Illuminate\Console\Command;
use App\Services\FieldMaintenance\FmImportService;
use App\Models\User;

class ImportParcCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fm:import-parc
                            {file? : Chemin du fichier Excel à importer}
                            {--user-id= : ID de l\'utilisateur qui effectue l\'import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importer le parc de sites INWI depuis un fichier Excel';

    /**
     * Execute the console command.
     */
    public function handle(FmImportService $importService)
    {
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  Import du Parc Sites INWI');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        // Déterminer le fichier à importer
        $filePath = $this->argument('file');

        if (!$filePath) {
            // Utiliser le fichier par défaut
            $filePath = storage_path('app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx');
        }

        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            $this->error("❌ Fichier introuvable: {$filePath}");
            return 1;
        }

        $this->info("📁 Fichier: " . basename($filePath));

        // Récupérer l'ID utilisateur
        $userId = $this->option('user-id');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->warn("⚠️  Utilisateur ID {$userId} introuvable. Import sans utilisateur.");
                $userId = null;
            } else {
                $this->info("👤 Importé par: {$user->name} ({$user->email})");
            }
        }

        $this->info('');
        $this->info('🚀 Démarrage de l\'import...');
        $this->newLine();

        try {
            // Lancer l'import
            $startTime = now();

            $importLog = $importService->importParcFromExcel($filePath, $userId);

            $duration = now()->diffInSeconds($startTime);

            // Afficher les résultats
            $this->newLine();
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('  Résultats de l\'import');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->newLine();

            $stats = $importService->getStatistics();

            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Total traité', $stats['total_processed']],
                    ['✅ Réussis', $stats['successful']],
                    ['❌ Échoués', $stats['failed']],
                    ['🆕 Créés', $stats['created']],
                    ['🔄 Mis à jour', $stats['updated']],
                    ['⚠️  Avertissements', $stats['warnings']],
                    ['⏱️  Durée', "{$duration}s"],
                ]
            );

            // Afficher les avertissements s'il y en a
            if ($stats['warnings'] > 0) {
                $this->newLine();
                $this->warn("⚠️  {$stats['warnings']} avertissements détectés");

                if ($this->confirm('Voulez-vous voir les détails des avertissements ?', false)) {
                    $warnings = $importLog->warnings;

                    if (!empty($warnings)) {
                        $this->newLine();
                        $warningsTable = array_map(function ($warning) {
                            return [
                                $warning['row'] ?? 'N/A',
                                $warning['field'] ?? 'N/A',
                                $warning['value'] ?? 'N/A',
                                $warning['message'] ?? 'N/A',
                            ];
                        }, array_slice($warnings, 0, 20)); // Limiter à 20 premiers

                        $this->table(
                            ['Ligne', 'Champ', 'Valeur', 'Message'],
                            $warningsTable
                        );

                        if (count($warnings) > 20) {
                            $this->info("... et " . (count($warnings) - 20) . " autres avertissements");
                        }
                    }
                }
            }

            // Afficher les erreurs s'il y en a
            if ($stats['errors'] > 0) {
                $this->newLine();
                $this->error("❌ {$stats['errors']} erreurs détectées");

                $errors = $importLog->errors;

                if (!empty($errors)) {
                    $this->newLine();
                    $errorsTable = array_map(function ($error) {
                        return [
                            $error['row'] ?? 'N/A',
                            $error['error'] ?? 'N/A',
                        ];
                    }, array_slice($errors, 0, 10)); // Limiter à 10 premières

                    $this->table(
                        ['Ligne', 'Erreur'],
                        $errorsTable
                    );

                    if (count($errors) > 10) {
                        $this->info("... et " . (count($errors) - 10) . " autres erreurs");
                    }
                }
            }

            $this->newLine();

            if ($stats['failed'] === 0) {
                $this->info('✅ Import terminé avec succès !');
            } else {
                $this->warn("⚠️  Import terminé avec {$stats['failed']} échec(s)");
            }

            $this->info("📊 Log d'import ID: {$importLog->id}");
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Erreur lors de l\'import:');
            $this->error($e->getMessage());
            $this->newLine();

            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }

            return 1;
        }
    }
}
