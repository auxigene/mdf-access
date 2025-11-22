# Stratégie de Développement - Programme FM Sites INWI

**Date:** 13 novembre 2025
**Version:** 1.0 (Draft)
**Statut:** Validée

---

## 1. Contexte

### 1.1 Situation Actuelle

- **Client:** SAMSIC MAINTENANCE MAROC
- **Domaine:** Maintenance terrain (Field Maintenance)
- **Portfolio:** Projets INWI
- **Programme cible:** FM Sites INWI (Field Maintenance des Sites INWI)

### 1.2 Projets du Programme FM

Le programme FM englobe trois projets reconduits annuellement :

1. **MP** - Maintenances Préventives
2. **MC** - Maintenances Correctives
3. **OT** - Ordres de Travaux

### 1.3 Problématique

- Échecs successifs avec les GMAO (DimoMaint, Synchroteam, Mission One)
- ~50 projets actuellement en cours
- Besoin d'outils digitaux d'appui aux opérations terrain
- Base de données évolutive du parc de sites GSM INWI
- Changements fréquents : activation/désactivation, classification, géographie, ajout de colonnes

### 1.4 Données Disponibles

- **Fichier:** `storage/app/excel/data/fm-inwi/Parc_Sites_INWI_Version_08-10-2025.xlsx`
- **Feuille principale:** PARC_SITES_INWI
- **Anomalies identifiées:** Voir `anomalies_parc_sites.txt`

---

## 2. Architecture Globale Proposée

### 2.1 Fondations PMBOK Existantes ✓

La plateforme dispose déjà d'une base PMBOK solide :

```
Portfolio
└── Program
    └── Project
        ├── Phases
        ├── WBS Elements
        ├── Deliverables
        ├── Tasks
        ├── Milestones
        ├── Risks
        ├── Issues
        └── Change Requests
```

**Composants existants:**
- Multi-tenancy avec Organizations
- Gestion des rôles et permissions (ACL)
- API Keys pour l'authentification
- Système de templates de phases PMBOK
- Allocations de ressources

### 2.2 Architecture en 3 Couches

#### **Couche 1 : PMBOK Générique** (Existante ✓)

```
Portfolio: INWI
└── Programme: FM Sites INWI {{année}}
    ├── Projet: MP (Maintenance Préventive)
    ├── Projet: MC (Maintenance Corrective)
    └── Projet: OT (Ordres de Travaux)
```

#### **Couche 2 : Données de Référence Métier** (À créer - PRIORITAIRE)

Système de gestion du **parc de sites GSM** :

```
Tables à créer:
├── fm_sites                # Parc de sites
├── fm_regions              # Découpage géographique
├── fm_site_classes         # Classification des sites
├── fm_site_type_colocation # Les Types de colocations
├── fm_energy_sources       # Sources d'énergie
├── fm_site_history         # Historique des modifications
└── fm_parc_snapshots       # Snapshots du parc
```

#### **Couche 3 : Données Opérationnelles** (Phase ultérieure)

Données spécifiques aux interventions terrain :

```
Tables futures:
├── fm_interventions        # Interventions terrain
├── fm_work_orders          # Ordres de travail
├── fm_maintenance_plans    # Plans de maintenance
├── fm_equipment            # Équipements
└── fm_intervention_teams   # Équipes d'intervention
```

---

## 3. Plan de Développement Structuré

### Phase 1 : Fondations - Gestion du Parc de Sites (PRIORITAIRE)

**Objectif:** Créer un système robuste pour gérer le référentiel des sites INWI

**Durée estimée:** 2-3 semaines

#### 3.1.1 Modélisation des Données du Parc

**Tâches:**

1. **Analyse approfondie du fichier Excel**
   - Examiner toutes les feuilles du fichier Excel
   - Identifier les colonnes et leurs types
   - Comprendre les relations entre les entités
   - Documenter les règles métier

