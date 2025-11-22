# Authentification - Guide Rapide

## Installation rapide

```bash
# 1. Installer les dépendances
composer install
npm install

# 2. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 3. Configurer la base de données dans .env
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite

# 4. Exécuter les migrations
php artisan migrate

# 5. Compiler les assets
npm run build

# 6. Lancer le serveur
php artisan serve
```

## Fonctionnalités principales

### ✅ Inscription et connexion
- Formulaires sécurisés avec validation
- Hachage des mots de passe (Bcrypt 12 rounds)
- Protection CSRF automatique

### ✅ Vérification d'email obligatoire
- Email envoyé automatiquement à l'inscription
- Liens signés sécurisés
- Accès au dashboard uniquement après vérification

### ✅ Réinitialisation de mot de passe
- Envoi d'email sécurisé
- Tokens avec expiration (60 min)
- Rate limiting

### ✅ Authentification à deux facteurs (2FA)
- Compatible Google Authenticator / Authy
- Activation/désactivation depuis le dashboard
- Secret chiffré en base de données

### ✅ Protection contre les attaques
- **Rate limiting** : 5 tentatives de connexion/minute
- **CSRF Protection** : Tokens automatiques sur tous les formulaires
- **XSS Protection** : Échappement automatique Blade
- **SQL Injection** : Protection par Eloquent ORM
- **Session Hijacking** : Régénération après connexion

## Routes principales

```
/login           - Connexion
/register        - Inscription
/forgot-password - Réinitialisation mot de passe
/dashboard       - Tableau de bord (authentifié + email vérifié)
/2fa/setup       - Configuration 2FA
```

## Configuration email

Dans `.env` :

```env
# Développement (Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@example.com

# Production
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

## Documentation complète

Consultez [AUTHENTICATION.md](./AUTHENTICATION.md) pour :
- Documentation détaillée de chaque fonctionnalité
- Architecture du système
- Guide de sécurité
- Configuration production
- Dépannage

## Tests manuels

1. **Inscription** : Créer un compte → Vérifier email → Se connecter
2. **Password Reset** : Forgot password → Email → Nouveau mot de passe
3. **2FA** : Dashboard → Enable 2FA → Scanner QR → Tester connexion

## Sécurité en production

⚠️ **Checklist obligatoire** :

```env
APP_DEBUG=false
APP_ENV=production
SESSION_SECURE_COOKIE=true  # Requiert HTTPS
```

- [ ] Activer HTTPS
- [ ] Configurer SMTP réel
- [ ] Tester tous les emails
- [ ] Vérifier rate limiting
- [ ] Backups base de données

## Support

- Documentation complète : [AUTHENTICATION.md](./AUTHENTICATION.md)
- Logs : `storage/logs/laravel.log`
- Laravel Docs : https://laravel.com/docs/12.x/authentication
