# 🚀 Getting Started with MDF Access

Welcome to **MDF Access** - a comprehensive Multi-Tenant PMBOK Project Management Platform built with Laravel 12.

This section will help you quickly understand and start working with the MDF Access platform.

---

## 📚 Table of Contents

1. [Quick Start Guide](./Quick-Start-Guide.md) - Get up and running in 5 minutes
2. [Project Overview](./Project-Overview.md) - Understand what MDF Access is and what it does
3. [Technology Stack](./Technology-Stack.md) - Technologies, frameworks, and tools used
4. [Glossary](./Glossary.md) - Key terms and concepts

---

## 🎯 What is MDF Access?

**MDF Access** is a **multi-tenant project management platform** designed to manage complex projects following **PMBOK (Project Management Body of Knowledge)** standards. It supports:

- ✅ **Multi-tenant architecture** with complete data isolation
- ✅ **PMBOK-compliant project management** (Portfolios → Programs → Projects → Phases → Tasks)
- ✅ **Flexible permissions system** with 174 permissions and 29 roles
- ✅ **Multi-organization project participation** (MOA, MOE, Sponsors, Subcontractors)
- ✅ **Phase templates** for PMBOK, Scrum, and Hybrid methodologies
- ✅ **Field maintenance module** for telecom/infrastructure sites
- ✅ **Excel import/export** for bulk data operations
- ✅ **API integrations** with Odoo, Kizeo Forms, and more

---

## 🏃 Quick Links

### For New Users
- 📖 [Project Overview](./Project-Overview.md) - Start here to understand the platform
- 🚀 [Quick Start Guide](./Quick-Start-Guide.md) - Installation and first steps
- 📚 [Glossary](./Glossary.md) - Learn key terminology

### For Developers
- 🏗️ [Architecture Overview](../01-ARCHITECTURE/system-design/Overview.md)
- 💻 [Development Guide](../06-DEVELOPMENT/README.md)
- 🧪 [Testing Guide](../06-DEVELOPMENT/Testing-Guide.md)

### For Administrators
- 🚀 [Deployment Guide](../05-DEPLOYMENT/README.md)
- 👥 [User Management](../04-WORKFLOWS/user-management/User-Creation.md)
- 🔧 [Platform Operations](../07-OPERATIONS/Platform-Operations.md)

### For Project Managers
- 📊 [Project Management Features](../02-FEATURES/project-management/Projects.md)
- 👥 [Team Management](../02-FEATURES/team-management/Teams.md)
- 📈 [Portfolio & Program Management](../02-FEATURES/portfolios-programs/Portfolio-Management.md)

---

## 🎓 Learning Path

### Beginner Path (0-2 hours)
1. Read the [Project Overview](./Project-Overview.md)
2. Review the [Technology Stack](./Technology-Stack.md)
3. Follow the [Quick Start Guide](./Quick-Start-Guide.md)
4. Explore the [Glossary](./Glossary.md)

### Intermediate Path (2-8 hours)
1. Understand the [Multi-Tenant Architecture](../01-ARCHITECTURE/multi-tenant/Multi-Tenant-Architecture.md)
2. Learn about [RBAC Permissions](../01-ARCHITECTURE/permissions/RBAC-System.md)
3. Explore [PMBOK Implementation](../01-ARCHITECTURE/pmbok/Phase-Templates-Implementation.md)
4. Review [API Documentation](../03-API-REFERENCE/README.md)

### Advanced Path (8+ hours)
1. Deep dive into [System Architecture](../01-ARCHITECTURE/system-design/Overview.md)
2. Study [Development Guides](../06-DEVELOPMENT/README.md)
3. Learn [Deployment Strategies](../05-DEPLOYMENT/README.md)
4. Review [Migration Guides](../08-MIGRATION/README.md)

---

## 💡 Key Concepts

### Multi-Tenancy
Every organization has **complete data isolation**. Users can only see and manage data within their organization (except SAMSIC internal users who have global visibility).

### PMBOK Hierarchy
```
Portfolio (strategic grouping)
  └── Program (related projects)
      └── Project (single initiative)
          └── Phase (PMBOK phases: Initiation, Planning, Execution, Monitoring, Closure)
              └── Task (work items)
```

### Multi-Organization Projects
A single project can involve **multiple organizations** in different roles:
- **Sponsor** (financial stakeholder)
- **MOA** (Maître d'Ouvrage - project owner)
- **MOE** (Maître d'Œuvre - primary contractor)
- **Subcontractor** (service provider)

### Permissions System
- **174 permissions** organized by resource and action
- **29 roles** (Global, Organization, Project, Task scopes)
- Dynamic permission assignment based on context

---

## 🆘 Need Help?

- 📖 **Documentation:** You're in the right place! Browse the sections above
- 🐛 **Issues:** Check the [Troubleshooting Guide](../07-OPERATIONS/Troubleshooting.md)
- 💬 **Support:** Contact the development team

---

## 📊 Project Status

- **Version:** 1.0
- **Progression:** 42%
- **Last Updated:** November 2025
- **Active Development:** ✅ Yes

See the [Current Roadmap](../09-PROJECT-HISTORY/roadmap/Current-Status.md) for detailed progress.

---

**Next:** [Project Overview →](./Project-Overview.md)
