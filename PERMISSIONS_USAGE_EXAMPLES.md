# 📖 Guide d'Utilisation : Système de Permissions Flexibles

Ce guide montre comment utiliser le système de permissions dans votre application Laravel.

---

## 🔧 1. Dans les Routes (Middleware)

### Protéger une route avec une permission spécifique

```php
use Illuminate\Support\Facades\Route;

// Protéger une route GET
Route::middleware('permission:projects_view')->get('/projects', [ProjectController::class, 'index']);

// Protéger une route POST
Route::middleware('permission:projects_create')->post('/projects', [ProjectController::class, 'store']);

// Protéger plusieurs routes avec le même middleware
Route::middleware('permission:projects_view')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
});

// Combinaison auth + permission
Route::middleware(['auth', 'permission:budgets_approve'])->post('/budgets/{budget}/approve', ...);
```

---

## 🎯 2. Dans les Controllers (Policies)

### Utiliser `authorize()` avec les policies

```php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show(Project $project)
    {
        // Vérifie automatiquement via ProjectPolicy::view()
        $this->authorize('view', $project);

        return view('projects.show', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        // Vérifie via ProjectPolicy::update()
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('projects.show', $project);
    }

    public function destroy(Project $project)
    {
        // Vérifie via ProjectPolicy::delete()
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index');
    }
}
```

### Vérifier avant de faire une action

```php
public function index()
{
    // Vérifier si l'utilisateur peut voir la liste
    if (Gate::denies('viewAny', Project::class)) {
        abort(403, 'Vous ne pouvez pas voir les projets.');
    }

    $projects = Project::all();
    return view('projects.index', compact('projects'));
}
```

---

## 🔐 3. Dans le Code (Helpers)

### Utiliser les helpers globaux

```php
// Vérifier une permission
if (user_can('projects_view')) {
    // L'utilisateur peut voir les projets
}

// Vérifier avec un scope (projet spécifique)
if (user_can('projects_edit', $project)) {
    // L'utilisateur peut modifier ce projet
}

// Vérifier plusieurs permissions (OR)
if (user_can_any(['projects_view', 'projects_create'])) {
    // L'utilisateur a au moins une de ces permissions
}

// Vérifier plusieurs permissions (AND)
if (user_can_all(['projects_view', 'projects_edit', 'projects_delete'])) {
    // L'utilisateur a toutes ces permissions
}

// Vérifier un rôle
if (user_has_role('admin')) {
    // L'utilisateur est admin
}

// Vérifier si admin système
if (user_is_admin()) {
    // L'utilisateur est system admin (bypass toutes permissions)
}

// Construire un slug de permission
$slug = permission_slug('view', 'projects'); // Résultat: "view_projects"

// Lancer une 403 si pas la permission
abort_unless_can('budgets_approve', $budget, 'Vous ne pouvez pas approuver ce budget.');
```

---

## 🖼️ 4. Dans les Vues Blade

### Affichage conditionnel avec `@can`

```blade
{{-- Vérifier une permission simple --}}
@can('projects_create')
    <a href="{{ route('projects.create') }}" class="btn btn-primary">
        Créer un projet
    </a>
@endcan

{{-- Vérifier avec un modèle spécifique --}}
@can('update', $project)
    <a href="{{ route('projects.edit', $project) }}" class="btn btn-secondary">
        Modifier
    </a>
@endcan

@can('delete', $project)
    <form action="{{ route('projects.destroy', $project) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Supprimer</button>
    </form>
@endcan

{{-- Condition inverse --}}
@cannot('projects_view')
    <p>Vous n'avez pas accès aux projets.</p>
@endcannot

{{-- Vérifier un rôle --}}
@can('system-admin')
    <div class="admin-panel">
        <!-- Panneau admin -->
    </div>
@endcan
```

### Avec les helpers (alternative)

```blade
@if(user_can('projects_view'))
    <h2>Liste des projets</h2>
@endif

@if(user_can('projects_edit', $project))
    <button>Modifier</button>
@endif

@if(user_is_admin())
    <div class="admin-tools">
        <!-- Outils admin -->
    </div>
@endif
```

---

## 👤 5. Dans le Model User

### Méthodes disponibles directement sur User

```php
$user = auth()->user();

// Vérifier une permission
$user->hasPermission('projects_view');
$user->hasPermission('projects_edit', $project); // Avec scope

// Vérifier un rôle
$user->hasRole('project_manager');

// Vérifier si admin système
$user->isSystemAdmin();

// Récupérer toutes les permissions
$permissions = $user->getAllPermissions();

// Récupérer les rôles pour un projet
$roles = $user->rolesForProject($projectId);
```

---

## 🚪 6. Avec les Gates

### Définir et utiliser des Gates personnalisés

