# 🚀 Guide Complet: Import Odoo → MDF Access

Guide étape par étape pour importer vos données Odoo dans MDF Access.

---

## 📋 RÉSUMÉ DU PROCESSUS

```
┌─────────────┐
│ Base Odoo   │
│ PostgreSQL  │
└──────┬──────┘
       │
       │ 1️⃣ Export SQL → CSV
       │
       ▼
┌──────────────┐
│ Fichiers CSV │
│ (6 fichiers) │
└──────┬───────┘
       │
       │ 2️⃣ Transfert vers Windows
       │
       ▼
┌──────────────────────┐
│ storage/app/odoo-csv │
└──────┬───────────────┘
       │
       │ 3️⃣ Conversion CSV → Excel
       │
       ▼
┌──────────────────────────┐
│ storage/app/excel/data   │
│ (11 fichiers .xlsx)      │
└──────┬───────────────────┘
       │
       │ 4️⃣ Import dans MDF
       │
       ▼
┌──────────────┐
│ Base MDF     │
│ PostgreSQL   │
└──────────────┘
```

---

## ⚡ ÉTAPE 1: EXPORT DEPUIS ODOO (SQL)

### 1.1 Connexion à PostgreSQL

Depuis votre serveur ayant accès à la base Odoo :

```bash
psql -h 173.212.230.240 -p 5432 -U odoo -d samsic
```

### 1.2 Créer le dossier d'export

```bash
mkdir -p /tmp/odoo-exports
cd /tmp/odoo-exports
```

### 1.3 Exécuter les scripts SQL

Ouvrez le fichier **`docs/ODOO_SQL_EXPORT_SCRIPTS.md`** et exécutez les 7 scripts SQL :

1. **Script 1** : Export organisations → `01_organizations.csv`
2. **Script 2** : Export utilisateurs → `02_users.csv`
3. **Script 3** : Export projets → `03_projects.csv`
4. **Script 4** : Export tâches → `04_tasks.csv`
5. **Script 5** : Export jalons → `05_milestones.csv` (optionnel)
6. **Script 6** : Export stages → `06_stages.csv`
7. **Script 7** : Statistiques (vérification)

### 1.4 Vérifier les exports

```bash
ls -lh /tmp/odoo-exports/
```

Vous devriez voir 6 fichiers CSV :
```
01_organizations.csv
02_users.csv
03_projects.csv
04_tasks.csv
05_milestones.csv
06_stages.csv
```

---

## 📥 ÉTAPE 2: TRANSFERT VERS WINDOWS

### Option A: SCP depuis Windows

```powershell
# Depuis PowerShell sur votre machine Windows
scp user@serveur-odoo:/tmp/odoo-exports/*.csv D:\auxigene\mdf-access\storage\app\odoo-csv\
```

### Option B: Compression + téléchargement

Sur le serveur :
```bash
cd /tmp/odoo-exports
tar -czf odoo-exports.tar.gz *.csv
```

Puis téléchargez `odoo-exports.tar.gz` et extrayez dans :
```
D:\auxigene\mdf-access\storage\app\odoo-csv\
```

### Vérification

Sur Windows, vérifiez que les fichiers sont bien placés :

```bash
dir storage\app\odoo-csv\
```

---

## 🔄 ÉTAPE 3: CONVERSION CSV → EXCEL

### 3.1 Lancer la conversion

```bash
php artisan odoo:csv-to-excel
```

### 3.2 Sortie attendue

```
╔═══════════════════════════════════════════════════════════════╗
║  📊 CONVERSION CSV ODOO → EXCEL MDF                          ║
╚═══════════════════════════════════════════════════════════════╝

📂 Vérification des fichiers CSV...
   ✅ Organisations: 01_organizations.csv
   ✅ Utilisateurs: 02_users.csv
   ✅ Projets: 03_projects.csv
   ✅ Tâches: 04_tasks.csv

🔍 Chargement du mapping des organisations...
   26 organisations MDF trouvées

🔄 Début de la conversion...

1️⃣  Conversion: Organisations...
   ✅ 15 nouvelles organisations, 11 déjà existantes

2️⃣  Conversion: Utilisateurs...
   ✅ 45 utilisateurs convertis

3️⃣  Conversion: Projets...
   ✅ 125 projets convertis

4️⃣  Conversion: Tâches...
   ✅ 850 tâches converties

╔═══════════════════════════════════════════════════════════════╗
║  ✅ CONVERSION TERMINÉE                                       ║
╚═══════════════════════════════════════════════════════════════╝

+-------------------------+----------+
| Entité                  | Quantité |
+-------------------------+----------+
| Organisations (nouv.)   | 15       |
| Organisations (exist.)  | 11       |
| Utilisateurs            | 45       |
| Projets                 | 125      |
| Tâches                  | 850      |
+-------------------------+----------+

📁 Fichiers Excel générés dans:
   D:\auxigene\mdf-access\storage\app\excel\data

🚀 Prochaine étape:
   php artisan db:seed --class=TestDataMasterSeeder
```

