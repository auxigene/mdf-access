# API de Mise à Jour Excel - Documentation

## Description

Cette API permet de mettre à jour un fichier Excel stocké localement avec des données provenant de Kizeo Forms.

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

## Utilisation de l'API

### Endpoint

```
POST /api/excel/update
```

### Headers

```
Content-Type: application/json
Accept: application/json
X-API-Key: votre-cle-api
```

**Important** : Une clé API valide est maintenant **requise** pour utiliser cette API. Voir la section "Authentification" ci-dessous.

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
  -H "X-API-Key: votre-cle-api" \
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
    'X-API-Key': 'votre-cle-api'
  },
  body: JSON.stringify(data)
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Erreur:', error));
```

## Authentification

Cette API utilise un système d'authentification par clé API. Chaque requête doit inclure une clé API valide.

### Obtenir une clé API

Les clés API sont créées par les administrateurs. Contactez votre administrateur système pour obtenir une clé.

### Utiliser la clé API

La clé API peut être fournie de deux façons :

1. **Via le header HTTP** (recommandé) :
```bash
X-API-Key: votre-cle-api-en-clair
```

2. **Via un paramètre de query** :
```bash
?api_key=votre-cle-api-en-clair
```

### Caractéristiques de sécurité

- La clé API doit être de type `excel_update`
- La clé API doit avoir le niveau d'accès `write` ou supérieur
- Les clés peuvent avoir une date d'expiration
- Les clés peuvent être désactivées par un administrateur
- Chaque utilisation de la clé est enregistrée

### Erreurs d'authentification

#### Clé API manquante (401)

```json
{
  "error": "API key is missing",
  "message": "Vous devez fournir une clé API via le header X-API-Key ou le paramètre api_key"
}
```

#### Clé API invalide (401)

```json
{
  "error": "Invalid API key",
  "message": "La clé API fournie est invalide"
}
```

#### Clé API désactivée (401)

```json
{
  "error": "Invalid API key",
  "message": "La clé API est désactivée"
}
```

#### Clé API expirée (401)

```json
{
  "error": "Invalid API key",
  "message": "La clé API a expiré"
}
```

#### Type d'API non autorisé (403)

```json
{
  "error": "Unauthorized API type",
  "message": "Cette clé API n'a pas accès au type d'API: excel_update"
}
```

#### Niveau d'accès insuffisant (403)

```json
{
  "error": "Insufficient access level",
  "message": "Cette clé API ne dispose pas du niveau d'accès requis: write"
}
```

Pour plus d'informations sur la gestion des clés API, consultez [API_KEY_DOCUMENTATION.md](./API_KEY_DOCUMENTATION.md).

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

## Validation des données

Les règles de validation appliquées sont :

- `data` : Requis, doit être un tableau avec au moins 1 élément
- `data.*.colonne_excel` : Requis, doit être une chaîne de caractères
- `data.*.champ_kizeo` : Requis, doit être une chaîne de caractères
- `data.*.valeur` : Peut être null ou une valeur
- `data.*.rang` : Requis, doit être un entier supérieur ou égal à 1
- `fichier_excel` : Optionnel, doit être une chaîne de caractères

## Structure du projet

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── ExcelUpdateController.php  # Contrôleur principal
│   └── Requests/
│       └── UpdateExcelRequest.php         # Validation des requêtes
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

Cette API est maintenant sécurisée avec un système d'authentification par clé API. Pour une utilisation en production, considérez également :

- ✅ Authentification par clé API (implémenté)
- ✅ Contrôle des types d'API et niveaux d'accès (implémenté)
- ✅ Tracking de l'utilisation des clés (implémenté)
- Utiliser HTTPS en production
- Valider les permissions d'accès aux fichiers
- Limiter la taille des requêtes
- Ajouter du rate limiting
- Sauvegarder les fichiers Excel avant modification
- Implémenter un système de logs d'audit

Pour la gestion complète des clés API, consultez [API_KEY_DOCUMENTATION.md](./API_KEY_DOCUMENTATION.md).
