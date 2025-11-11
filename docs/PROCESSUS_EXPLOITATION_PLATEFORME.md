# 📋 Processus d'Exploitation de la Plateforme MDF Access

**Date :** 9 novembre 2025
**Version :** 1.0
**Type :** Documentation Processus Métier
**Architecture :** Multi-Tenant Pur (sans propriétaire)

---

## 🎯 Vue d'Ensemble

Dans l'architecture multi-tenant pure, **il n'y a plus de notion de "propriétaire de plateforme"**. Toutes les organisations sont égales et leurs rôles sont définis contextuellement par projet.

---

## 👥 Rôles Plateforme (Administration Système)

### 1. Super Administrateur Système

**Qui :** Administrateur technique de la plateforme (DevOps, IT)

**Caractéristiques :**
- `is_system_admin = true`
- `organization_id = NULL` (pas lié à une organisation)
- Accès total à toutes les données
- Ne participe PAS aux projets métier
- Rôle purement technique

**Responsabilités :**
- ✅ Configuration système
- ✅ Gestion des utilisateurs système
- ✅ Maintenance base de données
- ✅ Monitoring et logs
- ✅ Backup et restauration
- ❌ Ne crée PAS de projets métier
- ❌ Ne gère PAS les participations projets

**Exemple :**
```php
User {
    email: 'admin@mdf-platform.com',
    name: 'Admin Système',
    organization_id: NULL,
    is_system_admin: true
}
```

---

### 2. Administrateur Organisation

**Qui :** Chef de projet, PMO, Administrateur métier d'une organisation

**Caractéristiques :**
- `is_system_admin = false`
- `organization_id = X` (lié à une organisation)
- Rôle métier : `organization-admin` (scope: global ou organization)
- Accès selon les projets où son organisation participe

**Responsabilités :**
- ✅ Créer/modifier des projets pour son organisation
- ✅ Inviter des organisations externes sur ses projets
- ✅ Gérer les utilisateurs de son organisation
- ✅ Attribuer des rôles à ses collaborateurs
- ✅ Gérer les participations multi-organisations
- ❌ Ne voit PAS les projets d'autres organisations (sauf participation)

**Exemple :**
```php
User {
    email: 'chef.projet@samsic.ma',
    name: 'Mohamed Alami',
    organization_id: 27,  // SAMSIC MAINTENANCE MAROC
    is_system_admin: false
}

UserRole {
    user_id: X,
    role_id: Y,  // organization-admin
    portfolio_id: NULL,
    program_id: NULL,
    project_id: NULL  // Scope global dans l'organisation
}
```

---

### 3. Chef de Projet

**Qui :** Responsable d'un ou plusieurs projets spécifiques

**Caractéristiques :**
- Rôle métier : `project-manager` (scope: project)
- Accès limité aux projets assignés
- Peut gérer les participations sur ses projets

**Responsabilités :**
- ✅ Gérer son/ses projet(s) assigné(s)
- ✅ Inviter des organisations externes
- ✅ Gérer l'équipe projet
- ✅ Suivre l'avancement
- ❌ Ne crée PAS de nouveaux projets (selon config)
- ❌ Accès limité à ses projets uniquement

---

## 📊 Processus Complets

---

## 🏢 PROCESSUS 1 : Gestion des Organisations

### P1.1 : Création d'une Organisation

**Déclencheur :** Nouvelle organisation doit utiliser la plateforme

**Acteur :** Super Admin Système

**Étapes :**

#### Étape 1 : Collecter les Informations
```yaml
Informations requises:
  - Nom de l'organisation
  - Adresse complète
  - Ville
  - Informations de contact (email, téléphone, fax)
  - Logo (optionnel)
  - Statut initial: 'active'
```

#### Étape 2 : Créer l'Organisation
```php
// Via Interface Admin ou Script
$organization = Organization::create([
    'name' => 'ABC Industries',
    'address' => '123 Boulevard Mohammed V',
    'ville' => 'Casablanca',
    'contact_info' => [
        'email' => 'contact@abc-industries.ma',
        'phone' => '+212 522 123456',
        'fax' => '+212 522 123457',
    ],
    'logo' => 'path/to/logo.png',
    'status' => 'active',
]);

// ID généré : 50
```

