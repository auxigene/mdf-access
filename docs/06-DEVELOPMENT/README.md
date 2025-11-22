# 💻 Development Documentation

Complete development guide for contributing to MDF Access.

---

## 📚 Table of Contents

1. [Project Structure](./Project-Structure.md) - Codebase organization
2. [Coding Standards](./Coding-Standards.md) - Code style and conventions
3. [Models Guide](./Models-Guide.md) - Eloquent models and relationships
4. [Controllers Guide](./Controllers-Guide.md) - HTTP controllers
5. [Middleware Guide](./Middleware-Guide.md) - Request middleware
6. [Testing Guide](./Testing-Guide.md) - Unit and feature testing
7. [Contributing](./Contributing.md) - Contribution guidelines

---

## 🏗️ Project Structure

```
mdf-access/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # HTTP controllers
│   │   │   ├── Api/          # API controllers
│   │   │   └── Auth/         # Authentication controllers
│   │   ├── Middleware/       # Request middleware
│   │   └── Requests/         # Form requests
│   ├── Models/               # Eloquent models (40+ models)
│   ├── Services/             # Business logic services
│   ├── Traits/               # Reusable traits
│   ├── Imports/              # Excel import classes
│   ├── Exports/              # Excel export classes
│   ├── Policies/             # Authorization policies
│   └── Helpers/              # Helper functions
├── database/
│   ├── migrations/           # Database migrations (50+)
│   ├── seeders/              # Database seeders
│   └── factories/            # Model factories
├── routes/
│   ├── web.php               # Web routes
│   ├── api.php               # API routes
│   └── console.php           # Artisan commands
├── resources/
│   ├── views/                # Blade templates
│   ├── js/                   # JavaScript files
│   └── css/                  # CSS files
├── tests/
│   ├── Unit/                 # Unit tests
│   └── Feature/              # Feature tests
└── docs/                     # Documentation (you are here!)
```

---

## 📝 Coding Standards

### PSR-12 Compliance

MDF Access follows **PSR-12** coding standards enforced by **Laravel Pint**.

```bash
# Run Pint to format code
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

### Naming Conventions

| Type | Convention | Example |
|------|-----------|---------|
| **Classes** | PascalCase | `ProjectController` |
| **Methods** | camelCase | `createProject()` |
| **Variables** | camelCase | `$projectName` |
| **Constants** | UPPER_SNAKE_CASE | `MAX_UPLOAD_SIZE` |
| **Database Tables** | snake_case | `project_organizations` |
| **Database Columns** | snake_case | `created_at` |
| **Routes** | kebab-case | `/api/project-teams` |
| **Blade Views** | kebab-case | `project-details.blade.php` |

### Laravel Best Practices

```php
// ✅ Good: Use Eloquent relationships
$project->tasks;

// ❌ Bad: Manual joins
DB::table('projects')
    ->join('tasks', 'projects.id', '=', 'tasks.project_id')
    ->get();

// ✅ Good: Use query scopes
Project::active()->get();

// ❌ Bad: Repeated where clauses
Project::where('status', 'active')->get();

// ✅ Good: Use form requests
public function store(StoreProjectRequest $request)

// ❌ Bad: Validation in controller
$request->validate([...]);
```

---

## 🗄️ Models Guide

### Model Structure

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantScoped;

class Project extends Model
{
    use TenantScoped; // Multi-tenant support

    // Table name (if not default)
    protected $table = 'projects';

    // Mass-assignable fields
    protected $fillable = [
        'name', 'organization_id', 'status', 'start_date'
    ];

    // Hidden fields (API responses)
    protected $hidden = ['deleted_at'];

    // Casts
    protected $casts = [
        'start_date' => 'date',
        'metadata' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function phases()
    {
        return $this->hasMany(Phase::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getProgressAttribute()
    {
        return $this->calculateProgress();
    }

    // Methods
    public function calculateProgress()
    {
        // Business logic here
    }
}
```

### Common Traits

```php
// Multi-tenant filtering
use TenantScoped;

// UUID primary keys
use HasUuid;

// Permissions
use HasPermissions;

// Soft deletes
use SoftDeletes;
```

---

## 🎮 Controllers Guide

### Controller Structure

```php
namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::with('phases', 'organization')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());

        // Auto-create phases from template
        if ($request->methodology_template_id) {
            $project->instantiatePhasesFromTemplate(
                $request->methodology_template_id
            );
        }

        return response()->json([
            'success' => true,
            'data' => $project->load('phases'),
            'message' => 'Project created successfully'
        ], 201);
    }

    public function update(StoreProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $project,
            'message' => 'Project updated successfully'
        ]);
    }
}
```

---

## 🧪 Testing Guide

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ProjectTest

# Run with coverage
php artisan test --coverage
```

### Writing Tests

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Organization, Project};

class ProjectTest extends TestCase
{
    public function test_user_can_create_project()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/projects', [
            'name' => 'Test Project',
            'organization_id' => $org->id,
            'methodology_template_id' => 1,
            'status' => 'active'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'phases']
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project'
        ]);
    }

    public function test_tenant_scoping_works()
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org1->id]);

        Project::factory()->create(['organization_id' => $org1->id]);
        Project::factory()->create(['organization_id' => $org2->id]);

        $this->actingAs($user);

        $projects = Project::all();

        // Should only see org1's project
        $this->assertCount(1, $projects);
    }
}
```

---

## 🔧 Development Workflow

### 1. Feature Development

```bash
# 1. Create feature branch
git checkout -b feature/my-new-feature

# 2. Create migration if needed
php artisan make:migration create_something_table

# 3. Create model
php artisan make:model Something -m

# 4. Create controller
php artisan make:controller SomethingController --api

# 5. Create tests
php artisan make:test SomethingTest

# 6. Implement feature

# 7. Run tests
php artisan test

# 8. Format code
./vendor/bin/pint

# 9. Commit and push
git add .
git commit -m "feat: add something feature"
git push origin feature/my-new-feature
```

### 2. Database Changes

```bash
# Create migration
php artisan make:migration add_field_to_table

# Edit migration file

# Run migration
php artisan migrate

# If error, rollback
php artisan migrate:rollback

# Create seeder
php artisan make:seeder SomethingSeeder

# Run seeder
php artisan db:seed --class=SomethingSeeder
```

---

## 🛠️ Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models

# List routes
php artisan route:list

# Interactive REPL
php artisan tinker

# Check code quality
./vendor/bin/pint --test

# Run specific test
php artisan test --filter=test_method_name
```

---

## 📖 Related Documentation

- **Architecture:** [System Design](../01-ARCHITECTURE/system-design/Overview.md)
- **Features:** [Features Guide](../02-FEATURES/README.md)
- **API Reference:** [API Endpoints](../03-API-REFERENCE/README.md)
- **Testing:** [Testing Recommendations](../10-APPENDICES/Testing-Recommendations.md)

---

**Last Updated:** November 2025
**Laravel Version:** 12.x
**PHP Version:** 8.2+
