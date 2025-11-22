# 📚 Glossary

Key terms and concepts used throughout MDF Access documentation.

---

## A

### Action
In the permissions system, an **action** is an operation that can be performed on a resource (e.g., `view`, `create`, `edit`, `delete`, `approve`, `export`).

### API Key
A secure token used to authenticate API requests. API keys have **type** (e.g., `excel_update`, `projects`) and **access level** (e.g., `read`, `write`, `admin`).

---

## B

### Budget
Financial allocation for a project, including `total_budget`, `spent_budget`, and `remaining_budget`.

---

## C

### Change Request
A formal request to modify project scope, timeline, or budget. Requires approval workflow.

### Client Organization
An organization with `type = 'client'`. Clients are external organizations that sponsor or receive services from projects.

---

## D

### Deliverable
A tangible output of a project phase or task. Deliverables have **approval workflows** with statuses: `pending`, `approved`, `rejected`.

---

## E

### Eloquent
Laravel's **Object-Relational Mapping (ORM)** system for database interactions. Provides an elegant syntax for querying and manipulating data.

---

## F

### Field Maintenance (FM)
Specialized module for managing telecom/infrastructure sites, including sites, tenants, regions, and energy sources.

### FM Site
A physical location (e.g., GSM tower, colocation facility) tracked in the Field Maintenance module.

---

## G

### Global Scope
An Eloquent feature that automatically applies query constraints to all queries on a model. MDF Access uses `TenantScope` for multi-tenancy.

---

## I

### Internal Organization
An organization with `type = 'internal'`. SAMSIC is the internal organization that operates the platform and has **global visibility** across all tenants.

### Issue
A problem or obstacle encountered during project execution. Issues are tracked with severity, status, and resolution.

---

## M

### Methodology Template
A pre-configured project management methodology (e.g., PMBOK, Scrum, Hybrid) that defines phase templates.

### Milestone
A significant checkpoint in a project timeline, often marking the completion of a major deliverable or phase.

### MOA (Maître d'Ouvrage)
French term for **project owner**. The organization that defines requirements and accepts deliverables. In MDF Access, an organization can have the `moa` role on a project.

### MOE (Maître d'Œuvre)
French term for **prime contractor**. The organization responsible for project execution and delivery. In MDF Access, an organization can have the `moe` role on a project.

### Multi-Organization Project
A project involving **multiple organizations** in different roles (Sponsor, MOA, MOE, Subcontractor). Managed via the `project_organizations` pivot table.

### Multi-Tenancy
An architecture pattern where a **single application instance** serves **multiple tenants** (organizations) with **complete data isolation**. Each tenant only sees their own data.

---

## O

### Organization
A tenant in the multi-tenant system. Organizations can be:
- **Internal** (SAMSIC - platform operator)
- **Client** (external organizations)
- **Partner** (service providers)

### Organization Type
The classification of an organization: `internal`, `client`, or `partner`.

---

## P

### Partner Organization
An organization with `type = 'partner'`. Partners are external organizations that provide services or collaborate on projects.

### Permission
A specific capability to perform an **action** on a **resource** within a **scope** (e.g., `projects.edit` at `organization` scope).

### Phase
A PMBOK-defined stage of a project (e.g., Initiation, Planning, Execution, Monitoring, Closure). Phases can have **parent-child relationships** for hierarchical structures.

### Phase Template
A pre-configured phase definition with metadata (activities, deliverables, entry/exit criteria) that can be instantiated in projects.

### PMBOK
**Project Management Body of Knowledge** - A set of standard terminology and guidelines published by the Project Management Institute (PMI). MDF Access implements PMBOK best practices.

### Portfolio
The highest level of the PMBOK hierarchy. A **strategic grouping** of programs and projects aligned with business objectives.

### Program
A collection of **related projects** managed in a coordinated manner to achieve strategic benefits. Programs belong to portfolios.

### Project
A **temporary endeavor** with defined start/end dates to create a unique product, service, or result. Projects belong to programs or portfolios.

### Project Team
The collection of **users** assigned to a project with specific **roles** (e.g., Project Manager, Team Lead, Developer).

---

## R

### RBAC (Role-Based Access Control)
An authorization model where **permissions** are assigned to **roles**, and **roles** are assigned to **users**.

### Resource
In the permissions system, a **resource** is an entity that can be acted upon (e.g., `projects`, `tasks`, `budgets`, `deliverables`).

### Risk
A potential event that could negatively impact a project. Risks are tracked with probability, impact, and mitigation strategies.

### Role
A collection of **permissions**. Examples: `project_manager`, `team_member`, `viewer`. Roles can have different **scopes** (global, organization, project, task).

### Row-Level Security (RLS)
A security model that restricts database access at the **row level** based on user attributes. In MDF Access, implemented via Eloquent Global Scopes.

---

## S