**Résultat :** Organisation créée, prête à recevoir des utilisateurs

**⚠️ Note Importante :** Aucun "type" n'est défini. Le rôle de cette organisation sera défini projet par projet.

---

### P1.2 : Activation/Désactivation d'une Organisation

**Déclencheur :** Fin de contrat, mise en pause, réactivation

**Acteur :** Super Admin ou Admin Organisation

**Étapes :**

```php
// Désactivation
$organization = Organization::find(50);
$organization->status = 'inactive';
$organization->save();

// Impact :
// - Utilisateurs de cette org ne peuvent plus se connecter
// - Projets existants restent visibles (historique)
// - Impossible de créer de nouveaux projets avec cette org

// Réactivation
$organization->status = 'active';
$organization->save();
```

---

### P1.3 : Modification d'une Organisation

**Déclencheur :** Changement d'adresse, contact, etc.

**Acteur :** Admin Organisation ou Super Admin

**Étapes :**

```php
$organization = Organization::find(50);
$organization->update([
    'name' => 'ABC Industries Morocco',
    'address' => 'Nouvelle adresse',
    'contact_info' => [
        'email' => 'nouveau@abc.ma',
        'phone' => '+212 522 999999',
    ],
]);
```

---

## 👤 PROCESSUS 2 : Gestion des Utilisateurs

### P2.1 : Création d'un Utilisateur

**Déclencheur :** Nouvelle personne doit accéder à la plateforme

**Acteur :** Admin Organisation (pour son org) ou Super Admin

**Étapes :**

#### Étape 1 : Créer le Compte Utilisateur
```php
$user = User::create([
    'name' => 'Fatima Zahra Benali',
    'email' => 'f.benali@abc-industries.ma',
    'password' => Hash::make('MotDePasseTemporaire123'),
    'organization_id' => 50,  // ABC Industries
    'is_system_admin' => false,
]);

// ID généré : 100
```

#### Étape 2 : Envoyer Email de Bienvenue
```php
Mail::to($user)->send(new WelcomeEmail($user, $temporaryPassword));
```

#### Étape 3 : Attribuer un ou plusieurs Rôles (voir P3)

**Résultat :** Utilisateur créé, lié à son organisation, prêt à recevoir des rôles

---

### P2.2 : Désactivation d'un Utilisateur

**Déclencheur :** Départ de l'entreprise, congé longue durée

**Acteur :** Admin Organisation

**Étapes :**

```php
// Option 1 : Soft Delete (recommandé)
$user = User::find(100);
$user->delete();  // Soft delete, peut être restauré

// Impact :
// - Ne peut plus se connecter
// - Données historiques préservées
// - Peut être restauré

// Option 2 : Désactivation manuelle (si colonne exists)
$user->is_active = false;
$user->save();
```

---

## 🎭 PROCESSUS 3 : Attribution des Rôles aux Utilisateurs

### P3.1 : Attribuer un Rôle Global (Organisation-wide)

**Déclencheur :** Utilisateur doit avoir des permissions sur toute l'organisation

**Acteur :** Admin Organisation

**Exemples de Rôles Globaux :**
- `organization-admin` : Administrateur de l'organisation
- `pmo-manager` : Gestionnaire PMO
- `portfolio-manager` : Gestionnaire de portefeuilles

**Étapes :**

```php
$user = User::find(100);
$role = Role::where('slug', 'organization-admin')->first();

// Attribution rôle GLOBAL (aucun scope)
UserRole::create([
    'user_id' => $user->id,
    'role_id' => $role->id,
    'portfolio_id' => NULL,  // Pas de scope
    'program_id' => NULL,
    'project_id' => NULL,
]);

// Résultat :
// Fatima peut maintenant gérer tous les projets de ABC Industries
```

---

### P3.2 : Attribuer un Rôle sur un Projet Spécifique

**Déclencheur :** Utilisateur rejoint l'équipe d'un projet

