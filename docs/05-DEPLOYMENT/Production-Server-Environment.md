# Production Server Environment

Complete specification of the production server hosting the MDF Access platform.

---

## Server Information

### Hosting Details
```
Provider:       Contabo VPS
Hostname:       vmi1399789.contaboserver.net
Public IP:      173.212.230.240
Location:       [To be documented]
```

### Domain Configuration
```
Production URL: https://projets.samsic.cloud
Environment:    Production
SSL/TLS:        Enabled (Certbot/Let's Encrypt)
```

### Other Services on Server
```
- embed.samsic.cloud    (Embedding service)
- n8n.samsic.cloud      (Workflow automation)
- intranet.samsic.cloud (Internal portal)
- qdrant.samsic.cloud   (Vector database)
- builder.samsic.cloud  (Builder service)
```

---

## System Specifications

### Operating System
```
Distribution:   Ubuntu 22.04.5 LTS (Jammy Jellyfish)
Kernel:         Linux 5.15.0-143-generic
Architecture:   x86_64 (64-bit)
Build Date:     Fri Jun 13 19:10:45 UTC 2025
```

### Hardware Resources
```
CPU Cores:      4 cores
RAM:            7.8 GB (8 GB total)
  - Used:       3.4 GB
  - Available:  3.8 GB
  - Swap:       0 GB (disabled)

Storage:        391 GB total
  - Used:       267 GB (72%)
  - Available:  105 GB (28%)
  - Mount:      /dev/sda3
```

---

## Software Stack

### Web Server
```
Nginx:          1.18.0 (Ubuntu)
Config Path:    /etc/nginx/sites-enabled/
Status:         Active
```

### Application Runtime
```
PHP Version:    8.4.14
  - CLI:        Built Oct 27 2025
  - Zend:       Engine v4.4.14
  - OPcache:    Enabled

Laravel:        Framework ^12.0
Composer:       2.8.12
Laravel CLI:    5.22.0
```

### JavaScript Runtime
```
Node.js:        v21.6.1
NPM:            10.4.0
```

### Database Systems
```
PostgreSQL:     18.0 (Primary - Ubuntu 18.0-1.pgdg22.04+3)
  - Host:       127.0.0.1
  - Port:       5432
  - Database:   samsic_maroc
  - User:       odoo

MySQL:          8.0.43 (Secondary/Backup)
  - Version:    8.0.43-0ubuntu0.22.04.2
  - Available:  Yes
```

### Caching & Queuing
```
Redis:          6.0.16
  - Host:       127.0.0.1
  - Port:       6379
  - Status:     Available
```

### SSL/TLS Management
```
Certbot:        1.21.0
Provider:       Let's Encrypt
Auto-renewal:   Configured
```

### Process Management
```
Supervisor:     Not installed
Queue Workers:  Manual/Systemd (to be configured)
```

---

## Application Configuration

### Environment Settings
```
APP_NAME:       SAMSICLaravel
APP_ENV:        production
APP_DEBUG:      false
APP_URL:        https://projets.samsic.cloud
APP_LOCALE:     fr

LOG_CHANNEL:    daily
LOG_LEVEL:      warning

SESSION_DRIVER: database
CACHE_STORE:    database
QUEUE_CONN:     database
```

### Application Path
```
Root Directory: /var/www/samsic/mdf-access
Public Path:    /var/www/samsic/mdf-access/public
Storage Path:   /var/www/samsic/mdf-access/storage
```

---

## Network Configuration

### Primary IP Address
```
Public IPv4:    173.212.230.240
```

### Docker Networks (Multiple containers detected)
```
172.17.0.1
172.18.0.1
172.19.0.1
172.20.0.1
172.21.0.1
172.22.0.1
172.23.0.1
```

### Firewall
```
UFW Status:     [To be documented]
Open Ports:     80, 443 (HTTP/HTTPS)
SSH Port:       [To be documented]
```

---

## Security Configuration

### SSL/TLS Certificates
```
Provider:       Let's Encrypt (Certbot 1.21.0)
Status:         Active for projets.samsic.cloud
Auto-renewal:   Enabled
Certificate Path: /etc/letsencrypt/live/projets.samsic.cloud/
```

### Application Security
```
APP_KEY:        Configured (base64 encoded)
APP_DEBUG:      false (Production mode)
Encryption:     BCrypt (12 rounds)
Session Encrypt: false
```

