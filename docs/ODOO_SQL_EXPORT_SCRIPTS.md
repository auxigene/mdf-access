# 📊 Scripts SQL pour Export Odoo → CSV

Ce fichier contient tous les scripts SQL à exécuter directement dans votre client `psql` pour extraire les données d'Odoo.

---

## 🚀 DÉMARRAGE RAPIDE

### 1. Connexion à la base Odoo

```bash
# Depuis votre serveur ayant accès à Odoo
psql -h 173.212.230.240 -p 5432 -U odoo -d samsic
```

### 2. Créer le dossier de sortie

```bash
# Créer un dossier pour les exports
mkdir -p /tmp/odoo-exports
cd /tmp/odoo-exports
```

### 3. Exécuter les scripts ci-dessous

Copiez-collez chaque bloc SQL dans votre session `psql`.

---

## 📦 SCRIPT 1: ORGANISATIONS (res_partner)

```sql
-- Export des organisations (partenaires/clients)
\copy (
      SELECT
          p.id as odoo_id,
          p.name,
          COALESCE(p.vat, '') as registration_number,
          CASE
              WHEN p.name ILIKE '%samsic%' THEN 'vendor'
              ELSE 'client'
          END as type,
          COALESCE(p.street, '') as address_line1,
          COALESCE(p.street2, '') as address_line2,
          COALESCE(p.zip, '') as postal_code,
          COALESCE(p.city, '') as city,
          COALESCE(c.name->>'fr_FR', c.name->>'en_US', 'Maroc') as country,
          COALESCE(p.phone, '') as phone,
          COALESCE(p.email, '') as email,
          COALESCE(p.website, '') as website,
          p.active,
          p.create_date as created_at,
          p.write_date as updated_at
      FROM res_partner p
      LEFT JOIN res_country c ON p.country_id = c.id
      WHERE p.is_company = true
      ORDER BY p.name
  ) TO '/tmp/odoo-exports/01_organizations.csv' WITH CSV HEADER;
```

**Résultat attendu :** `01_organizations.csv`

---

## 👥 SCRIPT 2: UTILISATEURS (res_users + res_partner)

```sql
-- Export des utilisateurs avec infos du partner
\copy (
    SELECT
        u.id as odoo_user_id,
        u.login as email,
        p.name as full_name,
        'ChangeMeOdoo123!' as password_temp,
        COALESCE(comp.id, 1) as organization_odoo_id,
        COALESCE(comp.name, 'SAMSIC') as organization_name,
        CASE
            WHEN u.id = 1 THEN true
            WHEN EXISTS (
                SELECT 1 FROM res_groups_users_rel r
                JOIN res_groups g ON r.gid = g.id
                WHERE r.uid = u.id AND g.name LIKE '%Admin%'
            ) THEN true
            ELSE false
        END as is_system_admin,
        u.active,
        COALESCE(p.phone, '') as phone,
        COALESCE(p.mobile, '') as mobile,
        COALESCE(p.function, '') as job_title,
        u.create_date as created_at
    FROM res_users u
    JOIN res_partner p ON u.partner_id = p.id
    LEFT JOIN res_partner comp ON p.company_id = comp.id
    WHERE u.active = true
    AND u.share = false
    ORDER BY u.login
) TO '/tmp/odoo-exports/02_users.csv' WITH CSV HEADER;
```

**Résultat attendu :** `02_users.csv`

**⚠️ Note :** Tous les utilisateurs auront le mot de passe temporaire `ChangeMeOdoo123!` à changer lors de la première connexion.

---

## 🎯 SCRIPT 3: PROJETS (project_project)

```sql
-- Export des projets avec toutes les infos
 \copy (
      SELECT
          pp.id as odoo_project_id,
          COALESCE(pp.name->>'fr_FR', pp.name->>'en_US', 'Projet sans nom') as project_name,
          CASE
              WHEN pp.name->>'fr_FR' ~ '^[A-Z]{3}[0-9]{6}' THEN substring(pp.name->>'fr_FR' from '^[A-Z]{3}[0-9]{6}')
              WHEN pp.name->>'fr_FR' ~ '[A-Z]{3}[0-9]{5,}' THEN substring(pp.name->>'fr_FR' from '[A-Z]{3}[0-9]{5,}')
              ELSE 'PROJ-' || LPAD(pp.id::text, 5, '0')
          END as project_code,
          COALESCE(pp.description, '') as description,
          COALESCE(client.id, 1) as client_organization_odoo_id,
          COALESCE(client.name, 'Client inconnu') as client_name,
          COALESCE(pm.login, '') as project_manager_email,
          COALESCE(pm_partner.name, '') as project_manager_name,
          'waterfall' as methodology,
          CASE
              WHEN pp.active = false THEN 'completed'
              WHEN pp.name->>'fr_FR' ILIKE '%test%' THEN 'cancelled'
              ELSE 'execution'
          END as status,
          0 as budget,
          pp.date_start,
          pp.date as date_end,
          0 as completion_percentage,
          pp.active,
          pp.create_date as created_at,
          pp.write_date as updated_at
      FROM project_project pp
      LEFT JOIN res_partner client ON pp.partner_id = client.id
      LEFT JOIN res_users pm ON pp.user_id = pm.id
      LEFT JOIN res_partner pm_partner ON pm.partner_id = pm_partner.id
      WHERE pp.active = true
      AND (pp.name->>'fr_FR' NOT ILIKE '%test%' OR pp.name->>'fr_FR' IS NULL)
      ORDER BY pp.id DESC
  ) TO '/tmp/odoo-exports/03_projects.csv' WITH CSV HEADER;
```