**Acteur :** Chef de Projet ou Admin Organisation

**Exemples de Rôles Projet :**
- `project-manager` : Chef de projet
- `team-member` : Membre de l'équipe
- `task-assignee` : Exécutant de tâches

**Étapes :**

```php
$user = User::find(100);
$role = Role::where('slug', 'project-manager')->first();
$project = Project::find(10);

// Attribution rôle SCOPÉ au projet
UserRole::create([
    'user_id' => $user->id,
    'role_id' => $role->id,
    'portfolio_id' => NULL,
    'program_id' => NULL,
    'project_id' => $project->id,  // Scope = ce projet uniquement
]);

// Résultat :
// Fatima est maintenant Chef de Projet pour le Projet #10 uniquement
```

---

### P3.3 : Retirer un Rôle

**Déclencheur :** Fin de mission, changement d'affectation

**Acteur :** Admin Organisation ou Chef de Projet

**Étapes :**

```php
$userRole = UserRole::where('user_id', 100)
                    ->where('role_id', $roleId)
                    ->where('project_id', 10)
                    ->first();

$userRole->delete();

// Résultat :
// Fatima n'est plus Chef de Projet pour le Projet #10
```

---

## 📁 PROCESSUS 4 : Création d'un Projet

### P4.1 : Créer un Nouveau Projet

**Déclencheur :** Nouveau contrat signé, nouvelle initiative

