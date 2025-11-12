# 📚 Documentation MDF Access

**Plateforme de Gestion de Projets PMBOK Multi-Tenant**

Bienvenue dans la documentation complète du projet MDF Access. Ce document sert d'index pour naviguer facilement dans l'ensemble de la documentation.

---

## 📋 Table des Matières

1. [État du Projet](#état-du-projet)
2. [Architecture](#architecture)
3. [Développement](#développement)
4. [Guides Techniques](#guides-techniques)
5. [Import et Migration](#import-et-migration)
6. [Fonctionnalités](#fonctionnalités)

---

## 🎯 État du Projet

### 📊 Roadmap et Progression

- **[ROADMAP_CURRENT_STATUS.md](./ROADMAP_CURRENT_STATUS.md)** - État actuel du projet et prochaines étapes
  - Progression globale : **42%**
  - Phase 0 : Architecture ✅
  - Phase 1 : Base de données ✅
  - Phase 2 : Models et Relations ✅
  - Phase 2b : Templates Phases PMBOK ✅
  - Phase 3-8 : En cours de développement

---

## 🏗️ Architecture

### Architecture Globale

- **[MULTI_TENANT_ARCHITECTURE.md](./MULTI_TENANT_ARCHITECTURE.md)** - Architecture multi-tenant complète
  - Structure de base de données tenant-aware
  - Logique Row-Level Security (RLS)
  - Système de permissions (174 permissions)
  - Système de rôles (29 rôles)

- **[MULTI_TENANT_MULTI_ORGANISATIONS.md](./MULTI_TENANT_MULTI_ORGANISATIONS.md)** - Gestion multi-organisations par projet
  - Table pivot `project_organizations`
  - Rôles organisationnels (Sponsor, MOA, MOE, Subcontractor)
  - Règles métier et validation

### Architecture des Permissions

- **[ROLES_AND_PERMISSIONS.md](./ROLES_AND_PERMISSIONS.md)** - Système RBAC complet
  - 174 permissions définies
  - 29 rôles préconfigurés
  - Scopes hiérarchiques (global, organization, project)

- **[ARCHITECTURE_EVOLUTION_PERMISSIONS_FLEXIBLES.md](./ARCHITECTURE_EVOLUTION_PERMISSIONS_FLEXIBLES.md)** - Évolution vers permissions dynamiques
  - Tables `resources` et `actions`
  - Matrice d'applicabilité ressources ↔ actions
  - Réduction ~50% des permissions absurdes

### Architecture des Templates PMBOK

- **[PMBOK_PHASE_TEMPLATES_IMPLEMENTATION.md](./PMBOK_PHASE_TEMPLATES_IMPLEMENTATION.md)** ⭐ **NOUVEAU**
  - Système de templates de méthodologies (PMBOK, Scrum, Hybrid)
  - Templates de phases avec hiérarchie
  - Instanciation automatique dans les projets
  - Support cas complexes (interventions GSM multi-passages)
  - 3 méthodologies + 12 templates de phases

---

## 👨‍💻 Développement

### Plans et Sprints

- **[PLAN_FINALISATION_MULTI_TENANT.md](./PLAN_FINALISATION_MULTI_TENANT.md)** - Plan de finalisation détaillé
  - Sprint 1 : Models et Relations ✅
  - Sprint 2 : RLS Application Layer (en cours)
  - Sprint 3-7 : Services, API, Tests

- **[SPRINT1_SUMMARY.md](./SPRINT1_SUMMARY.md)** - Résumé Sprint 1
  - 4 nouveaux models créés (1,617 lignes)
  - 3 models enrichis (1,247 lignes)
  - Toutes les relations testées et validées

- **[SPRINT2_PLAN_DETAILLE.md](./SPRINT2_PLAN_DETAILLE.md)** - Plan détaillé Sprint 2
  - RLS Application Layer (Row-Level Security)
  - Trait `TenantScoped` et Global Scope `TenantScope`
  - Middleware `CheckTenantAccess`
  - Filtrage automatique multi-tenant

### Évolutions Architecturales

- **[ARCHITECTURE_CHANGE_MULTI_TENANT_PURE.md](./ARCHITECTURE_CHANGE_MULTI_TENANT_PURE.md)** - Passage au multi-tenant pur
- **[ARCHITECTURE_CHANGE_IMPACT_ANALYSIS.md](./ARCHITECTURE_CHANGE_IMPACT_ANALYSIS.md)** - Analyse d'impact des changements

---

## 🛠️ Guides Techniques

### Import de Données

- **[EXCEL_IMPORT_SETUP.md](./EXCEL_IMPORT_SETUP.md)** - Configuration import Excel
  - PhpSpreadsheet
  - Validation et mapping des colonnes
  - Import de projets, tâches, phases

- **[EXCEL_TEMPLATES_GUIDE.md](./EXCEL_TEMPLATES_GUIDE.md)** - Guide templates Excel
  - Structure des templates
  - Format des colonnes
  - Exemples et validation

### Migration Odoo

- **[ODOO_IMPORT_GUIDE.md](./ODOO_IMPORT_GUIDE.md)** - Guide d'import depuis Odoo
  - Extraction des données Odoo
  - Mapping vers structure PMBOK
  - Scripts de migration

- **[ODOO_IMPORT_SUMMARY.md](./ODOO_IMPORT_SUMMARY.md)** - Résumé import Odoo
  - 58 utilisateurs importés
  - 66 projets importés
  - 9,626 tâches importées

- **[ODOO_EXTRACTION_REQUIREMENTS.md](./ODOO_EXTRACTION_REQUIREMENTS.md)** - Spécifications extraction Odoo

- **[ODOO_SQL_EXPORT_SCRIPTS.md](./ODOO_SQL_EXPORT_SCRIPTS.md)** - Scripts SQL d'export Odoo

### Migration SAMSIC

- **[MIGRATION_PLAN_SAMSIC.md](./MIGRATION_PLAN_SAMSIC.md)** - Plan de migration SAMSIC
  - Étapes détaillées
  - Checklist de validation
  - Rollback procedures

---

## 🚀 Fonctionnalités

### Templates de Phases PMBOK ⭐ **NOUVEAU**

- **[PMBOK_PHASE_TEMPLATES_IMPLEMENTATION.md](./PMBOK_PHASE_TEMPLATES_IMPLEMENTATION.md)**
  - **3 méthodologies pré-configurées :**
    - PMBOK Waterfall (5 phases : Initiation, Planning, Execution, Monitoring, Closure)
    - Agile Scrum (3 phases : Sprint 0, Development Sprints, Release)
    - Hybrid PMBOK + Agile (4 phases)
  - **12 templates de phases** avec activités et livrables PMBOK
  - **Hiérarchie de phases/sous-phases** (support 3+ niveaux)
  - **Multi-tenant** : templates système + templates par organisation
  - **Héritage de méthodologies** : organisation peut étendre PMBOK
  - **Instanciation automatique** : phases créées automatiquement dans projets
  - **Cas d'usage :** Projets GSM avec interventions multi-passages

### Multi-Tenant & Multi-Organisations

- Isolation complète des données par organisation
- Support 3 types d'organisations : Internal, Client, Partner
- Gestion multi-organisations par projet (Sponsor, MOA, MOE, Subcontractors)
- Système RBAC avec 174 permissions et 29 rôles

### Processus d'Exploitation

- **[PROCESSUS_EXPLOITATION_PLATEFORME.md](./PROCESSUS_EXPLOITATION_PLATEFORME.md)** - Processus d'exploitation quotidienne
  - Gestion des utilisateurs
  - Création et suivi des projets
  - Workflows d'approbation

---

## 📈 Implémentations Récentes

### 12 Novembre 2025 - Templates de Phases PMBOK ✅

**Commit :** `8005077` - Implémentation système de templates de phases PMBOK avec hiérarchie

**Nouveaux Composants :**
- 3 nouvelles tables : `methodology_templates`, `phase_templates`, et hiérarchie dans `project_phases`
- 3 nouveaux models : `MethodologyTemplate` (399 lignes), `PhaseTemplate` (527 lignes)
- 1 nouveau service : `PhaseTemplateService` (368 lignes)
- 1 nouveau seeder : `MethodologyTemplatesSeeder` (3 méthodologies + 12 phases)
- Model `Phase` enrichi avec support templates et hiérarchie (+244 lignes)

**Fonctionnalités :**
- ✅ Phases PMBOK standard (Initiation, Planning, Execution, Monitoring, Closure)
- ✅ Multi-tenant (templates système + templates par organisation)
- ✅ Héritage de méthodologies
- ✅ Hiérarchie de phases/sous-phases illimitée
- ✅ Métadonnées complètes (activités, livrables, critères entrée/sortie)
- ✅ Instanciation automatique dans les projets

**Documentation :** [PMBOK_PHASE_TEMPLATES_IMPLEMENTATION.md](./PMBOK_PHASE_TEMPLATES_IMPLEMENTATION.md)

### 9 Novembre 2025 - Sprint 1 Complété ✅

**Sprint 1 : Models et Relations**
- 4 nouveaux models créés (1,617 lignes)
- 3 models enrichis (1,247 lignes)
- Toutes les relations multi-tenant testées

**Documentation :** [SPRINT1_SUMMARY.md](./SPRINT1_SUMMARY.md)

---

## 🔍 Statistiques du Projet

### Base de Données
- **Tables PMBOK :** 39 tables
- **Tables Multi-Tenant :** 11 tables
- **Tables Multi-Organisations :** 4 tables
- **Tables Templates PMBOK :** 3 tables
- **Total :** 57 tables

### Code
- **Models Eloquent :** 40+ models
- **Migrations :** 50+ migrations
- **Seeders :** 5 seeders principaux
- **Services :** PhaseTemplateService + à venir

### Données
- **Permissions :** 174 permissions
- **Rôles :** 29 rôles
- **Utilisateurs :** 58 utilisateurs (test)
- **Projets :** 66 projets (test)
- **Tâches :** 9,626 tâches (test)
- **Organisations :** 27 organisations
- **Méthodologies :** 3 méthodologies (PMBOK, Scrum, Hybrid)
- **Templates de phases :** 12 templates

---

## 🎯 Prochaines Étapes

### Sprint 2 : RLS Application Layer (En cours)
- [ ] Créer Trait `TenantScoped`
- [ ] Créer Global Scope `TenantScope`
- [ ] Créer Middleware `CheckTenantAccess`
- [ ] Appliquer aux models PMBOK
- [ ] Tests RLS complets

**Documentation :** [SPRINT2_PLAN_DETAILLE.md](./SPRINT2_PLAN_DETAILLE.md)

### Fonctionnalités Prioritaires

1. **API Templates PMBOK** (Sprint 3+)
   - Endpoints pour lister méthodologies disponibles
   - Endpoints pour instancier phases depuis template
   - Endpoints pour gérer sous-phases

2. **UI Gestion Templates** (Sprint 5+)
   - Sélection méthodologie lors création projet
   - Visualisation hiérarchie phases (tree view)
   - Gestion templates custom par organisation

3. **Exports Excel** (Sprint 4+)
   - Export hiérarchie complète phases
   - Import sous-phases depuis Excel

---

## 📞 Support et Contribution

### Structure du Projet

```
mdf-access/
├── app/
│   ├── Models/              # Models Eloquent
│   ├── Services/            # Services métier
│   └── Http/
│       ├── Controllers/     # Controllers API
│       └── Middleware/      # Middlewares
├── database/
│   ├── migrations/          # Migrations DB
│   └── seeders/             # Seeders
├── docs/                    # Documentation (vous êtes ici)
└── tests/                   # Tests
```

### Conventions

- **Models :** PascalCase (ex: `MethodologyTemplate`)
- **Tables :** snake_case (ex: `methodology_templates`)
- **Relations :** camelCase (ex: `phaseTemplates()`)
- **Scopes :** camelCase (ex: `rootPhases()`)

---

## 📝 Notes

- Tous les documents sont en français
- La documentation est mise à jour après chaque sprint
- Les exemples de code incluent des commentaires explicatifs
- La roadmap est actualisée hebdomadairement

---

**Dernière mise à jour :** 12 novembre 2025
**Version :** 1.0
**Progression globale du projet :** 42%