**Résultat attendu :** `03_projects.csv`

---

## 📋 SCRIPT 4: TÂCHES (project_task)

```sql
-- Export des tâches avec toutes les relations
\copy (
      SELECT
          pt.id as odoo_task_id,
          pp.id as project_odoo_id,
          CASE
              WHEN pp.name->>'fr_FR' ~ '^[A-Z]{3}[0-9]{6}' THEN substring(pp.name->>'fr_FR' from '^[A-Z]{3}[0-9]{6}')
              WHEN pp.name->>'fr_FR' ~ '[A-Z]{3}[0-9]{5,}' THEN substring(pp.name->>'fr_FR' from '[A-Z]{3}[0-9]{5,}')
              ELSE 'PROJ-' || LPAD(pp.id::text, 5, '0')
          END as project_code,
          pt.name as task_name,
          COALESCE(pt.description, '') as task_description,
          COALESCE(stage.name->>'fr_FR', stage.name->>'en_US', '') as stage_name,
          stage.id as stage_odoo_id,
          pt.parent_id as parent_task_odoo_id,
          '' as assigned_to_email,
          CASE
              WHEN pt.priority = '0' THEN 'medium'
              WHEN pt.priority = '1' THEN 'high'
              WHEN pt.priority = '2' THEN 'high'
              WHEN pt.priority = '3' THEN 'critical'
              ELSE 'medium'
          END as priority,
          CASE
              WHEN pt.is_closed = true THEN 'completed'
              WHEN pt.kanban_state = 'blocked' THEN 'on_hold'
              WHEN pt.active = false THEN 'cancelled'
              ELSE 'in_progress'
          END as status,
          COALESCE(pt.planned_hours, 0) as estimated_hours,
          0 as actual_hours,
          pt.date_deadline as due_date,
          pt.date_end::date as end_date,
          CASE
              WHEN pt.is_closed = true THEN 100
              ELSE 0
          END as completion_percentage,
          pt.active,
          pt.create_date as created_at,
          pt.write_date as updated_at
      FROM project_task pt
      JOIN project_project pp ON pt.project_id = pp.id
      LEFT JOIN project_task_type stage ON pt.stage_id = stage.id
      WHERE pt.active = true
      AND pp.active = true
      AND (pp.name->>'fr_FR' NOT ILIKE '%test%' OR pp.name->>'fr_FR' IS NULL)
      ORDER BY pp.id, pt.id
  ) TO '/tmp/odoo-exports/04_tasks.csv' WITH CSV HEADER;
```

**Résultat attendu :** `04_tasks.csv`

---

## 🎯 SCRIPT 5: JALONS (project_milestone) - SI EXISTE

```sql
-- Vérifier d'abord si la table existe
SELECT EXISTS (
    SELECT FROM information_schema.tables
    WHERE table_name = 'project_milestone'
);

-- Si TRUE, exécuter:
\copy (
    SELECT
        pm.id as odoo_milestone_id,
        pp.id as project_odoo_id,
        CASE
            WHEN pp.name->>'fr_FR' ~ '^[A-Z]{3}[0-9]{6}' THEN substring(pp.name->>'fr_FR' from '^[A-Z]{3}[0-9]{6}')
            ELSE 'PROJ-' || LPAD(pp.id::text, 5, '0')
        END as project_code,
        pm.name as milestone_name,
        COALESCE(pm.description, '') as description,
        pm.deadline as due_date,
        CASE
            WHEN pm.is_reached = true THEN 'achieved'
            WHEN pm.deadline < CURRENT_DATE THEN 'missed'
            ELSE 'pending'
        END as status,
        true as is_critical,
        CASE WHEN pm.is_reached = true THEN pm.deadline ELSE NULL END as achieved_date,
        pm.create_date as created_at
    FROM project_milestone pm
    JOIN project_project pp ON pm.project_id = pp.id
    WHERE pp.active = true
    ORDER BY pm.deadline
) TO '/tmp/odoo-exports/05_milestones.csv' WITH CSV HEADER;
```

