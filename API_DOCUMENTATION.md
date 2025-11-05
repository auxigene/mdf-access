# API de Mise à Jour Excel - Documentation

## Description

Cette API permet de mettre à jour, télécharger et lister des fichiers Excel stockés localement. Elle est protégée par un système de clés API pour garantir un accès sécurisé.

## Configuration

### Prérequis

- PHP 8.2 ou supérieur
- Laravel 12
- Composer

### Installation

1. Installer les dépendances :
```bash
composer install
```

2. Configurer votre environnement :
```bash
cp .env.example .env
php artisan key:generate
```

3. Placer votre fichier Excel dans le répertoire :
```
storage/app/excel/
```

Par défaut, le système cherchera un fichier nommé `template.xlsx`. Vous pouvez spécifier un autre nom dans la requête.

4. Exécuter les migrations :
```bash
php artisan migrate
```

## Authentification

### Génération d'une clé API

Toutes les routes API sont protégées par une clé API. Pour générer une clé :

```bash
php artisan api-key:generate "Nom de votre application"
```

Exemple :
```bash
php artisan api-key:generate "Application Kizeo"
```

La commande affichera votre nouvelle clé API. **Conservez-la en lieu sûr** car elle ne pourra pas être récupérée ultérieurement.

### Utilisation de la clé API

La clé API peut être fournie de trois manières différentes :

#### 1. Header X-API-Key (Recommandé)
```
X-API-Key: mdf_votre_cle_api_ici
```

#### 2. Bearer Token
```
Authorization: Bearer mdf_votre_cle_api_ici
```

#### 3. Paramètre de requête (query parameter)
```
?api_key=mdf_votre_cle_api_ici
```

### Gestion des clés API

Les clés API sont stockées dans la table `api_keys` avec les informations suivantes :
- `name` : Nom/description de la clé
- `key` : La clé API unique (préfixée par `mdf_`)
- `is_active` : Statut d'activation (true/false)
- `last_used_at` : Date de dernière utilisation
- `created_at` : Date de création

## Utilisation de l'API

### Endpoints disponibles

1. **POST /api/excel/update** - Mettre à jour un fichier Excel
2. **GET /api/excel/download/{filename?}** - Télécharger un fichier Excel
3. **GET /api/excel/list** - Lister les fichiers Excel disponibles

---

## 1. Mettre à jour un fichier Excel

### Endpoint

```
POST /api/excel/update
```

### Headers

```
Content-Type: application/json
Accept: application/json
X-API-Key: mdf_votre_cle_api_ici
```

### Corps de la requête

La requête doit contenir un objet JSON avec la structure suivante :

```json
{
  "fichier_excel": "template.xlsx",  // Optionnel - nom du fichier Excel (par défaut: template.xlsx)
  "data": [
    {
      "colonne_excel": "A",           // Lettre de la colonne (A, B, C, ..., AA, AB, etc.)
      "champ_kizeo": "ai_zone_8c4e183b",  // Identifiant du champ Kizeo
      "valeur": "marrakech",          // Valeur à insérer (peut être null)
      "rang": 653                     // Numéro de ligne dans Excel
    },
    {
      "colonne_excel": "B",
      "champ_kizeo": "ai_code_site_4728e766",
      "valeur": "MNB-1011",
      "rang": 653
    }
  ]
}
```

### Exemple de requête avec curl

```bash
curl -X POST http://localhost:8000/api/excel/update \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-Key: mdf_votre_cle_api_ici" \
  -d '{
    "fichier_excel": "template.xlsx",
    "data": [
      {
        "colonne_excel": "A",
        "champ_kizeo": "ai_zone_8c4e183b",
        "valeur": "marrakech",
        "rang": 653
      },
      {
        "colonne_excel": "B",
        "champ_kizeo": "ai_code_site_4728e766",
        "valeur": "MNB-1011",
        "rang": 653
      }
    ]
  }'
```

### Exemple de requête avec JavaScript (fetch)

