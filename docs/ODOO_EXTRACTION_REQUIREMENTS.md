# 📊 Requirements pour l'Extraction Odoo → Excel

Ce fichier contient toutes les informations nécessaires pour extraire automatiquement les données de votre base Odoo et générer les fichiers Excel d'import pour le système MDF Access.

---

## 🔌 1. CONNEXION À LA BASE DE DONNÉES ODOO

```yaml
# Remplissez les informations de connexion à votre base PostgreSQL Odoo
DATABASE_CONNECTION:
  host: "173.212.230.240"              # Exemple: localhost, 192.168.1.100, db.example.com
  port: "5432"              # Exemple: 5432 (défaut PostgreSQL)
  database: "samsic"          # Exemple: odoo_production, samsic_odoo
  username: "odoo"          # Exemple: odoo, postgres
  password: "samsicadmina"          # Mot de passe de l'utilisateur

# Test de connexion (optionnel - nous le ferons automatiquement)
# psql -h HOST -p PORT -U USERNAME -d DATABASE
```

---

## 📋 2. STRUCTURE DES TABLES ODOO

### 2.1 Tables principales utilisées

Cochez les tables présentes dans votre installation Odoo :

- [X] `project_project` - Projets ✅ **CONFIRMÉ**
- [X] `project_task` - Tâches ✅ **CONFIRMÉ**
- [X] `res_partner` - Partenaires/Organisations/Clients ✅ **CONFIRMÉ**
- [X] `res_users` - Utilisateurs (probable)
- [ ] `hr_employee` - Employés (à vérifier)
- [ ] `project_project_stage` - Étapes/Phases du projet (probable)
- [X] `account_analytic_account` - Comptes analytiques (budgets) - VU dans project_project.analytic_account_id
- [X] `project_milestone` - Jalons ✅ **VU dans project_task.milestone_id**
- [ ] `project_tags` - Tags de projets (à vérifier)
- [ ] `mail_activity` - Activités/Problèmes (à vérifier)

**Autres tables importantes** (listez-les) :
```
- project_task_type (pour les types de tâches, vu dans task.type_id)
- res_groups (pour les rôles utilisateurs)
- res_groups_users_rel (relation users ↔ groupes)
```

### 2.2 Exploration de la structure

Pour nous aider, exécutez ces requêtes SQL et collez les résultats ci-dessous :

#### **Requête 1: Structure de la table project_project**
```sql
SELECT column_name, data_type, character_maximum_length, is_nullable
FROM information_schema.columns
WHERE table_name = 'project_project'
ORDER BY ordinal_position;
```

**Résultat** (collez ici) :
```
        column_name         |          data_type          | character_maximum_length | is_nullable
----------------------------+-----------------------------+--------------------------+-------------
 id                         | integer                     |                          | NO
 message_main_attachment_id | integer                     |                          | YES
 alias_id                   | integer                     |                          | NO
 sequence                   | integer                     |                          | YES
 partner_id                 | integer                     |                          | YES
 company_id                 | integer                     |                          | NO
 analytic_account_id        | integer                     |                          | YES
 color                      | integer                     |                          | YES
 user_id                    | integer                     |                          | YES
 stage_id                   | integer                     |                          | YES
 last_update_id             | integer                     |                          | YES
 create_uid                 | integer                     |                          | YES
 write_uid                  | integer                     |                          | YES
 access_token               | character varying           |                          | YES
 partner_email              | character varying           |                          | YES
 partner_phone              | character varying           |                          | YES
 privacy_visibility         | character varying           |                          | NO
 rating_status              | character varying           |                          | NO
 rating_status_period       | character varying           |                          | NO
 last_update_status         | character varying           |                          | NO
 date_start                 | date                        |                          | YES
 date                       | date                        |                          | YES
 name                       | jsonb                       |                          | NO
 label_tasks                | jsonb                       |                          | YES
 task_properties_definition | jsonb                       |                          | YES
 description                | text                        |                          | YES
 active                     | boolean                     |                          | YES
 allow_subtasks             | boolean                     |                          | YES
 allow_recurring_tasks      | boolean                     |                          | YES
 allow_task_dependencies    | boolean                     |                          | YES
 allow_milestones           | boolean                     |                          | YES
 rating_active              | boolean                     |                          | YES
 rating_request_deadline    | timestamp without time zone |                          | YES
 create_date                | timestamp without time zone |                          | YES
 write_date                 | timestamp without time zone |                          | YES
 type_id                    | integer                     |                          | YES
(36 lignes)

```