### SAMSIC
The **internal organization** that operates the MDF Access platform. SAMSIC users have **global visibility** and can see all organizations' data.

### Scope (Permission)
The level at which a permission applies:
- **Global** - Entire platform
- **Organization** - Within a single organization
- **Project** - Within a single project
- **Task** - Within a single task
- **WBS Element** - Within a single WBS element

### Seeder
A Laravel class that populates the database with **initial or test data**. MDF Access has seeders for permissions, roles, methodologies, and templates.

### Sponsor
An organization or individual providing **financial support** for a project. In MDF Access, an organization can have the `sponsor` role on a project.

### Subcontractor
An organization providing **specialized services** as part of a larger project. In MDF Access, an organization can have the `subcontractor` role on a project.

---

## T

### Task
A unit of work within a project. Tasks can:
- Be assigned to users
- Have dependencies
- Have parent-child relationships (subtasks)
- Belong to phases or WBS elements

### Tenant
In multi-tenancy, a **tenant** is an organization that uses the platform. Each tenant's data is **isolated** from other tenants.

### TenantScope
An Eloquent Global Scope that automatically filters queries to show only data belonging to the authenticated user's organization.

### TenantScoped Trait
A reusable Eloquent trait that applies `TenantScope` to a model, making it tenant-aware.

### Two-Factor Authentication (2FA)
An additional security layer requiring a **second form of verification** (typically a TOTP code from Google Authenticator) beyond username/password.

---

## W

### WBS (Work Breakdown Structure)
A **hierarchical decomposition** of project work into smaller, manageable components. WBS elements can have parent-child relationships.

### WBS Element
A node in the Work Breakdown Structure. Tasks are assigned to WBS elements for better organization.

---

## Acronyms & Abbreviations

| Acronym | Full Form | Meaning |
|---------|-----------|---------|
| **API** | Application Programming Interface | Interface for programmatic access |
| **CRUD** | Create, Read, Update, Delete | Basic data operations |
| **CSV** | Comma-Separated Values | Text file format for tabular data |
| **FM** | Field Maintenance | Infrastructure/site management module |
| **HMR** | Hot Module Replacement | Live reload without full page refresh |
| **HTTP** | Hypertext Transfer Protocol | Web communication protocol |
| **JSON** | JavaScript Object Notation | Data interchange format |
| **MDF** | Multi-tenant Document Facility | Platform name |
| **MOA** | Maître d'Ouvrage | Project owner (French term) |
| **MOE** | Maître d'Œuvre | Prime contractor (French term) |
| **ORM** | Object-Relational Mapping | Database abstraction layer |
| **PMBOK** | Project Management Body of Knowledge | PMI standards |
| **RBAC** | Role-Based Access Control | Authorization model |
| **REST** | Representational State Transfer | API architectural style |
| **RLS** | Row-Level Security | Database security model |
| **SQL** | Structured Query Language | Database query language |
| **TOTP** | Time-based One-Time Password | 2FA algorithm |
| **UI** | User Interface | Visual interface |
| **UX** | User Experience | User interaction design |
| **WBS** | Work Breakdown Structure | Project decomposition |

---

## PMBOK-Specific Terms

### Process Groups (PMBOK)
1. **Initiation** - Define and authorize the project
2. **Planning** - Establish scope, objectives, and procedures
3. **Execution** - Complete the work defined in the plan
4. **Monitoring & Controlling** - Track, review, and regulate progress
5. **Closure** - Finalize all activities and formally close the project

### Knowledge Areas (PMBOK)
1. Integration Management
2. Scope Management
3. Schedule Management
4. Cost Management
5. Quality Management
6. Resource Management
7. Communications Management
8. Risk Management
9. Procurement Management
10. Stakeholder Management

---

## Status Values

### Project Status
- `draft` - Being planned
- `active` - Currently executing
- `on_hold` - Temporarily paused
- `completed` - Successfully finished
- `cancelled` - Terminated before completion

### Task Status
- `pending` - Not started
- `in_progress` - Currently being worked on
- `completed` - Finished
- `blocked` - Waiting on dependency

### Deliverable Status
- `pending` - Awaiting approval
- `approved` - Accepted
- `rejected` - Not accepted

### Organization Status
- `active` - Operational
- `inactive` - Disabled
- `suspended` - Temporarily disabled

---

## Related Documentation

- **Architecture:** [Multi-Tenant Architecture](../01-ARCHITECTURE/multi-tenant/Multi-Tenant-Architecture.md)
- **Permissions:** [RBAC System](../01-ARCHITECTURE/permissions/RBAC-System.md)
- **PMBOK:** [Phase Templates](../01-ARCHITECTURE/pmbok/Phase-Templates-Implementation.md)
- **Features:** [Project Management](../02-FEATURES/project-management/Projects.md)

---

**Last Updated:** November 2025
**Version:** 1.0
