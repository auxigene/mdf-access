# 🚀 Deployment Documentation

Complete guide for deploying and configuring MDF Access in production environments.

---

## 📚 Table of Contents

1. [Production Server Environment](./Production-Server-Environment.md) - Current production server specifications
2. [Installation](./Installation.md) - Step-by-step installation guide
3. [Configuration](./Configuration.md) - Environment and application configuration
4. [Database Setup](./Database-Setup.md) - Database configuration and optimization
5. [Environment Variables](./Environment-Variables.md) - Complete .env reference
6. [Production Checklist](./Production-Checklist.md) - Pre-launch checklist

---

## ⚡ Quick Start

### Minimum Requirements

```
PHP:         8.2+
Database:    MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+
Web Server:  Apache 2.4+ / Nginx 1.18+
Memory:      512MB (2GB recommended)
Storage:     1GB+ (excluding user data)
```

### 5-Minute Production Deployment

```bash
# 1. Clone and enter directory
git clone <repo-url> mdf-access
cd mdf-access

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Configure environment
cp .env.example .env
nano .env  # Edit database and app settings

# 4. Generate key and setup database
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force

# 5. Build frontend assets
npm install --production
npm run build

# 6. Set permissions
chmod -R 755 storage bootstrap/cache

# 7. Configure web server (see below)
# 8. Enable SSL/HTTPS
# 9. Start services
```

---

## 🌐 Web Server Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name mdf-access.example.com;
    root /var/www/mdf-access/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName mdf-access.example.com
    DocumentRoot /var/www/mdf-access/public

    <Directory /var/www/mdf-access/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mdf-access-error.log
    CustomLog ${APACHE_LOG_DIR}/mdf-access-access.log combined
</VirtualHost>
```

---

## 🗄️ Database Configuration

### MySQL/PostgreSQL (Production)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mdf_access
DB_USERNAME=mdf_user
DB_PASSWORD=secure_password_here
```

### Database Optimization

```sql
-- MySQL: Optimize tables
OPTIMIZE TABLE projects, tasks, users;

-- Add indexes for performance
CREATE INDEX idx_projects_org ON projects(organization_id);
CREATE INDEX idx_tasks_phase ON tasks(phase_id);
CREATE INDEX idx_users_org ON users(organization_id);
```

---

## 🔒 Security Checklist

- [ ] SSL/TLS certificate installed (Let's Encrypt recommended)
- [ ] `.env` file secured (chmod 600)
- [ ] APP_DEBUG=false in production
- [ ] APP_ENV=production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secured
- [ ] File permissions correct (755 directories, 644 files)
- [ ] `storage/` and `bootstrap/cache/` writable
- [ ] CORS configured if needed
- [ ] Rate limiting enabled
- [ ] Firewall configured (UFW/iptables)
- [ ] Fail2ban configured for SSH/HTTP
- [ ] Regular backups scheduled
- [ ] Log monitoring enabled

---

## ⚙️ Performance Optimization

### Cache Configuration

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Queue Workers

```bash
# Start queue worker (supervisor recommended)
php artisan queue:work --tries=3 --timeout=90

# Supervisor config: /etc/supervisor/conf.d/mdf-access-worker.conf
[program:mdf-access-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mdf-access/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/mdf-access/storage/logs/worker.log
```

### Redis (Optional but Recommended)

```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 📊 Monitoring

### Log Monitoring

```bash
# Real-time logs
tail -f storage/logs/laravel.log

# Using Pail (development)
php artisan pail
```

### Health Checks

```bash
# Database connection
php artisan db:show

# Check permissions
php artisan permission:check

# Check queue
php artisan queue:monitor
```

---

## 🔄 Deployment Workflow

### Zero-Downtime Deployment

```bash
#!/bin/bash
# deploy.sh

# 1. Enable maintenance mode
php artisan down --retry=60

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Run migrations
php artisan migrate --force

# 5. Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Build assets
npm install --production
npm run build

# 7. Restart queue workers
php artisan queue:restart

# 8. Disable maintenance mode
php artisan up

echo "Deployment completed successfully!"
```

---

## 📦 Backup Strategy

### Database Backup

```bash
# MySQL backup
mysqldump -u username -p mdf_access > backup_$(date +%Y%m%d).sql

# Automated daily backups (cron)
0 2 * * * /usr/bin/mysqldump -u user -ppassword mdf_access | gzip > /backups/mdf_$(date +\%Y\%m\%d).sql.gz
```

### File Backup

```bash
# Backup storage and database
tar -czf backup_$(date +%Y%m%d).tar.gz storage/ database/database.sqlite .env

# Upload to remote storage (S3, etc.)
```

---

## 🐳 Docker Deployment (Optional)

### Docker Compose

```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: mdf_access
      MYSQL_ROOT_PASSWORD: secret
    volumes:
      - dbdata:/var/lib/mysql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

volumes:
  dbdata:
```

---

## 🆘 Related Documentation

- **Installation:** [Detailed Installation Guide](./Installation.md)
- **Configuration:** [Environment Variables](./Environment-Variables.md)
- **Operations:** [Platform Operations](../07-OPERATIONS/Platform-Operations.md)
- **Troubleshooting:** [Common Issues](../07-OPERATIONS/Troubleshooting.md)

---

## 🌐 Production Environment

**Current Production Server:** Contabo VPS (vmi1399789.contaboserver.net)
- **URL:** https://projets.samsic.cloud
- **IP:** 173.212.230.240
- **Specs:** 4 CPU cores, 7.8GB RAM, 391GB storage
- **Stack:** Ubuntu 22.04, Nginx 1.18, PHP 8.4.14, PostgreSQL 18.0, Node.js 21.6.1

For complete server specifications, see [Production-Server-Environment.md](./Production-Server-Environment.md)

---

**Last Updated:** December 2025
**Deployment Method:** Manual / Docker / Laravel Forge compatible