#### **Requête 2: Structure de la table project_task**
```sql
SELECT column_name, data_type, character_maximum_length, is_nullable
FROM information_schema.columns
WHERE table_name = 'project_task'
ORDER BY ordinal_position;
```

**Résultat** (collez ici) :
```
            column_name            |          data_type          | character_maximum_length | is_nullable
-----------------------------------+-----------------------------+--------------------------+-------------
 id                                | integer                     |                          | NO
 message_main_attachment_id        | integer                     |                          | YES
 sequence                          | integer                     |                          | YES
 stage_id                          | integer                     |                          | YES
 project_id                        | integer                     |                          | YES
 display_project_id                | integer                     |                          | YES
 partner_id                        | integer                     |                          | YES
 company_id                        | integer                     |                          | NO
 color                             | integer                     |                          | YES
 displayed_image_id                | integer                     |                          | YES
 parent_id                         | integer                     |                          | YES
 ancestor_id                       | integer                     |                          | YES
 milestone_id                      | integer                     |                          | YES
 recurrence_id                     | integer                     |                          | YES
 analytic_account_id               | integer                     |                          | YES
 create_uid                        | integer                     |                          | YES
 write_uid                         | integer                     |                          | YES
 email_cc                          | character varying           |                          | YES
 access_token                      | character varying           |                          | YES
 name                              | character varying           |                          | NO
 priority                          | character varying           |                          | YES
 kanban_state                      | character varying           |                          | NO
 partner_email                     | character varying           |                          | YES
 partner_phone                     | character varying           |                          | YES
 email_from                        | character varying           |                          | YES
 date_deadline                     | date                        |                          | YES
 task_properties                   | jsonb                       |                          | YES
 description                       | text                        |                          | YES
 working_hours_open                | numeric                     |                          | YES
 working_hours_close               | numeric                     |                          | YES
 active                            | boolean                     |                          | YES
 is_closed                         | boolean                     |                          | YES
 is_blocked                        | boolean                     |                          | YES
 recurring_task                    | boolean                     |                          | YES
 is_analytic_account_id_changed    | boolean                     |                          | YES
 create_date                       | timestamp without time zone |                          | YES
 write_date                        | timestamp without time zone |                          | YES
 date_end                          | timestamp without time zone |                          | YES
 date_assign                       | timestamp without time zone |                          | YES
 date_last_stage_update            | timestamp without time zone |                          | YES
 rating_last_value                 | double precision            |                          | YES
 planned_hours                     | double precision            |                          | YES
 working_days_open                 | double precision            |                          | YES
 working_days_close                | double precision            |                          | YES
 x_date_incident                   | timestamp without time zone |                          | YES
 x_all_zones                       | character varying           |                          | YES
 x_zones_nord                      | character varying           |                          | YES
 x_zones_sud                       | character varying           |                          | YES
 x_date_du_go                      | date                        |                          | YES
 x_criticite                       | character varying           |                          | YES
 x_numero_devis                    | character varying           |                       60 | YES
 x_code_site                       | character varying           |                       60 | YES
 x_numero_da                       | character varying           |                       60 | YES
 x_prestataire                     | integer                     |                          | YES
 x_equipe                          | integer                     |                          | YES
 x_statut_reception_technique      | character varying           |                          | YES
 x_statut_realisation              | character varying           |                          | YES
 x_numero_intervention_synchroteam | character varying           |                          | YES
 x_date_fin_da                     | date                        |                          | YES
 x_date_debut_da                   | date                        |                          | YES
 x_date_reception_technique        | timestamp without time zone |                          | YES
 x_date_fin_realisation            | timestamp without time zone |                          | YES
 x_date_depot_pf                   | timestamp without time zone |                          | YES
 x_date_debut_realisation          | timestamp without time zone |                          | YES
 x_date_reception_pv_systeme       | timestamp without time zone |                          | YES
 x_quantite_realisee               | double precision            |                          | YES
 x_numero_bc                       | character varying           |                          | YES
 x_wo                              | character varying           |                          | YES
 x_date_bc                         | date                        |                          | YES
 x_quantite_validee                | double precision            |                          | YES
 x_statut_reception_systeme        | character varying           |                          | YES
 x_numero_pv_systeme               | character varying           |                          | YES
 x_numero_projet_facture           | character varying           |                          | YES
 x_date_pv_systeme                 | date                        |                          | YES
 x_quantite_receptionnee           | double precision            |                          | YES
 x_quantite_achetee                | double precision            |                          | YES
 x_date_debut_reception_technique  | timestamp without time zone |                          | YES
 x_famille_article                 | character varying           |                          | YES
 x_date_planifiee                  | date                        |                          | YES
 x_date_jr                         | date                        |                          | YES
 x_date_jr_fin                     | date                        |                          | YES
 type_id                           | integer                     |                          | YES
 x_date_livraison_prevue           | date                        |                          | YES
 x_date_depot_pv_technique_zi      | date                        |                          | YES
 x_date_signature_pv_technique     | date                        |                          | YES
 x_date_demande_pv_systeme         | date                        |                          | YES
(86 lignes)


```

