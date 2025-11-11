<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Organization;

class OdooCsvToExcel extends Command
{
    protected $signature = 'odoo:csv-to-excel {--dry-run : Simulation sans génération de fichiers}';
    protected $description = 'Convertit les exports CSV Odoo en fichiers Excel pour import MDF';

    private $csvPath;
    private $excelPath;
    private $stats = [];
    private $organizationMapping = [];

    public function handle()
    {
        $this->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->info("║  📊 CONVERSION CSV ODOO → EXCEL MDF                          ║");
        $this->info("╚═══════════════════════════════════════════════════════════════╝");
        $this->newLine();

        $this->csvPath = storage_path('app/odoo-csv');
        $this->excelPath = storage_path('app/excel/data');

        // Créer les dossiers si nécessaire
        if (!is_dir($this->csvPath)) {
            mkdir($this->csvPath, 0755, true);
        }
        if (!is_dir($this->excelPath)) {
            mkdir($this->excelPath, 0755, true);
        }

        // Vérifier les fichiers CSV
        if (!$this->checkCsvFiles()) {
            return self::FAILURE;
        }

        // Charger le mapping des organisations existantes
        $this->loadOrganizationMapping();

        try {
            // Conversion des fichiers
            $this->info("🔄 Début de la conversion...");
            $this->newLine();

            $this->convertOrganizations();
            $this->convertUsers();
            $this->convertProjects();
            $this->convertTasks();

            // Afficher le résumé
            $this->displaySummary();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    private function checkCsvFiles(): bool
    {
        $this->info("📂 Vérification des fichiers CSV...");

        $required = [
            '01_organizations.csv' => 'Organisations',
            '02_users.csv' => 'Utilisateurs',
            '03_projects.csv' => 'Projets',
            '04_tasks.csv' => 'Tâches',
        ];

        $missing = [];
        foreach ($required as $file => $label) {
            $path = $this->csvPath . '/' . $file;
            if (file_exists($path)) {
                $this->line("   ✅ $label: " . $file);
            } else {
                $missing[] = $file;
                $this->warn("   ❌ $label: " . $file . " (manquant)");
            }
        }

        $this->newLine();

        if (!empty($missing)) {
            $this->error("❌ Fichiers manquants. Veuillez exécuter les scripts SQL d'export.");
            $this->newLine();
            $this->warn("💡 Voir: docs/ODOO_SQL_EXPORT_SCRIPTS.md");
            return false;
        }

        return true;
    }

    private function loadOrganizationMapping()
    {
        $this->info("🔍 Chargement du mapping des organisations...");

        $orgs = Organization::all();
        foreach ($orgs as $org) {
            // Mapping par nom (similarité)
            $this->organizationMapping[$org->name] = $org->id;
        }

        $this->line("   " . count($this->organizationMapping) . " organisations MDF trouvées");
        $this->newLine();
    }

    private function convertOrganizations()
    {
        $this->info("1️⃣  Conversion: Organisations...");

        $csv = $this->readCsv('01_organizations.csv');
        if (empty($csv)) {
            $this->warn("   ⚠️  Aucune organisation à convertir");
            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $headers = ['name', 'type', 'registration_number', 'address_line1', 'address_line2',
                    'postal_code', 'city', 'country', 'phone', 'email', 'website', 'is_active'];
        $sheet->fromArray($headers, null, 'A1');

        // Données
        $row = 2;
        $newOrgs = 0;
        $existingOrgs = 0;

        foreach ($csv as $data) {
            // Vérifier si l'organisation existe déjà
            $exists = false;
            foreach ($this->organizationMapping as $name => $id) {
                if (similar_text(strtolower($name), strtolower($data['name'])) > 5) {
                    $exists = true;
                    $existingOrgs++;
                    break;
                }
            }

            if (!$exists) {
                $sheet->fromArray([
                    $data['name'],
                    $data['type'] ?? 'client',
                    $data['registration_number'] ?? '',
                    $data['address_line1'] ?? '',
                    $data['address_line2'] ?? '',
                    $data['postal_code'] ?? '',
                    $data['city'] ?? '',
                    $data['country'] ?? 'Maroc',
                    $data['phone'] ?? '',
                    $data['email'] ?? '',
                    $data['website'] ?? '',
                    $data['active'] === 't' ? 'Oui' : 'Non',
                ], null, 'A' . $row);
                $row++;
                $newOrgs++;
            }
        }

        // Sauvegarder
        if (!$this->option('dry-run')) {
            $writer = new Xlsx($spreadsheet);
            $writer->save($this->excelPath . '/01_users.xlsx'); // Note: On garde ce nom mais ce sont les orgs
        }

        $this->stats['organizations'] = [
            'total_odoo' => count($csv),
            'nouvelles' => $newOrgs,
            'existantes' => $existingOrgs,
        ];

        $this->line("   ✅ {$newOrgs} nouvelles organisations, {$existingOrgs} déjà existantes");
        $this->newLine();
    }

    private function convertUsers()
    {
        $this->info("2️⃣  Conversion: Utilisateurs...");

        $csv = $this->readCsv('02_users.csv');
        if (empty($csv)) {
            $this->warn("   ⚠️  Aucun utilisateur à convertir");
            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes selon notre template
        $headers = ['name', 'email', 'password', 'organization_id', 'is_system_admin'];
        $sheet->fromArray($headers, null, 'A1');

        // Données
        $row = 2;
        foreach ($csv as $data) {
            // Trouver l'organization_id MDF
            $orgId = 1; // Par défaut
            if (!empty($data['organization_name'])) {
                foreach ($this->organizationMapping as $name => $id) {
                    if (stripos($name, $data['organization_name']) !== false ||
                        stripos($data['organization_name'], $name) !== false) {
                        $orgId = $id;
                        break;
                    }
                }
            }

            $sheet->fromArray([
                $data['full_name'],
                $data['email'],
                $data['password_temp'] ?? 'ChangeMeOdoo123!',
                $orgId,
                $data['is_system_admin'] === 't' ? 'Oui' : 'Non',
            ], null, 'A' . $row);
            $row++;
        }

        if (!$this->option('dry-run')) {
            $writer = new Xlsx($spreadsheet);
            $writer->save($this->excelPath . '/01_users.xlsx');
        }

        $this->stats['users'] = count($csv);
        $this->line("   ✅ " . count($csv) . " utilisateurs convertis");
        $this->newLine();
    }

    private function convertProjects()
    {
        $this->info("3️⃣  Conversion: Projets...");

        $csv = $this->readCsv('03_projects.csv');
        if (empty($csv)) {
            $this->warn("   ⚠️  Aucun projet à convertir");
            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $headers = ['code', 'name', 'description', 'client_organization_id', 'project_manager_email',
                    'methodology', 'status', 'priority', 'health_status', 'budget', 'start_date', 'end_date', 'completion_percentage'];
        $sheet->fromArray($headers, null, 'A1');

        // Données
        $row = 2;
        $usedCodes = [];

        foreach ($csv as $data) {
            // Mapper client organization
            $clientOrgId = 1;
            if (!empty($data['client_name'])) {
                foreach ($this->organizationMapping as $name => $id) {
                    if (stripos($name, $data['client_name']) !== false) {
                        $clientOrgId = $id;
                        break;
                    }
                }
            }

            // Gérer les codes en double
            $projectCode = $data['project_code'];
            if (in_array($projectCode, $usedCodes)) {
                $suffix = 2;
                while (in_array($projectCode . '-' . $suffix, $usedCodes)) {
                    $suffix++;
                }
                $projectCode = $projectCode . '-' . $suffix;
            }
            $usedCodes[] = $projectCode;

            $sheet->fromArray([
                $projectCode,
                $data['project_name'],
                $data['description'] ?? '',
                $clientOrgId,
                $data['project_manager_email'] ?? '',
                $data['methodology'] ?? 'waterfall',
                $data['status'] ?? 'execution',
                'medium',  // priority par défaut
                'green',   // health_status par défaut
                $data['budget'] ?? 0,
                $data['date_start'] ?? '',
                $data['date_end'] ?? '',
                $data['completion_percentage'] ?? 0,
            ], null, 'A' . $row);
            $row++;
        }

        if (!$this->option('dry-run')) {
            $writer = new Xlsx($spreadsheet);
            $writer->save($this->excelPath . '/04_projects.xlsx');
        }

        $this->stats['projects'] = count($csv);
        $this->line("   ✅ " . count($csv) . " projets convertis");
        $this->newLine();
    }

    private function convertTasks()
    {
        $this->info("4️⃣  Conversion: Tâches...");

        $csv = $this->readCsv('04_tasks.csv');
        if (empty($csv)) {
            $this->warn("   ⚠️  Aucune tâche à convertir");
            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $headers = ['project_code', 'name', 'description', 'assigned_to_email', 'priority',
                    'status', 'estimated_hours', 'actual_hours', 'start_date', 'end_date', 'completion_percentage'];
        $sheet->fromArray($headers, null, 'A1');

        // Données
        $row = 2;
        foreach ($csv as $data) {
            // Tronquer le nom de la tâche si trop long
            $taskName = $data['task_name'];
            if (strlen($taskName) > 250) {
                $taskName = substr($taskName, 0, 247) . '...';
            }

            // Mapper les statuts Odoo vers MDF
            $status = $data['status'] ?? 'in_progress';
            if ($status === 'on_hold') {
                $status = 'blocked';
            }

            $sheet->fromArray([
                $data['project_code'],
                $taskName,
                $data['task_description'] ?? '',
                $data['assigned_to_email'] ?? '',
                $data['priority'] ?? 'medium',
                $status,
                $data['estimated_hours'] ?? 0,
                $data['actual_hours'] ?? 0,
                '',  // start_date
                $data['due_date'] ?? '',
                $data['completion_percentage'] ?? 0,
            ], null, 'A' . $row);
            $row++;
        }

        if (!$this->option('dry-run')) {
            $writer = new Xlsx($spreadsheet);
            $writer->save($this->excelPath . '/07_tasks.xlsx');
        }

        $this->stats['tasks'] = count($csv);
        $this->line("   ✅ " . count($csv) . " tâches converties");
        $this->newLine();
    }

    private function readCsv(string $filename): array
    {
        $path = $this->csvPath . '/' . $filename;
        if (!file_exists($path)) {
            return [];
        }

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = array_combine($headers, $data);
            }
            fclose($handle);
        }

        return $rows;
    }

    private function displaySummary()
    {
        $this->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->info("║  ✅ CONVERSION TERMINÉE                                       ║");
        $this->info("╚═══════════════════════════════════════════════════════════════╝");
        $this->newLine();

        $this->table(
            ['Entité', 'Quantité'],
            [
                ['Organisations (nouvelles)', $this->stats['organizations']['nouvelles'] ?? 0],
                ['Organisations (existantes)', $this->stats['organizations']['existantes'] ?? 0],
                ['Utilisateurs', $this->stats['users'] ?? 0],
                ['Projets', $this->stats['projects'] ?? 0],
                ['Tâches', $this->stats['tasks'] ?? 0],
            ]
        );
        $this->newLine();

        $this->info("📁 Fichiers Excel générés dans:");
        $this->line("   " . $this->excelPath);
        $this->newLine();

        $this->info("🚀 Prochaine étape:");
        $this->line("   php artisan db:seed --class=TestDataMasterSeeder");
        $this->newLine();
    }
}
