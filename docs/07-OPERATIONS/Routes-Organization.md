# Organisation des Routes - MDF Access

## Vue d'ensemble

Les routes de l'application sont organisées de manière modulaire pour faciliter la maintenance et la scalabilité. Chaque fichier de routes a une responsabilité spécifique.

## Structure des fichiers

```
routes/
├── web.php         # Point d'entrée principal + routes publiques
├── api.php         # Routes API (authentification par clé API)
├── auth.php        # Routes d'authentification (login, register, etc.)
├── dashboard.php   # Routes du dashboard utilisateur
├── admin.php       # Routes d'administration (system admin only)
└── console.php     # Commandes Artisan (CLI)
```

---

## 📁 Détail des fichiers

### `routes/web.php`

**Rôle** : Point d'entrée principal et routes publiques

**Contenu** :
- Routes publiques (homepage, download page)
- Inclusion des autres fichiers de routes via `require`

**Exemple** :
```php
// Homepage
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Load modular route files
require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/admin.php';
```

**Routes disponibles** :
- `GET /` - Homepage
- `GET /download` - Page de téléchargement
- `GET /excel/download/{fileName}` - Téléchargement public de fichiers Excel

---

### `routes/auth.php`

**Rôle** : Toutes les routes d'authentification

**Middleware** : `guest` (non authentifié) et `auth` (authentifié)

**Contenu** :

#### Routes publiques (guest)
- **Login**
  - `GET /login` - Formulaire de connexion
  - `POST /login` - Traitement de la connexion

- **Registration**
  - `GET /register` - Formulaire d'inscription
  - `POST /register` - Traitement de l'inscription

- **Password Reset**
  - `GET /forgot-password` - Demande de réinitialisation
  - `POST /forgot-password` - Envoi de l'email
  - `GET /reset-password/{token}` - Formulaire de nouveau mot de passe
  - `POST /reset-password` - Mise à jour du mot de passe

- **2FA Verification**
  - `GET /2fa/verify` - Formulaire de vérification 2FA
  - `POST /2fa/verify` - Traitement du code 2FA

#### Routes authentifiées (auth)
- **Logout**
  - `POST /logout` - Déconnexion

- **Email Verification**
  - `GET /email/verify` - Page de notification
  - `GET /email/verify/{id}/{hash}` - Vérification (lien signé)
  - `POST /email/resend` - Renvoi de l'email

- **2FA Setup** (requires verified email)
  - `GET /2fa/setup` - Configuration 2FA
  - `POST /2fa/enable` - Activation 2FA
  - `POST /2fa/disable` - Désactivation 2FA

**Contrôleurs utilisés** :
- `LoginController`
- `RegisterController`
- `PasswordResetController`
- `EmailVerificationController`
- `TwoFactorAuthController`

---

### `routes/dashboard.php`

**Rôle** : Routes du dashboard et fonctionnalités utilisateur

**Middleware** : `auth` + `verified` (email vérifié requis)

**Contenu actuel** :
- `GET /dashboard` - Tableau de bord principal

**Routes futures (commentées, à implémenter)** :
- Profil utilisateur (`/profile`)
- Paramètres utilisateur (`/settings`)
- Gestion des projets (`/projects`)
- Gestion des portfolios (`/portfolios`)
- Gestion des programmes (`/programs`)
- Gestion des tâches (`/tasks`)
- Gestion des ressources (`/resources`)
- Gestion des budgets (`/budgets`)

**Exemple d'implémentation future** :
```php
// Projects
Route::resource('projects', ProjectController::class);

// Custom project routes
Route::get('/projects/{project}/phases', [ProjectController::class, 'phases'])
    ->name('projects.phases');
```

---

### `routes/admin.php`

**Rôle** : Panel d'administration système

**Middleware** : `auth` + `verified` + (system admin check to be implemented)

**Prefix** : `/admin`

**Name prefix** : `admin.`

**Routes futures (commentées, à implémenter)** :
- Dashboard admin (`/admin/dashboard`)
- Gestion des utilisateurs (`/admin/users`)
- Gestion des organisations (`/admin/organizations`)
- Gestion des rôles (`/admin/roles`)
- Gestion des permissions (`/admin/permissions`)
- Gestion des clés API (`/admin/api-keys`)
- Paramètres système (`/admin/settings`)
- Logs d'activité (`/admin/logs`)
- Santé du système (`/admin/health`)

**Exemple d'implémentation future** :
```php
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class);
});
```

**Note** : Un middleware `admin` devra être créé pour vérifier `$user->is_system_admin`

---

### `routes/api.php`

**Rôle** : API REST avec authentification par clé API

**Middleware** : `api.key:{type},{level}`

**Prefix** : `/api` (automatique via Laravel)

**Contenu actuel** :

#### Excel Update API (Kizeo Integration)
- `POST /api/excel/update` - Mise à jour d'un fichier Excel
  - Middleware: `api.key:excel_update,write`
  - Controller: `ExcelUpdateController@update`

- `GET /api/excel/download/{fileName}` - Téléchargement d'un fichier Excel
  - Middleware: `api.key:excel_update,read`
  - Controller: `ExcelUpdateController@download`

**Routes futures (commentées)** :
- API Projects (`/api/projects`)
- API Tasks (`/api/tasks`)
- API Resources
- API Budgets

