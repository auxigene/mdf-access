# 🔄 Workflows Documentation

Step-by-step workflows for common tasks and operations in MDF Access.

---

## 📚 Table of Contents

1. [User Management](#user-management) - Creating and managing users
2. [Project Lifecycle](#project-lifecycle) - Project creation and management workflows
3. [Data Operations](#data-operations) - Import, export, and bulk operations

---

## 👥 User Management

User and organization management workflows.

### Documents

📄 **[User Creation](./user-management/User-Creation.md)**
- Complete user creation workflow
- Email verification process
- Role assignment
- 2FA setup

📄 **[Add Organization Without UI](./user-management/Add-Organization-Without-UI.md)**
- Create organizations via CLI
- Database direct access
- Bulk organization creation

📄 **[Add User Without UI](./user-management/Add-User-Without-UI.md)**
- Create users via artisan commands
- Tinker-based user creation
- Bulk user import

📄 **[Processes README](./user-management/Processes-README.md)**
- Overview of management processes
- Common administrative tasks

### Quick Workflows

#### Create Organization + Admin User

```bash
php artisan tinker
```

```php
// 1. Create organization
$org = Organization::create([
    'name' => 'New Company Ltd',
    'type' => 'client',
    'status' => 'active'
]);

// 2. Create admin user for organization
$user = User::create([
    'name' => 'Jane Admin',
    'email' => 'admin@newcompany.com',
    'password' => bcrypt('SecurePass123!'),
    'organization_id' => $org->id,
    'email_verified_at' => now()
]);

// 3. Assign admin role
$adminRole = Role::where('slug', 'organization_admin')->first();
$user->roles()->attach($adminRole);

echo "Organization and admin user created successfully!";
```

---

## 📊 Project Lifecycle

Complete project lifecycle workflows from creation to closure.

### Documents

📄 **[Project Creation](./project-lifecycle/Project-Creation.md)**
- Step-by-step project creation
- Methodology selection
- Automatic phase instantiation
- Team assignment

📄 **[Phase Management](./project-lifecycle/Phase-Management.md)**
- Managing PMBOK phases
- Creating sub-phases
- Phase transitions
- Progress tracking

📄 **[Task Assignment](./project-lifecycle/Task-Assignment.md)**
- Creating and assigning tasks
- Setting dependencies
- Resource allocation
- Progress tracking

📄 **[Project Closure](./project-lifecycle/Project-Closure.md)**
- Closure checklist
- Final deliverables
- Lessons learned
- Archive process

### Quick Workflows

#### Create Project with Full Setup

```php
// 1. Create project
$project = Project::create([
    'name' => 'Website Redesign 2025',
    'organization_id' => auth()->user()->organization_id,
    'methodology_template_id' => 1, // PMBOK
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31',
    'total_budget' => 150000,
    'status' => 'planning'
]);

// 2. Phases are auto-created from template
// Initiation, Planning, Execution, Monitoring, Closure

// 3. Add multi-org participation
$project->organizations()->attach($clientOrg->id, [
    'role' => 'sponsor'
]);
$project->organizations()->attach($contractorOrg->id, [
    'role' => 'moe'
]);

// 4. Add team members
$project->teamMembers()->attach([
    $pm->id => ['role' => 'project_manager', 'is_primary' => true],
    $dev1->id => ['role' => 'developer'],
    $dev2->id => ['role' => 'developer'],
    $qa->id => ['role' => 'qa_engineer']
]);

// 5. Create first tasks
$planningPhase = $project->phases()->where('name', 'Planning')->first();
Task::create([
    'name' => 'Create project charter',
    'phase_id' => $planningPhase->id,
    'assigned_to' => $pm->id,
    'start_date' => '2025-01-05',
    'due_date' => '2025-01-10',
    'status' => 'pending'
]);
```

---

## 📥 Data Operations

Bulk import, export, and data management workflows.

### Documents

📄 **[Excel Import](./data-operations/Excel-Import.md)**
- Excel import setup
- Data validation
- Bulk project/task import
- Error handling

📄 **[Excel Templates](./data-operations/Excel-Templates.md)**
- Template structure
- Column mapping
- Format requirements
- Examples

📄 **[Data Export](./data-operations/Data-Export.md)**
- Exporting project data
- Custom reports
- Export formats

### Quick Workflows

#### Import Projects from Excel

```php
use App\Imports\ProjectsImport;
use Maatwebsite\Excel\Facades\Excel;

// 1. Upload Excel file
$file = $request->file('excel_file');

// 2. Validate and import
Excel::import(new ProjectsImport, $file);

// 3. Import includes:
//    - Project details
//    - Phases
//    - Tasks
//    - Team assignments
//    - Budgets
```

#### Export Project Data

```php
use App\Exports\ProjectsExport;
use Maatwebsite\Excel\Facades\Excel;

// Export all projects for organization
return Excel::download(
    new ProjectsExport(auth()->user()->organization_id),
    'projects.xlsx'
);

// Export specific project with all data
return Excel::download(
    new ProjectDetailExport($projectId),
    "project_{$projectId}.xlsx"
);
```

---

## 🔄 Common Workflows

### 1. Onboard New Client Organization

```
1. Create organization (type: client)
2. Create admin user for organization
3. Send welcome email with login credentials
4. User verifies email
5. User sets up 2FA (optional)
6. Admin assigns appropriate roles
7. User creates first project
```

### 2. Start New Project

```
1. Project Manager creates project
2. Selects PMBOK/Scrum/Hybrid methodology
3. System auto-creates phases from template
4. PM adds team members with roles
5. PM adds other organizations (MOA, MOE, Subcontractors)
6. PM creates initial tasks in Planning phase
7. Team members start working on assigned tasks
```

### 3. Import Historical Data

```
1. Download Excel template
2. Fill template with historical data
3. Validate data format
4. Upload Excel file
5. System validates and imports
6. Review import log
7. Fix any errors and re-import if needed
```

### 4. Close Completed Project

```
1. Ensure all tasks are completed (100%)
2. Submit final deliverables
3. Get client approval on deliverables
4. Close all open issues and risks
5. Archive project documents
6. Conduct lessons learned session
7. Update project status to 'completed'
8. Generate final report
```

---

## ⚙️ Workflow Automation

### Available Automations

| Automation | Trigger | Action |
|------------|---------|--------|
| Phase Creation | Project created | Auto-create phases from template |
| Task Notification | Task assigned | Email to assignee |
| Deliverable Approval | Deliverable submitted | Email to approver |
| Milestone Alert | Milestone approaching | Email to PM |
| Budget Alert | Budget threshold reached | Email to PM & Sponsor |

### Setting Up Automation

```php
// In EventServiceProvider
protected $listen = [
    ProjectCreated::class => [
        InstantiatePhasesFromTemplate::class,
        NotifyProjectTeam::class,
    ],
    TaskAssigned::class => [
        SendTaskNotification::class,
    ],
    DeliverableSubmitted::class => [
        SendApprovalRequest::class,
    ],
];
```

---

## 📊 Workflow Templates

### Project Templates

```
1. IT Project Template
   - Requirements gathering phase
   - Design phase
   - Development phase
   - Testing phase
   - Deployment phase

2. Construction Project Template
   - Feasibility study
   - Design & planning
   - Permitting
   - Construction
   - Handover

3. Consulting Engagement Template
   - Discovery
   - Analysis
   - Recommendations
   - Implementation support
   - Final report
```

---

## 🆘 Related Documentation

- **Features:** [Project Management](../02-FEATURES/project-management/Projects.md)
- **API Reference:** [API Endpoints](../03-API-REFERENCE/endpoints/API-Documentation.md)
- **Development:** [Controllers Guide](../06-DEVELOPMENT/Controllers-Guide.md)
- **Operations:** [Platform Operations](../07-OPERATIONS/Platform-Operations.md)

---

**Last Updated:** November 2025
**Total Workflows:** 15+
