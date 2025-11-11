# 📊 GUIDE DES TEMPLATES EXCEL - DONNÉES DE TEST

## Vue d'ensemble

Ce document décrit tous les templates Excel à créer pour importer les données de test dans l'application.

**Emplacement des templates:** `storage/app/excel/templates/`
**Emplacement des données:** `storage/app/excel/data/`

---

## 📁 STRUCTURE DES FICHIERS

```
storage/app/excel/
├── templates/          # Templates vides (avec en-têtes + exemples)
│   ├── 01_users.xlsx
│   ├── 02_user_roles.xlsx
│   ├── 03_portfolios_programs.xlsx
│   ├── 04_projects.xlsx
│   ├── 05_project_organizations.xlsx
│   ├── 06_phases.xlsx
│   ├── 07_tasks.xlsx
│   ├── 08_wbs_deliverables.xlsx
│   ├── 09_risks_issues.xlsx
│   ├── 10_milestones_change_requests.xlsx
│   └── 11_resources.xlsx
│
└── data/              # Vos fichiers remplis (à créer)
    ├── 01_users.xlsx
    ├── 02_user_roles.xlsx
    └── ...
```

---

## 📋 TEMPLATES DÉTAILLÉS

### **Template 1: Users** (`01_users.xlsx`)

**Objectif:** Créer les utilisateurs de test

**Colonnes:**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| name | Texte | ✅ | Nom complet | Admin Système |
| email | Email | ✅ | Email unique | admin@samsic.fr |
| password | Texte | ✅ | Mot de passe (sera hashé) | Password123! |
| organization_id | Nombre | ✅ | ID organisation (voir liste) | 1 |
| is_system_admin | Oui/Non | ✅ | Admin système? | Oui |

**Règles de validation:**
- Email doit être unique
- organization_id doit exister dans la table organizations
- password minimum 8 caractères

**Exemple de données:**

| name | email | password | organization_id | is_system_admin |
|------|-------|----------|-----------------|-----------------|
| Admin Système | admin@samsic.fr | Password123! | 1 | Oui |
| PMO Manager | pmo@samsic.fr | Password123! | 1 | Non |
| Jean Dupont | jean.dupont@samsic.fr | Password123! | 1 | Non |
| Marie Martin | marie.martin@client.fr | Password123! | 2 | Non |
| Pierre Durand | pierre.durand@samsic.fr | Password123! | 1 | Non |

**Nombre recommandé:** 5-10 lignes

---

### **Template 2: User Roles** (`02_user_roles.xlsx`)

**Objectif:** Assigner des rôles aux utilisateurs

**Colonnes:**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| user_email | Email | ✅ | Email utilisateur | admin@samsic.fr |
| role_slug | Texte | ✅ | Slug du rôle | super_admin |
| scope_type | Texte | ❌ | Type scope: global/project/program/portfolio | global |
| scope_id | Nombre | ❌ | ID du scope (si non global) | 1 |

**Liste des rôles disponibles (slug):**
```
super_admin, pmo, project_manager, project_coordinator,
business_analyst, technical_lead, developer, tester,
client_admin, client_user, client_sponsor,
responsable_moa, controleur_qualite_moa, assistant_moa
```

**Règles:**
- Si scope_type est vide ou "global", alors scope_id doit être vide
- Si scope_type = "project", scope_id = ID du projet
- role_slug doit exister dans la table roles

**Exemple de données:**

| user_email | role_slug | scope_type | scope_id |
|------------|-----------|------------|----------|
| admin@samsic.fr | super_admin | global | |
| pmo@samsic.fr | pmo | global | |
| jean.dupont@samsic.fr | project_manager | global | |
| jean.dupont@samsic.fr | project_manager | project | 1 |
| marie.martin@client.fr | client_sponsor | global | |
| pierre.durand@samsic.fr | responsable_moa | global | |

**Nombre recommandé:** 5-15 lignes

---

### **Template 3: Portfolios & Programs** (`03_portfolios_programs.xlsx`)

