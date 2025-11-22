# 🏗️ Architecture Documentation

Complete architectural documentation for the MDF Access multi-tenant PMBOK project management platform.

---

## 📚 Table of Contents

1. [System Design](#system-design) - Overall system architecture
2. [Multi-Tenant](#multi-tenant) - Multi-tenancy implementation
3. [Permissions](#permissions) - RBAC and access control
4. [PMBOK](#pmbok) - PMBOK implementation details
5. [Evolution](#evolution) - Architectural changes and evolution

---

## 🎯 System Design

High-level architecture, database schema, and system diagrams.

### Documents

📄 **[Overview](./system-design/Overview.md)**
- System architecture overview
- Component diagram
- Technology stack
- Design patterns

📄 **[Database Schema](./system-design/Database-Schema.md)**
- Entity-relationship diagrams
- Table structures
- Relationships and constraints
- Indexing strategy

📄 **[Architecture Diagrams](./system-design/Architecture-Diagrams.md)**
- Component diagrams
- Sequence diagrams
- Deployment diagrams

---

## 🏢 Multi-Tenant

Multi-tenant architecture with complete data isolation between organizations.

### Documents

📄 **[Multi-Tenant Architecture](./multi-tenant/Multi-Tenant-Architecture.md)**
- Complete multi-tenant implementation
- Row-Level Security (RLS) design
- TenantScope global scope
- Tenant isolation mechanisms
- 174 permissions and 29 roles

📄 **[Multi-Organization Support](./multi-tenant/Multi-Organization-Support.md)**
- Project organizations table
- Organization roles (Sponsor, MOA, MOE, Subcontractor)
- Multi-org project participation
- Business rules and validation

📄 **[Row-Level Security](./multi-tenant/Row-Level-Security.md)**
- RLS implementation details
- TenantScoped trait
- Query filtering mechanism
- Bypass rules for SAMSIC users

📄 **[Tenant Scoping](./multi-tenant/Tenant-Scoping.md)**
- How tenant scoping works
- Eloquent global scopes
- Multi-tenant queries
- Best practices

### Key Concepts

```php
// Automatic tenant filtering via TenantScope
class Project extends Model {
    use TenantScoped; // Automatically filters by organization_id
}

// All queries are tenant-scoped
Project::all(); // Only returns projects from user's organization

// SAMSIC internal users bypass scoping
if (auth()->user()->isSamsicInternal()) {
    // Can see all organizations' data
}
```

---

## 🔐 Permissions

Role-Based Access Control (RBAC) with flexible resource-action model.

### Documents

📄 **[RBAC System](./permissions/RBAC-System.md)**
- 174 permissions defined
- 29 pre-configured roles
- Permission scopes (global, organization, project, task)
- Role assignment logic

📄 **[Permissions Evolution](./permissions/Permissions-Evolution.md)**
- Evolution towards dynamic permissions
- Resource-action matrix
- Elimination of invalid permissions
- ~50% reduction in absurd permissions

📄 **[Permission Usage Examples](./permissions/Permission-Usage-Examples.md)**
- Real-world permission examples
- Code snippets for authorization
- Policy implementation
- Blade template conditionals

### Key Concepts

#### Permission Structure
```
resource.action@scope

Examples:
- projects.view@organization
- tasks.edit@project
- budgets.approve@global
```

#### Permission Scopes
- **Global** - System-wide (admin-level)
- **Organization** - Within user's organization
- **Project** - Within specific project
- **Task** - Within specific task
- **WBS Element** - Within specific WBS element

#### Checking Permissions
```php
// In controller
if (!$user->hasPermissionTo('projects.edit', $project)) {
    abort(403);
}

// In Blade template
@can('edit', $project)
    <button>Edit Project</button>
@endcan

// Using policy
Gate::authorize('update', $project);
```

---

## 📊 PMBOK

PMBOK-compliant project management implementation.

### Documents

📄 **[PMBOK Implementation](./pmbok/PMBOK-Implementation.md)**
- PMBOK process groups
- Knowledge areas implementation
- Portfolio → Program → Project hierarchy
- Best practices

📄 **[Phase Templates Implementation](./pmbok/Phase-Templates-Implementation.md)**
- 3 methodology templates (PMBOK, Scrum, Hybrid)
- 12 phase templates
- Hierarchical phases (parent-child)
- Automatic instantiation
- Multi-tenant template support

📄 **[Methodology Templates](./pmbok/Methodology-Templates.md)**
- PMBOK Waterfall methodology
- Agile Scrum methodology
- Hybrid PMBOK + Agile methodology
- Creating custom methodologies

📄 **[Project Hierarchy](./pmbok/Project-Hierarchy.md)**
- Portfolio management
- Program coordination
- Project organization
- Phase structure
- WBS decomposition

### Key Concepts

#### PMBOK Hierarchy
```
Portfolio (Strategic grouping)
  └── Program (Related projects)
      └── Project (Single initiative)
          └── Phase (Process group)
              └── WBS Element (Decomposition)
                  └── Task (Work item)
```

#### PMBOK Process Groups
1. **Initiation** - Define and authorize
2. **Planning** - Establish scope and procedures
3. **Execution** - Complete the work
4. **Monitoring & Controlling** - Track progress
5. **Closure** - Finalize and close

#### Phase Templates
```php
// Methodologies available
MethodologyTemplate::all();
// Returns: PMBOK, Scrum, Hybrid

// Create project with PMBOK methodology
$project = Project::create([
    'name' => 'New Project',
    'methodology_template_id' => 1, // PMBOK
    // ...
]);

// Phases auto-created from template
$project->phases;
// Returns: Initiation, Planning, Execution, Monitoring, Closure
```

---

## 🔄 Evolution

Architectural changes, impact analysis, and evolution tracking.

### Documents

📄 **[Architecture Changes](./evolution/Architecture-Changes.md)**
- Migration to pure multi-tenant architecture
- Breaking changes
- Migration strategy
- Backward compatibility

📄 **[Impact Analysis](./evolution/Impact-Analysis.md)**
- Impact of architectural changes
- Affected components
- Migration steps
- Risk assessment

### Key Changes

| Change | Impact | Status |
|--------|--------|--------|
| Pure multi-tenant architecture | All models tenant-scoped | ✅ Completed |
| Flexible permissions system | Resource-action model | ✅ Completed |
| Phase templates | Hierarchical phases | ✅ Completed |
| Multi-organization projects | `project_organizations` table | ✅ Completed |

---

## 🎯 Architecture Principles

### 1. Multi-Tenancy First
- **Complete data isolation** between organizations
- **No cross-tenant data leaks** via global scopes
- **SAMSIC exception** for platform management

### 2. PMBOK Compliance
- Follow **PMBOK 6th Edition** standards
- Implement all **5 process groups**
- Support **10 knowledge areas**

### 3. Flexible Permissions
- **Resource-action model** for extensibility
- **Scope-based permissions** for granularity
- **Policy-driven authorization** for maintainability

### 4. Separation of Concerns
- **Models** - Data access layer
- **Services** - Business logic
- **Controllers** - HTTP request handling
- **Policies** - Authorization logic

### 5. Convention Over Configuration
- Laravel conventions
- RESTful routing
- Eloquent naming conventions

---

## 📊 Architecture Statistics

### Database
- **57 total tables**
- **39 PMBOK tables**
- **11 multi-tenant tables**
- **7 field maintenance tables**

### Code Organization
- **40+ Eloquent models**
- **50+ migrations**
- **3 core services** (PhaseTemplateService, FmImportService)
- **4 custom traits** (TenantScoped, HasPermissions)
- **2 global scopes** (TenantScope)

### Permissions
- **174 permissions**
- **29 roles**
- **5 scopes** (global, organization, project, task, wbs_element)

---

## 🔍 Quick Reference

### Multi-Tenant Queries
```php
// Automatic filtering by organization
$projects = Project::all(); // Only user's org

// Bypass for SAMSIC users
$projects = Project::withoutGlobalScope(TenantScope::class)->get();
```

### Permission Checks
```php
// Check permission
$user->hasPermissionTo('projects.edit', $project);

// Authorize or abort
Gate::authorize('update', $project);

// In Blade
@can('edit', $project)
    <!-- Show edit button -->
@endcan
```

### Phase Templates
```php
// Get methodology templates
$methodologies = MethodologyTemplate::with('phaseTemplates')->get();

// Instantiate phases from template
$project->instantiatePhasesFromTemplate($methodologyId);
```

---

## 🆘 Related Documentation

- **Getting Started:** [Quick Start Guide](../00-GETTING-STARTED/Quick-Start-Guide.md)
- **Features:** [Features Overview](../02-FEATURES/README.md)
- **API Reference:** [API Documentation](../03-API-REFERENCE/README.md)
- **Development:** [Development Guide](../06-DEVELOPMENT/README.md)

---

**Last Updated:** November 2025
**Architecture Version:** 2.0 (Pure Multi-Tenant)