Les Gates sont **déjà enregistrés automatiquement** pour toutes les permissions dans `AuthServiceProvider`.

```php
use Illuminate\Support\Facades\Gate;

// Vérifier avec Gate::allows()
if (Gate::allows('projects_view')) {
    // Permission accordée
}

// Vérifier avec Gate::denies()
if (Gate::denies('budgets_approve', $budget)) {
    abort(403);
}

// Vérifier avant une action
Gate::authorize('projects_create');

// Le Gate 'system-admin' est aussi disponible
if (Gate::allows('system-admin')) {
    // Utilisateur est system admin
}
```

---

## 📋 7. Liste des Permissions Disponibles

### Format des slugs de permissions

Les permissions suivent le format : **`{action}_{resource}`**

**Actions disponibles** :
- `view` - Voir/lister
- `create` - Créer
- `edit` - Modifier
- `delete` - Supprimer
- `approve` - Approuver
- `export` - Exporter

**Exemples de slugs** :
- `view_projects`
- `create_tasks`
- `edit_budgets`
- `delete_risks`
- `approve_budgets`
- `export_reports`

### Vérifier les permissions disponibles

```php
use App\Models\Permission;

// Toutes les permissions actives
$permissions = Permission::where('is_active', true)->get();

// Groupées par ressource
$grouped = Permission::groupedByResource();

// Toutes les ressources distinctes
$resources = Permission::getDistinctResources();
```

---

## 🧪 8. Exemples Complets

### Exemple 1 : Controller avec vérifications multiples

```php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        // Méthode 1 : Via policy
        $this->authorize('viewAny', Project::class);

        $projects = auth()->user()->getAccessibleProjects();
        return view('projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        // Méthode 2 : Via helper
        abort_unless_can('projects_create');

        $project = Project::create($request->validated());
        return redirect()->route('projects.show', $project);
    }

    public function approve(Project $project)
    {
        // Méthode 3 : Via user
        if (!auth()->user()->hasPermission('approve_projects', $project)) {
            abort(403, 'Vous ne pouvez pas approuver ce projet.');
        }

        $project->update(['status' => 'approved']);
        return back()->with('success', 'Projet approuvé');
    }
}
```

### Exemple 2 : Vue Blade complète

```blade
<div class="project-card">
    <h3>{{ $project->name }}</h3>

    <div class="actions">
        @can('update', $project)
            <a href="{{ route('projects.edit', $project) }}">
                <i class="icon-edit"></i> Modifier
            </a>
        @endcan

        @can('delete', $project)
            <form action="{{ route('projects.destroy', $project) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Êtes-vous sûr ?')">
                    <i class="icon-trash"></i> Supprimer
                </button>
            </form>
        @endcan

        @if(user_can('approve_projects', $project) && $project->status === 'pending')
            <form action="{{ route('projects.approve', $project) }}" method="POST">
                @csrf
                <button type="submit" class="btn-success">
                    <i class="icon-check"></i> Approuver
                </button>
            </form>
        @endif
    </div>

    @can('system-admin')
        <div class="admin-badge">Admin Access</div>
    @endcan
</div>
```

---

## 🎓 Bonnes Pratiques

1. **Privilégier les Policies** pour les autorisations sur models
2. **Utiliser le Middleware** pour protéger les routes
3. **Utiliser les Helpers** dans la logique métier
4. **Utiliser `@can`** dans les vues Blade
5. **System Admin bypass** : Les system admins ont TOUTES les permissions automatiquement
6. **Scopes** : Toujours passer le scope (projet, programme, portfolio) quand applicable

---

## 🔍 Debugging

### Vérifier les permissions d'un utilisateur

```php
$user = User::find(1);

// Voir toutes les permissions
dd($user->getAllPermissions()->pluck('slug'));

// Voir les rôles
dd($user->roles->pluck('name'));

// Tester une permission
dd($user->hasPermission('projects_view'));
```

### Vérifier la matrice d'applicabilité

```php
use App\Models\AclResource;

$projects = AclResource::where('slug', 'projects')->first();

// Actions applicables à projects
dd($projects->applicableActions()->pluck('slug'));
```

---

## ✅ Résumé

| Contexte | Méthode Recommandée | Exemple |
|----------|---------------------|---------|
| Routes | Middleware `permission:` | `Route::middleware('permission:projects_view')` |
| Controllers | `$this->authorize()` | `$this->authorize('update', $project)` |
| Code métier | Helpers `user_can()` | `if (user_can('projects_view'))` |
| Vues Blade | `@can` directive | `@can('update', $project)` |
| Gates | `Gate::allows()` | `if (Gate::allows('projects_create'))` |

🎉 Votre système de permissions est maintenant opérationnel !