**Feuille 1: Portfolios**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| name | Texte | ✅ | Nom du portfolio | Portfolio Transformation Digitale |
| organization_id | Nombre | ✅ | ID organisation propriétaire | 1 |
| manager_email | Email | ❌ | Email du manager | pmo@samsic.fr |
| description | Texte | ❌ | Description | Ensemble des projets IT 2025 |
| budget | Nombre | ❌ | Budget total | 5000000 |
| start_date | Date | ❌ | Date début (YYYY-MM-DD) | 2025-01-01 |
| end_date | Date | ❌ | Date fin | 2025-12-31 |
| status | Liste | ✅ | active/inactive/completed/on_hold | active |

**Feuille 2: Programs**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| portfolio_name | Texte | ❌ | Nom du portfolio parent | Portfolio Transformation Digitale |
| name | Texte | ✅ | Nom du programme | Programme Infrastructure IT |
| manager_email | Email | ❌ | Email du manager | pmo@samsic.fr |
| description | Texte | ❌ | Description | Modernisation infrastructure |
| budget | Nombre | ❌ | Budget | 2000000 |
| objectives | Texte | ❌ | Objectifs | Migrer vers le cloud |
| status | Liste | ✅ | active/inactive/completed/on_hold | active |

**Nombre recommandé:** 1-2 portfolios, 1-3 programs

---

### **Template 4: Projects** (`04_projects.xlsx`)

**Objectif:** Créer les projets

**Colonnes:**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| program_name | Texte | ❌ | Nom du programme parent | Programme Infrastructure IT |
| client_organization_id | Nombre | ✅ | ID organisation cliente | 2 |
| client_reference | Texte | ❌ | Référence client | CLI-2025-001 |
| code | Texte | ✅ | Code projet unique | SAMSIC-PAIE-2025 |
| name | Texte | ✅ | Nom du projet | Refonte Système Paie |
| description | Texte | ❌ | Description détaillée | Migration système paie legacy |
| project_manager_email | Email | ❌ | Email chef de projet | jean.dupont@samsic.fr |
| project_type | Texte | ❌ | Type de projet | IT, Construction, etc. |
| methodology | Liste | ✅ | waterfall/agile/hybrid | waterfall |
| start_date | Date | ❌ | Date début | 2025-02-01 |
| end_date | Date | ❌ | Date fin | 2025-08-31 |
| baseline_start | Date | ❌ | Date baseline début | 2025-02-01 |
| baseline_end | Date | ❌ | Date baseline fin | 2025-08-31 |
| budget | Nombre | ❌ | Budget | 500000 |
| actual_cost | Nombre | ❌ | Coût actuel | 120000 |
| status | Liste | ✅ | initiation/planning/execution/monitoring/closure/on_hold/cancelled | execution |
| priority | Liste | ✅ | low/medium/high/critical | high |
| health_status | Liste | ✅ | green/yellow/red | green |
| completion_percentage | Nombre | ✅ | % completion (0-100) | 35 |

**Exemple:**

| code | name | client_organization_id | project_manager_email | methodology | status | priority | health_status | budget | completion_percentage |
|------|------|------------------------|----------------------|-------------|--------|----------|---------------|--------|---------------------|
| SAMSIC-PAIE-2025 | Refonte Système Paie | 2 | jean.dupont@samsic.fr | waterfall | execution | high | green | 500000 | 35 |
| SAMSIC-MOBILE-2025 | Application Mobile RH | 3 | jean.dupont@samsic.fr | agile | planning | medium | yellow | 300000 | 15 |

**Nombre recommandé:** 2-5 projets

---

### **Template 5: Project Organizations** (`05_project_organizations.xlsx`)

**Objectif:** Définir les organisations participantes par projet

**Colonnes:**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| organization_id | Nombre | ✅ | ID organisation | 1 |
| role | Liste | ✅ | sponsor/moa/moe/subcontractor | sponsor |
| reference | Texte | ❌ | Référence interne | SPONSOR-2025-001 |
| scope_description | Texte | ❌ | Description scope (sous-traitant seulement) | Développement mobile iOS/Android |
| is_primary | Oui/Non | ✅ | MOE primaire? (moe/subcontractor seulement) | Oui |
| start_date | Date | ❌ | Date début intervention | 2025-02-01 |
| end_date | Date | ❌ | Date fin intervention | 2025-08-31 |
| status | Liste | ✅ | active/inactive/completed | active |