#### **Requête 3: Structure de la table res_partner**
```sql
SELECT column_name, data_type, character_maximum_length, is_nullable
FROM information_schema.columns
WHERE table_name = 'res_partner'
ORDER BY ordinal_position;
```

**Résultat** (collez ici) :
```
        column_name         |          data_type          | character_maximum_length | is_nullable
----------------------------+-----------------------------+--------------------------+-------------
 id                         | integer                     |                          | NO
 company_id                 | integer                     |                          | YES
 create_date                | timestamp without time zone |                          | YES
 name                       | character varying           |                          | YES
 title                      | integer                     |                          | YES
 parent_id                  | integer                     |                          | YES
 user_id                    | integer                     |                          | YES
 state_id                   | integer                     |                          | YES
 country_id                 | integer                     |                          | YES
 industry_id                | integer                     |                          | YES
 color                      | integer                     |                          | YES
 commercial_partner_id      | integer                     |                          | YES
 create_uid                 | integer                     |                          | YES
 write_uid                  | integer                     |                          | YES
 display_name               | character varying           |                          | YES
 ref                        | character varying           |                          | YES
 lang                       | character varying           |                          | YES
 tz                         | character varying           |                          | YES
 vat                        | character varying           |                          | YES
 company_registry           | character varying           |                          | YES
 website                    | character varying           |                          | YES
 function                   | character varying           |                          | YES
 type                       | character varying           |                          | YES
 street                     | character varying           |                          | YES
 street2                    | character varying           |                          | YES
 zip                        | character varying           |                          | YES
 city                       | character varying           |                          | YES
 email                      | character varying           |                          | YES
 phone                      | character varying           |                          | YES
 mobile                     | character varying           |                          | YES
 commercial_company_name    | character varying           |                          | YES
 company_name               | character varying           |                          | YES
 date                       | date                        |                          | YES
 comment                    | text                        |                          | YES
 partner_latitude           | numeric                     |                          | YES
 partner_longitude          | numeric                     |                          | YES
 active                     | boolean                     |                          | YES
 employee                   | boolean                     |                          | YES
 is_company                 | boolean                     |                          | YES
 partner_share              | boolean                     |                          | YES
 write_date                 | timestamp without time zone |                          | YES
 message_main_attachment_id | integer                     |                          | YES
 message_bounce             | integer                     |                          | YES
 email_normalized           | character varying           |                          | YES
 signup_token               | character varying           |                          | YES
 signup_type                | character varying           |                          | YES
 signup_expiration          | timestamp without time zone |                          | YES
 team_id                    | integer                     |                          | YES
 partner_gid                | integer                     |                          | YES
 additional_info            | character varying           |                          | YES
 phone_sanitized            | character varying           |                          | YES
(51 lignes)

```

#### **Requête 4: Aperçu de quelques projets**
```sql
SELECT id, name, active, date_start, date, user_id, partner_id
FROM project_project
LIMIT 15;
```

