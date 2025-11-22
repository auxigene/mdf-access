# Système d'Authentification - Documentation

## Vue d'ensemble

Ce système d'authentification implémente les meilleures pratiques de sécurité modernes pour Laravel 12, incluant :

- ✅ **Authentification par session** avec protection CSRF
- ✅ **Enregistrement et connexion** avec validation robuste
- ✅ **Vérification d'email** obligatoire
- ✅ **Réinitialisation de mot de passe** sécurisée
- ✅ **Authentification à deux facteurs (2FA)** optionnelle via Google Authenticator
- ✅ **Rate limiting** contre les attaques par force brute
- ✅ **Hachage de mots de passe** avec Bcrypt (12 rounds)
- ✅ **Protection contre les attaques** courantes (CSRF, XSS, injection SQL)

---

## Installation

### 1. Installer les dépendances

```bash
composer install
npm install
```

### 2. Configurer l'environnement

Copiez le fichier `.env.example` vers `.env` et configurez :

```env
APP_NAME="MDF Access"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de données
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# Configuration email (pour password reset et email verification)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# Session (sécurisé)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false  # mettre à true en production avec HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### 3. Générer la clé d'application

```bash
php artisan key:generate
```

### 4. Exécuter les migrations

```bash
php artisan migrate
```

Cette commande va créer toutes les tables nécessaires :
- `users` - Utilisateurs avec support 2FA
- `sessions` - Sessions sécurisées
- `password_reset_tokens` - Tokens de réinitialisation
- Tables RBAC (roles, permissions, etc.)

### 5. Compiler les assets

```bash
npm run build  # Pour production
npm run dev    # Pour développement
```

### 6. Lancer le serveur

```bash
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

---

## Fonctionnalités

### 1. Inscription (`/register`)

**Champs requis :**
- Nom complet
- Email (unique)
- Mot de passe (minimum 8 caractères)
- Confirmation du mot de passe
- Organisation (optionnel)

**Processus :**
1. L'utilisateur remplit le formulaire
2. Le système valide les données
3. Le mot de passe est haché avec Bcrypt
4. Un email de vérification est envoyé
5. L'utilisateur doit vérifier son email avant de se connecter

### 2. Connexion (`/login`)

**Champs requis :**
- Email
- Mot de passe
- Case "Se souvenir de moi" (optionnel)

**Protection :**
- **Rate limiting** : Maximum 5 tentatives par minute
- Après 5 échecs, le compte est verrouillé pendant 1 minute
- Les identifiants sont vérifiés de manière sécurisée

**Processus :**
1. L'utilisateur entre ses identifiants
2. Le système vérifie si l'email est validé
3. Si 2FA est activé, redirection vers la page 2FA
4. Sinon, connexion et redirection vers le dashboard

### 3. Vérification d'email

**Processus :**
1. Lors de l'inscription, un email est envoyé avec un lien signé
2. L'utilisateur clique sur le lien dans l'email
3. Le système vérifie la signature et valide l'email
4. L'utilisateur peut maintenant se connecter

**Sécurité :**
- Les liens sont **signés** et ne peuvent pas être falsifiés
- Les liens expirent après un certain temps
- Rate limiting sur le renvoi d'emails (6 par minute)

### 4. Réinitialisation du mot de passe

**Processus :**
1. L'utilisateur clique sur "Mot de passe oublié ?"
2. Entre son email
3. Reçoit un email avec un lien de réinitialisation
4. Clique sur le lien et entre un nouveau mot de passe
5. Le mot de passe est mis à jour et l'utilisateur peut se connecter

**Sécurité :**
- Les tokens sont stockés en base de données avec hachage
- Les tokens expirent après 60 minutes
- Rate limiting : 1 email par minute maximum
- Le remember_token est régénéré après la réinitialisation

### 5. Authentification à deux facteurs (2FA)

**Activation :**
1. Connectez-vous au dashboard
2. Cliquez sur "Enable 2FA"
3. Scannez le QR code avec Google Authenticator ou Authy
4. Entrez le code à 6 chiffres pour vérifier
5. Confirmez avec votre mot de passe

**Utilisation :**
- Lors de la connexion, un code à 6 chiffres est demandé
- Le code change toutes les 30 secondes
- Impossible de se connecter sans le code

**Désactivation :**
- Depuis le dashboard, entrez votre mot de passe pour désactiver