**Format des routes API** :
```php
Route::prefix('projects')->middleware('api.key:projects,read')->group(function () {
    Route::get('/', [ProjectApiController::class, 'index']);
    Route::get('/{project}', [ProjectApiController::class, 'show']);
    Route::post('/', [ProjectApiController::class, 'store'])
        ->middleware('api.key:projects,write');
    Route::put('/{project}', [ProjectApiController::class, 'update'])
        ->middleware('api.key:projects,write');
    Route::delete('/{project}', [ProjectApiController::class, 'destroy'])
        ->middleware('api.key:projects,admin');
});
```

---

## 🔒 Middleware disponibles

### Middleware Laravel natifs

- **`guest`** : Route accessible uniquement aux non-authentifiés
- **`auth`** : Route accessible uniquement aux authentifiés
- **`verified`** : Requiert un email vérifié
- **`signed`** : Vérifie la signature de l'URL (email verification)
- **`throttle:x,y`** : Rate limiting (x requêtes par y minutes)

### Middleware personnalisés

- **`api.key:{type},{level}`** : Authentification par clé API
  - Types : `excel_update`, `projects`, `tasks`, etc.
  - Niveaux : `read`, `write`, `admin`
  - Exemples :
    - `api.key:excel_update,write`
    - `api.key:projects,read`

- **`permission:{slug}`** : Vérification de permission RBAC
  - Exemple : `permission:projects_view`

- **`admin`** : (À créer) Vérification system admin
  - Vérifie `$user->is_system_admin`

---

## 📋 Convention de nommage des routes

### Format général
```
{domain}.{resource}.{action}
```

### Exemples

**Routes web** :
- `home` - Homepage
- `login` - Page de connexion
- `register` - Page d'inscription
- `dashboard` - Dashboard principal
- `projects.index` - Liste des projets
- `projects.show` - Détail d'un projet
- `projects.create` - Formulaire de création
- `projects.store` - Enregistrement
- `projects.edit` - Formulaire d'édition
- `projects.update` - Mise à jour
- `projects.destroy` - Suppression

**Routes admin** :
- `admin.dashboard` - Dashboard admin
- `admin.users.index` - Liste des utilisateurs
- `admin.users.edit` - Édition d'un utilisateur

**Routes API** :
- `api.excel.update` - Update Excel
- `api.excel.download` - Download Excel
- `api.projects.index` - Liste des projets (API)
- `api.projects.show` - Détail d'un projet (API)

---

## 🚀 Ajouter de nouvelles routes

### 1. Déterminer le fichier approprié

- **Routes publiques** → `web.php`
- **Routes d'authentification** → `auth.php`
- **Routes utilisateur authentifié** → `dashboard.php`
- **Routes admin** → `admin.php`
- **Routes API** → `api.php`

### 2. Respecter les conventions

✅ **Bonnes pratiques** :
```php
// Grouper les routes liées
Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
});

// Utiliser les middleware appropriés
Route::middleware(['auth', 'verified'])->group(function () {
    // Routes...
});

// Utiliser resource pour les CRUD
Route::resource('projects', ProjectController::class);
```

❌ **À éviter** :
```php
// Routes non groupées
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/create', [ProjectController::class, 'create']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);

// Noms de routes incohérents
Route::get('/projects', ...)->name('projectList');
Route::get('/projects/{id}', ...)->name('project-detail');
```

### 3. Documenter les nouvelles routes

Ajoutez des commentaires clairs :
```php
// ===================================
// Project Management
// ===================================

// List all projects
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

// Show project detail
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
```

---

## 🧪 Tester les routes

### Lister toutes les routes
```bash
php artisan route:list
```

### Filtrer par nom
```bash
php artisan route:list --name=projects
```

### Filtrer par méthode
```bash
php artisan route:list --method=GET
```

### Filtrer par chemin
```bash
php artisan route:list --path=api
```

---

## 📊 Tableau récapitulatif

| Fichier | Rôle | Middleware | Prefix | Exemples |
|---------|------|------------|--------|----------|
| `web.php` | Routes publiques | - | - | `/`, `/download` |
| `auth.php` | Authentification | `guest`, `auth` | - | `/login`, `/register` |
| `dashboard.php` | Dashboard utilisateur | `auth`, `verified` | - | `/dashboard`, `/profile` |
| `admin.php` | Panel admin | `auth`, `verified`, `admin` | `/admin` | `/admin/users` |
| `api.php` | API REST | `api.key` | `/api` | `/api/projects` |

---

## 🔄 Migration depuis l'ancienne structure

Si vous aviez toutes les routes dans `web.php`, voici comment les migrer :

1. **Identifier les routes d'authentification** → Déplacer vers `auth.php`
2. **Identifier les routes dashboard** → Déplacer vers `dashboard.php`
3. **Identifier les routes admin** → Déplacer vers `admin.php`
4. **Garder uniquement les routes publiques** dans `web.php`
5. **Ajouter les `require`** dans `web.php`

---

## 🎯 Avantages de cette organisation

✅ **Séparation des responsabilités** : Chaque fichier a un rôle clair

✅ **Scalabilité** : Facile d'ajouter de nouvelles routes sans polluer un seul fichier

✅ **Maintenabilité** : Plus facile de trouver et modifier des routes

✅ **Travail en équipe** : Moins de conflits Git sur un seul gros fichier

✅ **Performance** : Laravel charge seulement les routes nécessaires

✅ **Lisibilité** : Code plus organisé et compréhensible

---

## 📚 Ressources

- [Documentation Laravel Routing](https://laravel.com/docs/12.x/routing)
- [Laravel Route Groups](https://laravel.com/docs/12.x/routing#route-groups)
- [Laravel Route Model Binding](https://laravel.com/docs/12.x/routing#route-model-binding)
- [Laravel Middleware](https://laravel.com/docs/12.x/middleware)