**Règles critiques:**
- ⚠️ **UN SEUL sponsor actif par projet**
- ⚠️ **UN SEUL MOA actif par projet**
- ⚠️ **UN SEUL MOE primaire actif par projet**
- scope_description UNIQUEMENT pour subcontractor
- is_primary = "Oui" UNIQUEMENT pour moe ou subcontractor

**Exemple:**

| project_code | organization_id | role | reference | is_primary | status |
|--------------|-----------------|------|-----------|------------|--------|
| SAMSIC-PAIE-2025 | 2 | sponsor | SPONSOR-2025-001 | Non | active |
| SAMSIC-PAIE-2025 | 1 | moa | MOA-2025-001 | Non | active |
| SAMSIC-PAIE-2025 | 1 | moe | MOE-2025-001 | Oui | active |
| SAMSIC-MOBILE-2025 | 3 | sponsor | | Non | active |
| SAMSIC-MOBILE-2025 | 1 | moa | | Non | active |
| SAMSIC-MOBILE-2025 | 1 | moe | | Oui | active |
| SAMSIC-MOBILE-2025 | 5 | subcontractor | ST-MOBILE-001 | Non | active |

**Nombre recommandé:** 3-7 par projet (minimum 3: sponsor, moa, moe)

---

### **Template 6: Phases** (`06_phases.xlsx`)

**Objectif:** Définir les phases des projets

**Colonnes:**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| name | Texte | ✅ | Nom de la phase | Initiation |
| description | Texte | ❌ | Description | Phase de démarrage |
| sequence | Nombre | ✅ | Ordre (1, 2, 3...) | 1 |
| start_date | Date | ❌ | Date début | 2025-02-01 |
| end_date | Date | ❌ | Date fin | 2025-02-28 |
| status | Liste | ✅ | not_started/in_progress/completed/on_hold | completed |
| completion_percentage | Nombre | ✅ | % completion (0-100) | 100 |

**Exemple:**

| project_code | name | sequence | start_date | end_date | status | completion_percentage |
|--------------|------|----------|------------|----------|--------|--------------------|
| SAMSIC-PAIE-2025 | Initiation | 1 | 2025-02-01 | 2025-02-28 | completed | 100 |
| SAMSIC-PAIE-2025 | Planification | 2 | 2025-03-01 | 2025-04-30 | in_progress | 60 |
| SAMSIC-PAIE-2025 | Exécution | 3 | 2025-05-01 | 2025-07-31 | not_started | 0 |

**Nombre recommandé:** 3-5 phases par projet

---

### **Template 7: Tasks** (`07_tasks.xlsx`)

**Objectif:** Créer les tâches

**Colonnes:**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| wbs_code | Texte | ❌ | Code WBS parent | 1.1 |
| parent_task_name | Texte | ❌ | Nom tâche parente | Analyse besoins |
| name | Texte | ✅ | Nom de la tâche | Recueil besoins utilisateurs |
| description | Texte | ❌ | Description | Interviews utilisateurs |
| assigned_to_email | Email | ❌ | Email assigné | jean.dupont@samsic.fr |
| assigned_organization_id | Nombre | ❌ | ID org assignée | 1 |
| priority | Liste | ✅ | low/medium/high/critical | high |
| status | Liste | ✅ | not_started/in_progress/completed/blocked/cancelled | completed |
| estimated_hours | Nombre | ❌ | Heures estimées | 40 |
| actual_hours | Nombre | ❌ | Heures réelles | 42 |
| start_date | Date | ❌ | Date début | 2025-02-01 |
| end_date | Date | ❌ | Date fin | 2025-02-15 |
| completion_percentage | Nombre | ✅ | % completion | 100 |

**Exemple:**

| project_code | name | assigned_to_email | priority | status | estimated_hours | actual_hours | completion_percentage |
|--------------|------|-------------------|----------|--------|-----------------|--------------|---------------------|
| SAMSIC-PAIE-2025 | Analyse besoins | jean.dupont@samsic.fr | high | completed | 40 | 42 | 100 |
| SAMSIC-PAIE-2025 | Conception architecture | jean.dupont@samsic.fr | high | in_progress | 60 | 30 | 50 |
| SAMSIC-PAIE-2025 | Développement module paie | | medium | not_started | 120 | 0 | 0 |