### File Permissions
```
Recommended:
  - Directories: 755
  - Files:       644
  - .env:        600
  - storage/:    775 (www-data writable)
  - bootstrap/cache/: 775 (www-data writable)
```

---

## Performance Optimization

### PHP Configuration
```
OPcache:        Enabled (Zend OPcache v8.4.14)
Workers:        4 (PHP_CLI_SERVER_WORKERS)
Memory Limit:   [To be documented]
Max Execution:  [To be documented]
Upload Max:     [To be documented]
```

### Laravel Optimization
```
Config Cache:   [To be verified]
Route Cache:    [To be verified]
View Cache:     [To be verified]
Autoloader:     [To be verified]
```

### Database Optimization
```
Connection Pool: [To be configured]
Query Caching:   [To be configured]
Index Strategy:  [To be documented]
```

---

## Backup Strategy

### Database Backups
```
Schedule:       [To be configured]
Retention:      [To be configured]
Location:       [To be configured]
Method:         pg_dump for PostgreSQL
```

### Application Backups
```
Files:          /var/www/samsic/mdf-access
Storage:        /var/www/samsic/mdf-access/storage
Environment:    .env (secure backup)
Schedule:       [To be configured]
```

---

## Monitoring & Logging

### Application Logs
```
Location:       /var/www/samsic/mdf-access/storage/logs
Format:         Daily rotation
Level:          Warning (production)
Monitoring:     [To be configured]
```

### System Logs
```
Nginx Logs:     /var/log/nginx/
  - Access:     access.log
  - Error:      error.log

System Logs:    /var/log/syslog
PHP-FPM:        [To be documented]
```

### Health Checks
```
Uptime Monitor: [To be configured]
Status Page:    [To be configured]
Alerts:         [To be configured]
```

---

## Deployment Workflow

### Current Setup
```
Git Repository: Connected (main branch)
Deployment:     Manual
CI/CD:          [To be configured]
```

### Deployment Process
```bash
# Standard deployment steps
cd /var/www/samsic/mdf-access
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm install --production
npm run build
php artisan queue:restart
```

---

## Known Issues & Warnings

### PHP Extensions
```
WARNING: pdo_pgsql - module loading issue detected
  - Path: /usr/lib/php/20240924/pdo_pgsql.so
  - Issue: undefined symbol: pdo_dbh_ce
  - Impact: May affect PostgreSQL PDO operations

WARNING: Duplicate module loading
  - fileinfo: Already loaded
  - gd: Already loaded
  - mbstring: Already loaded
  - pgsql: Already loaded
```

### Resource Concerns
```
DISK USAGE: 72% (267GB/391GB used)
  - Recommend monitoring and cleanup
  - Consider log rotation
  - Review storage/ directory size
```

---

## Recommended Improvements

### High Priority
- [ ] Install and configure Supervisor for queue workers
- [ ] Set up automated database backups
- [ ] Configure Redis for caching and sessions
- [ ] Resolve PHP extension warnings
- [ ] Set up disk space monitoring
- [ ] Configure proper log rotation

### Medium Priority
- [ ] Enable Laravel caching (config, routes, views)
- [ ] Set up application monitoring (e.g., Sentry)
- [ ] Configure fail2ban for security
- [ ] Document firewall rules
- [ ] Set up automated backups to remote storage
- [ ] Configure email service for notifications

### Low Priority
- [ ] Implement CI/CD pipeline
- [ ] Add performance monitoring (e.g., New Relic)
- [ ] Configure CDN for static assets
- [ ] Set up development/staging environment
- [ ] Document disaster recovery procedures

---

## Maintenance Schedule

### Regular Maintenance
```
Daily:
  - Log monitoring
  - Disk space check
  - Application health check

Weekly:
  - Security updates check
  - Backup verification
  - Performance review

Monthly:
  - Full system update
  - Certificate renewal check
  - Storage cleanup
  - Database optimization
```

---

## Support & Contacts

### Server Access
```
SSH Access:     [To be documented]
User:           [To be documented]
Key Path:       [To be documented]
```

### Emergency Contacts
```
System Admin:   [To be documented]
Database Admin: [To be documented]
On-Call:        [To be documented]
```

---

## Version History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2025-12-08 | 1.0 | Initial production server documentation | Claude Code |

---

**Last Updated:** December 8, 2025
**Server Status:** Production Active
**Next Review:** January 2026