**Résultat** (collez ici) :
```
  id  |                                                     name                                                     | active | date_start | date | user_id | partner_id
------+--------------------------------------------------------------------------------------------------------------+--------+------------+------+---------+------------
 4594 | {"en_US": "Planning S2- 2024_sites", "fr_FR": "Planning S2- 2024_sites"}                                     | t      |            |      |     228 |
 4453 | {"en_US": "MTN220007 - MC & MP SUD", "fr_FR": "MTN220007 - MC & MP SUD"}                                     | t      |            |      |         |          7
 4596 | {"en_US": "Projet SSI", "fr_FR": "Projet SSI"}                                                               | t      |            |      |     223 |
 4460 | {"en_US": "MTN240019 - DC BENI MELLAL PROJET IAM GSM", "fr_FR": "MTN240019 - DC BENI MELLAL PROJET IAM GSM"} | t      |            |      |         |
 3799 | {"en_US": "BC4500013679/ODT200001", "fr_FR": "BC4500013679/ODT200001"}                                       | f      |            |      |         |          7
 4461 | {"en_US": "MTN240018 - DC SETTAT  PROJET IAM GSM", "fr_FR": "MTN240018 - DC SETTAT  PROJET IAM GSM"}         | t      |            |      |         |
 4575 | {"en_US": "INCIDENTS OT / POP : ODT240007", "fr_FR": "INCIDENTS OT / POP : ODT240007"}                       | t      |            |      |     199 |
 4456 | {"en_US": "MTN220008 - MAINTENANCE DATA CENTER NORD", "fr_FR": "MTN220008 - MAINTENANCE DATA CENTER NORD"}   | t      |            |      |     205 |          7
 4597 | {"en_US": "IAM-Mobile-SETTAT- V2-1Þre annÚe", "fr_FR": "IAM-Mobile-SETTAT- V2-1Þre annÚe"}                   | t      |            |      |     228 |
 4038 | {"en_US": "BC4500002450/ODT200001", "fr_FR": "BC4500002450/ODT200001"}                                       | f      |            |      |         |          7
 4595 | {"en_US": "POP OT TEST D IMPORT", "fr_FR": "POP OT TEST D IMPORT"}                                           | f      |            |      |     223 |
 4446 | {"en_US": "MTN230008 -FM Data center  NORD", "fr_FR": "MTN230008 -FM Data center  NORD"}                     | t      |            |      |         |          7
 4457 | {"en_US": "INCIDENT OT/ DATA CENTER : ODT240008", "fr_FR": "INCIDENT OT/ DATA CENTER : ODT240008"}           | t      |            |      |     212 |          7
 4452 | {"en_US": "MTN220008 -MAINTENANCE DATA CENTER SUD ", "fr_FR": "MTN220008 -MAINTENANCE DATA CENTER SUD "}     | t      |            |      |         |          7
 4451 | {"en_US": "MTN220009 -MAINTENANCE POP SUD ", "fr_FR": "MTN220009 -MAINTENANCE POP SUD "}                     | t      |            |      |         |          7
(15 lignes)


```

---

## 🗺️ 3. MAPPING DES DONNÉES

### 3.1 Organisations (res_partner)

**Question 1:** Comment identifiez-vous les organisations clientes ?
- [X] Tous les `res_partner` avec `is_company = true`
- [X] `res_partner` liés aux projets via `partner_id`
- [ ] `res_partner` avec un tag/catégorie spécifique : `________________`
- [ ] Autre : `_______________________________`

**💡 Proposition :** Extraire tous les `res_partner` où `is_company = true` OU qui sont référencés dans `project_project.partner_id`

**Question 2:** Avez-vous des types d'organisations (MOA, MOE, Sponsor, Subcontractor) ?
- [ ] Oui, dans un champ : `________________`
- [ ] Oui, via des tags : `________________`
- [X] Non, à déterminer manuellement

**💡 Proposition :** Par défaut, mettre `type = "client"` pour tous. Vous pourrez ajuster manuellement dans Excel après.

**Question 3:** Champ pour le SIRET/SIREN :
```
Nom du champ: vat
```

