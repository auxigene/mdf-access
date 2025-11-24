# 🚀 Quick Start Guide

Get MDF Access up and running in **5 minutes** with this step-by-step guide.

---

## 📋 Prerequisites

Before starting, ensure you have:

- ✅ **PHP 8.2+** installed
- ✅ **Composer** (PHP package manager)
- ✅ **Node.js 18+** and NPM
- ✅ **SQLite 3.35+** (or MySQL 8.0+ / PostgreSQL 13+)
- ✅ **Git** installed

### Check Your Environment

```bash
php --version      # Should be 8.2 or higher
composer --version # Should be 2.x
node --version     # Should be 18.x or higher
npm --version      # Should be 9.x or higher
```

---

## ⚡ Quick Installation (5 Minutes)

### Step 1: Clone the Repository

```bash
git clone <repository-url> mdf-access
cd mdf-access
```

### Step 2: Run Setup Script

```bash
composer setup
```

This single command will:
1. Install PHP dependencies
2. Create `.env` file from `.env.example`
3. Generate application key
4. Run database migrations
5. Install NPM dependencies
6. Build frontend assets

### Step 3: Configure Database

**Option A: SQLite (Default - Easiest)**

SQLite is already configured. Skip to Step 4!

**Option B: MySQL/PostgreSQL**

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mdf_access
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Then run:
```bash
php artisan migrate
```

### Step 4: Seed the Database (Optional but Recommended)

```bash
php artisan db:seed
```

This will create:
- 174 permissions
- 29 roles
- 3 methodology templates (PMBOK, Scrum, Hybrid)
- 12 phase templates
- Sample organizations, users, and projects

### Step 5: Start Development Server

```bash
composer dev
```

This starts **4 concurrent servers**:
- 🌐 Laravel application (http://localhost:8000)
- 🔄 Queue worker
- 📜 Log viewer (Pail)
- ⚡ Vite dev server (HMR)

---

## 🎯 First Login

### Default Admin Account

After seeding, you can log in with:

```
Email:    admin@samsic.fr
Password: password
```

⚠️ **IMPORTANT:** Change this password immediately in production!

### Create Your First User

If you didn't seed, create a user manually:

```bash
php artisan tinker
```

```php
$org = Organization::create([
    'name' => 'SAMSIC',
    'type' => 'internal',
    'status' => 'active'
]);

$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'organization_id' => $org->id,
    'email_verified_at' => now()
]);
```

---

## 📖 Next Steps

### 1. Explore the Dashboard

Navigate to http://localhost:8000 and:
- ✅ Log in with admin credentials
- ✅ Browse existing projects
- ✅ Explore organizations
- ✅ Check user roles and permissions

### 2. Create Your First Project

1. Go to **Projects → Create New**
2. Fill in project details:
   - Name, description
   - Start/end dates
   - Budget
   - Select methodology (PMBOK, Scrum, Hybrid)
3. Phases are **automatically created** from templates
4. Add team members
5. Create tasks under phases

### 3. Understand Multi-Tenancy

Try creating multiple organizations:

```bash
php artisan tinker
```

```php
// Create a client organization
$client = Organization::create([
    'name' => 'Acme Corp',
    'type' => 'client',
    'status' => 'active'
]);

// Create a user in that organization
$user = User::create([
    'name' => 'John Client',
    'email' => 'john@acme.com',
    'password' => bcrypt('password'),
    'organization_id' => $client->id,
    'email_verified_at' => now()
]);
```

Log in as `john@acme.com` and notice:
- ✅ Only sees Acme Corp data
- ❌ Cannot see SAMSIC data
- This is **multi-tenancy** in action!

### 4. Explore Phase Templates

```bash
php artisan tinker
```

```php
// List all methodology templates
MethodologyTemplate::with('phaseTemplates')->get();

// Create a project with PMBOK template
$project = Project::create([
    'name' => 'Test Project',
    'organization_id' => 1,
    'methodology_template_id' => 1, // PMBOK
    'status' => 'planning'
]);

// Phases are auto-created!
$project->phases; // Returns: Initiation, Planning, Execution, Monitoring, Closure
```

### 5. Test API Endpoints

Generate an API key:

```php
$apiKey = ApiKey::create([
    'organization_id' => 1,
    'type' => 'projects',
    'access_level' => 'write',
    'key' => Str::random(64),
    'is_active' => true
]);
```

Test the API:

```bash
curl -X GET http://localhost:8000/api/projects \
  -H "X-API-Key: your-api-key-here" \
  -H "Accept: application/json"
```

**Learn more:** [API Documentation](../03-API-REFERENCE/README.md)

---

## 🛠️ Development Workflow

### Running Individual Servers

Instead of `composer dev`, you can run servers individually:

```bash
# Laravel application server
php artisan serve

# Queue worker
php artisan queue:work

# Log viewer
php artisan pail

# Vite dev server
npm run dev
```

### Running Tests

```bash
composer test
# or
php artisan test
```

### Code Formatting

```bash
./vendor/bin/pint
```

### Database Management

```bash
# Create new migration
php artisan make:migration create_example_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh database (⚠️ destroys all data!)
php artisan migrate:fresh --seed
```

---

## 🐛 Troubleshooting

### Issue: "Class 'PDO' not found"

**Solution:** Install PHP PDO extension

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-sqlite3

# macOS (Homebrew)
brew install php@8.2
```

### Issue: "Permission denied" when running commands

**Solution:** Fix file permissions

```bash
chmod -R 755 storage bootstrap/cache
```

### Issue: "npm run dev" fails

**Solution:** Clear cache and reinstall

```bash
rm -rf node_modules package-lock.json
npm install
npm run dev
```

### Issue: Vite assets not loading

**Solution:** Build assets for production

```bash
npm run build
```

Or ensure `npm run dev` is running in development.

### Issue: "419 Page Expired" on form submission

**Solution:** Ensure CSRF token in forms

```blade
<form method="POST">
    @csrf
    <!-- form fields -->
</form>
```

---

## 📚 Useful Commands

### Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# List all routes
php artisan route:list

# List all database tables
php artisan db:show

# Generate IDE helper files (for autocomplete)
php artisan ide-helper:generate

# Interactive shell
php artisan tinker
```

---

## 🎓 Learning Path

Now that you're set up, continue learning:

1. **Architecture** - [Multi-Tenant Architecture](../01-ARCHITECTURE/multi-tenant/Multi-Tenant-Architecture.md)
2. **Features** - [Project Management](../02-FEATURES/project-management/Projects.md)
3. **API** - [API Reference](../03-API-REFERENCE/README.md)
4. **Development** - [Development Guide](../06-DEVELOPMENT/README.md)

---

## 🆘 Need Help?

- 📖 **Full Documentation:** Browse the `/docs` folder
- 🐛 **Troubleshooting:** [Troubleshooting Guide](../07-OPERATIONS/Troubleshooting.md)
- 💬 **Support:** Contact the development team

---

## ✅ Success Checklist

After completing this guide, you should have:

- [x] ✅ MDF Access installed and running
- [x] ✅ Database seeded with sample data
- [x] ✅ Admin account created and logged in
- [x] ✅ First project created with auto-generated phases
- [x] ✅ Understanding of multi-tenancy
- [x] ✅ Development servers running

**Congratulations! 🎉 You're ready to start using MDF Access!**

---

**Next:** [Project Overview](./Project-Overview.md) | [Architecture](../01-ARCHITECTURE/README.md)

**Last Updated:** November 2025
