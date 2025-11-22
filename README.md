# MDF Access

**Multi-Tenant PMBOK Project Management Platform**

MDF Access is a comprehensive project management platform built on Laravel 12 that implements PMBOK (Project Management Body of Knowledge) best practices with complete multi-tenant architecture.

---

## 🚀 Quick Start

```bash
# Install dependencies and setup
composer setup

# Start development servers
composer dev

# Access the application
http://localhost:8000
```

**See:** [Complete Quick Start Guide →](docs/00-GETTING-STARTED/Quick-Start-Guide.md)

---

## 🌟 Key Features

- ✅ **Multi-Tenant Architecture** - Complete data isolation per organization
- ✅ **PMBOK Compliance** - Full Portfolio → Program → Project hierarchy
- ✅ **Flexible Permissions** - 174 permissions, 29 roles
- ✅ **Phase Templates** - PMBOK, Scrum, Hybrid methodologies
- ✅ **Multi-Organization Projects** - MOA, MOE, Sponsor, Subcontractor roles
- ✅ **Field Maintenance Module** - Infrastructure/telecom site management
- ✅ **Excel Import/Export** - Bulk data operations
- ✅ **RESTful API** - Complete API with authentication
- ✅ **2FA Support** - Google Authenticator integration

---

## 📚 Documentation

**Complete documentation is available in the [/docs](docs/README.md) directory:**

| Section | Description |
|---------|-------------|
| 🚀 [Getting Started](docs/00-GETTING-STARTED/README.md) | Installation, overview, quick start |
| 🏗️ [Architecture](docs/01-ARCHITECTURE/README.md) | System design, multi-tenancy, permissions |
| ✨ [Features](docs/02-FEATURES/README.md) | All platform capabilities |
| 🔌 [API Reference](docs/03-API-REFERENCE/README.md) | Complete API documentation |
| 🔄 [Workflows](docs/04-WORKFLOWS/README.md) | Step-by-step guides |
| 🚀 [Deployment](docs/05-DEPLOYMENT/README.md) | Production setup |
| 💻 [Development](docs/06-DEVELOPMENT/README.md) | Developer guides |
| ⚙️ [Operations](docs/07-OPERATIONS/README.md) | Platform operations |
| 🔄 [Migration](docs/08-MIGRATION/README.md) | Data migration guides |
| 📜 [Project History](docs/09-PROJECT-HISTORY/README.md) | Development history |

**New to MDF Access?** Start with the [Project Overview →](docs/00-GETTING-STARTED/Project-Overview.md)

---

## 📊 Project Statistics

### Database
- **57 Total Tables**
  - 39 PMBOK tables
  - 11 Multi-tenant tables
  - 7 Field Maintenance tables

### Code
- **40+ Eloquent Models**
- **50+ Migrations**
- **16 Excel Import Classes**
- **174 Permissions** across 29 roles

### Data (from Odoo migration)
- **58 Users**
- **66 Projects**
- **9,626 Tasks**

### Progress
- **Overall:** 42% complete
- **Current Phase:** Row-Level Security (RLS) implementation

---

## 🛠️ Technology Stack

- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Frontend:** Blade + Tailwind CSS 4.0 + Vite 7.0
- **Database:** SQLite (dev) / MySQL 8.0+ or PostgreSQL 13+ (production)
- **Authentication:** Laravel Breeze + 2FA (Google Authenticator)

**See:** [Complete Technology Stack →](docs/00-GETTING-STARTED/Technology-Stack.md)

---

## 🎯 Use Cases

- **Construction Project Management** - Multi-phase projects with subcontractors
- **IT Project Portfolios** - Coordinate multiple development teams
- **Telecom Infrastructure** - Field maintenance and site management
- **Consulting Services** - Client engagements with deliverables

---

## 💻 Development

### Requirements
- PHP 8.2+
- Composer
- Node.js 18+
- SQLite/MySQL/PostgreSQL

### Setup
```bash
# Clone repository
git clone <repo-url> mdf-access
cd mdf-access

# Install and setup
composer setup

# Start development servers
composer dev
```

### Available Commands
```bash
composer setup    # Install dependencies and setup
composer dev      # Start all dev servers (Laravel, Vite, Queue, Logs)
composer test     # Run PHPUnit tests
```

**See:** [Development Guide →](docs/06-DEVELOPMENT/README.md)

---

## 🤝 Contributing

Contributions are welcome! Please see [Contributing Guide →](docs/06-DEVELOPMENT/Contributing.md)

---

## 📝 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

MDF Access is proprietary software. All rights reserved.

---

## 📞 Credits

**Built with:**
- [Laravel 12](https://laravel.com)
- [Tailwind CSS 4.0](https://tailwindcss.com)
- [Vite 7.0](https://vitejs.dev)

**MDF Access** - Multi-Tenant PMBOK Project Management Platform

**Version:** 1.0
**Last Updated:** November 2025
**Progress:** 42%

---

**Ready to start?** → [Quick Start Guide](docs/00-GETTING-STARTED/Quick-Start-Guide.md)
