# 📖 MDF Access - Project Overview

## What is MDF Access?

**MDF Access** (Multi-tenant Document Facility Access) is a comprehensive **project management platform** built on **Laravel 12** that implements **PMBOK (Project Management Body of Knowledge)** best practices while supporting **multi-tenant architecture** and **multi-organization collaboration**.

---

## 🎯 Core Purpose

MDF Access is designed to manage **complex, multi-stakeholder projects** across different organizations with:

1. **Complete data isolation** between tenant organizations
2. **Standardized PMBOK workflows** for project management
3. **Flexible permission system** for granular access control
4. **Multi-organization participation** in individual projects
5. **Field maintenance capabilities** for infrastructure/telecom sites

---

## 🏢 Target Users

### Organizations
- **Client Organizations** - Manage their own projects and teams
- **Partner Organizations** - Collaborate on shared projects

### User Roles
- **System Administrators** - Platform configuration and management
- **Portfolio Managers** - Strategic portfolio oversight
- **Program Managers** - Multi-project coordination
- **Project Managers** - Individual project execution
- **Team Members** - Task execution and collaboration
- **Field Technicians** - Field maintenance operations

---

## 🌟 Key Features

### 1. Multi-Tenant Architecture
- **Complete data isolation** per organization
- **Row-Level Security (RLS)** via Eloquent global scopes
- **Tenant-aware queries** automatic filtering
- **Cross-tenant collaboration** for multi-org projects

**Learn more:** [Multi-Tenant Architecture](../01-ARCHITECTURE/multi-tenant/Multi-Tenant-Architecture.md)

### 2. PMBOK Project Management

#### Project Hierarchy
```
Portfolio
  └── Program
      └── Project
          └── Phase (Initiation, Planning, Execution, Monitoring, Closure)
              └── WBS Element (Work Breakdown Structure)
                  └── Task
```

#### Supported Entities
- **Projects** - With budgets, timelines, and multi-org participation
- **Phases** - PMBOK phases with templates and hierarchy
- **Tasks** - Assignable work items with dependencies
- **Deliverables** - With approval workflows
- **Milestones** - Key project checkpoints
- **Risks** - Risk identification and mitigation
- **Issues** - Issue tracking and resolution
- **Change Requests** - Formal change management

**Learn more:** [PMBOK Implementation](../01-ARCHITECTURE/pmbok/Phase-Templates-Implementation.md)

### 3. Phase Templates

Pre-configured methodology templates:
- **PMBOK Waterfall** (5 phases)
- **Agile Scrum** (3 phases)
- **Hybrid PMBOK + Agile** (4 phases)

Templates support:
- ✅ Hierarchical sub-phases (unlimited nesting)
- ✅ Activities, deliverables, entry/exit criteria
- ✅ Automatic instantiation on project creation
- ✅ Organization-specific customization

**Learn more:** [Phase Templates](../01-ARCHITECTURE/pmbok/Phase-Templates-Implementation.md)

### 4. Multi-Organization Projects

Organizations participate in projects with specific roles:

| Role | Description | Responsibilities |
|------|-------------|------------------|
| **Sponsor** | Financial stakeholder | Budget approval, strategic oversight |
| **MOA** | Maître d'Ouvrage (Owner) | Requirements definition, acceptance |
| **MOE** | Maître d'Œuvre (Contractor) | Project execution, delivery |
| **Subcontractor** | Service provider | Specific task execution |

**Learn more:** [Multi-Organization Support](../01-ARCHITECTURE/multi-tenant/Multi-Organization-Support.md)

### 5. Permissions & Access Control

- **174 permissions** across all resources
- **29 pre-configured roles** (Global, Organization, Project, Task scopes)
- **Dynamic permission evaluation** based on context
- **Resource-action permission model** (flexible and extensible)

**Learn more:** [RBAC System](../01-ARCHITECTURE/permissions/RBAC-System.md)

### 6. Field Maintenance Module

Specialized module for managing telecom/infrastructure sites:
- **Site Management** - GSM towers, colocation facilities
- **Tenant Relationships** - Multiple telecom operators per site
- **Energy Sources** - Tracking power configurations
- **Maintenance Typologies** - Preventive, corrective, upgrade
- **Site History** - Complete audit trail

**Learn more:** [Field Maintenance](../02-FEATURES/field-maintenance/FM-Overview.md)

### 7. Excel Import/Export

Bulk data operations via Excel:
- **16 import classes** for all major entities
- **Template-based imports** with validation
- **API-driven updates** (Kizeo Forms integration)
- **Hierarchical data** support (phases, tasks, WBS)

**Learn more:** [Excel Operations](../04-WORKFLOWS/data-operations/Excel-Import.md)

### 8. API Integration

- **RESTful API** for all major operations
- **API Key authentication** with granular permissions
- **Odoo integration** (58 users, 66 projects, 9,626 tasks migrated)
- **Kizeo Forms integration** for field data collection

**Learn more:** [API Reference](../03-API-REFERENCE/README.md)

---

## 🏗️ Technical Architecture