### 3.3 Fichiers Excel générés

Les fichiers suivants sont créés dans `storage/app/excel/data/` :

- ✅ `01_users.xlsx` - Utilisateurs
- ✅ `04_projects.xlsx` - Projets
- ✅ `07_tasks.xlsx` - Tâches
- ⚠️ Autres fichiers (portfolios, phases, etc.) → à créer manuellement si nécessaire

---

## 💾 ÉTAPE 4: IMPORT DANS MDF ACCESS

### 4.1 Vérifier la base de données

```bash
php artisan db:show
```

Assurez-vous que vous êtes connecté à la bonne base.

### 4.2 Lancer l'import complet

```bash
php artisan db:seed --class=TestDataMasterSeeder
```

### 4.3 Sortie attendue

```
╔═══════════════════════════════════════════════════════════════╗
║  🚀 IMPORT DES DONNÉES DE TEST DEPUIS EXCEL                  ║
╚═══════════════════════════════════════════════════════════════╝

📊 État actuel de la base de données:
+---------------+---------------------------+
| Table         | Nombre d'enregistrements |
+---------------+---------------------------+
| Organizations | 26                        |
| Users         | 0                         |
| Roles         | 29                        |
| Permissions   | 174                       |
| Portfolios    | 0                         |
| Programs      | 0                         |
| Projects      | 0                         |
+---------------+---------------------------+

🔹 [1/11] Import Utilisateurs...
📥 Import des utilisateurs depuis Excel...
✅ Utilisateurs importés: 45

🔹 [2/11] Import Rôles Utilisateurs...
(pas de fichier - skip)

🔹 [3/11] Import Portfolios & Programmes...
(pas de fichier - skip)

🔹 [4/11] Import Projets...
📥 Import des projets depuis Excel...
✅ Projets importés: 125

🔹 [5/11] Import Participations Organisations...
(pas de fichier - skip)

🔹 [6/11] Import Phases & Tâches...
📥 Import des tâches depuis Excel...
✅ Tâches importées: 850

...

╔═══════════════════════════════════════════════════════════════╗
║  ✅ IMPORT TERMINÉ AVEC SUCCÈS                               ║
╚═══════════════════════════════════════════════════════════════╝

📊 Résumé des données importées:

🔷 DONNÉES DE BASE:
+-------------------+--------+
| Entité            | Nombre |
+-------------------+--------+
| Utilisateurs      | 45     |
| Rôles utilisateurs| 0      |
| Organisations     | 41     |
+-------------------+--------+

🔷 STRUCTURE HIÉRARCHIQUE:
+---------------------------+--------+
| Entité                    | Nombre |
+---------------------------+--------+
| Portfolios                | 0      |
| Programmes                | 0      |
| Projets                   | 125    |
| Participations Orgs       | 0      |
+---------------------------+--------+

🔷 GESTION DE PROJET:
+----------------+--------+
| Entité         | Nombre |
+----------------+--------+
| Phases         | 0      |
| Tâches         | 850    |
| Éléments WBS   | 0      |
| Livrables      | 0      |
| Jalons         | 0      |
+----------------+--------+

⏱️  Temps d'exécution: 12.45 secondes

✨ Vous pouvez maintenant tester vos modèles avec des données réelles!
```

---

## ✅ VÉRIFICATION POST-IMPORT

### Vérifier les données

```bash
php artisan tinker
```

