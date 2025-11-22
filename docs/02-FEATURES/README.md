# ✨ Features Documentation

Complete guide to all features available in MDF Access.

---

## 📚 Table of Contents

1. [Project Management](#project-management) - PMBOK-based project management
2. [Team Management](#team-management) - Users, teams, organizations
3. [Field Maintenance](#field-maintenance) - Infrastructure site management
4. [Portfolios & Programs](#portfolios--programs) - Strategic project grouping

---

## 📊 Project Management

PMBOK-compliant project management features.

### Documents

📄 **[Projects](./project-management/Projects.md)**
- Create and manage projects
- Multi-organization participation
- Budget tracking
- Status management
- Methodology selection

📄 **[Phases](./project-management/Phases.md)**
- PMBOK phases (Initiation, Planning, Execution, Monitoring, Closure)
- Phase templates
- Hierarchical sub-phases
- Progress tracking

📄 **[Tasks](./project-management/Tasks.md)**
- Task creation and assignment
- Parent-child relationships (subtasks)
- Dependencies
- Progress and completion tracking

📄 **[WBS Elements](./project-management/WBS-Elements.md)**
- Work Breakdown Structure
- Hierarchical decomposition
- Task organization

📄 **[Deliverables](./project-management/Deliverables.md)**
- Deliverable tracking
- Approval workflows
- Document management

📄 **[Milestones](./project-management/Milestones.md)**
- Project checkpoints
- Milestone tracking
- Timeline management

📄 **[Risks](./project-management/Risks.md)**
- Risk identification
- Probability and impact assessment
- Mitigation strategies

📄 **[Issues](./project-management/Issues.md)**
- Issue tracking
- Severity levels
- Resolution workflow

📄 **[Change Requests](./project-management/Change-Requests.md)**
- Formal change management
- Approval process
- Impact assessment

### Quick Reference

```php
// Create a project
$project = Project::create([
    'name' => 'Website Redesign',
    'organization_id' => 1,
    'methodology_template_id' => 1, // PMBOK
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31',
    'total_budget' => 100000,
    'status' => 'active'
]);

// Phases auto-created from template
$project->phases; // Initiation, Planning, Execution, Monitoring, Closure

// Create a task
$task = Task::create([
    'name' => 'Design Homepage',
    'phase_id' => $phase->id,
    'assigned_to' => $user->id,
    'start_date' => '2025-02-01',
    'due_date' => '2025-02-15',
    'status' => 'pending'
]);

// Track progress
$task->update(['completion_percentage' => 50, 'status' => 'in_progress']);
```

---

## 👥 Team Management

User, team, and organization management.

### Documents

📄 **[Users](./team-management/Users.md)**
- User accounts and profiles
- Email verification
- Two-factor authentication
- Password management

📄 **[Teams](./team-management/Teams.md)**
- Project teams
- Team member roles
- Team assignments
- Role lifecycle

📄 **[Organizations](./team-management/Organizations.md)**
- Organization types (Internal, Client, Partner)
- Multi-tenant isolation
- Organization settings

📄 **[Resource Allocation](./team-management/Resource-Allocation.md)**
- Assign resources to tasks
- Workload tracking
- Capacity planning

### Quick Reference

```php
// Create organization
$org = Organization::create([
    'name' => 'Acme Corp',
    'type' => 'client',
    'status' => 'active'
]);

// Create user
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@acme.com',
    'password' => bcrypt('password'),
    'organization_id' => $org->id,
    'email_verified_at' => now()
]);

// Add to project team
$project->teamMembers()->attach($user->id, [
    'role' => 'developer',
    'start_date' => now(),
    'is_active' => true
]);

// Allocate to task
ResourceAllocation::create([
    'task_id' => $task->id,
    'user_id' => $user->id,
    'allocated_hours' => 40,
    'start_date' => '2025-02-01',
    'end_date' => '2025-02-15'
]);
```

---

## 🏗️ Field Maintenance

Infrastructure and telecom site management module.

### Documents

📄 **[FM Overview](./field-maintenance/FM-Overview.md)**
- Field Maintenance module introduction
- Use cases (GSM towers, colocation facilities)
- Site management workflow

📄 **[Sites Management](./field-maintenance/Sites-Management.md)**
- Site creation and tracking
- Site metadata (location, specifications)
- Site history and audit trail

📄 **[FM Excel Analysis](./field-maintenance/FM-Excel-Analysis.md)**
- Excel import for field maintenance
- Data mapping
- Validation rules

📄 **[FM Sites Strategy](./field-maintenance/FM-Sites-Strategy.md)**
- INWI sites strategy
- Multi-tenant site relationships
- Energy source management

### Quick Reference

```php
// Create FM site
$site = FmSite::create([
    'name' => 'GSM Tower - Casablanca 01',
    'site_code' => 'CAS-001',
    'region_id' => $region->id,
    'latitude' => 33.5731,
    'longitude' => -7.5898,
    'site_class_id' => $siteClass->id,
    'organization_id' => auth()->user()->organization_id
]);

// Add tenant to site
$site->tenants()->attach($tenant->id, [
    'rank' => 1,
    'is_primary' => true,
    'status' => 'active'
]);

// Track maintenance
FmMaintenanceTypology::create([
    'site_id' => $site->id,
    'type' => 'preventive',
    'scheduled_date' => '2025-03-01',
    'status' => 'scheduled'
]);
```

---

## 📈 Portfolios & Programs

Strategic project grouping and program management.

### Documents

📄 **[Portfolio Management](./portfolios-programs/Portfolio-Management.md)**
- Create and manage portfolios
- Strategic alignment
- Portfolio-level reporting

📄 **[Program Management](./portfolios-programs/Program-Management.md)**
- Program coordination
- Multi-project management
- Program-level benefits realization

### Quick Reference

```php
// Create portfolio
$portfolio = Portfolio::create([
    'name' => 'Digital Transformation 2025',
    'organization_id' => 1,
    'status' => 'active'
]);

// Create program
$program = Program::create([
    'name' => 'Website & Mobile Apps',
    'portfolio_id' => $portfolio->id,
    'organization_id' => 1,
    'status' => 'active'
]);

// Create project under program
$project = Project::create([
    'name' => 'Website Redesign',
    'program_id' => $program->id,
    'organization_id' => 1,
    'methodology_template_id' => 1,
    'status' => 'active'
]);

// Hierarchy: Portfolio → Program → Project
```

---

## 🎯 Feature Matrix

| Feature | PMBOK Compliance | Multi-Tenant | API Support | Excel Import/Export |
|---------|------------------|--------------|-------------|---------------------|
| Projects | ✅ Full | ✅ Yes | ✅ Yes | ✅ Yes |
| Phases | ✅ Full | ✅ Yes | ✅ Yes | ✅ Yes |
| Tasks | ✅ Full | ✅ Yes | ✅ Yes | ✅ Yes |
| Deliverables | ✅ Full | ✅ Yes | ✅ Yes | ✅ Yes |
| Milestones | ✅ Full | ✅ Yes | ✅ Yes | ✅ Yes |
| Risks | ✅ Full | ✅ Yes | ✅ Yes | ✅ Yes |
| Issues | ✅ Full | ✅ Yes | ✅ Yes | ✅ Yes |
| Change Requests | ✅ Full | ✅ Yes | ✅ Yes | ✅ Yes |
| Field Maintenance | ⚠️ Custom | ✅ Yes | ✅ Yes | ✅ Yes |
| Portfolios | ✅ Full | ✅ Yes | ✅ Yes | ⚠️ Partial |
| Programs | ✅ Full | ✅ Yes | ✅ Yes | ⚠️ Partial |

---

## 🆘 Related Documentation

- **Architecture:** [Multi-Tenant Architecture](../01-ARCHITECTURE/multi-tenant/Multi-Tenant-Architecture.md)
- **API Reference:** [API Endpoints](../03-API-REFERENCE/endpoints/API-Documentation.md)
- **Workflows:** [Project Lifecycle](../04-WORKFLOWS/project-lifecycle/Project-Creation.md)
- **Development:** [Models Guide](../06-DEVELOPMENT/Models-Guide.md)

---

**Last Updated:** November 2025
**Feature Count:** 50+ features across 4 major categories
