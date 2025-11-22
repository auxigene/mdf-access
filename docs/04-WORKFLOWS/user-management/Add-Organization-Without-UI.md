# Process 000: Add a New Organization Without UI Access

**Purpose:** Create new organizations programmatically during development when the user interface is unavailable or not yet implemented.

**Use Cases:**
- Development and testing
- Initial system setup
- Automated testing
- Seeding test data
- Setting up organizations before creating users

---

## Method 1: Using Tinker (Recommended for Quick Testing)

### Step 1: Access Laravel Tinker
```bash
php artisan tinker
```

### Step 2: Create a Basic Organization
```php
$org = \App\Models\Organization::create([
    'name' => 'Acme Corporation',
    'address' => '123 Business Street, City',
    'ville' => 'Paris',
    'status' => 'active',
]);
```

### Step 3: Add Contact Information (Optional)
```php
$org->updateContact([
    'email' => 'contact@acme.com',
    'phone' => '+33 1 23 45 67 89',
    'fax' => '+33 1 23 45 67 90',
    'website' => 'https://www.acme.com',
]);
```

### Step 4: Alternative - Create with Contact Info in One Step
```php
$org = \App\Models\Organization::create([
    'name' => 'Acme Corporation',
    'address' => '123 Business Street',
    'ville' => 'Paris',
    'contact_info' => [
        'email' => 'contact@acme.com',
        'phone' => '+33 1 23 45 67 89',
        'fax' => '+33 1 23 45 67 90',
        'website' => 'https://www.acme.com',
    ],
    'status' => 'active',
]);
```

### Step 5: Verify Organization Created
```php
echo "Organization ID: {$org->id}\n";
echo "Name: {$org->name}\n";
echo "Status: {$org->status}\n";
```

---

## Method 2: Using Database Seeder (Recommended for Reproducible Setup)

### Step 1: Create a Seeder File
```bash
php artisan make:seeder DevelopmentOrganizationsSeeder
```

### Step 2: Edit the Seeder File
Open `database/seeders/DevelopmentOrganizationsSeeder.php` and add:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;

class DevelopmentOrganizationsSeeder extends Seeder
{
    public function run(): void
    {
        // Check environment to prevent running in production
        if (app()->environment('production')) {
            $this->command->error('Cannot run in production!');
            return;
        }

        // Create Client Organization
        $client = Organization::create([
            'name' => 'Client Corp',
            'address' => '456 Client Avenue',
            'ville' => 'Lyon',
            'contact_info' => [
                'email' => 'info@clientcorp.com',
                'phone' => '+33 4 12 34 56 78',
                'website' => 'https://www.clientcorp.com',
            ],
            'status' => 'active',
        ]);

        $this->command->info("Created Client Organization: {$client->name} (ID: {$client->id})");

        // Create Partner Organization
        $partner = Organization::create([
            'name' => 'Partner Solutions',
            'address' => '789 Partner Street',
            'ville' => 'Marseille',
            'contact_info' => [
                'email' => 'contact@partner.com',
                'phone' => '+33 4 98 76 54 32',
                'website' => 'https://www.partner.com',
            ],
            'status' => 'active',
        ]);

        $this->command->info("Created Partner Organization: {$partner->name} (ID: {$partner->id})");

        // Create Internal Organization
        $internal = Organization::create([
            'name' => 'Internal Operations',
            'address' => '321 Internal Road',
            'ville' => 'Paris',
            'contact_info' => [
                'email' => 'internal@company.com',
                'phone' => '+33 1 11 22 33 44',
            ],
            'status' => 'active',
        ]);

        $this->command->info("Created Internal Organization: {$internal->name} (ID: {$internal->id})");
    }
}
```

### Step 3: Run the Seeder
```bash
php artisan db:seed --class=DevelopmentOrganizationsSeeder
```

---

## Method 3: Using Artisan Command (Recommended for Production-Like Setup)

### Step 1: Create Custom Command
```bash
php artisan make:command CreateOrganization
```

### Step 2: Edit the Command File
Open `app/Console/Commands/CreateOrganization.php` and modify:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use Illuminate\Support\Facades\Validator;

class CreateOrganization extends Command
{
    protected $signature = 'org:create
                            {name : The name of the organization}
                            {--address= : Organization address}
                            {--ville= : City}
                            {--email= : Contact email}
                            {--phone= : Contact phone}
                            {--fax= : Contact fax}
                            {--website= : Website URL}
                            {--status=active : Organization status (active, inactive, archived)}';

    protected $description = 'Create a new organization from the command line';

    public function handle()
    {
        $validator = Validator::make([
            'name' => $this->argument('name'),
            'status' => $this->option('status'),
        ], [
            'name' => 'required|string|max:255',
            'status' => 'in:active,inactive,archived',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $contactInfo = array_filter([
            'email' => $this->option('email'),
            'phone' => $this->option('phone'),
            'fax' => $this->option('fax'),
            'website' => $this->option('website'),
        ]);

        $organization = Organization::create([
            'name' => $this->argument('name'),
            'address' => $this->option('address'),
            'ville' => $this->option('ville'),
            'contact_info' => !empty($contactInfo) ? $contactInfo : null,
            'status' => $this->option('status'),
        ]);

        $this->info("Organization created successfully!");
        $this->info("ID: {$organization->id}");
        $this->info("Name: {$organization->name}");
        $this->info("Status: {$organization->status}");

        if ($organization->contact_info) {
            $this->info("Contact Info:");
            foreach ($organization->contact_info as $key => $value) {
                $this->info("  - {$key}: {$value}");
            }
        }

        return 0;
    }
}
```

