# API Excel - Système de Clés API

## Installation

### 1. Installer les dépendances

```bash
composer install
```

### 2. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurer la base de données

#### Option A : SQLite (Recommandé pour développement)

Installer l'extension PHP SQLite3 :

```bash
# Ubuntu/Debian
sudo apt-get install php-sqlite3

# macOS (avec Homebrew)
brew install php@8.2 --with-sqlite

# Windows (décommenter dans php.ini)
extension=sqlite3
```

Créer le fichier de base de données :

```bash
touch database/database.sqlite
```

#### Option B : MySQL (Recommandé pour production)

Modifier le fichier `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mdf_access
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### 4. Exécuter les migrations

```bash
php artisan migrate
```

### 5. Générer une clé API

```bash
php artisan api-key:generate "Nom de votre application"
```

Exemple :
```bash
php artisan api-key:generate "Application Kizeo"
```

La commande affichera votre clé API. **Conservez-la en lieu sûr !**

## Utilisation

### Endpoints disponibles

Tous les endpoints nécessitent une clé API pour l'authentification.

#### 1. Mettre à jour un fichier Excel

```bash
POST /api/excel/update
```

#### 2. Télécharger un fichier Excel

```bash
GET /api/excel/download/{filename?}
```

#### 3. Lister les fichiers Excel disponibles

```bash
GET /api/excel/list
```

### Authentification

La clé API peut être fournie de 3 manières :

#### Header X-API-Key (Recommandé)
```bash
curl -H "X-API-Key: mdf_votre_cle_api_ici" http://localhost:8000/api/excel/list
```

#### Bearer Token
```bash
curl -H "Authorization: Bearer mdf_votre_cle_api_ici" http://localhost:8000/api/excel/list
```

#### Query Parameter
```bash
curl "http://localhost:8000/api/excel/list?api_key=mdf_votre_cle_api_ici"
```

## Exemples d'utilisation

### Télécharger un fichier Excel

```bash
curl -X GET http://localhost:8000/api/excel/download/template.xlsx \
  -H "X-API-Key: mdf_votre_cle_api_ici" \
  -o template.xlsx
```

### Lister les fichiers disponibles

```bash
curl -X GET http://localhost:8000/api/excel/list \
  -H "X-API-Key: mdf_votre_cle_api_ici"
```

### Mettre à jour un fichier Excel

```bash
curl -X POST http://localhost:8000/api/excel/update \
  -H "Content-Type: application/json" \
  -H "X-API-Key: mdf_votre_cle_api_ici" \
  -d '{
    "fichier_excel": "template.xlsx",
    "data": [
      {
        "colonne_excel": "A",
        "champ_kizeo": "ai_zone",
        "valeur": "marrakech",
        "rang": 653
      }
    ]
  }'
```

## Sécurité

### Mesures implémentées

- ✅ Authentification par clé API
- ✅ Clés uniques avec préfixe `mdf_`
- ✅ Tracking de l'utilisation (last_used_at)
- ✅ Activation/Désactivation des clés
- ✅ Protection contre directory traversal
- ✅ Validation des entrées

### Recommandations production

- Utilisez HTTPS obligatoirement
- Implémentez le rate limiting
- Activez les logs d'audit
- Effectuez des backups réguliers
- Utilisez une base de données robuste (MySQL/PostgreSQL)

## Gestion des clés API

### Désactiver une clé

Accédez à la base de données et modifiez le champ `is_active` :

```sql
UPDATE api_keys SET is_active = 0 WHERE key = 'mdf_votre_cle';
```

### Vérifier l'utilisation

```sql
SELECT name, last_used_at, is_active FROM api_keys;
```

## Documentation complète

Consultez le fichier [API_DOCUMENTATION.md](API_DOCUMENTATION.md) pour la documentation complète de l'API.

## Démarrage du serveur

```bash
php artisan serve
```

L'API sera accessible sur `http://localhost:8000`

## Structure du projet

```
app/
├── Console/Commands/
│   └── GenerateApiKey.php         # Commande pour générer des clés
├── Http/
│   ├── Controllers/Api/
│   │   ├── ExcelUpdateController.php    # Mise à jour
│   │   └── ExcelDownloadController.php  # Téléchargement
│   ├── Middleware/
│   │   └── ValidateApiKey.php     # Authentification
│   └── Requests/
│       └── UpdateExcelRequest.php
├── Models/
│   └── ApiKey.php                 # Modèle de clé API
database/
└── migrations/
    └── *_create_api_keys_table.php
routes/
└── api.php                        # Routes API protégées
```

## Support

Pour toute question ou problème, consultez la documentation complète ou ouvrez une issue.