```javascript
const data = {
  fichier_excel: "template.xlsx",
  data: [
    {
      colonne_excel: "A",
      champ_kizeo: "ai_zone_8c4e183b",
      valeur: "marrakech",
      rang: 653
    },
    {
      colonne_excel: "B",
      champ_kizeo: "ai_code_site_4728e766",
      valeur: "MNB-1011",
      rang: 653
    }
  ]
};

fetch('http://localhost:8000/api/excel/update', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-API-Key': 'mdf_votre_cle_api_ici'
  },
  body: JSON.stringify(data)
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Erreur:', error));
```

### Réponses

#### Succès (200 OK)

```json
{
  "success": true,
  "message": "Fichier Excel mis à jour avec succès",
  "fichier": "template.xlsx",
  "cellules_modifiees": 2,
  "details": [
    {
      "cellule": "A653",
      "valeur": "marrakech",
      "champ_kizeo": "ai_zone_8c4e183b"
    },
    {
      "cellule": "B653",
      "valeur": "MNB-1011",
      "champ_kizeo": "ai_code_site_4728e766"
    }
  ]
}
```

#### Fichier non trouvé (404 Not Found)

```json
{
  "success": false,
  "message": "Le fichier Excel 'template.xlsx' n'existe pas dans storage/app/excel/",
  "path": "/path/to/storage/app/excel/template.xlsx"
}
```

#### Erreur de validation (422 Unprocessable Entity)

```json
{
  "message": "The data field is required.",
  "errors": {
    "data": [
      "The data field is required."
    ]
  }
}
```

#### Erreur serveur (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Erreur lors de la mise à jour du fichier Excel",
  "error": "Message d'erreur détaillé"
}
```

### Validation des données

Les règles de validation appliquées sont :

- `data` : Requis, doit être un tableau avec au moins 1 élément
- `data.*.colonne_excel` : Requis, doit être une chaîne de caractères
- `data.*.champ_kizeo` : Requis, doit être une chaîne de caractères
- `data.*.valeur` : Peut être null ou une valeur
- `data.*.rang` : Requis, doit être un entier supérieur ou égal à 1
- `fichier_excel` : Optionnel, doit être une chaîne de caractères

---

## 2. Télécharger un fichier Excel

### Endpoint

```
GET /api/excel/download/{filename?}
```

### Headers

```
X-API-Key: mdf_votre_cle_api_ici
```

### Paramètres

- `filename` (optionnel) : Nom du fichier Excel à télécharger. Par défaut : `template.xlsx`

### Exemple de requête avec curl

```bash
# Télécharger le fichier par défaut (template.xlsx)
curl -X GET http://localhost:8000/api/excel/download \
  -H "X-API-Key: mdf_votre_cle_api_ici" \
  -o template.xlsx

# Télécharger un fichier spécifique
curl -X GET http://localhost:8000/api/excel/download/rapport.xlsx \
  -H "X-API-Key: mdf_votre_cle_api_ici" \
  -o rapport.xlsx
```

### Exemple avec query parameter

```bash
curl -X GET "http://localhost:8000/api/excel/download?api_key=mdf_votre_cle_api_ici" \
  -o template.xlsx
```

### Exemple de requête avec JavaScript (fetch)

```javascript
fetch('http://localhost:8000/api/excel/download/template.xlsx', {
  method: 'GET',
  headers: {
    'X-API-Key': 'mdf_votre_cle_api_ici'
  }
})
.then(response => response.blob())
.then(blob => {
  // Créer un lien de téléchargement
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'template.xlsx';
  document.body.appendChild(a);
  a.click();
  a.remove();
  window.URL.revokeObjectURL(url);
})
.catch(error => console.error('Erreur:', error));
```

### Réponses

#### Succès (200 OK)
Le fichier Excel est retourné en tant que téléchargement binaire avec les headers appropriés.

#### Fichier non trouvé (404 Not Found)

```json
{
  "error": "File not found",
  "message": "The requested Excel file 'template.xlsx' does not exist"
}
```

#### Nom de fichier invalide (400 Bad Request)

```json
{
  "error": "Invalid filename",
  "message": "Filename cannot contain directory separators"
}
```

---

## 3. Lister les fichiers Excel disponibles

### Endpoint

```
GET /api/excel/list
```

### Headers

```
X-API-Key: mdf_votre_cle_api_ici
```

### Exemple de requête avec curl

```bash
curl -X GET http://localhost:8000/api/excel/list \
  -H "X-API-Key: mdf_votre_cle_api_ici"