### Step 3: Use the Command
```bash
# Create basic organization
php artisan org:create "Acme Corporation"

# Create organization with full details
php artisan org:create "Tech Solutions" \
  --address="123 Tech Street" \
  --ville="Paris" \
  --email="contact@techsolutions.com" \
  --phone="+33 1 23 45 67 89" \
  --website="https://www.techsolutions.com" \
  --status="active"

# Create inactive organization
php artisan org:create "Old Company" --status="inactive"
```

---

## Method 4: Using Factory (Recommended for Testing)

### Step 1: Check if Factory Exists
First, check if an Organization factory exists:
```bash
ls database/factories/ | grep Organization
```

### Step 2: Create Factory if Needed
If no factory exists:
```bash
php artisan make:factory OrganizationFactory
```

Edit `database/factories/OrganizationFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->streetAddress(),
            'ville' => $this->faker->city(),
            'contact_info' => [
                'email' => $this->faker->companyEmail(),
                'phone' => $this->faker->phoneNumber(),
                'website' => $this->faker->url(),
            ],
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
```

### Step 3: Use Factory in Tinker
```bash
php artisan tinker
```

```php
// Create single organization
$org = \App\Models\Organization::factory()->create();

// Create organization with specific name
$org = \App\Models\Organization::factory()->create([
    'name' => 'Specific Company Name',
]);

// Create multiple organizations
\App\Models\Organization::factory()->count(5)->create();

// Create inactive organization
$org = \App\Models\Organization::factory()->inactive()->create();

// Create archived organization
$org = \App\Models\Organization::factory()->archived()->create();
```

---

## Required and Optional Organization Fields

| Field | Required | Type | Description | Example |
|-------|----------|------|-------------|---------|
| `name` | Yes | String | Organization name | "Acme Corporation" |
| `address` | No | Text | Physical address | "123 Business Street, Suite 100" |
| `ville` | No | String | City name | "Paris" |
| `contact_info` | No | JSON | Contact details | `{"email": "...", "phone": "..."}` |
| `logo` | No | String | Logo file path | "logos/acme.png" |
| `status` | No | Enum | Organization status | `active` (default), `inactive`, `archived` |

### Contact Info Structure
The `contact_info` field accepts a JSON object with the following optional keys:
- `email` - Contact email address
- `phone` - Contact phone number
- `fax` - Contact fax number
- `website` - Organization website URL

---

## Organization Status Values

| Status | Description | Use Case |
|--------|-------------|----------|
| `active` | Organization is currently active | Default state for operational organizations |
| `inactive` | Temporarily inactive | Organizations on hold or suspended |
| `archived` | Permanently archived | Historical organizations no longer in business |

---

## Working with Contact Information

### Using Accessors (Read)
```php
$org = \App\Models\Organization::find(1);

// Get individual contact fields
echo $org->contact_email;
echo $org->contact_phone;
echo $org->contact_fax;
echo $org->contact_website;

// Get all formatted contact info
print_r($org->getFormattedContact());
```

### Using Mutators (Write)
```php
$org = \App\Models\Organization::find(1);

// Set individual contact fields
$org->contact_email = 'newemail@example.com';
$org->contact_phone = '+33 1 23 45 67 89';
$org->save();

// Update all contact info at once
$org->updateContact([
    'email' => 'info@example.com',
    'phone' => '+33 1 23 45 67 89',
    'fax' => '+33 1 23 45 67 90',
    'website' => 'https://www.example.com',
]);
```