```php
// Vérifier les organisations
Organization::count(); // Devrait être 26 + nouvelles

// Vérifier les utilisateurs
User::count(); // Devrait montrer les utilisateurs importés

// Vérifier les projets
Project::count();
Project::with('client')->take(5)->get();

// Vérifier les tâches
Task::count();
Task::with('project', 'assignedUser')->take(10)->get();
```

### Se connecter à l'application

1. Ouvrez http://localhost:8000
2. Connectez-vous avec un utilisateur Odoo:
   - Email: `[email depuis Odoo]`
   - Password: `ChangeMeOdoo123!`
3. Changez le mot de passe au premier login

---

## 🔧 DÉPANNAGE

### Problème: Fichiers CSV manquants

```
❌ Fichiers manquants. Veuillez exécuter les scripts SQL d'export.
```

**Solution:** Vérifiez que vous avez bien exécuté tous les scripts SQL et transféré les fichiers.

### Problème: Erreur de mapping organisation

```
❌ Organisation non trouvée: [nom]
```

**Solution:** Les organisations Odoo doivent exister dans MDF. Soit :
1. Importez les organisations d'abord
2. Ou créez-les manuellement

### Problème: Utilisateurs sans organisation

**Solution:** Le convertisseur assigne l'organisation ID=1 par défaut. Ajustez manuellement après import si nécessaire.

### Problème: Projets sans chef de projet

**Solution:** Normal si l'utilisateur n'existe pas encore. Les projets seront créés, ajoutez le chef de projet après.

---

## 📊 DONNÉES NON IMPORTÉES

Ces données ne sont PAS dans l'export actuel (ajout manuel possible) :

- ❌ **Portfolios/Programmes** : Créer un portfolio "Projets Odoo" manuellement
- ❌ **Phases** : Les stages Odoo peuvent être importés séparément
- ❌ **Rôles utilisateurs** : À assigner manuellement après import
- ❌ **Participations organisations** : Définir MOA/MOE/Sponsor manuellement
- ❌ **WBS/Livrables** : Pas de structure standard dans Odoo
- ❌ **Risques/Issues** : Pas de table standard
- ❌ **Ressources/Allocations** : Complexe (timesheets)

---

## 🎯 ÉTAPES SUIVANTES

### 1. Créer un Portfolio par défaut

```php
php artisan tinker

$portfolio = Portfolio::create([
    'name' => 'Projets Odoo',
    'description' => 'Projets importés depuis Odoo',
    'status' => 'active',
]);

// Rattacher tous les projets au portfolio
Project::whereNull('program_id')->update(['portfolio_id' => $portfolio->id]);
```

### 2. Assigner les rôles utilisateurs

Via l'interface MDF ou :

```php
php artisan tinker

$user = User::where('email', 'chef.projet@samsic.fr')->first();
$role = Role::where('slug', 'project-manager')->first();
$user->roles()->attach($role->id);
```

### 3. Définir les participations organisations

Pour chaque projet, définir manuellement :
- 1 Sponsor
- 1 MOA (Maîtrise d'Ouvrage)
- 1 MOE primaire (Maîtrise d'Œuvre)

### 4. Tester l'application

- Naviguez dans les projets
- Vérifiez les tâches
- Testez les permissions
- Ajoutez les données manquantes progressivement

---

## 📚 FICHIERS DE RÉFÉRENCE

- **`ODOO_SQL_EXPORT_SCRIPTS.md`** : Scripts SQL d'export
- **`ODOO_EXTRACTION_REQUIREMENTS.md`** : Configuration et mapping
- **`EXCEL_TEMPLATES_GUIDE.md`** : Structure des templates Excel
- **`EXCEL_IMPORT_SETUP.md`** : Guide d'import Excel

---

## 🚀 COMMANDES UTILES

```bash
# Test connexion Odoo
php artisan odoo:test-connection

# Conversion CSV → Excel
php artisan odoo:csv-to-excel

# Simulation (dry-run)
php artisan odoo:csv-to-excel --dry-run

# Import complet
php artisan db:seed --class=TestDataMasterSeeder

# Import spécifique
php artisan db:seed --class=UsersFromExcelSeeder
php artisan db:seed --class=ProjectsFromExcelSeeder
php artisan db:seed --class=TasksFromExcelSeeder
```

---

**Vous êtes prêt ! 🎉**

Suivez les étapes dans l'ordre et vos données Odoo seront importées dans MDF Access.