**Nombre recommandé:** 5-10 tâches par projet

---

### **Template 8: WBS & Deliverables** (`08_wbs_deliverables.xlsx`)

**Feuille 1: WBS Elements**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| parent_code | Texte | ❌ | Code WBS parent | 1.0 |
| code | Texte | ✅ | Code WBS | 1.1 |
| name | Texte | ✅ | Nom | Analyse et Conception |
| description | Texte | ❌ | Description | Phase d'analyse |
| level | Nombre | ✅ | Niveau hiérarchique | 2 |
| assigned_organization_id | Nombre | ❌ | ID org assignée | 1 |
| start_date | Date | ❌ | Date début | 2025-02-01 |
| end_date | Date | ❌ | Date fin | 2025-04-30 |
| completion_percentage | Nombre | ✅ | % completion | 60 |

**Feuille 2: Deliverables**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| wbs_code | Texte | ❌ | Code WBS | 1.1 |
| name | Texte | ✅ | Nom du livrable | Document Spécifications |
| description | Texte | ❌ | Description | Spéc fonctionnelles |
| type | Texte | ❌ | Type | Document, Software, etc. |
| assigned_organization_id | Nombre | ❌ | ID org assignée | 1 |
| due_date | Date | ❌ | Date échéance | 2025-04-15 |
| delivery_date | Date | ❌ | Date livraison | 2025-04-14 |
| status | Liste | ✅ | not_started/in_progress/completed/rejected | completed |
| approved_by_email | Email | ❌ | Email approbateur | marie.martin@client.fr |
| approved_at | DateTime | ❌ | Date approbation | 2025-04-14 10:30:00 |

**Nombre recommandé:** 2-5 WBS, 2-5 deliverables

---

### **Template 9: Risks & Issues** (`09_risks_issues.xlsx`)

**Feuille 1: Risks**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| category | Texte | ❌ | Catégorie | Technique, Planning, Budget |
| description | Texte | ✅ | Description du risque | Incompatibilité système legacy |
| probability | Nombre | ✅ | Probabilité 0-100 | 60 |
| impact | Nombre | ✅ | Impact 0-100 | 80 |
| mitigation_strategy | Texte | ❌ | Stratégie d'atténuation | POC technique préalable |
| owner_email | Email | ❌ | Email propriétaire | jean.dupont@samsic.fr |
| status | Liste | ✅ | identified/assessed/mitigated/closed/occurred | assessed |
| identified_date | Date | ✅ | Date identification | 2025-02-15 |
| review_date | Date | ❌ | Date révision | 2025-03-15 |

**Feuille 2: Issues**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| title | Texte | ✅ | Titre court | Bug calcul cotisations |
| description | Texte | ❌ | Description détaillée | Erreur dans le calcul |
| severity | Liste | ✅ | low/medium/high/critical | high |
| priority | Liste | ✅ | low/medium/high/critical | high |
| status | Liste | ✅ | open/in_progress/resolved/closed | in_progress |
| reported_by_email | Email | ❌ | Email rapporteur | marie.martin@client.fr |
| assigned_to_email | Email | ❌ | Email assigné | jean.dupont@samsic.fr |
| reported_date | Date | ✅ | Date signalement | 2025-04-20 |
| resolved_date | Date | ❌ | Date résolution | |
| resolution | Texte | ❌ | Texte résolution | |

**Nombre recommandé:** 2-5 risks, 1-3 issues

---

### **Template 10: Milestones & Change Requests** (`10_milestones_change_requests.xlsx`)

**Feuille 1: Milestones**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| name | Texte | ✅ | Nom du jalon | Go-Live Phase 1 |
| description | Texte | ❌ | Description | Mise en prod module paie |
| due_date | Date | ✅ | Date échéance | 2025-06-30 |
| status | Liste | ✅ | pending/achieved/missed | pending |
| critical | Oui/Non | ✅ | Jalon critique? | Oui |
| achieved_date | Date | ❌ | Date atteinte | |