2. **Création des modèles Eloquent**
   - `FmSite` (modèle principal)
   - `FmRegion` (régions géographiques)
   - `FmSiteClass` (classes de sites)
   - `FmSiteTypeColocation` (types de colocation)
   - `FmEnergySource` (sources d'énergie)
   - `FmSiteHistory` (historique)

3. **Migrations PostgreSQL**
   - Contraintes d'intégrité référentielle
   - Index pour performances
   - Soft deletes pour traçabilité
   - Colonnes d'audit (created_at, updated_at, etc.)

4. **Relations entre entités**
   - FmSite belongsTo FmRegion
   - FmSite belongsTo FmSiteClass
   - FmSite belongsTo FmSiteTypeColocation
   - FmSite hasMany FmSiteHistory

#### 3.1.2 Import Initial du Parc

**Tâches:**

1. **Service d'importation Excel**
   - Parser le fichier Excel (PhpSpreadsheet)
   - Mapper les colonnes → champs base de données
   - Validation des données
   - Gestion des erreurs et anomalies

2. **Traitement des anomalies**
   - Classification vide → valeur par défaut
   - Type colocation manquant → Pas de colocation
   - Données incomplètes → workflow de correction
   - Logs d'import détaillés

3. **Traçabilité des importations**
   - Table `fm_import_logs`
   - Enregistrement de chaque import
   - Statistiques (succès/échecs)
   - Possibilité de rollback

#### 3.1.3 API CRUD pour le Parc de Sites

**Tâches:**

1. **Endpoints RESTful**
   ```
   GET    /api/inwi/fm/sites           # Liste paginée
   GET    /api/inwi/fm/sites/{id}      # Détail d'un site
   POST   /api/inwi/fm/sites           # Création
   PUT    /api/inwi/fm/sites/{id}      # Mise à jour
   DELETE /api/inwi/fm/sites/{id}      # Suppression (soft)
   ```

2. **Filtres avancés**
   - Par région
   - Par classe
   - Par statut (actif/inactif)
   - Par type de colocation
   - Par source d'énergie
   - Recherche textuelle (code site, nom)

3. **Endpoints complémentaires**
   ```
   GET /api/inwi/fm/sites/{id}/history     # Historique d'un site
   GET /api/inwi/fm/regions                # Liste des régions
   GET /api/inwi/fm/site-classes           # Liste des classes
   GET /api/inwi/fm/statistics             # Statistiques du parc
   ```

4. **Authentification**
   - Utiliser le système API Keys existant
   - Type d'API: `fm_sites`
   - Niveaux d'accès: read, write, admin

#### 3.1.4 Système de Versionnement du Parc

**Tâches:**

1. **Historique des modifications**
   - Enregistrer chaque changement (activation, désactivation, etc.)
   - Qui a fait la modification
   - Quand
   - Ancienne/nouvelle valeur

2. **Snapshots annuels**
   - Capture de l'état complet du parc à une date donnée
   - Comparaisons inter-annuelles
   - Base pour reconduction des projets

3. **Audit trail complet**
   - Traçabilité totale
   - Conformité
   - Reporting historique

---

### Phase 2 : Intégration PMBOK - Contexte Métier

**Objectif:** Lier le parc de sites aux projets FM

**Durée estimée:** 2 semaines

#### 3.2.1 Relation Portfolio/Programme → Parc

**Tâches:**

1. **Table pivot program_fm_sites**
   - Associer un programme au parc de sites
   - Date de début/fin de validité
   - Version du parc utilisée

2. **Scope de sites par projet**
   - Un projet MP peut cibler certains sites
   - Un projet MC d'autres sites
   - Flexibilité dans l'affectation

3. **API d'association**
   ```
   GET  /api/programs/{id}/sites      # Sites du programme
   POST /api/projects/{id}/sites      # Assigner des sites à un projet
   ```

#### 3.2.2 Configuration des Projets FM

**Tâches:**

1. **Extension du modèle Project**
   - Métadonnées spécifiques FM
   - Table `project_fm_configs`
   - Types d'interventions autorisées
   - Paramètres opérationnels

2. **KPIs spécifiques FM**
   - Taux de disponibilité des sites
   - Temps de réponse moyen
   - Taux de réussite des interventions
   - Coûts par type d'intervention

3. **Dashboard projet FM**
   - Vue consolidée
   - Indicateurs en temps réel
   - Alertes

#### 3.2.3 WBS Template pour FM

**Tâches:**

1. **Templates pré-configurés**
   - Template MP (Maintenance Préventive)
   - Template MC (Maintenance Corrective)
   - Template OT (Ordres de Travaux)

2. **Livrables types**
   - Rapports d'intervention
   - Certificats de conformité
   - Photos avant/après
   - Fiches techniques

3. **Intégration avec templates PMBOK existants**
   - Réutiliser le système de `MethodologyTemplate` et `PhaseTemplate`

---

### Phase 3 : Opérations Terrain

**Objectif:** Descendre dans les détails opérationnels

**Durée estimée:** 3-4 semaines

#### 3.3.1 Ordres de Travail et Interventions

**Tâches:**

1. **Modèles pour interventions terrain**
   - `FmIntervention`
   - `FmWorkOrder`
   - Statuts et workflows

2. **Planification et affectation**
   - Assigner des équipes
   - Calendrier d'interventions
   - Gestion des disponibilités

3. **Suivi d'exécution**
   - Début/fin d'intervention
   - Temps passé
   - Matériaux utilisés
   - Photos et documents

#### 3.3.2 Intégration Kizeo Forms

**Tâches:**

1. **Webhook pour réception des formulaires**
   - Endpoint pour Kizeo
   - Validation des données
   - Traitement asynchrone (queues)

2. **Mapping des champs**
   - Champs Kizeo → Base de données
   - Configuration flexible
   - Gestion des types de formulaires

3. **Synchronisation bidirectionnelle**
   - Envoyer des données vers Kizeo
   - Récupérer les réponses
   - Mise à jour automatique

4. **Extension du système Excel existant**
   - Réutiliser `ExcelUpdateController`
   - Adapter pour les besoins FM

#### 3.3.3 Tableaux de Bord et Reporting

**Tâches:**

1. **Dashboard par projet**
   - Vue MP
   - Vue MC
   - Vue OT

2. **Indicateurs de performance**
   - Métriques PMBOK (budget, délais)
   - KPIs métier FM
   - Comparaisons

3. **Rapports automatisés**
   - Rapports hebdomadaires/mensuels
   - Export Excel/PDF
   - Envoi par email

---

## 4. Structure de Fichiers Proposée

```
app/
├── Models/
│   ├── Portfolio.php                  # Existant
│   ├── Program.php                    # Existant
│   ├── Project.php                    # Existant
│   ├── Phase.php                      # Existant
│   ├── WbsElement.php                 # Existant
│   └── FieldMaintenance/              # NOUVEAU MODULE FM
│       ├── FmSite.php
│       ├── FmRegion.php
│       ├── FmSiteClass.php
│       ├── FmSiteTypeColocation.php
│       ├── FmEnergySource.php
│       ├── FmSiteHistory.php
│       ├── FmParcSnapshot.php
│       ├── FmImportLog.php
│       ├── FmIntervention.php         # Phase 3
│       └── FmWorkOrder.php            # Phase 3
│
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── ExcelUpdateController.php    # Existant
│   │       └── FieldMaintenance/            # NOUVEAU
│   │           ├── FmSiteController.php
│   │           ├── FmRegionController.php
│   │           ├── FmStatisticsController.php
│   │           ├── FmImportController.php
│   │           └── FmInterventionController.php  # Phase 3
│   │
│   └── Requests/
│       └── FieldMaintenance/
│           ├── StoreFmSiteRequest.php
│           ├── UpdateFmSiteRequest.php
│           └── ImportFmParcRequest.php
│
├── Services/
│   └── FieldMaintenance/
│       ├── FmSiteService.php
│       ├── FmImportService.php
│       ├── FmSnapshotService.php
│       ├── FmStatisticsService.php
│       └── FmInterventionService.php    # Phase 3
│
└── Exports/
    └── FieldMaintenance/
        ├── FmSitesExport.php
        └── FmStatisticsExport.php

database/
├── migrations/
│   └── field_maintenance/
│       ├── 2025_11_13_100001_create_fm_regions_table.php
│       ├── 2025_11_13_100002_create_fm_site_classes_table.php
│       ├── 2025_11_13_100003_create_fm_site_type_colocations_table.php
│       ├── 2025_11_13_100004_create_fm_energy_sources_table.php
│       ├── 2025_11_13_100005_create_fm_sites_table.php
│       ├── 2025_11_13_100006_create_fm_site_history_table.php
│       ├── 2025_11_13_100007_create_fm_parc_snapshots_table.php
│       ├── 2025_11_13_100008_create_fm_import_logs_table.php
│       ├── 2025_11_13_200001_create_program_fm_sites_table.php
│       ├── 2025_11_13_200002_create_project_fm_configs_table.php
│       └── 2025_11_13_300001_create_fm_interventions_table.php  # Phase 3
│
└── seeders/
    └── FieldMaintenance/
        ├── FmRegionSeeder.php
        ├── FmSiteClassSeeder.php
        └── FmInitialParcSeeder.php

routes/
└── api.php
    # Ajouter les routes FM:
    # Route::prefix('inwi/fm')->group(function () { ... });

tests/
└── Feature/
    └── FieldMaintenance/
        ├── FmSiteTest.php
        ├── FmImportTest.php
        └── FmApiTest.php

docs/
└── field_maintenance/
    ├── FM_DATABASE_SCHEMA.md
    ├── FM_API_DOCUMENTATION.md
    └── FM_IMPORT_GUIDE.md
```

---

## 5. Schéma de Données Préliminaire

### Table: `fm_regions`

```sql
id                  SERIAL PRIMARY KEY
code                VARCHAR(10) UNIQUE NOT NULL
name                VARCHAR(100) NOT NULL
parent_region_id    INTEGER REFERENCES fm_regions(id)
level               INTEGER DEFAULT 1
status              VARCHAR(20) DEFAULT 'active'
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP
```

### Table: `fm_site_classes`

```sql
id                  SERIAL PRIMARY KEY
code                VARCHAR(20) UNIQUE NOT NULL
name                VARCHAR(100) NOT NULL
description         TEXT
priority            INTEGER DEFAULT 0
status              VARCHAR(20) DEFAULT 'active'
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP
```

### Table: `fm_site_type_colocations`

```sql
id                  SERIAL PRIMARY KEY
code                VARCHAR(20) UNIQUE NOT NULL
name                VARCHAR(100) NOT NULL
description         TEXT
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP
```

### Table: `fm_energy_sources`

```sql
id                  SERIAL PRIMARY KEY
code                VARCHAR(20) UNIQUE NOT NULL
name                VARCHAR(100) NOT NULL
description         TEXT
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP
```

### Table: `fm_sites` (PRINCIPALE)

```sql
id                      SERIAL PRIMARY KEY
site_code               VARCHAR(50) UNIQUE NOT NULL
site_name               VARCHAR(200)
region_id               INTEGER REFERENCES fm_regions(id)
site_class_id           INTEGER REFERENCES fm_site_classes(id)
site_type_id            INTEGER REFERENCES fm_site_types(id)
energy_source_id        INTEGER REFERENCES fm_energy_sources(id)
latitude                DECIMAL(10, 8)
longitude               DECIMAL(11, 8)
address                 TEXT
status                  VARCHAR(20) DEFAULT 'active'  -- active, inactive, decommissioned
is_colocation           BOOLEAN DEFAULT FALSE
colocation_details      JSONB
technical_specs         JSONB
metadata                JSONB  -- Colonnes Excel supplémentaires
activated_at            TIMESTAMP
deactivated_at          TIMESTAMP
created_at              TIMESTAMP
updated_at              TIMESTAMP
deleted_at              TIMESTAMP
```

### Table: `fm_site_history`

```sql
id                      SERIAL PRIMARY KEY
fm_site_id              INTEGER REFERENCES fm_sites(id) ON DELETE CASCADE
field_name              VARCHAR(100) NOT NULL
old_value               TEXT
new_value               TEXT
changed_by              INTEGER REFERENCES users(id)
change_reason           TEXT
changed_at              TIMESTAMP NOT NULL
```

### Table: `fm_parc_snapshots`

```sql
id                      SERIAL PRIMARY KEY
snapshot_date           DATE NOT NULL UNIQUE
description             TEXT
total_sites             INTEGER
active_sites            INTEGER
inactive_sites          INTEGER
data_snapshot           JSONB  -- Export complet du parc à cette date
created_by              INTEGER REFERENCES users(id)
created_at              TIMESTAMP
```

### Table: `fm_import_logs`

```sql
id                      SERIAL PRIMARY KEY
import_date             TIMESTAMP NOT NULL
file_name               VARCHAR(255)
file_path               VARCHAR(500)
total_rows              INTEGER
successful_imports      INTEGER
failed_imports          INTEGER
warnings_count          INTEGER
errors                  JSONB
warnings                JSONB
imported_by             INTEGER REFERENCES users(id)
status                  VARCHAR(20)  -- pending, completed, failed
completed_at            TIMESTAMP
created_at              TIMESTAMP
```

### Table: `program_fm_sites` (Phase 2)

```sql
id                      SERIAL PRIMARY KEY
program_id              INTEGER REFERENCES programs(id) ON DELETE CASCADE
fm_site_id              INTEGER REFERENCES fm_sites(id) ON DELETE CASCADE
valid_from              DATE
valid_to                DATE
scope_description       TEXT
is_active               BOOLEAN DEFAULT TRUE
created_at              TIMESTAMP
updated_at              TIMESTAMP

UNIQUE(program_id, fm_site_id, valid_from)
```

### Table: `project_fm_configs` (Phase 2)

```sql
id                      SERIAL PRIMARY KEY
project_id              INTEGER REFERENCES projects(id) ON DELETE CASCADE
intervention_types      JSONB  -- Types autorisés: preventive, corrective, work_order
target_response_time    INTEGER  -- En heures
target_availability     DECIMAL(5, 2)  -- Pourcentage
operational_params      JSONB
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

---

## 6. Bénéfices de cette Approche

### 6.1 Séparation des Préoccupations

- **PMBOK reste générique** : Aucune modification des modèles existants
- **Métier FM dans un module séparé** : Namespace `FieldMaintenance`
- **Couplage faible** : Relations via foreign keys, pas de dépendances fortes
- **Cohésion forte** : Tout le FM est regroupé logiquement

### 6.2 Évolutivité

- **Facile d'ajouter d'autres programmes/portfolios** : L'architecture supporte d'autres clients
- **Réutilisabilité des composants PMBOK** : Templates, phases, WBS, etc.
- **Extension progressive** : Phase par phase sans casser l'existant
- **Scalabilité** : Structure modulaire qui peut grandir

### 6.3 Conformité PMBOK

- **Respect de la hiérarchie** : Portfolio → Program → Project
- **Gouvernance claire** : Rôles et permissions
- **Reporting multi-niveaux** : Du portfolio au site individuel
- **Traçabilité** : Audit trail complet

### 6.4 Robustesse Opérationnelle

- **Référentiel fiable** : Source unique de vérité pour les sites
- **Traçabilité complète** : Historique de tous les changements
- **Intégrité des données** : Contraintes PostgreSQL
- **Versionnement** : Snapshots pour comparaisons historiques

### 6.5 Facilité de Maintenance

- **Code organisé** : Structure claire et logique
- **Tests unitaires** : Chaque service testable
- **Documentation** : Architecture documentée
- **Onboarding facile** : Nouveaux développeurs comprennent vite

---

## 7. Points d'Attention Identifiés

### 7.1 Anomalies dans les Données Excel

D'après `anomalies_parc_sites.txt` :

1. **Classification vide**
   - Site: SBE-1095
   - **Traitement:** Rejeter ou assigner une classe par défaut "NON_CLASSIFIE"

2. **Source d'énergie Coloc sans type de colocation précisé**
   - Sites: AGA-1203, BER-1154
   - **Traitement:**
     - Signaler comme warning
     - Créer un workflow de correction
     - Ajouter un type "COLOC_NON_PRECISE"

### 7.2 Stratégie de Traitement des Anomalies

```
Lors de l'import:
├── Données valides → Import direct
├── Données avec warnings → Import + signalement
│   └── Notification à l'administrateur
└── Données invalides → Rejet
    └── Log dans fm_import_logs
```

### 7.3 Gestion des Évolutions du Parc

Le parc évolue fréquemment. Il faut :

1. **API de mise à jour en temps réel**
   - Webhook pour notifications de changements
   - Synchronisation avec systèmes INWI (si possible)

2. **Workflow d'approbation**
   - Changements majeurs nécessitent validation
   - Historique des demandes de changement

3. **Impact sur projets en cours**
   - Alerter si un site d'un projet actif est désactivé
   - Permettre réaffectation

---

## 8. Risques et Mitigations

### Risque 1: Qualité des Données Source

**Risque:** Données Excel incomplètes ou incohérentes

**Impact:** Haute
**Probabilité:** Moyenne

**Mitigation:**
- Validation stricte à l'import
- Workflow de correction des anomalies
- Communication avec INWI pour qualité des données
- Phase de nettoyage avant mise en production

### Risque 2: Évolution du Format Excel

**Risque:** INWI change la structure du fichier Excel

**Impact:** Moyenne
**Probabilité:** Haute

**Mitigation:**
- Configuration flexible du mapping
- Versionning du format d'import
- Documentation du format attendu
- Tests automatisés pour détecter les changements

### Risque 3: Volume de Données

**Risque:** Performance avec des milliers de sites et interventions

**Impact:** Moyenne
**Probabilité:** Moyenne

**Mitigation:**
- Index PostgreSQL optimisés
- Pagination systématique
- Cache (Redis) pour données fréquemment accédées
- Archivage des données anciennes

### Risque 4: Intégration Kizeo Forms

**Risque:** Synchronisation défaillante avec Kizeo

**Impact:** Haute
**Probabilité:** Faible

**Mitigation:**
- Queue system pour traitement asynchrone
- Retry automatique en cas d'échec
- Logs détaillés
- Alertes en cas de problème

---

## 9. Prochaines Étapes

### 9.1 Validation de cette Stratégie

- [ ] Revue et amendements par l'équipe
- [ ] Validation de l'architecture
- [ ] Validation du plan de phases
- [ ] Validation des priorités

### 9.2 Phase 1 - Démarrage

1. **Analyse détaillée du fichier Excel**
   - Examiner toutes les feuilles
   - Documenter chaque colonne
   - Identifier toutes les relations

2. **Création du schéma de base de données**
   - Finaliser la structure des tables
   - Créer les migrations
   - Créer les seeders pour données de référence

3. **Développement des modèles Eloquent**
   - Modèles de base
   - Relations
   - Scopes et helpers

4. **Service d'import**
   - Parser Excel
   - Validation
   - Import en base

5. **API CRUD de base**
   - Endpoints essentiels
   - Tests unitaires
   - Documentation API

### 9.3 Livrables Phase 1

- [ ] Migrations database
- [ ] Modèles Eloquent
- [ ] Service d'import fonctionnel
- [ ] API CRUD testée
- [ ] Documentation technique
- [ ] Import du parc actuel réussi

---

## 10. Ressources Nécessaires

### 10.1 Techniques

- **Backend:** Laravel 12, PHP 8.2, PostgreSQL
- **Librairies:** PhpSpreadsheet (Excel), Laravel Excel
- **API:** RESTful, authentification via API Keys existantes
- **Queue:** Laravel Queues (Redis/Database)

### 10.2 Humaines

- **Développeur Backend Laravel:** 1 personne (temps plein)
- **Analyste Métier FM:** Support ponctuel pour validation
- **Admin Système:** Configuration infrastructure

### 10.3 Données

- **Fichier Excel parc sites** (disponible ✓)
- **Documentation métier INWI** (à obtenir)
- **Exemples de formulaires Kizeo** (phase 3)

---

## 11. Mesures de Succès

### Phase 1

- [ ] 100% des sites du parc importés avec succès
- [ ] < 5% d'anomalies bloquantes
- [ ] API répond en < 500ms pour requêtes simples
- [ ] Couverture de tests > 80%
- [ ] Documentation complète

### Phase 2

- [ ] Projets MP, MC, OT créés et liés au parc
- [ ] Scope de sites défini pour chaque projet
- [ ] Dashboard fonctionnel avec KPIs

### Phase 3

- [ ] Intégration Kizeo fonctionnelle
- [ ] Interventions enregistrées automatiquement
- [ ] Reporting automatisé opérationnel
- [ ] Formation équipes terrain effectuée

---

## 12. Annexes

### A. Glossaire

- **FM:** Field Maintenance (Maintenance Terrain)
- **MP:** Maintenance Préventive
- **MC:** Maintenance Corrective
- **OT:** Ordres de Travaux
- **GSM:** Global System for Mobile Communications (sites télécom)
- **GMAO:** Gestion de Maintenance Assistée par Ordinateur
- **PMBOK:** Project Management Body of Knowledge
- **WBS:** Work Breakdown Structure
- **KPI:** Key Performance Indicator

### B. Références

- Documentation PMBOK existante du projet
- Fichier: `Parc_Sites_INWI_Version_08-10-2025.xlsx`
- Fichier: `anomalies_parc_sites.txt`
- Migration log: `migration_log_20251109.md`

### C. Contacts

- **Chef de Projet SAMSIC:** [À compléter]
- **Contact INWI:** [À compléter]
- **Support Kizeo Forms:** [À compléter]

---

## Notes de Version

| Version | Date | Auteur | Modifications |
|---------|------|--------|---------------|
| 1.0 | 2025-11-13 | Claude Code | Création initiale du document stratégique |

---

**Statut:** 🔄 En attente de validation et amendements

**Prochaine action:** Revue et validation de cette stratégie avant démarrage de l'implémentation
