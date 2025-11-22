# 🔌 API Reference

Complete API documentation for MDF Access, including authentication, endpoints, and integrations.

---

## 📚 Table of Contents

1. [Authentication](#authentication) - API authentication methods
2. [Endpoints](#endpoints) - RESTful API endpoints
3. [Integration](#integration) - Third-party integrations

---

## 🔐 Authentication

API authentication, security, and access control.

### Documents

📄 **[Overview](./authentication/Overview.md)**
- Authentication methods
- Session-based authentication
- API key authentication
- Security best practices

📄 **[API Keys](./authentication/API-Keys.md)**
- API key types and access levels
- Creating and managing API keys
- Rate limiting
- Key rotation

📄 **[Two-Factor Auth](./authentication/Two-Factor-Auth.md)**
- Google Authenticator integration
- TOTP implementation
- QR code generation
- Recovery codes

📄 **[Email Verification](./authentication/Email-Verification.md)**
- Email verification workflow
- Verification tokens
- Resending verification emails

### Quick Reference

```php
// Create API key
$apiKey = ApiKey::create([
    'organization_id' => 1,
    'type' => 'projects',
    'access_level' => 'write',
    'key' => Str::random(64),
    'expires_at' => now()->addYear(),
    'is_active' => true
]);

// Use in API request
curl -X GET http://api.example.com/api/projects \
  -H "X-API-Key: your-api-key-here" \
  -H "Accept: application/json"

// Enable 2FA
$user->enableTwoFactorAuth();
$qrCode = $user->getTwoFactorQRCode();

// Verify 2FA code
$user->verifyTwoFactorCode($code);
```

---

## 🌐 Endpoints

RESTful API endpoints for all resources.

### Documents

📄 **[API Documentation](./endpoints/API-Documentation.md)**
- Complete API reference
- Request/response formats
- Error codes
- Examples

📄 **[Excel API](./endpoints/Excel-API.md)**
- Excel update endpoint
- Template download
- Kizeo Forms integration

📄 **[Tasks API](./endpoints/Tasks-API.md)**
- Task CRUD operations
- Subtask management
- Bulk operations

📄 **[Organizations API](./endpoints/Organizations-API.md)**
- Organization management
- Multi-org projects
- Role assignment

### API Endpoints Overview

#### Projects
```
GET     /api/projects              List projects
POST    /api/projects              Create project
GET     /api/projects/{id}         Get project details
PUT     /api/projects/{id}         Update project
DELETE  /api/projects/{id}         Delete project
```

#### Tasks
```
GET     /api/tasks                 List tasks
POST    /api/tasks                 Create task
GET     /api/tasks/{id}            Get task details
PUT     /api/tasks/{id}            Update task
DELETE  /api/tasks/{id}            Delete task
POST    /api/tasks/{id}/subtasks   Create subtask
```

#### Excel Operations
```
POST    /api/excel/update          Update Excel file
GET     /api/excel/download        Download template
POST    /api/excel/import          Import Excel data
```

### Response Format

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Project Name",
    "status": "active"
  },
  "meta": {
    "timestamp": "2025-11-22T10:30:00Z"
  }
}
```

### Error Format

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The name field is required.",
    "details": {
      "name": ["The name field is required."]
    }
  }
}
```

---

## 🔗 Integration

Third-party system integrations.

### Documents

📄 **[Odoo Integration](./integration/Odoo-Integration.md)**
- Odoo data extraction
- Migration mapping
- Import process
- 58 users, 66 projects, 9,626 tasks migrated

📄 **[Kizeo Forms](./integration/Kizeo-Forms.md)**
- Field data collection
- Real-time Excel updates
- API integration
- Webhook configuration

### Integration Examples

#### Odoo Import
```php
// Import users from Odoo
$importer = new OdooUserImporter();
$result = $importer->import($odooData);

// Import projects
$importer = new OdooProjectImporter();
$projects = $importer->import($odooProjects);

// Import tasks
$importer = new OdooTaskImporter();
$tasks = $importer->import($odooTasks);
```

#### Kizeo Forms Webhook
```php
// Receive data from Kizeo Forms
Route::post('/api/kizeo/webhook', function (Request $request) {
    $formData = $request->all();

    // Update Excel file with field data
    Excel::updateFromKizeoData($formData);

    return response()->json(['success' => true]);
});
```

---

## 🚀 Quick Start

### 1. Get an API Key

```bash
php artisan tinker
```

```php
$apiKey = ApiKey::create([
    'organization_id' => 1,
    'type' => 'projects',
    'access_level' => 'write',
    'key' => Str::random(64),
    'is_active' => true
]);

echo $apiKey->key;
```

### 2. Make Your First API Call

```bash
curl -X GET http://localhost:8000/api/projects \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Accept: application/json"
```

### 3. Create a Project via API

```bash
curl -X POST http://localhost:8000/api/projects \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "API Test Project",
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "total_budget": 100000,
    "methodology_template_id": 1,
    "status": "active"
  }'
```

---

## 📊 API Statistics

- **Total Endpoints:** 50+
- **Authentication Methods:** 2 (Session, API Key)
- **Supported Formats:** JSON
- **Rate Limiting:** 60 requests/minute per key
- **API Version:** v1

---

## 🔒 Security

### Rate Limiting
- **60 requests/minute** per API key
- **5 login attempts/minute** per IP
- Automatic throttling on abuse

### HTTPS
- **Required in production**
- SSL/TLS 1.2+ only
- HSTS headers enabled

### Input Validation
- All inputs validated
- SQL injection protection
- XSS protection
- CSRF tokens (web routes)

---

## 📖 Related Documentation

- **Authentication:** [RBAC System](../01-ARCHITECTURE/permissions/RBAC-System.md)
- **Features:** [API-Enabled Features](../02-FEATURES/README.md)
- **Workflows:** [API Integration Workflows](../04-WORKFLOWS/data-operations/Excel-Import.md)
- **Development:** [Controllers Guide](../06-DEVELOPMENT/Controllers-Guide.md)

---

**Last Updated:** November 2025
**API Version:** v1
**Base URL:** `/api`
