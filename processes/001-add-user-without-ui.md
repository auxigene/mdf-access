# Process 001: Add a New User Without UI Access

**Purpose:** Create new users programmatically during development when the user interface is unavailable or not yet implemented.

**Use Cases:**
- Development and testing
- Initial system setup
- Automated testing
- Seeding test data

---

## Method 1: Using Tinker (Recommended for Quick Testing)

### Step 1: Access Laravel Tinker
```bash
php artisan tinker
```

### Step 2: Create a Basic User
```php
$user = \App\Models\User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => \Hash::make('password123'),
    'email_verified_at' => now(), // Skip email verification
]);
```

### Step 3: Make User a System Admin (Optional)
```php
$user->update(['is_system_admin' => true]);
```

### Step 4: Assign Organization (Optional)
```php
$user->update(['organization_id' => 1]); // Replace 1 with actual org ID
```

### Step 5: Assign Role (Optional)
```php
\App\Models\UserRole::assignRole(
    userId: $user->id,
    roleId: 1, // Replace with desired role ID
    portfolioId: null,
    programId: null,
    projectId: null
);
```

---

## Method 2: Using Database Seeder (Recommended for Reproducible Setup)

### Step 1: Create a Seeder File
```bash
php artisan make:seeder DevelopmentUsersSeeder
```

### Step 2: Edit the Seeder File
Open `database/seeders/DevelopmentUsersSeeder.php` and add:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class DevelopmentUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create System Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'is_system_admin' => true,
        ]);

        // Create Regular User
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('test123'),
            'email_verified_at' => now(),
            'organization_id' => 1, // Optional
        ]);

        // Assign role to regular user (example: project manager)
        UserRole::assignRole(
            userId: $user->id,
            roleId: 6, // project_manager role ID
            portfolioId: null,
            programId: null,
            projectId: 1 // Optional: scope to specific project
        );
    }
}
```

### Step 3: Run the Seeder
```bash
php artisan db:seed --class=DevelopmentUsersSeeder
```

---

## Method 3: Using Artisan Command (Recommended for Production-Like Setup)

### Step 1: Create Custom Command
```bash
php artisan make:command CreateUser
```

### Step 2: Edit the Command File
Open `app/Console/Commands/CreateUser.php` and modify:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    protected $signature = 'user:create
                            {name : The name of the user}
                            {email : The email of the user}
                            {password : The password for the user}
                            {--admin : Make the user a system admin}
                            {--org= : Organization ID}';

    protected $description = 'Create a new user from the command line';

    public function handle()
    {
        $validator = Validator::make([
            'email' => $this->argument('email'),
            'name' => $this->argument('name'),
            'password' => $this->argument('password'),
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $user = User::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => Hash::make($this->argument('password')),
            'email_verified_at' => now(),
            'is_system_admin' => $this->option('admin'),
            'organization_id' => $this->option('org'),
        ]);

        $this->info("User created successfully!");
        $this->info("ID: {$user->id}");
        $this->info("Name: {$user->name}");
        $this->info("Email: {$user->email}");

        return 0;
    }
}
```

### Step 3: Use the Command
```bash
# Create regular user
php artisan user:create "John Doe" "john@example.com" "password123"

# Create system admin
php artisan user:create "Admin User" "admin@example.com" "admin123" --admin

# Create user with organization
php artisan user:create "Org User" "org@example.com" "password123" --org=1
```

---

## Method 4: Using Factory (Recommended for Testing)

### Quick Factory Usage in Tinker:
```bash
php artisan tinker
```

```php
// Create single user
$user = \App\Models\User::factory()->create([
    'email' => 'specific@example.com',
    'email_verified_at' => now(),
]);

// Create multiple users
\App\Models\User::factory()->count(10)->create();

// Create system admin
$admin = \App\Models\User::factory()->create([
    'is_system_admin' => true,
    'email_verified_at' => now(),
]);
```

---

## Required User Fields

| Field | Required | Description | Example |
|-------|----------|-------------|---------|
| `name` | Yes | User's full name | "John Doe" |
| `email` | Yes | Unique email address | "john@example.com" |
| `password` | Yes | Hashed password (min 8 chars) | `Hash::make('password123')` |
| `email_verified_at` | No | Email verification timestamp | `now()` or `null` |
| `organization_id` | No | Foreign key to organizations | `1` or `null` |
| `is_system_admin` | No | System admin flag | `true` or `false` (default) |
| `two_factor_enabled` | No | 2FA enabled flag | `true` or `false` (default) |

---

## Available Roles

To list all available roles, use Tinker:
```php
\App\Models\Role::all(['id', 'name', 'display_name', 'scope']);
```

Common roles:
- **1** - `super_admin` (global)
- **2** - `pmo_director` (global)
- **3** - `pmo_manager` (global)
- **4** - `portfolio_director` (organization)
- **5** - `program_manager` (program)
- **6** - `project_manager` (project)

---

## Troubleshooting

### Issue: "SQLSTATE[23000]: Integrity constraint violation"
**Solution:** Email already exists. Use a different email address.

### Issue: "Class 'Hash' not found"
**Solution:** Use the full namespace: `\Illuminate\Support\Facades\Hash::make()`

### Issue: User created but cannot login
**Solution:** Set `email_verified_at`:
```php
$user->update(['email_verified_at' => now()]);
```

### Issue: Need to reset a user's password
**Solution:** Update password:
```php
$user = \App\Models\User::where('email', 'john@example.com')->first();
$user->update(['password' => \Hash::make('newpassword123')]);
```

---

## Security Notes

⚠️ **Important Security Considerations:**

1. **Never commit credentials**: Don't hardcode passwords in seeders for production
2. **Strong passwords**: Use strong passwords even in development
3. **Email verification**: Skip only in development; require in production
4. **Admin access**: Be cautious when creating system admins
5. **Environment check**: Use `app()->environment()` to ensure dev-only operations

### Example Safe Seeder:
```php
public function run(): void
{
    if (app()->environment('production')) {
        $this->command->error('Cannot run in production!');
        return;
    }

    // Safe to create dev users here
}
```

---

## Quick Reference

**Create user via Tinker:**
```php
\App\Models\User::create(['name' => 'Name', 'email' => 'email@test.com', 'password' => \Hash::make('pass123'), 'email_verified_at' => now()]);
```

**Create admin via Tinker:**
```php
\App\Models\User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => \Hash::make('admin123'), 'email_verified_at' => now(), 'is_system_admin' => true]);
```

**Reset password:**
```php
\App\Models\User::where('email', 'user@test.com')->first()->update(['password' => \Hash::make('newpass')]);
```

---

**Document Version:** 1.0
**Last Updated:** 2025-11-20
**Related Documentation:**
- `USER_CREATION_DOCUMENTATION.md` - Comprehensive technical documentation
- `AUTHENTICATION.md` - Authentication system details
- `API_DOCUMENTATION.md` - API endpoints for user management