**Résultat attendu :** `05_milestones.csv` (ou vide si table n'existe pas)

---

## 🔍 SCRIPT 6: STAGES/PHASES (project_task_type)

```sql
-- Export des stages/phases pour mapping
\copy (
    SELECT
        pts.id as odoo_stage_id,
        COALESCE(pts.name->>'fr_FR', pts.name->>'en_US', 'Phase') as stage_name,
        pts.sequence,
        COUNT(pt.id) as task_count
    FROM project_task_type pts
    LEFT JOIN project_task pt ON pt.stage_id = pts.id AND pt.active = true
    GROUP BY pts.id, pts.name, pts.sequence
    ORDER BY pts.sequence
) TO '/tmp/odoo-exports/06_stages.csv' WITH CSV HEADER;
```

**Résultat attendu :** `06_stages.csv`

---

## 📊 SCRIPT 7: STATISTIQUES D'EXPORT

```sql
-- Vérifier ce qui a été exporté
SELECT
    'Organisations' as entite,
    COUNT(*) as nombre
FROM res_partner
WHERE is_company = true

UNION ALL

SELECT
    'Utilisateurs actifs',
    COUNT(*)
FROM res_users
WHERE active = true AND share = false

UNION ALL

SELECT
    'Projets actifs (hors tests)',
    COUNT(*)
FROM project_project
WHERE active = true
AND name->>'fr_FR' NOT ILIKE '%test%'

UNION ALL

SELECT
    'Tâches actives',
    COUNT(*)
FROM project_task pt
JOIN project_project pp ON pt.project_id = pp.id
WHERE pt.active = true
AND pp.active = true
AND pp.name->>'fr_FR' NOT ILIKE '%test%'

ORDER BY entite;
```

---

## 📥 ÉTAPES SUIVANTES

### 1. Vérifier les fichiers créés

```bash
ls -lh /tmp/odoo-exports/
```

Vous devriez voir :
- `01_organizations.csv`
- `02_users.csv`
- `03_projects.csv`
- `04_tasks.csv`
- `05_milestones.csv` (si existe)
- `06_stages.csv`

### 2. Transférer les fichiers vers votre machine Windows

```bash
# Option A: Via SCP depuis Windows
scp user@serveur:/tmp/odoo-exports/*.csv D:\auxigene\mdf-access\storage\app\odoo-csv\

# Option B: Compresser et télécharger
cd /tmp/odoo-exports
tar -czf odoo-exports.tar.gz *.csv
# Puis télécharger odoo-exports.tar.gz via votre méthode habituelle
```

### 3. Lancer la conversion CSV → Excel

Une fois les fichiers sur votre machine Windows dans `storage/app/odoo-csv/` :

```bash
php artisan odoo:csv-to-excel
```

Cette commande va :
1. ✅ Lire tous les CSV d'Odoo
2. ✅ Mapper les données vers la structure MDF
3. ✅ Générer les 11 fichiers Excel dans `storage/app/excel/data/`
4. ✅ Créer un rapport de conversion

### 4. Importer dans MDF Access

```bash
php artisan db:seed --class=TestDataMasterSeeder
```

---

## 🎯 NOTES IMPORTANTES

### Mapping des données

- **Organisations** : Fusion automatique avec les 26 organisations existantes par nom
- **Utilisateurs** : Password temporaire `ChangeMeOdoo123!` (à changer au premier login)
- **Projets** : Code extrait du nom ou généré (PROJ-00001)
- **Statuts** : Mapping automatique Odoo → MDF
- **Portfolio** : Tous les projets dans "Projets Odoo" par défaut

### Données NON exportées (ajout manuel possible après)

- ❌ Risques (pas de table standard)
- ❌ Issues/Problèmes (complexe)
- ❌ Ressources/Allocations (dans timesheets)
- ❌ Livrables (pas de table standard)
- ❌ Demandes de changement (pas de table standard)

### En cas d'erreur

Si un script échoue :
1. Vérifiez que vous êtes connecté : `\conninfo`
2. Vérifiez les permissions : `\dp project_project`
3. Adaptez le chemin de sortie si `/tmp/` n'est pas accessible
4. Utilisez `\o fichier.csv` puis `SELECT ...` comme alternative

---

**Prêt à exporter ?** Copiez les scripts ci-dessus dans votre session `psql` ! 🚀
