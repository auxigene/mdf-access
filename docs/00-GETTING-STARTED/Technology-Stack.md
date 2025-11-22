# 🛠️ Technology Stack

Complete overview of technologies, frameworks, libraries, and tools used in MDF Access.

---

## 🎯 Core Technologies

### Backend Framework
```
Framework:   Laravel 12
Language:    PHP 8.2+
Architecture: MVC with Service Layer
ORM:         Eloquent
```

**Why Laravel 12?**
- ✅ Modern PHP framework with excellent ecosystem
- ✅ Built-in authentication and authorization
- ✅ Eloquent ORM with global scopes (perfect for multi-tenancy)
- ✅ Robust migration system
- ✅ Extensive package ecosystem

### Frontend Stack
```
Template Engine:  Blade
CSS Framework:    Tailwind CSS 4.0
Build Tool:       Vite 7.0
HTTP Client:      Axios 1.11
JavaScript:       Vanilla JS + Alpine.js (lightweight)
```

**Why Blade + Tailwind?**
- ✅ Server-side rendering for better performance
- ✅ Tailwind's utility-first approach for rapid UI development
- ✅ Vite for lightning-fast hot module replacement (HMR)
- ✅ No heavy JavaScript framework overhead

### Database
```
Primary:     SQLite (development)
Production:  MySQL 8.0+ or PostgreSQL 13+
ORM:         Eloquent
Migrations:  Laravel Migrations
```

**Database Features:**
- ✅ 57 tables with foreign key constraints
- ✅ Indexes for performance optimization
- ✅ JSON columns for flexible metadata
- ✅ Soft deletes for audit trail

---

## 📦 Key Dependencies

### Backend Packages

#### Core Laravel Packages
```json
{
  "laravel/framework": "^12.0",
  "laravel/tinker": "^2.10.1"
}
```

#### Authentication & Security
```json
{
  "pragmarx/google2fa": "^8.0"
}
```
- **Two-Factor Authentication (2FA)** using Google Authenticator
- Time-based One-Time Passwords (TOTP)
- QR code generation for easy setup

#### Excel Import/Export
```json
{
  "maatwebsite/excel": "^3.1",
  "phpoffice/phppresentation": "^1.2"
}
```
- **maatwebsite/excel:** Excel reading/writing with Laravel integration
- **PhpSpreadsheet:** Under the hood for Excel processing
- **phpoffice/phppresentation:** PowerPoint generation (statistics reports)

### Development Packages

```json
{
  "fakerphp/faker": "^1.23",
  "laravel/pail": "^1.2.2",
  "laravel/pint": "^1.24",
  "laravel/sail": "^1.41",
  "mockery/mockery": "^1.6",
  "nunomaduro/collision": "^8.6",
  "phpunit/phpunit": "^11.5.3"
}
```