**Sécurité :**
- Secret stocké chiffré en base de données
- Algorithme TOTP standard (Google Authenticator)
- Protection contre les attaques de replay

---

## Architecture

### Contrôleurs

#### `LoginController.php`
- Gère la connexion et la déconnexion
- Implémente le rate limiting
- Vérifie la vérification d'email et 2FA

#### `RegisterController.php`
- Gère l'inscription des nouveaux utilisateurs
- Valide les données entrées
- Déclenche l'envoi de l'email de vérification

#### `PasswordResetController.php`
- Gère la demande de réinitialisation
- Envoie les emails de réinitialisation
- Traite la mise à jour du mot de passe

#### `EmailVerificationController.php`
- Affiche la page de notification
- Vérifie les liens signés
- Renvoie les emails de vérification

#### `TwoFactorAuthController.php`
- Gère la configuration de 2FA
- Vérifie les codes 2FA lors de la connexion
- Active/désactive 2FA

### Routes (`routes/web.php`)

**Routes publiques (guest) :**
- `GET /login` - Formulaire de connexion
- `POST /login` - Traitement de la connexion
- `GET /register` - Formulaire d'inscription
- `POST /register` - Traitement de l'inscription
- `GET /forgot-password` - Formulaire de réinitialisation
- `POST /forgot-password` - Envoi de l'email
- `GET /reset-password/{token}` - Formulaire de nouveau mot de passe
- `POST /reset-password` - Mise à jour du mot de passe
- `GET /2fa/verify` - Vérification 2FA
- `POST /2fa/verify` - Traitement de la vérification

**Routes authentifiées (auth) :**
- `POST /logout` - Déconnexion
- `GET /email/verify` - Page de notification
- `GET /email/verify/{id}/{hash}` - Vérification (signé)
- `POST /email/resend` - Renvoi d'email
- `GET /dashboard` - Tableau de bord (requiert email vérifié)

**Routes 2FA (auth + verified) :**
- `GET /2fa/setup` - Configuration 2FA
- `POST /2fa/enable` - Activation 2FA
- `POST /2fa/disable` - Désactivation 2FA

### Vues Blade (`resources/views/auth/`)

- `login.blade.php` - Page de connexion
- `register.blade.php` - Page d'inscription
- `forgot-password.blade.php` - Demande de réinitialisation
- `reset-password.blade.php` - Nouveau mot de passe
- `verify-email.blade.php` - Notification de vérification
- `2fa-verify.blade.php` - Vérification 2FA
- `2fa-setup.blade.php` - Configuration 2FA
- `dashboard.blade.php` - Tableau de bord

### Modèle (`app/Models/User.php`)

**Implémente :**
- `MustVerifyEmail` - Interface Laravel pour la vérification d'email

**Champs 2FA :**
- `two_factor_enabled` - Boolean
- `two_factor_secret` - Text (chiffré)

**Relations :**
- `organization()` - Organisation de l'utilisateur
- `roles()` - Rôles RBAC
- `apiKeys()` - Clés API

### Migration

**`2025_11_15_000001_add_two_factor_to_users_table.php`**

Ajoute les colonnes :
- `two_factor_enabled` (boolean, default: false)
- `two_factor_secret` (text, nullable, chiffré)

---

## Sécurité

### Meilleures pratiques implémentées

1. **Hachage des mots de passe**
   - Bcrypt avec 12 rounds (configurable dans `.env`)
   - Les mots de passe ne sont jamais stockés en clair

2. **Protection CSRF**
   - Tous les formulaires incluent `@csrf`
   - Laravel vérifie automatiquement les tokens

3. **Rate Limiting**
   - Connexion : 5 tentatives par minute
   - Email verification : 6 requêtes par minute
   - Password reset : 1 email par minute

4. **Sessions sécurisées**
   - Stockées en base de données
   - HTTP Only cookies (protection XSS)
   - SameSite protection (protection CSRF)
   - Régénération après connexion

5. **Email Verification**
   - Liens signés (impossible à falsifier)
   - Expiration automatique
   - Obligatoire avant l'accès au dashboard

6. **Password Reset sécurisé**
   - Tokens hachés en base de données
   - Expiration après 60 minutes
   - Remember token régénéré après reset

7. **2FA optionnel**
   - Secret chiffré en base de données
   - Algorithme TOTP standard
   - Codes valides 30 secondes

8. **Protection des routes**
   - Middleware `guest` pour les routes publiques
   - Middleware `auth` pour les routes authentifiées
   - Middleware `verified` pour les routes nécessitant un email vérifié