### Backend
- **Framework:** Laravel 12 (PHP 8.2+)
- **ORM:** Eloquent with custom scopes and traits
- **Database:** SQLite (default), MySQL/PostgreSQL supported
- **Authentication:** Laravel Breeze + 2FA (Google Authenticator)

### Frontend
- **Template Engine:** Blade
- **CSS Framework:** Tailwind CSS 4.0
- **Build Tool:** Vite 7.0
- **HTTP Client:** Axios 1.11

### Key Packages
- **maatwebsite/excel** 3.1 - Excel import/export
- **phpoffice/phppresentation** 1.2 - Presentations
- **pragmarx/google2fa** 8.0 - Two-factor authentication

**Learn more:** [Technology Stack](./Technology-Stack.md)

---

## 📊 Project Statistics

### Database
- **57 tables** total
- **39 PMBOK tables** (projects, phases, tasks, etc.)
- **11 multi-tenant tables** (organizations, users, teams)
- **7 field maintenance tables** (sites, tenants, regions)

### Code
- **40+ Eloquent models**
- **50+ database migrations**
- **16 Excel import classes**
- **5 major seeders**
- **174 permissions**
- **29 roles**

### Test Data (Odoo Import)
- **58 users**
- **66 projects**
- **9,626 tasks**
- **27 organizations**

---

## 🚀 Development Status

### Completed Phases
- ✅ **Phase 0:** Architecture design
- ✅ **Phase 1:** Database schema and migrations
- ✅ **Phase 2:** Models and relationships
- ✅ **Phase 2b:** PMBOK phase templates

### Current Phase
- 🔄 **Phase 3:** Row-Level Security (RLS) implementation
- 🔄 **Phase 4:** API development

### Upcoming Phases
- 📅 **Phase 5:** UI/UX implementation
- 📅 **Phase 6:** Testing and quality assurance
- 📅 **Phase 7:** Deployment and documentation
- 📅 **Phase 8:** Training and handover

**Overall Progress:** 42%

**Learn more:** [Current Roadmap](../09-PROJECT-HISTORY/roadmap/Current-Status.md)

---

## 🎯 Use Cases

### 1. Construction Project Management
Manage multi-phase construction projects with:
- Multiple subcontractors
- Budget tracking per phase
- Deliverable approvals (plans, permits)
- Site inspections and quality control

### 2. IT Project Portfolio
Coordinate multiple IT projects across:
- Development teams
- QA teams
- Infrastructure teams
- External vendors

### 3. Telecom Infrastructure Maintenance
Track and maintain telecom sites with:
- Multiple tenant operators
- Preventive maintenance schedules
- Energy source management
- Site history and compliance

### 4. Consulting Services Delivery
Manage consulting engagements with:
- Client organizations (MOA)
- Consulting firm (MOE)
- Specialized subcontractors
- Deliverable-based milestones

---

## 🔄 Typical Workflow

1. **Portfolio Manager** creates a Portfolio for strategic initiatives
2. **Program Manager** creates Programs within the Portfolio
3. **Project Manager** creates a Project, selects PMBOK methodology
4. System **automatically creates phases** from templates
5. **Team members** are assigned to the project
6. **Other organizations** join as MOA, MOE, or Subcontractors
7. **Tasks are created** under phases/WBS elements
8. **Progress is tracked** through task completion
9. **Deliverables are submitted** and approved
10. **Project closes** after final phase completion

**Learn more:** [Project Lifecycle](../04-WORKFLOWS/project-lifecycle/Project-Creation.md)

---

## 🆚 Comparison with Other Tools

| Feature | MDF Access | Jira | MS Project | Asana |
|---------|-----------|------|------------|-------|
| Multi-tenant | ✅ Native | ❌ Limited | ❌ No | ⚠️ Workspaces |
| PMBOK Support | ✅ Full | ❌ No | ✅ Partial | ❌ No |
| Multi-org Projects | ✅ Yes | ❌ No | ⚠️ Manual | ❌ No |
| Phase Templates | ✅ Yes | ❌ No | ⚠️ Custom | ❌ No |
| Field Maintenance | ✅ Built-in | ❌ No | ❌ No | ❌ No |
| Excel Integration | ✅ Bi-directional | ⚠️ Export only | ✅ Yes | ⚠️ Export only |
| Custom Deployment | ✅ Self-hosted | ❌ Cloud only | ⚠️ Limited | ❌ Cloud only |

---

## 🎓 Learning Resources

- **Documentation:** You're reading it!
- **API Reference:** [API Documentation](../03-API-REFERENCE/README.md)
- **Video Tutorials:** Coming soon
- **Sample Data:** Included in seeders

---

## 📞 Support & Contact

- **Technical Issues:** See [Troubleshooting Guide](../07-OPERATIONS/Troubleshooting.md)
- **Feature Requests:** Contact development team
- **Security Issues:** Report immediately to administrators

---

**Next Steps:**
- 🚀 [Quick Start Guide](./Quick-Start-Guide.md) - Install and configure
- 🏗️ [Architecture Overview](../01-ARCHITECTURE/system-design/Overview.md) - Understand the design
- 💻 [Development Guide](../06-DEVELOPMENT/README.md) - Start developing

---

**Last Updated:** November 2025
**Version:** 1.0
**Status:** Active Development
