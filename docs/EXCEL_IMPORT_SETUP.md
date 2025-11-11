# 🚀 CONFIGURATION IMPORT EXCEL - RÉSUMÉ RAPIDE

## 📂 Structure Créée

```
storage/app/excel/
├── templates/     ✅ Créé - Pour vos templates vides
└── data/          ✅ Créé - Pour vos fichiers remplis

app/Imports/       À créer - Import classes
database/seeders/  À créer - Seeders utilisant les imports
```

## 🎯 Architecture

### Flux de Données
```
1. Vous remplissez → storage/app/excel/data/01_users.xlsx
2. Import class lit → App\Imports\UsersImport
3. Seeder exécute → database/seeders/UsersFromExcelSeeder
4. Données en DB → Table users
```

## 📋 Liste des Imports & Seeders à Créer

| # | Import Class | Seeder | Template Excel | Priorité |
|---|--------------|--------|----------------|----------|
| 1 | `UsersImport` | `UsersFromExcelSeeder` | 01_users.xlsx | ⭐ CRITIQUE |
| 2 | `UserRolesImport` | `UserRolesFromExcelSeeder` | 02_user_roles.xlsx | ⭐ CRITIQUE |
| 3 | `PortfoliosProgramsImport` | `PortfoliosProgramsFromExcelSeeder` | 03_portfolios_programs.xlsx | Normal |
| 4 | `ProjectsImport` | `ProjectsFromExcelSeeder` | 04_projects.xlsx | ⭐ CRITIQUE |
| 5 | `ProjectOrganizationsImport` | `ProjectOrganizationsFromExcelSeeder` | 05_project_organizations.xlsx | ⭐ CRITIQUE |
| 6 | `PhasesImport` | `PhasesFromExcelSeeder` | 06_phases.xlsx | Normal |
| 7 | `TasksImport` | `TasksFromExcelSeeder` | 07_tasks.xlsx | Normal |
| 8 | `WbsDeliverablesImport` | `WbsDeliverablesFromExcelSeeder` | 08_wbs_deliverables.xlsx | Normal |
| 9 | `RisksIssuesImport` | `RisksIssuesFromExcelSeeder` | 09_risks_issues.xlsx | Normal |
| 10 | `MilestonesChangeRequestsImport` | `MilestonesChangeRequestsFromExcelSeeder` | 10_milestones_change_requests.xlsx | Normal |
| 11 | `ResourcesImport` | `ResourcesFromExcelSeeder` | 11_resources.xlsx | Normal |

## 🔧 Commandes pour Créer

### Imports
```bash
php artisan make:import UsersImport --model=User
php artisan make:import UserRolesImport --model=UserRole
php artisan make:import ProjectsImport --model=Project
php artisan make:import ProjectOrganizationsImport --model=ProjectOrganization
# ... etc
```

### Seeders
```bash
php artisan make:seeder UsersFromExcelSeeder
php artisan make:seeder UserRolesFromExcelSeeder
php artisan make:seeder ProjectsFromExcelSeeder
php artisan make:seeder ProjectOrganizationsFromExcelSeeder
# ... etc
```

## 📝 Template Import Class (Exemple)

```php
<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => Hash::make($row['password']),
            'organization_id' => $row['organization_id'],
            'is_system_admin' => strtolower($row['is_system_admin']) === 'oui',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'organization_id' => 'required|exists:organizations,id',
            'is_system_admin' => 'required',
        ];
    }
}
```

## 📝 Template Seeder (Exemple)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

class UsersFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/01_users.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Fichier non trouvé: {$filePath}");
            $this->command->warn("Veuillez créer et remplir le fichier Excel d'abord.");
            return;
        }

        $this->command->info("Import des utilisateurs depuis Excel...");

        Excel::import(new UsersImport, $filePath);

        $this->command->info("✓ Utilisateurs importés avec succès!");
    }
}
```

## 🎯 Plan d'Exécution

### Phase 1: Imports Critiques (Vous créez d'abord)
1. Créer Import + Seeder pour Users
2. Créer Import + Seeder pour UserRoles
3. Créer Import + Seeder pour Projects
4. Créer Import + Seeder pour ProjectOrganizations

### Phase 2: Imports Secondaires
5-11. Créer les autres Imports + Seeders

### Phase 3: Exécution
```bash
# Ordre d'exécution des seeders
php artisan db:seed --class=UsersFromExcelSeeder
php artisan db:seed --class=UserRolesFromExcelSeeder
php artisan db:seed --class=PortfoliosProgramsFromExcelSeeder
php artisan db:seed --class=ProjectsFromExcelSeeder
php artisan db:seed --class=ProjectOrganizationsFromExcelSeeder
php artisan db:seed --class=PhasesFromExcelSeeder
php artisan db:seed --class=TasksFromExcelSeeder
# ... etc
```

Ou créer un MasterSeeder:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestDataMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersFromExcelSeeder::class,
            UserRolesFromExcelSeeder::class,
            PortfoliosProgramsFromExcelSeeder::class,
            ProjectsFromExcelSeeder::class,
            ProjectOrganizationsFromExcelSeeder::class,
            PhasesFromExcelSeeder::class,
            TasksFromExcelSeeder::class,
            WbsDeliverablesFromExcelSeeder::class,
            RisksIssuesFromExcelSeeder::class,
            MilestonesChangeRequestsFromExcelSeeder::class,
            ResourcesFromExcelSeeder::class,
        ]);
    }
}
```

## ✅ Checklist Avant Import

- [ ] Tous les fichiers Excel dans `storage/app/excel/data/`
- [ ] Colonnes respectent exactement les noms du guide
- [ ] Formats de dates corrects (YYYY-MM-DD)
- [ ] IDs d'organisations existent
- [ ] Emails sont uniques
- [ ] Contraintes métier respectées (ProjectOrganizations)

## 🎨 Voulez-vous que je crée:

**Option A:** Les 4 Imports/Seeders critiques (Users, UserRoles, Projects, ProjectOrganizations)

**Option B:** Tous les 11 Imports/Seeders

**Option C:** Juste le guide (vous créerez les Imports/Seeders vous-même)

Que préférez-vous?