9. **Validation des entrées**
   - Tous les champs sont validés côté serveur
   - Protection contre les injections SQL (Eloquent ORM)
   - Protection contre les attaques XSS (échappement automatique Blade)

---

## Configuration en production

### Variables d'environnement importantes

```env
# PRODUCTION
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Sessions sécurisées (HTTPS requis)
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Email en production
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### Checklist de sécurité production

- [ ] `APP_DEBUG=false` dans `.env`
- [ ] `SESSION_SECURE_COOKIE=true` (HTTPS obligatoire)
- [ ] Configurer un vrai serveur SMTP (pas Mailtrap)
- [ ] Activer HTTPS sur le serveur
- [ ] Configurer les backups de base de données
- [ ] Mettre en place un monitoring des logs
- [ ] Configurer le rate limiting adapté à votre trafic
- [ ] Tester le système de password reset en production
- [ ] Tester l'envoi d'emails de vérification
- [ ] Vérifier que 2FA fonctionne correctement

---

## Tests

### Tests manuels

1. **Inscription**
   - [ ] Créer un compte avec email valide
   - [ ] Vérifier que l'email de vérification est reçu
   - [ ] Cliquer sur le lien de vérification
   - [ ] Vérifier que le compte est activé

2. **Connexion**
   - [ ] Se connecter avec identifiants corrects
   - [ ] Tester 5 tentatives échouées (rate limiting)
   - [ ] Vérifier le verrouillage temporaire
   - [ ] Tester "Se souvenir de moi"

3. **Password Reset**
   - [ ] Demander une réinitialisation
   - [ ] Vérifier la réception de l'email
   - [ ] Réinitialiser le mot de passe
   - [ ] Se connecter avec le nouveau mot de passe

4. **2FA**
   - [ ] Activer 2FA depuis le dashboard
   - [ ] Scanner le QR code avec Google Authenticator
   - [ ] Vérifier le code
   - [ ] Se déconnecter et se reconnecter avec 2FA
   - [ ] Désactiver 2FA

### Tests automatisés (à implémenter)

Créez des tests PHPUnit dans `tests/Feature/Auth/` :

```bash
php artisan make:test Auth/LoginTest
php artisan make:test Auth/RegisterTest
php artisan make:test Auth/PasswordResetTest
php artisan make:test Auth/EmailVerificationTest
php artisan make:test Auth/TwoFactorTest
```

---

## Dépannage

### Problème : Les emails ne sont pas envoyés

**Solution :**
1. Vérifiez la configuration SMTP dans `.env`
2. Testez avec Mailtrap en développement
3. Vérifiez les logs : `storage/logs/laravel.log`
4. Utilisez la commande : `php artisan queue:work` si vous utilisez les queues

### Problème : Rate limiting trop restrictif

**Solution :**
Modifiez les paramètres dans les contrôleurs :
- `LoginController.php` : ligne 73 (5 tentatives)
- Routes email : `throttle:6,1` dans `web.php`

### Problème : 2FA ne fonctionne pas

**Solution :**
1. Vérifiez que l'horloge du serveur est synchronisée (NTP)
2. Vérifiez que la bibliothèque `pragmarx/google2fa` est installée
3. Vérifiez que le secret est bien chiffré/déchiffré

### Problème : Sessions perdues

**Solution :**
1. Vérifiez que `SESSION_DRIVER=database` dans `.env`
2. Exécutez `php artisan migrate` pour créer la table sessions
3. Vérifiez les permissions sur `storage/framework/sessions`

---

## Maintenance

### Nettoyage régulier

**Sessions expirées :**
```bash
php artisan schedule:run  # Lance les tâches planifiées
```

Laravel nettoie automatiquement les sessions expirées.

**Password reset tokens expirés :**

Créez une commande artisan pour nettoyer les tokens > 60 minutes :

```bash
php artisan make:command CleanExpiredTokens
```

---

## Ressources

- [Documentation Laravel Authentication](https://laravel.com/docs/12.x/authentication)
- [Google2FA Package](https://github.com/antonioribeiro/google2fa)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [Laravel Security Best Practices](https://laravel.com/docs/12.x/security)

---

## Support

Pour toute question ou problème :
1. Consultez cette documentation
2. Vérifiez les logs dans `storage/logs/`
3. Consultez la documentation Laravel officielle
4. Créez une issue sur le repository du projet