```

### Exemple de requête avec JavaScript (fetch)

```javascript
fetch('http://localhost:8000/api/excel/list', {
  method: 'GET',
  headers: {
    'X-API-Key': 'mdf_votre_cle_api_ici'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Erreur:', error));
```

### Réponses

#### Succès (200 OK)

```json
{
  "files": [
    {
      "name": "template.xlsx",
      "size": 45678,
      "modified_at": "2025-11-05 22:30:15"
    },
    {
      "name": "rapport.xlsx",
      "size": 123456,
      "modified_at": "2025-11-04 15:20:10"
    }
  ],
  "count": 2
}
```

---

## Erreurs d'authentification

Si la clé API est manquante, invalide ou inactive, vous recevrez l'une de ces réponses :

### Clé API manquante (401 Unauthorized)

```json
{
  "error": "API key is required",
  "message": "Please provide a valid API key in X-API-Key header, Bearer token, or api_key query parameter"
}
```

### Clé API invalide (401 Unauthorized)

```json
{
  "error": "Invalid API key",
  "message": "The provided API key is not valid"
}
```

### Clé API inactive (403 Forbidden)

```json
{
  "error": "API key is inactive",
  "message": "The provided API key has been deactivated"
}
```

## Structure du projet

```
app/
├── Console/
│   └── Commands/
│       └── GenerateApiKey.php             # Commande pour générer des clés API
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── ExcelUpdateController.php  # Mise à jour des fichiers Excel
│   │       └── ExcelDownloadController.php # Téléchargement et listing
│   ├── Middleware/
│   │   └── ValidateApiKey.php             # Middleware d'authentification
│   └── Requests/
│       └── UpdateExcelRequest.php         # Validation des requêtes
├── Models/
│   └── ApiKey.php                          # Modèle de clé API
database/
└── migrations/
    └── *_create_api_keys_table.php         # Migration pour les clés API
routes/
└── api.php                                 # Routes API
storage/
└── app/
    └── excel/                              # Répertoire pour les fichiers Excel
        └── .gitkeep
```

## Démarrage du serveur

Pour tester l'API en local :

```bash
php artisan serve
```

L'API sera accessible sur `http://localhost:8000`

## Notes importantes

1. Le fichier Excel doit exister dans `storage/app/excel/` avant de faire des requêtes
2. Les cellules sont identifiées par leur colonne (A, B, C, etc.) et leur rang (numéro de ligne)
3. Les valeurs peuvent être null
4. Le fichier Excel est modifié directement sur le disque

## Sécurité

### Mesures de sécurité implémentées

Cette API implémente plusieurs mesures de sécurité :

1. **Authentification par clé API** : Toutes les routes sont protégées par un middleware d'authentification
2. **Clés uniques** : Chaque clé API est unique et préfixée par `mdf_`
3. **Tracking d'utilisation** : Chaque utilisation de clé est enregistrée avec `last_used_at`
4. **Activation/Désactivation** : Les clés peuvent être désactivées sans être supprimées
5. **Protection contre directory traversal** : Les noms de fichiers sont validés pour empêcher l'accès à des répertoires non autorisés
6. **Validation des entrées** : Toutes les données entrantes sont validées

### Recommandations pour la production

Pour une utilisation en production, considérez également :

- **HTTPS obligatoire** : Utilisez toujours HTTPS pour protéger les clés API en transit
- **Rate limiting** : Ajoutez une limitation de taux pour éviter les abus
- **Monitoring** : Surveillez l'utilisation des clés API et les tentatives d'accès non autorisées
- **Rotation des clés** : Implémentez une rotation régulière des clés API
- **Logs d'audit** : Enregistrez toutes les actions effectuées via l'API
- **Backup automatique** : Sauvegardez les fichiers Excel avant modification
- **Limite de taille** : Limitez la taille des requêtes et des fichiers
- **CORS** : Configurez correctement les headers CORS si nécessaire
- **Environnement sécurisé** : Stockez les fichiers Excel dans un emplacement sécurisé avec des permissions appropriées

### Gestion de la base de données en production

Pour la production, il est recommandé d'utiliser une base de données plus robuste que SQLite :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mdf_access
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```
