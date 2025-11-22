# ⚙️ Operations Documentation

Platform operations, monitoring, and maintenance guides for MDF Access.

---

## 📚 Documents

📄 **[Platform Operations](./Platform-Operations.md)**
- Daily operations workflow
- User management
- Project monitoring
- System maintenance

📄 **[Routes Organization](./Routes-Organization.md)**
- Route structure
- API endpoints organization
- Web routes management

📄 **[Monitoring](./Monitoring.md)**
- System health monitoring
- Performance metrics
- Log analysis
- Alerting

📄 **[Troubleshooting](./Troubleshooting.md)**
- Common issues and solutions
- Error diagnostics
- Performance troubleshooting
- Database issues

---

## 🔍 Quick Operations

### Daily Checklist

```bash
# Check system health
php artisan db:show
php artisan queue:monitor

# Review logs
tail -f storage/logs/laravel.log

# Check disk space
df -h

# Monitor queue
php artisan horizon:status  # if using Horizon
```

### User Management

```bash
# Create user
php artisan tinker
>>> User::create([...])

# Disable user
>>> User::find(1)->update(['is_active' => false]);

# Reset password
php artisan tinker
>>> $user = User::find(1);
>>> $user->password = bcrypt('newpassword');
>>> $user->save();
```

---

**Last Updated:** November 2025