**Feuille 2: Change Requests**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| project_code | Texte | ✅ | Code du projet | SAMSIC-PAIE-2025 |
| title | Texte | ✅ | Titre | Ajout module congés payés |
| description | Texte | ✅ | Description | Module de gestion CP |
| justification | Texte | ❌ | Justification | Demande client |
| impact_analysis | Texte | ❌ | Analyse impact | Impact développement |
| cost_impact | Nombre | ❌ | Impact coût | 50000 |
| schedule_impact | Nombre | ❌ | Impact délai (jours) | 30 |
| status | Liste | ✅ | submitted/under_review/approved/rejected/implemented | approved |
| requested_by_email | Email | ❌ | Email demandeur | marie.martin@client.fr |
| approved_by_email | Email | ❌ | Email approbateur | pmo@samsic.fr |
| approval_date | DateTime | ❌ | Date approbation | 2025-04-25 14:00:00 |

**Nombre recommandé:** 2-3 milestones, 1-2 change requests

---

### **Template 11: Resources** (`11_resources.xlsx`)

**Feuille 1: Resources**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| user_email | Email | ✅ | Email utilisateur | jean.dupont@samsic.fr |
| role | Texte | ❌ | Rôle ressource | Chef de Projet |
| department | Texte | ❌ | Département | PMO |
| cost_per_hour | Nombre | ❌ | Coût horaire | 85.00 |
| availability_percentage | Nombre | ✅ | Disponibilité % | 80 |
| skills | Texte | ❌ | Compétences (séparées par ;) | Project Management;Agile;Risk Management |
| status | Liste | ✅ | available/assigned/unavailable | assigned |

**Feuille 2: Resource Allocations**

| Colonne | Type | Obligatoire | Description | Exemple |
|---------|------|-------------|-------------|---------|
| resource_user_email | Email | ✅ | Email ressource | jean.dupont@samsic.fr |
| project_code | Texte | ✅ | Code projet | SAMSIC-PAIE-2025 |
| task_name | Texte | ❌ | Nom tâche | Analyse besoins |
| allocation_percentage | Nombre | ✅ | % allocation | 50 |
| start_date | Date | ✅ | Date début | 2025-02-01 |
| end_date | Date | ✅ | Date fin | 2025-08-31 |
| hours_allocated | Nombre | ❌ | Heures allouées | 600 |
| hours_worked | Nombre | ❌ | Heures travaillées | 210 |

**Nombre recommandé:** 3-5 resources, 4-8 allocations

---

## 🎯 ORDRE DE REMPLISSAGE

1. **01_users.xlsx** ⭐
2. **02_user_roles.xlsx** ⭐
3. **03_portfolios_programs.xlsx**
4. **04_projects.xlsx** ⭐
5. **05_project_organizations.xlsx** ⭐
6. **06_phases.xlsx**
7. **07_tasks.xlsx**
8. **08_wbs_deliverables.xlsx**
9. **09_risks_issues.xlsx**
10. **10_milestones_change_requests.xlsx**
11. **11_resources.xlsx**

---

## ⚠️ RÈGLES IMPORTANTES

### IDs des Organisations Existantes
Exécuter pour voir la liste:
```sql
SELECT id, name, type FROM organizations ORDER BY id;
```

### Emails Uniques
Tous les emails doivent être uniques dans la table users

### Dates
Format: `YYYY-MM-DD` (ex: 2025-02-01)
DateTime: `YYYY-MM-DD HH:MM:SS` (ex: 2025-04-14 10:30:00)

### Listes Déroulantes
Utiliser exactement les valeurs indiquées (sensible à la casse)

### Contraintes Métier ProjectOrganizations
- 1 sponsor actif max par projet
- 1 MOA actif max par projet
- 1 MOE primaire actif max par projet

---

## 📝 PROCHAINES ÉTAPES

1. Je vais créer les templates Excel vides avec ces colonnes
2. Je vais créer les Import classes correspondantes
3. Je vais créer les Seeders qui utilisent ces imports
4. Vous remplirez les templates avec vos données réelles
5. Vous exécuterez les seeders pour importer les données

Voulez-vous que je commence à créer les templates Excel et les Import classes?