**💡 Note :** Le champ `vat` contient généralement le numéro de TVA intracommunautaire. Pour un SIRET français, il faudra peut-être le nettoyer (enlever "FR" au début).

### 3.2 Utilisateurs

**Question 1:** Source des utilisateurs :
- [X] `res_users` uniquement
- [ ] `hr_employee` avec lien vers `res_users`
- [ ] Les deux

**💡 Proposition :** Extraire depuis `res_users` avec jointure vers `res_partner` pour les infos (email, phone, etc.)

**Question 2:** Comment déterminer l'organisation d'un utilisateur ?
```
Champ: company_id (dans res_users ou res_partner)
```

**💡 Note :** `company_id` dans Odoo représente l'entreprise à laquelle appartient l'utilisateur.

**Question 3:** Y a-t-il un champ pour le rôle/fonction ?
```
Champ: function (dans res_partner lié à res_users via partner_id)
```

**💡 Proposition :** Utiliser `res_partner.function` comme rôle métier de l'utilisateur.

### 3.3 Projets

**Question 1:** Champ pour le code projet unique :
- [X] `name` (nom du projet) - extraire la clé fr_FR du JSONB
- [ ] Champ personnalisé : `________________`
- [X] À générer automatiquement - créer un code basé sur le nom (ex: "MTN240019")

