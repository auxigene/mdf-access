<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class OdooTestConnection extends Command
{
    protected $signature = 'odoo:test-connection';
    protected $description = 'Test la connexion à la base de données Odoo PostgreSQL';

    public function handle()
    {
        $this->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->info("║  🔌 TEST DE CONNEXION ODOO                                    ║");
        $this->info("╚═══════════════════════════════════════════════════════════════╝");
        $this->newLine();

        // Configuration depuis le fichier requirements
        $config = [
            'host' => 'intranet.samsic.cloud',
            'port' => '5432',
            'database' => 'samsic',
            'username' => 'odoo',
            'password' => 'samsicadmina',
        ];

        $this->info("📋 Configuration:");
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Host', $config['host']],
                ['Port', $config['port']],
                ['Database', $config['database']],
                ['Username', $config['username']],
                ['Password', str_repeat('*', strlen($config['password']))],
            ]
        );
        $this->newLine();

        try {
            $this->info("🔄 Tentative de connexion...");

            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                $config['host'],
                $config['port'],
                $config['database']
            );

            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 10,
                ]
            );

            $this->info("✅ Connexion réussie !");
            $this->newLine();

            // Test 1: Version PostgreSQL
            $this->info("🔍 Test 1/7: Version PostgreSQL...");
            $stmt = $pdo->query("SELECT version()");
            $version = $stmt->fetchColumn();
            $this->line("   Version: " . substr($version, 0, 80));
            $this->newLine();

            // Test 2: Tables Odoo disponibles
            $this->info("🔍 Test 2/7: Tables Odoo disponibles...");
            $stmt = $pdo->query("
                SELECT table_name
                FROM information_schema.tables
                WHERE table_schema = 'public'
                AND table_name IN ('project_project', 'project_task', 'res_partner', 'res_users', 'project_milestone')
                ORDER BY table_name
            ");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($tables) > 0) {
                $this->line("   Tables trouvées: " . implode(', ', $tables));
            } else {
                $this->warn("   ⚠️ Aucune table Odoo trouvée!");
            }
            $this->newLine();

            // Test 3: Compter les projets actifs
            $this->info("🔍 Test 3/7: Projets actifs...");
            $stmt = $pdo->query("SELECT COUNT(*) FROM project_project WHERE active = true");
            $projectCount = $stmt->fetchColumn();
            $this->line("   Projets actifs: " . $projectCount);
            $this->newLine();

            // Test 4: Compter les tâches actives
            $this->info("🔍 Test 4/7: Tâches actives...");
            $stmt = $pdo->query("SELECT COUNT(*) FROM project_task WHERE active = true");
            $taskCount = $stmt->fetchColumn();
            $this->line("   Tâches actives: " . $taskCount);
            $this->newLine();

            // Test 5: Compter les organisations
            $this->info("🔍 Test 5/7: Organisations...");
            $stmt = $pdo->query("SELECT COUNT(*) FROM res_partner WHERE is_company = true");
            $orgCount = $stmt->fetchColumn();
            $this->line("   Organisations: " . $orgCount);
            $this->newLine();

            // Test 6: Compter les utilisateurs
            $this->info("🔍 Test 6/7: Utilisateurs actifs...");
            $stmt = $pdo->query("SELECT COUNT(*) FROM res_users WHERE active = true");
            $userCount = $stmt->fetchColumn();
            $this->line("   Utilisateurs actifs: " . $userCount);
            $this->newLine();

            // Test 7: Vérifier la table milestones
            $this->info("🔍 Test 7/7: Jalons (milestones)...");
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM project_milestone");
                $milestoneCount = $stmt->fetchColumn();
                $this->line("   Jalons: " . $milestoneCount);
            } catch (PDOException $e) {
                $this->warn("   ⚠️ Table project_milestone non trouvée");
            }
            $this->newLine();

            // Résumé
            $this->info("╔═══════════════════════════════════════════════════════════════╗");
            $this->info("║  ✅ CONNEXION VALIDÉE - PRÊT POUR L'EXTRACTION               ║");
            $this->info("╚═══════════════════════════════════════════════════════════════╝");
            $this->newLine();

            $this->table(
                ['Entité', 'Quantité'],
                [
                    ['Projets actifs', $projectCount],
                    ['Tâches actives', $taskCount],
                    ['Organisations', $orgCount],
                    ['Utilisateurs actifs', $userCount],
                ]
            );
            $this->newLine();

            $this->info("🚀 Vous pouvez maintenant lancer:");
            $this->line("   php artisan odoo:extract-to-excel");
            $this->newLine();

            return self::SUCCESS;

        } catch (PDOException $e) {
            $this->newLine();
            $this->error("╔═══════════════════════════════════════════════════════════════╗");
            $this->error("║  ❌ ÉCHEC DE LA CONNEXION                                    ║");
            $this->error("╚═══════════════════════════════════════════════════════════════╝");
            $this->newLine();

            $this->error("Erreur: " . $e->getMessage());
            $this->newLine();

            $this->warn("💡 Vérifications à faire:");
            $this->line("   1. Le serveur PostgreSQL est accessible depuis votre réseau");
            $this->line("   2. Le port 5432 n'est pas bloqué par un firewall");
            $this->line("   3. Les credentials sont corrects");
            $this->line("   4. La base de données 'samsic' existe");
            $this->line("   5. L'utilisateur 'odoo' a les permissions nécessaires");
            $this->newLine();

            return self::FAILURE;
        }
    }
}