**Acteur :** Admin Organisation (de l'organisation sponsor/MOA)

**Étapes :**

#### Étape 1 : Collecter les Informations Projet
```yaml
Informations requises:
  - Nom du projet
  - Code projet (unique)
  - Description
  - Dates début/fin
  - Budget
  - Organisation cliente (sponsor) : L'organisation qui "commande" le projet
  - Chef de projet
  - Méthodologie (agile, waterfall, hybride)
```

#### Étape 2 : Créer le Projet de Base
```php
$project = Project::create([
    'code' => 'PRJ-2025-ABC-001',
    'name' => 'Projet Digitalisation ABC',
    'description' => 'Mise en place d\'un système de gestion digitale',
    'client_organization_id' => 50,  // ABC Industries (sponsor/client)
    'project_manager_id' => 100,  // Fatima Zahra Benali
    'start_date' => '2025-01-15',
    'end_date' => '2025-12-31',
    'budget' => 500000.00,
    'status' => 'initiation',
    'methodology' => 'agile',
    'priority' => 'high',
    'health_status' => 'green',
]);

// ID généré : 10
```

**⚠️ Important :** À ce stade, le projet est créé mais **sans organisations participantes** encore. Il faut maintenant définir QUI fait QUOI via `project_organizations`.

---

### P4.2 : Définir les Organisations Participantes

**Déclencheur :** Après création du projet de base

**Acteur :** Admin Organisation ou Chef de Projet

**Processus Multi-Organisations :**

#### Étape 1 : Définir le Sponsor (Obligatoire)
```php
// Le sponsor est l'organisation qui "commande" et finance le projet
ProjectOrganization::create([
    'project_id' => 10,
    'organization_id' => 50,  // ABC Industries
    'role' => 'sponsor',
    'reference' => 'BC-2025-001',  // Référence interne du sponsor
    'status' => 'active',
    'start_date' => '2025-01-15',
]);

// Règle métier : UN SEUL sponsor actif par projet
```

#### Étape 2 : Définir la MOA - Maître d'Ouvrage (Obligatoire)
```php
// La MOA définit les besoins et valide les livrables
ProjectOrganization::create([
    'project_id' => 10,
    'organization_id' => 50,  // ABC Industries (peut être la même que sponsor)
    'role' => 'moa',
    'reference' => 'MOA-ABC-2025-001',
    'status' => 'active',
    'start_date' => '2025-01-15',
]);

// Règle métier : UNE SEULE MOA active par projet
// Note : Sponsor et MOA peuvent être la même organisation
```

#### Étape 3 : Définir la MOE - Maître d'Œuvre (Obligatoire)
```php
// La MOE réalise le projet
ProjectOrganization::create([
    'project_id' => 10,
    'organization_id' => 27,  // SAMSIC MAINTENANCE MAROC
    'role' => 'moe',
    'reference' => 'MOE-SAMSIC-2025-001',
    'is_primary' => true,  // MOE primaire
    'status' => 'active',
    'start_date' => '2025-01-15',
]);

// Règle métier : AU MOINS UNE MOE active
// Si plusieurs MOE : UNE SEULE est "primary"
```

#### Étape 4 : Ajouter des Sous-traitants (Optionnel)
```php
// Sous-traitant pour une partie spécifique
ProjectOrganization::create([
    'project_id' => 10,
    'organization_id' => 75,  // TechPartner Ltd
    'role' => 'subcontractor',
    'reference' => 'ST-TECH-2025-001',
    'scope_description' => 'Développement module CRM',  // OBLIGATOIRE pour subcontractor
    'status' => 'active',
    'start_date' => '2025-03-01',
    'end_date' => '2025-06-30',
]);

// Règle métier : Subcontractor DOIT avoir un scope_description
```

**Résultat Final :**
```
Projet #10 : Digitalisation ABC
├─ Sponsor : ABC Industries (finance)
├─ MOA : ABC Industries (définit besoins)
├─ MOE (primaire) : SAMSIC MAINTENANCE MAROC (réalise)
└─ Subcontractor : TechPartner Ltd (développe CRM)
```

---

### P4.3 : Validation des Règles Métier (Automatique)

**Déclencheur :** À chaque création/modification dans `project_organizations`

**Acteur :** Système (via Model ProjectOrganization)

**Règles Validées Automatiquement :**

```php
// Dans app/Models/ProjectOrganization.php

protected static function boot() {
    parent::boot();

    static::saving(function ($projectOrganization) {
        $projectOrganization->validateBusinessRules();
    });
}

// Règles vérifiées :
// ✅ Exactement UN sponsor actif
// ✅ Exactement UNE MOA active
// ✅ Au moins UNE MOE active
// ✅ Si plusieurs MOE : UNE SEULE primary
// ✅ Subcontractor DOIT avoir scope_description
```

**Exemple d'Erreur :**
```php
// Tentative d'ajouter un 2e sponsor
ProjectOrganization::create([
    'project_id' => 10,
    'organization_id' => 99,
    'role' => 'sponsor',  // ❌ ERREUR !
    'status' => 'active',
]);

// Exception levée :
// "Ce projet a déjà un sponsor actif.
//  Un projet ne peut avoir qu'un seul sponsor actif à la fois."
```

---

## 🔄 PROCESSUS 5 : Évolution des Participations Projet

### P5.1 : Ajouter une Organisation en Cours de Projet

**Déclencheur :** Besoin d'un nouveau sous-traitant, changement de MOE

**Acteur :** Chef de Projet ou Admin Organisation

**Exemple :** Ajouter un nouveau sous-traitant

```php
ProjectOrganization::create([
    'project_id' => 10,
    'organization_id' => 88,  // NewTech Solutions
    'role' => 'subcontractor',
    'scope_description' => 'Tests et recette applicative',
    'status' => 'active',
    'start_date' => now(),
]);

// Résultat :
// NewTech Solutions peut maintenant accéder au Projet #10
// Ses utilisateurs verront ce projet dans leur liste
```

---

### P5.2 : Remplacer une MOE

**Déclencheur :** Changement de prestataire en cours de projet

**Acteur :** Admin Organisation (MOA)

**Étapes :**

```php
// Étape 1 : Désactiver l'ancienne MOE
$oldMoe = ProjectOrganization::where('project_id', 10)
    ->where('organization_id', 27)  // SAMSIC
    ->where('role', 'moe')
    ->first();

$oldMoe->status = 'inactive';
$oldMoe->end_date = now();
$oldMoe->save();

// Étape 2 : Ajouter la nouvelle MOE
ProjectOrganization::create([
    'project_id' => 10,
    'organization_id' => 99,  // Nouveau prestataire
    'role' => 'moe',
    'is_primary' => true,
    'status' => 'active',
    'start_date' => now(),
]);

// Résultat :
// - SAMSIC ne voit plus le projet (sauf données historiques)
// - Nouveau prestataire a maintenant accès
// - Historique préservé
```

---

### P5.3 : Retirer une Organisation du Projet

**Déclencheur :** Fin de mission sous-traitant, résiliation

**Acteur :** Chef de Projet

**Étapes :**

```php
// Option 1 : Désactivation (recommandé - préserve historique)
$participation = ProjectOrganization::where('project_id', 10)
    ->where('organization_id', 88)
    ->where('role', 'subcontractor')
    ->first();

$participation->status = 'inactive';
$participation->end_date = now();
$participation->save();

// Option 2 : Suppression définitive (déconseillé)
$participation->delete();

// Résultat :
// NewTech Solutions ne voit plus le Projet #10
// Ses utilisateurs perdent l'accès
```

---

## 🔐 PROCESSUS 6 : Contrôle d'Accès (RLS)

### P6.1 : Accès aux Projets

**Principe :** Un utilisateur voit UNIQUEMENT les projets où son organisation participe

**Exemples :**

#### Utilisateur ABC Industries (Sponsor/MOA)
```php
Auth::login($userABC);  // Fatima de ABC Industries

Project::all();
// SQL automatique :
// SELECT * FROM projects
// WHERE EXISTS (
//   SELECT 1 FROM project_organizations
//   WHERE project_organizations.project_id = projects.id
//   AND project_organizations.organization_id = 50  // ABC
//   AND project_organizations.status = 'active'
// )

// Résultat : Voit le Projet #10 (et autres projets ABC)
```

#### Utilisateur SAMSIC (MOE)
```php
Auth::login($userSamsic);  // Mohamed de SAMSIC

Project::all();
// Résultat : Voit tous les projets où SAMSIC est MOE
```

#### Utilisateur TechPartner (Subcontractor)
```php
Auth::login($userTech);  // Ali de TechPartner

Project::all();
// Résultat : Voit UNIQUEMENT le Projet #10 (sa mission)
```

#### Super Admin
```php
Auth::login($superAdmin);

Project::all();
// Pas de filtre → Voit TOUS les projets de toutes les organisations
```

---

### P6.2 : Accès aux Données Projet (Tasks, Deliverables, etc.)

**Principe :** Héritage du contrôle d'accès du projet parent

```php
Auth::login($userTech);  // TechPartner (subcontractor Projet #10)

// Accès aux tâches du Projet #10
Task::where('project_id', 10)->get();  // ✅ Autorisé

// Tentative d'accès aux tâches d'un autre projet
Task::where('project_id', 999)->get();  // ❌ Retourne vide (filtre RLS)
```

---

## 📊 PROCESSUS 7 : Gestion du Cycle de Vie Projet

### P7.1 : Phases du Projet

**Statuts possibles :**
1. `initiation` - Démarrage du projet
2. `planning` - Planification
3. `execution` - Réalisation
4. `monitoring` - Suivi et contrôle
5. `closure` - Clôture
6. `on_hold` - En pause
7. `cancelled` - Annulé

**Transitions :**

```php
$project = Project::find(10);

// Phase initiation → planning
$project->status = 'planning';
$project->save();

// Phase execution
$project->status = 'execution';
$project->save();

// Clôture
$project->status = 'closure';
$project->completion_percentage = 100;
$project->save();
```

---

### P7.2 : Approbation de la Charte Projet

**Déclencheur :** Validation formelle du projet par le sponsor

**Acteur :** Admin Organisation (Sponsor) ou Chef de Projet

**Étapes :**

```php
$project = Project::find(10);
$approver = Auth::user();

$project->approveCharter($approver);

// Résultat :
// charter_approved_at: 2025-01-15 14:30:00
// charter_approved_by: 100 (Fatima)
```

---

## 📋 PROCESSUS 8 : Gestion des Rôles et Permissions

### P8.1 : Créer un Rôle Personnalisé

**Déclencheur :** Besoin d'un nouveau rôle spécifique

**Acteur :** Super Admin

**Étapes :**

```php
$role = Role::create([
    'name' => 'Contrôleur Qualité',
    'slug' => 'quality-controller',
    'description' => 'Responsable du contrôle qualité sur les projets',
    'scope' => 'project',  // global | organization | project
    'organization_id' => NULL,  // NULL = rôle disponible pour tous
]);

// Attribuer des permissions
$permissions = Permission::whereIn('slug', [
    'view_projects',
    'view_tasks',
    'view_deliverables',
    'approve_deliverables',
])->get();

$role->permissions()->attach($permissions);
```

---

### P8.2 : Vérifier les Permissions d'un Utilisateur

**Déclencheur :** Contrôle d'accès à une action

**Acteur :** Système (automatique)

**Exemples :**

```php
$user = Auth::user();
$project = Project::find(10);

// Vérifier permission globale
if ($user->hasPermission('view_projects')) {
    // Afficher la liste des projets
}

// Vérifier permission sur un projet spécifique
if ($user->hasPermission('edit_project', $project)) {
    // Autoriser modification
}

// Vérifier rôle
if ($user->hasRole('project-manager')) {
    // Actions réservées aux chefs de projet
}
```

---

## 🔄 WORKFLOWS TYPES

### Workflow 1 : Création Complète d'un Projet

**De A à Z :**

```
1. Admin Org ABC crée le projet de base
   ↓
2. Définit ABC comme Sponsor
   ↓
3. Définit ABC comme MOA
   ↓
4. Invite SAMSIC comme MOE primaire
   ↓
5. SAMSIC accepte (ou automatique)
   ↓
6. Admin ABC attribue rôle "project-manager" à Fatima sur ce projet
   ↓
7. Fatima (Chef Projet) invite TechPartner comme sous-traitant
   ↓
8. TechPartner accepte
   ↓
9. Fatima crée la structure WBS, phases, tâches
   ↓
10. Fatima assigne des tâches aux utilisateurs (ABC, SAMSIC, TechPartner)
    ↓
11. Projet démarre (status: execution)
```

---

### Workflow 2 : Changement de Prestataire en Cours de Projet

```
1. Admin MOA (ABC) décide de changer la MOE
   ↓
2. Désactive participation SAMSIC (status: inactive, end_date: now)
   ↓
3. Crée participation nouveau prestataire XYZ (role: moe, is_primary: true)
   ↓
4. Utilisateurs SAMSIC perdent accès au projet
   ↓
5. Utilisateurs XYZ obtiennent accès au projet
   ↓
6. Historique SAMSIC préservé (tâches, livrables réalisés)
```

---

### Workflow 3 : Fin de Mission Sous-Traitant

```
1. Chef Projet constate fin de mission TechPartner
   ↓
2. Désactive participation TechPartner (status: inactive, end_date: 2025-06-30)
   ↓
3. Utilisateurs TechPartner perdent accès au projet
   ↓
4. Données créées par TechPartner restent visibles pour ABC et SAMSIC
   ↓
5. TechPartner peut encore voir historique (en lecture seule)
```

---

## 📚 BONNES PRATIQUES

### 1. Nommage des Projets

**Recommandations :**
- ✅ Utiliser un code unique : `PRJ-YYYY-ORG-XXX`
- ✅ Inclure l'année
- ✅ Inclure l'organisation cliente
- ✅ Numéro séquentiel

**Exemples :**
- `PRJ-2025-ABC-001` : Premier projet ABC en 2025
- `PRJ-2025-WANA-012` : 12e projet Wana en 2025

---

### 2. Références Internes

**Chaque organisation doit avoir sa propre référence :**

```php
// Sponsor ABC
'reference' => 'BC-2025-001'  // Bon de Commande

// MOE SAMSIC
'reference' => 'SAMSIC-MAINT-2025-123'  // Référence interne SAMSIC

// Subcontractor
'reference' => 'DEVIS-2025-456'  // Référence devis
```

---

### 3. Gestion des Statuts

**Toujours utiliser les champs de date :**
```php
ProjectOrganization {
    status: 'active',
    start_date: '2025-01-15',
    end_date: NULL  // Mission en cours
}

// Fin de mission
ProjectOrganization {
    status: 'inactive',
    start_date: '2025-01-15',
    end_date: '2025-06-30'  // Historique
}
```

---

### 4. Attribution des Rôles Progressivement

**Ne pas attribuer trop de rôles d'un coup :**

```
Étape 1 : Créer l'utilisateur (sans rôles)
    ↓
Étape 2 : Attribuer UN rôle global (si admin org)
    ↓
Étape 3 : Au fil des projets, attribuer des rôles projets spécifiques
```

**Éviter :**
```php
// ❌ Mauvais : Attribuer 50 rôles d'un coup
UserRole::create([...]);  // project 1
UserRole::create([...]);  // project 2
// ... 48 autres
```

**Préférer :**
```php
// ✅ Bon : Rôle global suffit pour un admin
UserRole::create([
    'user_id' => $user->id,
    'role_id' => Role::where('slug', 'organization-admin')->first()->id,
    // Aucun scope → accès à tous les projets de l'org
]);
```

---

### 5. Soft Delete Plutôt que Suppression

**Toujours préférer la désactivation :**

```php
// ✅ Bon : Désactivation (préserve historique)
$participation->status = 'inactive';
$participation->end_date = now();
$participation->save();

// ❌ À éviter : Suppression définitive
$participation->delete();
```

---

## 🔍 CAS D'USAGE RÉELS

### Cas 1 : SAMSIC Réalise un Projet pour ABC

**Configuration :**
```
Projet : Installation Système HVAC ABC
├─ Sponsor : ABC Industries (finance)
├─ MOA : ABC Industries (définit besoins)
├─ MOE : SAMSIC MAINTENANCE MAROC (réalise)
└─ Subcontractor : CoolTech (sous-traitant climatisation)
```

**Accès :**
- Utilisateurs ABC : Voient et pilotent le projet
- Utilisateurs SAMSIC : Réalisent les travaux
- Utilisateurs CoolTech : Travaillent sur leur périmètre uniquement

---

### Cas 2 : SAMSIC Sous-Traite à XYZ pour un Projet Wana

**Configuration :**
```
Projet : Maintenance Réseau Wana
├─ Sponsor : WANA Corporate (finance)
├─ MOA : WANA Corporate (définit besoins)
├─ MOE : SAMSIC MAINTENANCE MAROC (coordonne)
└─ Subcontractor : XYZ Engineering (exécute terrain)
```

**Accès :**
- Wana : Pilote et valide
- SAMSIC : Coordonne et supervise
- XYZ : Exécute sur le terrain

---

### Cas 3 : Multi-Projets avec Rôles Différents

**Organisation ABC :**
```
Projet A : Installation HVAC
├─ Rôle ABC : Sponsor/MOA (commande)
├─ Rôle SAMSIC : MOE (réalise)

Projet B : Formation Maintenance
├─ Rôle ABC : MOE (forme)
├─ Rôle WANA : Sponsor/MOA (commande)
```

**Résultat :**
- ABC est CLIENTE sur Projet A
- ABC est PRESTATAIRE sur Projet B
- Même organisation, rôles différents ✅

---

## 📊 INDICATEURS DE SUIVI

### Indicateurs Plateforme

```sql
-- Nombre total d'organisations
SELECT COUNT(*) FROM organizations WHERE status = 'active';

-- Nombre total d'utilisateurs actifs
SELECT COUNT(*) FROM users WHERE deleted_at IS NULL;

-- Nombre total de projets
SELECT COUNT(*) FROM projects WHERE status NOT IN ('cancelled', 'closure');

-- Projets par organisation (en tant que sponsor)
SELECT o.name, COUNT(p.id) as nb_projets
FROM organizations o
LEFT JOIN project_organizations po ON po.organization_id = o.id AND po.role = 'sponsor'
LEFT JOIN projects p ON p.id = po.project_id
GROUP BY o.name
ORDER BY nb_projets DESC;
```

---

### Indicateurs Métier

```sql
-- Répartition des rôles sur les projets
SELECT role, COUNT(*) as nb_participations
FROM project_organizations
WHERE status = 'active'
GROUP BY role;

-- Organisations les plus actives (tous rôles)
SELECT o.name, COUNT(DISTINCT po.project_id) as nb_projets
FROM organizations o
JOIN project_organizations po ON po.organization_id = o.id
WHERE po.status = 'active'
GROUP BY o.name
ORDER BY nb_projets DESC
LIMIT 10;

-- Utilisateurs avec le plus de rôles
SELECT u.name, COUNT(ur.id) as nb_roles
FROM users u
JOIN user_roles ur ON ur.user_id = u.id
GROUP BY u.name
ORDER BY nb_roles DESC
LIMIT 10;
```

---

## ⚠️ ERREURS COURANTES À ÉVITER

### Erreur 1 : Oublier de Créer les Participations Projet

**Symptôme :** Projet créé mais personne ne le voit

**Cause :**
```php
// ❌ Mauvais
$project = Project::create([...]);
// STOP ! Pas de project_organizations créées
```

**Solution :**
```php
// ✅ Bon
$project = Project::create([...]);

// Toujours créer AU MOINS sponsor, MOA, MOE
ProjectOrganization::create(['role' => 'sponsor', ...]);
ProjectOrganization::create(['role' => 'moa', ...]);
ProjectOrganization::create(['role' => 'moe', ...]);
```

---

### Erreur 2 : Attribuer des Rôles Sans Vérifier les Permissions

**Symptôme :** Utilisateur a un rôle mais ne peut rien faire

**Cause :** Rôle sans permissions

**Solution :**
```php
// Vérifier qu'un rôle a des permissions
$role = Role::find($roleId);
if ($role->permissions()->count() === 0) {
    throw new Exception("Ce rôle n'a aucune permission !");
}
```

---

### Erreur 3 : Oublier de Désactiver les Participations

**Symptôme :** Un sous-traitant voit encore le projet après sa mission

**Cause :** Participation toujours `status = 'active'`

**Solution :**
```php
// Toujours désactiver quand la mission se termine
$participation->status = 'inactive';
$participation->end_date = now();
$participation->save();
```

---

## 📖 GLOSSAIRE

| Terme | Définition |
|-------|------------|
| **Sponsor** | Organisation qui finance et commande le projet |
| **MOA** | Maître d'Ouvrage - Définit les besoins et valide |
| **MOE** | Maître d'Œuvre - Réalise le projet |
| **Subcontractor** | Sous-traitant sur un périmètre spécifique |
| **RLS** | Row-Level Security - Filtrage automatique des données |
| **Scope** | Périmètre d'application d'un rôle (global, project) |
| **Participation** | Implication d'une organisation sur un projet |
| **System Admin** | Administrateur technique de la plateforme |
| **Organization Admin** | Administrateur métier d'une organisation |

---

## 🎓 FORMATION RECOMMANDÉE

### Formation Administrateurs

**Durée :** 2 jours

**Programme :**
1. Vue d'ensemble de l'architecture multi-tenant
2. Création et gestion des organisations
3. Gestion des utilisateurs et rôles
4. Création de projets et participations multi-organisations
5. Contrôle d'accès et permissions
6. Cas pratiques

### Formation Chefs de Projet

**Durée :** 1 jour

**Programme :**
1. Création d'un projet
2. Invitation d'organisations participantes
3. Gestion de l'équipe projet
4. Attribution de tâches
5. Suivi et reporting

---

## 📞 SUPPORT

### Problèmes Techniques
- Email : support-technique@mdf-platform.com
- Documentation : `/docs`
- Logs : `/storage/logs/laravel.log`

### Questions Métier
- Email : support-metier@mdf-platform.com
- FAQ : À créer
- Tutoriels vidéo : À créer

---

**Document créé :** 9 novembre 2025
**Version :** 1.0
**Auteur :** Équipe MDF Access
**Prochaine révision :** Après tests utilisateurs