| Package | Purpose |
|---------|---------|
| **faker** | Generate realistic test data |
| **pail** | Real-time log monitoring in CLI |
| **pint** | Code linting and formatting (Laravel's opinionated PSR-12) |
| **sail** | Docker development environment |
| **mockery** | Mocking framework for tests |
| **collision** | Beautiful error reporting in CLI |
| **phpunit** | Unit and feature testing |

### Frontend Packages

```json
{
  "tailwindcss": "^4.0",
  "vite": "^7.0",
  "axios": "^1.11",
  "concurrently": "^9.0"
}
```

| Package | Purpose |
|---------|---------|
| **tailwindcss** | Utility-first CSS framework |
| **vite** | Frontend build tool with HMR |
| **axios** | HTTP client for AJAX requests |
| **concurrently** | Run multiple dev servers simultaneously |

---

## 🏗️ Architecture Patterns

### 1. Multi-Tenant Architecture

**Pattern:** Row-Level Security (RLS) via Eloquent Global Scopes

```php
// TenantScoped Trait applied to all tenant-aware models
trait TenantScoped {
    protected static function bootTenantScoped() {
        static::addGlobalScope(new TenantScope);
    }
}

// TenantScope automatically filters queries by organization_id
class TenantScope implements Scope {
    public function apply(Builder $builder, Model $model) {
        if (auth()->check() && !auth()->user()->isSamsicInternal()) {
            $builder->where('organization_id', auth()->user()->organization_id);
        }
    }
}
```

**Learn more:** [Multi-Tenant Architecture](../01-ARCHITECTURE/multi-tenant/Multi-Tenant-Architecture.md)

### 2. Service Layer Pattern

Business logic separated from controllers:

```
app/
├── Http/Controllers/     # HTTP request handling
├── Services/             # Business logic
│   ├── PhaseTemplateService.php
│   └── FmImportService.php
├── Models/               # Data access layer
└── Traits/               # Reusable behaviors
```

### 3. Repository Pattern (Lightweight)

Models act as repositories with scopes and query methods:

```php
class Project extends Model {
    // Scopes for common queries
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function isCompleted() {
        return $this->status === 'completed';
    }
}
```

### 4. Policy-Based Authorization

```php
// Policies for resource-level authorization
class ProjectPolicy {
    public function update(User $user, Project $project) {
        return $user->hasPermissionTo('projects.edit', $project);
    }
}
```

**Learn more:** [RBAC System](../01-ARCHITECTURE/permissions/RBAC-System.md)

---

## 🔐 Security Stack

### Authentication
- **Laravel Breeze** - Minimal authentication scaffolding
- **Session-based** authentication (cookies)
- **Email verification** required before login
- **Password reset** via secure tokens

### Two-Factor Authentication (2FA)
- **pragmarx/google2fa** package
- TOTP (Time-based One-Time Password) algorithm
- QR code generation for mobile apps
- Recovery codes for account recovery

### Authorization
- **Custom RBAC system** (174 permissions, 29 roles)
- **Policy-based** resource authorization
- **Middleware** for route protection
- **Permission helpers** for Blade templates

### API Security
- **API Key authentication** with type and level
- **Rate limiting** via Laravel Throttle
- **CSRF protection** for web routes
- **Input validation** on all endpoints

**Learn more:** [Authentication Overview](../03-API-REFERENCE/authentication/Overview.md)

---

## 🗄️ Database Architecture

### Schema Overview

```
57 Total Tables:
├── 11 Multi-Tenant Tables (organizations, users, teams)
├── 39 PMBOK Tables (projects, phases, tasks, etc.)
└── 7 Field Maintenance Tables (sites, regions, tenants)
```

### Key Tables

| Category | Tables | Purpose |
|----------|--------|---------|
| **Core** | users, organizations, organization_types | Multi-tenancy foundation |
| **PMBOK** | projects, phases, tasks, deliverables | Project management |
| **Permissions** | permissions, roles, role_permission | Access control |
| **Templates** | methodology_templates, phase_templates | Phase templates |
| **Field Maintenance** | fm_sites, fm_tenants, fm_regions | Infrastructure management |

### Advanced Features
- ✅ **Hierarchical data** (phases, tasks, WBS elements)
- ✅ **Many-to-many relationships** (pivot tables with metadata)
- ✅ **JSON columns** for flexible metadata
- ✅ **Soft deletes** for audit trail
- ✅ **Timestamps** (created_at, updated_at) on all tables

**Learn more:** [Database Schema](../01-ARCHITECTURE/system-design/Database-Schema.md)

---

## 🧪 Testing Stack

### PHPUnit 11.5
```php
// Feature tests for API endpoints
public function test_user_can_create_project() {
    $response = $this->actingAs($user)->post('/api/projects', $data);
    $response->assertStatus(201);
}
```

### Test Types
- **Unit Tests** - Individual model/service methods
- **Feature Tests** - Full HTTP request/response cycles
- **Integration Tests** - Multi-component interactions

### Test Utilities
- **Faker** - Generate realistic test data
- **Mockery** - Mock external dependencies
- **Database Transactions** - Automatic rollback after tests

**Learn more:** [Testing Guide](../06-DEVELOPMENT/Testing-Guide.md)

---

## 🚀 Development Tools

### Laravel Pail
Real-time log monitoring in terminal:
```bash
php artisan pail
```

### Laravel Tinker
Interactive REPL for Laravel:
```bash
php artisan tinker
>>> User::count()
=> 58
```

### Laravel Pint
Code formatting and linting:
```bash
./vendor/bin/pint
```

### Composer Scripts
```bash
composer setup      # Install dependencies and setup
composer dev        # Start all dev servers (Laravel, Vite, Queue, Logs)
composer test       # Run PHPUnit tests
```

---

## 🐳 Docker Support (Laravel Sail)

Laravel Sail provides Docker-based development environment:

```bash
./vendor/bin/sail up        # Start Docker containers
./vendor/bin/sail artisan   # Run artisan commands
./vendor/bin/sail composer  # Run composer
./vendor/bin/sail npm       # Run npm
```

**Services:**
- PHP 8.2 container
- MySQL/PostgreSQL database
- Redis (caching and queues)
- Mailpit (local email testing)

---

## 📊 Performance Optimization

### Database
- ✅ Indexed columns for frequent queries
- ✅ Eager loading to prevent N+1 queries
- ✅ Query caching for static data

### Frontend
- ✅ Vite for optimized asset bundling
- ✅ Lazy loading for images
- ✅ CSS purging via Tailwind

### Caching
- ✅ Route caching
- ✅ Config caching
- ✅ View caching
- ✅ Redis for session/cache storage (production)

---

## 🌐 Browser Support

### Supported Browsers
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

### Mobile Support
- ✅ iOS Safari 14+
- ✅ Chrome Android 90+

---

## 📦 Server Requirements

### Minimum Requirements
```
PHP:        8.2 or higher
Database:   MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+
Web Server: Apache 2.4+ / Nginx 1.18+
Memory:     256MB (512MB recommended)
Storage:    500MB (excluding user data)
```

### PHP Extensions Required
```
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- Zip
- GD (for image processing)
```

**Learn more:** [Installation Guide](../05-DEPLOYMENT/Installation.md)

---

## 🔄 Version Management

### Semantic Versioning
```
Major.Minor.Patch
1.0.0 = Initial release
1.1.0 = New features (backward compatible)
1.0.1 = Bug fixes
```

### Dependencies
- **Composer** for PHP packages
- **NPM** for JavaScript packages

---

## 🎯 Technology Decisions

### Why SQLite for Development?
- ✅ Zero configuration
- ✅ File-based (no server setup)
- ✅ Fast for development
- ⚠️ Production should use MySQL/PostgreSQL

### Why Blade over Vue/React?
- ✅ Simpler architecture
- ✅ Server-side rendering (better SEO)
- ✅ Less JavaScript complexity
- ✅ Faster time-to-market
- ⚠️ Can be enhanced with Alpine.js for interactivity

### Why Tailwind CSS?
- ✅ Utility-first approach (rapid development)
- ✅ Small bundle size with purging
- ✅ Consistent design system
- ✅ Excellent documentation

### Why Laravel over Symfony/CodeIgniter?
- ✅ More modern and elegant syntax
- ✅ Better ecosystem (packages, community)
- ✅ Built-in features (auth, queues, broadcasting)
- ✅ Excellent documentation

---

## 📚 Learning Resources

### Official Documentation
- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [PHPUnit Docs](https://phpunit.de/documentation.html)

### MDF Access Specific
- [Development Guide](../06-DEVELOPMENT/README.md)
- [Architecture Overview](../01-ARCHITECTURE/system-design/Overview.md)
- [API Reference](../03-API-REFERENCE/README.md)

---

**Last Updated:** November 2025
**Laravel Version:** 12.x
**PHP Version:** 8.2+
