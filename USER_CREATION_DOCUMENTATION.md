# User Management & Creation Documentation

This document provides comprehensive guidance for creating and managing users programmatically during development and testing.

---

## Table of Contents

1. [Database Schema](#database-schema)
2. [User Model](#user-model)
3. [Required & Optional Fields](#required--optional-fields)
4. [Password Hashing & Authentication](#password-hashing--authentication)
5. [Creating Users Programmatically](#creating-users-programmatically)
6. [User Roles & Permissions](#user-roles--permissions)
7. [Database Connection Setup](#database-connection-setup)
8. [Two-Factor Authentication](#two-factor-authentication)
9. [Email Verification](#email-verification)
10. [Code Examples](#code-examples)

---

## Database Schema

### Users Table Structure

The main `users` table stores user information:

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT NULLABLE FOREIGN KEY,
    is_system_admin BOOLEAN DEFAULT false,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULLABLE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULLABLE,
    two_factor_enabled BOOLEAN DEFAULT false,
    two_factor_secret TEXT NULLABLE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    -- Indices
    INDEX(organization_id),
    INDEX(is_system_admin)
);
```

### Related Tables

**user_roles table** - Manages role assignments with optional scope:

```sql
CREATE TABLE user_roles (
    user_id BIGINT FOREIGN KEY,
    role_id BIGINT FOREIGN KEY,
    portfolio_id BIGINT NULLABLE FOREIGN KEY,
    program_id BIGINT NULLABLE FOREIGN KEY,
    project_id BIGINT NULLABLE FOREIGN KEY,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    -- Constraints
    UNIQUE(user_id, role_id, portfolio_id, program_id, project_id),
    INDEX(user_id),
    INDEX(role_id),
    INDEX(portfolio_id),
    INDEX(program_id),
    INDEX(project_id)
);
```

**roles table** - Defines available roles:

```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULLABLE,
    scope VARCHAR(50) NOT NULL, -- 'global', 'organization', 'project'
    organization_id BIGINT NULLABLE FOREIGN KEY,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL
);
```

**role_permission table** - Links roles to permissions:

```sql
CREATE TABLE role_permission (
    role_id BIGINT FOREIGN KEY,
    permission_id BIGINT FOREIGN KEY,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL
);
```

---

## User Model

**Location:** `/home/user/mdf-access/app/Models/User.php`

The User model is based on Laravel's `Authenticatable` class and implements `MustVerifyEmail`.

### Model Features

- **Mass Assignable Fields:** `name`, `email`, `password`, `organization_id`, `is_system_admin`, `two_factor_enabled`, `two_factor_secret`
- **Hidden Fields:** `password`, `remember_token`, `two_factor_secret`
- **Casts:** 
  - `email_verified_at` → datetime
  - `password` → hashed (automatically hashed via Laravel's cast)
  - `is_system_admin` → boolean
  - `two_factor_enabled` → boolean

### Key Relationships

```php
// Organization
$user->organization();  // belongsTo(Organization)

// Roles & Permissions
$user->userRoles();     // hasMany(UserRole)
$user->roles();         // belongsToMany(Role) via user_roles
$user->apiKeys();       // hasMany(ApiKey)
```

### Key Methods

```php
// Check admin status
$user->isSystemAdmin(): bool

// Check project participation
$user->isClientForProject(int $projectId): bool
$user->isMoeForProject(int $projectId): bool
$user->isMoaForProject(int $projectId): bool

// Get accessible projects
$user->getAccessibleProjects()
$user->getProjectsWhereClient()
$user->getProjectsWhereMoe()
$user->getProjectsWhereMoa()

// Permissions check
$user->hasPermission(string $permissionSlug, ?Model $scope = null): bool
$user->hasRole(string $roleSlug): bool
$user->getAllPermissions()

// Role queries
$user->globalRoles()
$user->rolesForProject(int $projectId)
$user->rolesForProgram(int $programId)
$user->rolesForPortfolio(int $portfolioId)
```

---

## Required & Optional Fields

### Required Fields

| Field | Type | Description | Validation |
|-------|------|-------------|-----------|
| `name` | string | User's full name | required, max:255 |
| `email` | string | User's email address | required, email, unique:users, max:255 |
| `password` | string | User's password (will be hashed) | required, min:8, confirmed |

### Optional Fields

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `organization_id` | bigint | NULL | Which organization the user belongs to |
| `is_system_admin` | boolean | false | Is this a system administrator? |
| `email_verified_at` | timestamp | NULL | When was email verified? |
| `two_factor_enabled` | boolean | false | Is 2FA enabled? |
| `two_factor_secret` | text | NULL | 2FA secret (Google Authenticator) |
| `remember_token` | string | NULL | "Remember me" token |

### Validation Rules

From `UsersImport.php`:

```php
'name' => 'required|string|max:255',
'email' => 'required|email|unique:users,email',
'password' => 'required|string|min:8',
'organization_id' => 'required|exists:organizations,id',
'is_system_admin' => 'required',
```

---

## Password Hashing & Authentication

### Password Hashing

The application uses **Bcrypt** with **12 rounds** for password hashing.

**Configuration:**
- **Location:** `.env` file
- **Key:** `BCRYPT_ROUNDS=12`
- **Method:** Laravel's `Hash::make()` function

### How Password Hashing Works

```php
// During user creation
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('plaintext_password'), // Hashed with Bcrypt
]);

// During login verification
if (Auth::attempt(['email' => $email, 'password' => $password])) {
    // Laravel automatically uses Hash::check() to verify
}
```

### Authentication Flow

1. **User submits credentials** at `/login`
2. **LoginController validates** email and password
3. **Rate limiting check** (5 failed attempts before throttle)
4. **Auth::attempt()** checks credentials:
   - Finds user by email
   - Uses `Hash::check()` to verify password against hashed version
5. **Email verification check** - must be verified before full login
6. **2FA check** - if enabled, redirects to 2FA verification
7. **Session created** - user is logged in

### Authentication Guards

**Type:** Session-based (not token-based)

**Configuration** (`config/auth.php`):

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

---

## Creating Users Programmatically

### Method 1: Using User Factory (Development)

**Location:** `/home/user/mdf-access/database/factories/UserFactory.php`

```php
use Database\Factories\UserFactory;

// Create a single user with defaults
$user = User::factory()->create();

// Create with custom attributes
$user = User::factory()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'organization_id' => 1,
]);

// Create unverified (email not verified)
$user = User::factory()->unverified()->create([
    'email' => 'unverified@example.com',
]);

// Create multiple users
$users = User::factory(10)->create();
```

### Method 2: Manual Creation with Hash

```php
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
    'password' => Hash::make('secure_password_123'),
    'organization_id' => 1,
    'is_system_admin' => false,
    'email_verified_at' => now(), // Mark as verified
    'two_factor_enabled' => false,
]);
```

### Method 3: Using Registration Controller

The app includes a registration endpoint that validates and creates users:

**Location:** `/home/user/mdf-access/app/Http/Controllers/Auth/RegisterController.php`

```php
// POST /register
// Validates: name, email, password (confirmed), organization_id

// Response: Redirects to login with message
// Fires: Registered event (triggers email verification)
```

### Method 4: Excel Import (Batch Creation)

**Location:** `/home/user/mdf-access/app/Imports/UsersImport.php`

Used by `UsersFromExcelSeeder` to import users from Excel files.

```php
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

Excel::import(new UsersImport, 'path/to/users.xlsx');
```

**Excel Requirements:**
- Headers: `name`, `email`, `password`, `organization_id`, `is_system_admin`
- Password minimum 8 characters
- Email must be unique
- Organization must exist

### Method 5: Database Seeder

**Location:** `/home/user/mdf-access/database/seeders/DatabaseSeeder.php`

```php
php artisan db:seed --class=DatabaseSeeder
```

Creates a test user:
- Name: Test User
- Email: test@example.com
- Password: password (hashed)

---

## User Roles & Permissions

### Assigning Roles to Users

#### Via UserRole Model

```php
use App\Models\UserRole;

// Assign a global role
UserRole::assignRole(
    userId: 1,
    roleId: 5,
    portfolioId: null,
    programId: null,
    projectId: null
);

// Assign a project-scoped role
UserRole::assignRole(
    userId: 1,
    roleId: 3,
    projectId: 42
);

// Or use firstOrCreate
UserRole::firstOrCreate([
    'user_id' => 1,
    'role_id' => 5,
    'portfolio_id' => null,
    'program_id' => null,
    'project_id' => null,
]);
```

#### Via Excel Import

**Location:** `/home/user/mdf-access/app/Imports/UserRolesImport.php`

```php
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserRolesImport;

Excel::import(new UserRolesImport, 'path/to/user_roles.xlsx');
```

**Excel Requirements:**
- Headers: `user_email`, `role_slug`, `scope_type`, `scope_id`
- `scope_type`: null/global, portfolio, program, project
- User must exist
- Role must exist

### Available Roles

Standard system roles:

| Slug | Name | Scope | Description |
|------|------|-------|-------------|
| `super_admin` | Super Administrateur | global | Full system access |
| `pmo_director` | Directeur PMO | global | PMO director with portfolio view |
| `pmo_manager` | Manager PMO | global | PMO manager with extended project access |
| `portfolio_director` | Directeur de Portfolio | organization | Portfolio responsibility |
| `program_manager` | Manager de Programme | project | Program management |
| `project_manager` | Chef de Projet | project | Project responsibility |

### Checking Permissions

```php
// Check single permission
if ($user->hasPermission('edit_projects')) {
    // User can edit projects
}

// Check with scope
if ($user->hasPermission('edit_projects', $project)) {
    // User can edit this specific project
}

// Check role
if ($user->hasRole('project_manager')) {
    // User is a project manager
}

// Get all permissions
$permissions = $user->getAllPermissions();
```

---

## Database Connection Setup

### Supported Databases

The application supports:

1. **SQLite** (default for development)
2. **MySQL** / **MariaDB**
3. **PostgreSQL**
4. **SQL Server**

### Configuration

**Location:** `config/database.php` and `.env` file

#### SQLite (Default Development)

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

#### MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mdf_access
DB_USERNAME=root
DB_PASSWORD=password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

#### PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mdf_access
DB_USERNAME=postgres
DB_PASSWORD=password
DB_CHARSET=utf8
```

### Creating Tables

```bash
# Run all pending migrations
php artisan migrate

# Create specific table
php artisan migrate --only=create_users_table
```

### Database Initialization

```bash
# 1. Set up environment
cp .env.example .env
php artisan key:generate

# 2. Configure database in .env
# Edit DB_CONNECTION, DB_HOST, DB_DATABASE, etc.

# 3. Run migrations
php artisan migrate

# 4. Run seeders (optional)
php artisan db:seed
```

---

## Two-Factor Authentication

### Enabling 2FA

```php
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

// Generate secret
$secret = $google2fa->generateSecretKey();

// Store in user
$user->update([
    'two_factor_secret' => $secret,
    'two_factor_enabled' => true,
]);
```

### Fields Required for 2FA

| Field | Type | Description |
|-------|------|-------------|
| `two_factor_enabled` | boolean | Is 2FA active? |
| `two_factor_secret` | text | TOTP secret key |

### 2FA Routes

```
GET  /2fa/setup             - Show setup form (requires verified email)
POST /2fa/enable            - Enable 2FA
POST /2fa/disable           - Disable 2FA
GET  /2fa/verify            - Show verification form (guest)
POST /2fa/verify            - Verify 2FA code
```

---

## Email Verification

### Email Verification Flow

1. **User registers** or is created
2. **Registered event fires** → Verification email sent
3. **User clicks link** in email
4. **Email verified** → User can now log in

### Manual Email Verification

```php
// Mark user as verified
$user->markEmailAsVerified();

// Or set timestamp
$user->update(['email_verified_at' => now()]);
```

### Email Routes

```
GET  /email/verify               - Verification notice
GET  /email/verify/{id}/{hash}   - Verify email (from link)
POST /email/resend               - Resend verification email
```

### Testing Without Email

During development with email disabled, manually verify:

```php
// In seeder or artisan command
User::factory()->create([
    'email_verified_at' => now(), // Immediately verified
]);
```

---

## Code Examples

### Complete User Creation Workflow

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'user:create 
        {name : User name} 
        {email : User email} 
        {--password=password : User password} 
        {--admin : Make user system admin} 
        {--org=1 : Organization ID}';

    public function handle()
    {
        try {
            // 1. Create user
            $user = User::create([
                'name' => $this->argument('name'),
                'email' => $this->argument('email'),
                'password' => Hash::make($this->option('password')),
                'organization_id' => $this->option('org'),
                'is_system_admin' => $this->option('admin') ? true : false,
                'email_verified_at' => now(), // Auto-verify in development
            ]);

            $this->info("User created: {$user->name} ({$user->email})");

            // 2. Assign roles (if not admin)
            if (!$this->option('admin')) {
                $role = Role::where('slug', 'project_manager')->first();
                if ($role) {
                    UserRole::assignRole(
                        userId: $user->id,
                        roleId: $role->id
                    );
                    $this->info("Role assigned: project_manager");
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
```

Usage:
```bash
php artisan user:create "John Doe" "john@example.com" --password=secure123
php artisan user:create "Admin User" "admin@example.com" --admin
```

### Creating Test Users in Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create test organization
        $org = Organization::create([
            'name' => 'Test Organization',
            'slug' => 'test-org',
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_system_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'organization_id' => $org->id,
            'email_verified_at' => now(),
        ]);

        // Assign role to regular user
        $role = Role::where('slug', 'project_manager')->first();
        if ($role) {
            $user->userRoles()->create([
                'role_id' => $role->id,
            ]);
        }

        $this->command->info('Test users created successfully');
    }
}
```

Run with:
```bash
php artisan db:seed --class=TestUsersSeeder
```

### Factory Usage in Tests

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_creation()
    {
        $org = Organization::factory()->create();

        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'organization_id' => $org->id,
        ]);
    }

    public function test_password_hashing()
    {
        $password = 'test_password_123';
        
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $this->assertTrue(
            Hash::check($password, $user->password)
        );
    }

    public function test_user_roles()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->userRoles()->create([
            'role_id' => $role->id,
        ]);

        $this->assertTrue($user->hasRole($role->slug));
    }
}
```

### Bulk Import from Excel

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class BulkUserImportSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/excel/data/users.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        try {
            Excel::import(new UsersImport, $filePath);
            
            $count = \App\Models\User::count();
            $this->command->info("Users imported successfully! Total: {$count}");
        } catch (\Exception $e) {
            $this->command->error("Import failed: " . $e->getMessage());
            throw $e;
        }
    }
}
```

---

## Summary

### Key Takeaways

1. **Password Hashing:** Always use `Hash::make()` - never store plaintext passwords
2. **Database:** Default is SQLite, supports MySQL, PostgreSQL, MSSQL
3. **User Fields:** Name, email, password (required); organization_id, is_system_admin (optional)
4. **Authentication:** Session-based with email verification required
5. **Roles:** Via `user_roles` table with optional scoping (project/program/portfolio)
6. **2FA:** Optional with Google Authenticator
7. **Creation Methods:** Factory, manual, registration, Excel import, seeders

### Common Tasks

```bash
# Create test database
php artisan migrate

# Create test user
php artisan tinker
> User::factory()->create(['email' => 'test@example.com'])

# Import from Excel
php artisan db:seed --class=UsersFromExcelSeeder

# Run all seeders
php artisan db:seed

# Create custom command
php artisan make:command CreateUser
```

### Validation Checklist

- [ ] Email is unique
- [ ] Password minimum 8 characters (if using registration)
- [ ] Organization exists (if organization_id provided)
- [ ] Password is hashed before saving
- [ ] Email verification enabled in production
- [ ] 2FA secret stored if 2FA enabled