**💡 Proposition :** Utiliser le `name->>'fr_FR'` comme nom, et générer un code à partir de ce nom (ou utiliser l'ID si pas de code évident dans le nom).

**Question 2:** Méthodologie du projet (Agile, Waterfall, Hybride) :
- [ ] Champ dédié : `________________`
- [ ] Via tags : `________________`
- [X] Non renseigné (mettre "waterfall" par défaut)

**💡 Proposition :** Par défaut "waterfall". Si vous avez des tags ou un champ custom, indiquez-le.

**Question 3:** Budget du projet :
- [ ] Dans `project_project` : champ `________________`
- [X] Dans `account_analytic_account` : lié via `analytic_account_id`
- [ ] Non disponible

**💡 Proposition :** Faire une jointure avec `account_analytic_account` via `project_project.analytic_account_id` pour récupérer le budget.

**Question 4:** Chef de projet :
- [X] `user_id` dans `project_project`
- [ ] Champ personnalisé : `________________`
- [ ] Non renseigné

**💡 Note :** `user_id` représente généralement le chef de projet dans Odoo.

**Question 5:** Client du projet :
- [X] `partner_id` dans `project_project`
- [ ] Champ personnalisé : `________________`

**💡 Note :** `partner_id` est l'organisation cliente du projet.

**Question 6:** Statut du projet - mapping vers nos statuts :
```yaml
# Vos statuts Odoo → Nos statuts MDF
# Format: "statut_odoo" : "notre_statut"
# Nos statuts: initiation, planning, execution, monitoring, closing, completed, on_hold, cancelled

# À COMPLÉTER AVEC VOS STAGES ODOO
# Pour obtenir les stages, exécutez:
# SELECT id, name FROM project_project_stage;

# Mapping proposé (à adapter selon vos stages réels):
"active=true" : "execution"
"active=false" : "completed"
```

**💡 Action requise :** Exécutez `SELECT id, name FROM project_project_stage;` dans Odoo pour voir vos étapes et complétez le mapping.

### 3.4 Tâches

**Question 1:** Lien avec les phases/WBS :
- [X] Les tâches ont un champ `stage_id` (phase)
- [X] Les tâches ont une hiérarchie via `parent_id`
- [ ] Pas de structure hiérarchique

**💡 Proposition :** Utiliser `stage_id` pour les phases ET `parent_id` pour créer une structure WBS hiérarchique.

**Question 2:** Assignation des tâches :
- [X] Champ personnalisé : `user_ids` (relation many2many)
- [ ] `user_id` dans `project_task`
- [ ] Autre : `________________`

**💡 Note :** Dans Odoo, les tâches peuvent avoir plusieurs assignés via `user_ids`. Nous prendrons le premier ou créerons une tâche par assigné.

**Question 3:** Priorité des tâches - mapping :
```yaml
# Vos priorités Odoo → Nos priorités
# Nos priorités: low, medium, high, critical
# Dans Odoo, priority est souvent: "0" (normal), "1" (urgent)

"0" : "medium"
"1" : "high"
"2" : "high"
"3" : "critical"
```

**💡 Note :** Si votre `priority` est un varchar, adaptez selon vos valeurs réelles.

**Question 4:** Statut des tâches - mapping :
```yaml
# Vos statuts Odoo → Nos statuts
# Nos statuts: not_started, in_progress, completed, on_hold, cancelled

# Basé sur les champs Odoo:
"is_closed=false,kanban_state=normal" : "in_progress"
"is_closed=false,kanban_state=blocked" : "on_hold"
"is_closed=true" : "completed"
"active=false" : "cancelled"
```

**💡 Action requise :** Exécutez `SELECT DISTINCT kanban_state, is_closed FROM project_task;` pour voir vos statuts réels.

**Question 5:** Heures estimées/réelles :
- [X] `planned_hours` pour les heures estimées
- [ ] `effective_hours` pour les heures réelles (vérifier si existe)
- [ ] Autres champs : `working_hours_open`, `working_hours_close`
- [ ] Non disponible

**💡 Note :** Odoo a `planned_hours`. Les heures réelles sont souvent dans `timesheet` (table séparée).

### 3.5 Portfolios et Programmes

**Question 1:** Avez-vous une structure Portfolio/Programme ?
- [ ] Oui, via un champ dans `project_project` : `________________`
- [ ] Oui, via une table dédiée : `________________`
- [X] Non, tous les projets sont indépendants

**💡 Proposition :** Si pas de structure Portfolio/Programme dans Odoo, nous créerons un portfolio par défaut "Projets Odoo" et tous les projets y seront rattachés.

**Si Oui :**
```
Table portfolios: ________________
Table programmes: ________________
Champ de liaison dans project_project: ________________
```

### 3.6 Risques et Problèmes

**Question 1:** Les risques sont-ils suivis dans Odoo ?
- [ ] Oui, table : `project_risk` (vérifier si existe)
- [ ] Via les activités (`mail_activity`)
- [X] Non - NE PAS IMPORTER pour l'instant

**💡 Proposition :** Ne pas importer les risques dans un premier temps. Vous pourrez les ajouter manuellement après.

**Question 2:** Les problèmes/issues sont suivis dans :
- [ ] `project_issue` (si module installé)
- [X] `mail_activity` - pourrait être utilisé
- [ ] Autre : `________________`
- [X] Non - NE PAS IMPORTER pour l'instant

**💡 Proposition :** Ne pas importer les issues pour l'instant. Focaliser sur Projets et Tâches d'abord.

### 3.7 Jalons (Milestones)

**Question 1:** Les jalons sont-ils suivis ?
- [X] Oui, table `project_milestone` - VU dans project_task.milestone_id !
- [ ] Via les tâches avec un flag spécial
- [ ] Non

**💡 Proposition :** Extraire les jalons depuis la table `project_milestone` liée aux tâches via `milestone_id`.

**💡 Action requise :** Exécutez `SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'project_milestone';` pour voir la structure.

### 3.8 Ressources et Allocations

**Question 1:** Le suivi des ressources existe-t-il ?
- [ ] Oui, via `resource_resource`
- [X] Oui, via `hr_employee` (probablement)
- [ ] Non

**💡 Proposition :** Utiliser `hr_employee` comme base pour les ressources, lié à `res_users`.

**Question 2:** Les allocations sont suivies dans :
- [ ] `project_assignment`
- [ ] `resource_calendar`
- [ ] Autre : `timesheet` (account_analytic_line)
- [X] Non - NE PAS IMPORTER pour l'instant

**💡 Proposition :** Les allocations dans Odoo sont souvent dans les timesheets. C'est complexe, on peut sauter pour l'instant.

---

## 🎯 4. RÈGLES MÉTIER ET FILTRES

### 4.1 Filtrage des données

**Question 1:** Souhaitez-vous extraire :
- [ ] Tous les projets
- [X] Uniquement les projets actifs (`active = true`)
- [ ] Projets d'une période spécifique :
  ```
  Date début: 2024-01-01
  Date fin: 2025-12-31
  ```
- [ ] Projets avec un tag spécifique : `________________`

**💡 Proposition :** Commencer par les projets actifs uniquement. Vous pourrez ajuster après.

**Question 2:** Exclusions :
```
Exclure les projets avec statut: N/A (on filtre déjà sur active=true)
Exclure les projets de type: N/A
Exclure les projets tests: name LIKE '%TEST%' OU name LIKE '%test d import%'
```

**💡 Note :** J'ai vu "POP OT TEST D IMPORT" dans vos données - on pourra filtrer les tests.

### 4.2 Valeurs par défaut

Pour les données manquantes, que faut-il utiliser par défaut ?

```yaml
DEFAULTS:
  project_methodology: "waterfall"     # waterfall, agile, hybride
  project_status: "execution"          # initiation, planning, execution, etc.
  task_priority: "medium"              # low, medium, high, critical
  task_status: "in_progress"           # not_started, in_progress, completed, etc.
  user_is_system_admin: false          # true/false
  organization_type: "client"          # client, vendor, partner
  portfolio_name: "Projets Odoo"       # Portfolio par défaut
  program_name: null                   # Pas de programme par défaut
```

**💡 Note :** Ces valeurs seront utilisées quand le champ est NULL dans Odoo.

---

## 📦 5. DONNÉES EXISTANTES À RÉUTILISER

### 5.1 Organizations déjà importées

**Question:** Voulez-vous :
- [X] Réutiliser les 26 organisations déjà en base (SAMSIC, etc.)
- [X] Importer uniquement les nouvelles organisations d'Odoo (fusion intelligente)
- [ ] Remplacer complètement par les organisations Odoo

**💡 Proposition :**
1. Chercher les organisations Odoo dans la base MDF par nom
2. Si trouvée → utiliser l'ID MDF existant
3. Si nouvelle → créer dans MDF et mapper l'ID

**Si réutilisation :** Le mapping sera automatique par nom. Exemple :
```
# Mapping automatique par similarité de nom:
# "SAMSIC" dans Odoo → Organization ID 1 "SAMSIC FACILITY" dans MDF
# "IAM" dans Odoo → Nouveau dans MDF

# Si vous voulez un mapping manuel spécifique, ajoutez-le ici:
Organization Odoo ID | Organization MDF ID | Nom
---------------------|---------------------|------------------------
7                    | 1                   | SAMSIC FACILITY (à vérifier)
```

**💡 Action requise :** Exécutez dans Odoo `SELECT id, name FROM res_partner WHERE is_company=true LIMIT 20;` pour voir vos organisations.

### 5.2 Roles et Permissions

**Question:** Les rôles utilisateurs dans Odoo correspondent à :
```yaml
# Groupes Odoo → Rôles MDF
# Exemples de rôles MDF: system-admin, pmo-manager, project-manager, team-member, client-user

# Mapping proposé (à adapter):
"base.group_system" : "system-admin"           # Administrateur système
"project.group_project_manager" : "project-manager"  # Chef de projet
"project.group_project_user" : "team-member"   # Utilisateur projet
"base.group_portal" : "client-user"            # Utilisateur portal (client)
"base.group_user" : "team-member"              # Utilisateur interne

# Par défaut si pas de groupe spécifique:
"default" : "team-member"
```

**💡 Action requise :** Exécutez `SELECT DISTINCT g.name FROM res_groups g JOIN res_groups_users_rel r ON g.id = r.gid LIMIT 20;` pour voir vos groupes Odoo.

---

## ✅ 6. VALIDATION

Une fois ce fichier rempli, nous créerons :

1. ✅ Script de connexion et validation
2. ✅ Extracteur automatique avec mapping
3. ✅ Générateur des 11 fichiers Excel
4. ✅ Rapport de transformation avec statistiques
5. ✅ Script de vérification des données extraites

---

## 📝 7. NOTES ET COMMENTAIRES

Ajoutez ici toute information supplémentaire importante :

```
-
-
-
```

---

## 🚀 8. COMMANDE D'EXÉCUTION

Une fois ce fichier rempli, la commande suivante sera créée :

```bash
# Test de connexion
php artisan odoo:test-connection

# Extraction et génération des Excel
php artisan odoo:extract-to-excel

# Options disponibles :
# --dry-run         : Simulation sans génération de fichiers
# --only=projects   : Extraire uniquement les projets
# --limit=100       : Limiter le nombre d'enregistrements
# --verbose         : Affichage détaillé
```

---

## 📊 RÉSUMÉ DES ACTIONS REQUISES

Avant de lancer l'extraction, veuillez exécuter ces requêtes SQL dans Odoo et compléter les sections manquantes :

### ✅ Requêtes SQL à exécuter :

```sql
-- 1. Voir les stages/étapes de projets
SELECT id, name FROM project_project_stage;

-- 2. Voir les statuts kanban et clôture des tâches
SELECT DISTINCT kanban_state, is_closed, active FROM project_task;

-- 3. Voir la structure de la table milestones
SELECT column_name, data_type FROM information_schema.columns
WHERE table_name = 'project_milestone';

-- 4. Voir vos organisations
SELECT id, name, vat, is_company FROM res_partner
WHERE is_company = true
ORDER BY name LIMIT 30;

-- 5. Voir les groupes utilisateurs
SELECT DISTINCT g.name
FROM res_groups g
JOIN res_groups_users_rel r ON g.id = r.gid
WHERE g.name LIKE '%project%' OR g.name LIKE '%manager%' OR g.name LIKE '%user%'
ORDER BY g.name;

-- 6. Voir la relation users → companies
SELECT u.id, u.login, p.name as partner_name, p.company_id
FROM res_users u
JOIN res_partner p ON u.partner_id = p.id
LIMIT 10;

-- 7. Compter les données disponibles
SELECT
    (SELECT COUNT(*) FROM project_project WHERE active = true) as projets_actifs,
    (SELECT COUNT(*) FROM project_task WHERE active = true) as taches_actives,
    (SELECT COUNT(*) FROM res_partner WHERE is_company = true) as organisations,
    (SELECT COUNT(*) FROM res_users WHERE active = true) as utilisateurs,
    (SELECT COUNT(*) FROM project_milestone) as jalons;
```

### 📋 Sections à compléter manuellement :

1. **Section 3.3 - Question 6** : Compléter le mapping des statuts de projets selon vos stages Odoo
2. **Section 3.6** : Si vous avez des risques/issues, indiquer les tables
3. **Section 5.1** : Vérifier le mapping des organisations Odoo ↔ MDF si nécessaire

### 🎯 Données qui SERONT extraites (avec les valeurs actuelles) :

- ✅ **Organizations** : `res_partner` où `is_company = true`
- ✅ **Users** : `res_users` actifs avec infos depuis `res_partner`
- ✅ **Projects** : `project_project` actifs (hors tests)
- ✅ **Tasks** : `project_task` liées aux projets actifs
- ✅ **Milestones** : `project_milestone` (si la table existe)
- ⚠️ **Phases** : Via `stage_id` des tâches (mapping à confirmer)
- ❌ **Portfolios** : Créer un portfolio par défaut "Projets Odoo"
- ❌ **Programs** : Non extrait (pas de structure dans Odoo standard)
- ❌ **Risks/Issues** : Non extrait dans un premier temps
- ❌ **Resources/Allocations** : Non extrait (trop complexe pour démarrage)

### 🚀 Prochaine étape :

Une fois les requêtes SQL exécutées et les résultats ajoutés ci-dessus, je créerai :
1. `app/Console/Commands/OdooTestConnection.php` - Test de connexion
2. `app/Console/Commands/OdooExtractToExcel.php` - Extracteur principal
3. `app/Services/OdooExtractor.php` - Service d'extraction
4. Les 11 fichiers Excel dans `storage/app/excel/data/`

---

**Statut de complétion :** [X] 75% - Pré-rempli avec propositions intelligentes

**Date de pré-remplissage :** 2025-01-09

**Pré-rempli par :** Claude (Assistant IA)

**À compléter par vous :**
- [ ] Exécuter les requêtes SQL ci-dessus
- [ ] Coller les résultats dans les sections correspondantes
- [ ] Vérifier les mappings proposés
- [ ] Ajuster les valeurs par défaut si nécessaire