---

## Common Operations

### List All Organizations
```php
// In Tinker
\App\Models\Organization::all(['id', 'name', 'status']);

// Only active organizations
\App\Models\Organization::active()->get(['id', 'name']);

// With contact info
\App\Models\Organization::all()->map(function($org) {
    return [
        'id' => $org->id,
        'name' => $org->name,
        'email' => $org->contact_email,
    ];
});
```

### Update Organization
```php
$org = \App\Models\Organization::find(1);
$org->update([
    'name' => 'New Organization Name',
    'status' => 'inactive',
]);
```

### Archive Organization
```php
$org = \App\Models\Organization::find(1);
$org->update(['status' => 'archived']);
```

### Soft Delete Organization
```php
$org = \App\Models\Organization::find(1);
$org->delete(); // Soft delete

// Restore soft-deleted organization
$org = \App\Models\Organization::withTrashed()->find(1);
$org->restore();

// Permanently delete
$org->forceDelete();
```

### Find Organization by Name
```php
$org = \App\Models\Organization::where('name', 'like', '%Acme%')->first();
```

---

## Troubleshooting

### Issue: "SQLSTATE[23000]: Integrity constraint violation"
**Solution:** This could be due to duplicate name or other constraint. Check existing organizations:
```php
\App\Models\Organization::where('name', 'Your Org Name')->exists();
```

### Issue: Contact info not saving correctly
**Solution:** Ensure contact_info is an array:
```php
// Correct
$org->contact_info = ['email' => 'test@example.com'];

// Or use the helper method
$org->updateContact(['email' => 'test@example.com']);
```

### Issue: Cannot find organization after creation
**Solution:** Check if it was soft-deleted:
```php
\App\Models\Organization::withTrashed()->where('name', 'Your Org')->get();
```

### Issue: Need to update contact info without overwriting
**Solution:** Use the merge method:
```php
$org = \App\Models\Organization::find(1);
$org->contact_info = array_merge($org->contact_info ?? [], [
    'email' => 'newemail@example.com'
]);
$org->save();

// Or use the helper
$org->updateContact(['email' => 'newemail@example.com']);
```

---

## Security Notes

⚠️ **Important Security Considerations:**

1. **Environment checks**: Use `app()->environment()` to prevent test data in production
2. **Validation**: Always validate organization names and contact information
3. **Data sanitization**: Sanitize address and contact fields to prevent injection
4. **Access control**: Ensure only authorized users can create organizations in production
5. **Audit logging**: Consider logging organization creation for audit trails

### Example Safe Seeder:
```php
public function run(): void
{
    if (app()->environment('production')) {
        $this->command->error('Cannot run development seeder in production!');
        return;
    }

    // Safe to create test organizations here
    Organization::create([...]);
}
```

---

## Integration with Users

After creating an organization, you can assign users to it:

```php
// Create organization first
$org = \App\Models\Organization::create([
    'name' => 'Tech Company',
    'status' => 'active',
]);

// Then create user with this organization
$user = \App\Models\User::create([
    'name' => 'John Doe',
    'email' => 'john@techcompany.com',
    'password' => \Hash::make('password123'),
    'organization_id' => $org->id,
    'email_verified_at' => now(),
]);

// Verify the relationship
echo $user->organization->name; // "Tech Company"
echo $org->users->count(); // 1
```

---

## Quick Reference

**Create organization via Tinker:**
```php
\App\Models\Organization::create(['name' => 'Company Name', 'ville' => 'Paris', 'status' => 'active']);
```

**Create with contact info:**
```php
\App\Models\Organization::create(['name' => 'Company', 'ville' => 'Paris', 'contact_info' => ['email' => 'info@company.com', 'phone' => '+33 1 23 45 67 89']]);
```

**List all active organizations:**
```php
\App\Models\Organization::active()->get(['id', 'name']);
```

**Update organization status:**
```php
\App\Models\Organization::find(1)->update(['status' => 'inactive']);
```

**Get organization with users count:**
```php
\App\Models\Organization::withCount('users')->find(1);
```

---

**Document Version:** 1.0
**Last Updated:** 2025-11-20
**Related Documentation:**
- `001-add-user-without-ui.md` - Creating users (requires organization to be created first)
- `ROUTES_ORGANIZATION.md` - Organization routes and API endpoints
- `app/Models/Organization.php` - Organization model reference
