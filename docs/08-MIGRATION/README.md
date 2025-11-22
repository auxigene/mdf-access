# 🔄 Migration Documentation

Data migration guides for importing from external systems.

---

## 📚 Migration Sources

### Odoo

📄 **[Import Guide](./odoo/Import-Guide.md)**
- Step-by-step Odoo import process
- Data extraction
- Mapping rules

📄 **[Import Summary](./odoo/Import-Summary.md)**
- 58 users imported
- 66 projects imported
- 9,626 tasks imported

📄 **[Extraction Requirements](./odoo/Extraction-Requirements.md)**
- Required Odoo fields
- Export format

📄 **[SQL Export Scripts](./odoo/SQL-Export-Scripts.md)**
- SQL queries for Odoo extraction
- Data transformation scripts

### SAMSIC

📄 **[Migration Plan](./samsic/Migration-Plan.md)**
- SAMSIC-specific migration strategy
- Timeline and phases

📄 **[Migration Log](./samsic/Migration-Log.md)**
- November 9, 2025 migration log
- Issues encountered
- Solutions applied

### General

📄 **[Data Migration Guide](./general/Data-Migration-Guide.md)**
- Generic migration workflow
- Excel-based imports
- Best practices

---

## 🚀 Quick Import

```bash
# Run Odoo import
php artisan import:odoo users.csv
php artisan import:odoo projects.csv
php artisan import:odoo tasks.csv

# Verify import
php artisan db:show
```

---

**Last Updated:** November 2025
**Total Migrated:** 58 users, 66 projects, 9,626 tasks
