# Documentation des Clés API

## Vue d'ensemble

Le système de clés API permet de sécuriser et contrôler l'accès aux différentes API de l'application. Il supporte plusieurs types d'API et niveaux d'accès pour s'adapter à la croissance future de l'application.

## Structure de la table API Keys

La table `api_keys` contient les champs suivants :

| Champ | Type | Description |
|-------|------|-------------|
| `id` | bigint | Identifiant unique |
| `name` | string | Nom/description de la clé |
| `key` | string | Clé API (hashée avec SHA-256) |
| `api_type` | string | Type d'API (excel_update, reporting, analytics, etc.) |
| `access_level` | enum | Niveau d'accès (read, write, admin) |
| `permissions` | json | Permissions granulaires optionnelles |
| `user_id` | bigint | Utilisateur associé (optionnel) |
| `expires_at` | timestamp | Date d'expiration (optionnel) |
| `last_used_at` | timestamp | Dernière utilisation |
| `is_active` | boolean | Clé active/inactive |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de mise à jour |

## Types d'API

Les types d'API permettent de catégoriser les clés selon leur usage :

- **excel_update** : Pour l'API de mise à jour des fichiers Excel
- **reporting** : Pour les API de rapports (futur)
- **analytics** : Pour les API d'analytiques (futur)
- **custom** : Pour les APIs personnalisées

Vous pouvez ajouter de nouveaux types selon vos besoins.

## Niveaux d'accès

Les niveaux d'accès sont hiérarchiques :

1. **read** : Lecture seule (niveau 1)
2. **write** : Lecture et écriture (niveau 2)
3. **admin** : Accès administrateur complet (niveau 3)

Une clé avec un niveau supérieur a automatiquement accès aux opérations des niveaux inférieurs.

## Permissions granulaires

Le champ `permissions` (JSON) permet de définir des permissions spécifiques au-delà des niveaux d'accès. Exemple :

```json
[
    "excel.create",
    "excel.update",
    "excel.delete",
    "reports.generate"
]
```

## Création d'une clé API

### Via Tinker (console)

```bash
php artisan tinker
```

```php
use App\Models\ApiKey;

// Générer une nouvelle clé
$plainKey = ApiKey::generateKey();

// Créer l'enregistrement dans la base de données
$apiKey = ApiKey::create([
    'name' => 'Kizeo Forms Integration',
    'key' => ApiKey::hashKey($plainKey),
    'api_type' => 'excel_update',
    'access_level' => 'write',
    'permissions' => ['excel.update'],
    'is_active' => true,
    'expires_at' => now()->addYear(), // Expire dans 1 an
]);

// IMPORTANT : Sauvegarder $plainKey de manière sécurisée
echo "Votre clé API : " . $plainKey . "\n";
echo "ID de la clé : " . $apiKey->id . "\n";
```

**Important** : La clé en clair (`$plainKey`) doit être sauvegardée immédiatement car elle ne peut pas être récupérée ultérieurement (seul le hash est stocké).

### Exemple avec expiration

```php
// Clé qui expire dans 30 jours
$apiKey = ApiKey::create([
    'name' => 'Test API Key',
    'key' => ApiKey::hashKey(ApiKey::generateKey()),
    'api_type' => 'excel_update',
    'access_level' => 'write',
    'expires_at' => now()->addDays(30),
    'is_active' => true,
]);
```

### Exemple avec utilisateur associé

```php
use App\Models\User;

$user = User::find(1);
$plainKey = ApiKey::generateKey();

$apiKey = $user->apiKeys()->create([
    'name' => 'Personal API Key',
    'key' => ApiKey::hashKey($plainKey),
    'api_type' => 'excel_update',
    'access_level' => 'write',
    'is_active' => true,
]);
```

## Utilisation de l'API

### Via Header HTTP

```bash
curl -X POST https://votre-domaine.com/api/excel/update \
  -H "X-API-Key: votre-cle-api-en-clair" \
  -H "Content-Type: application/json" \
  -d '{"data": "..."}'
```

### Via Query Parameter

```bash
curl -X POST "https://votre-domaine.com/api/excel/update?api_key=votre-cle-api-en-clair" \
  -H "Content-Type: application/json" \
  -d '{"data": "..."}'
```

## Protection des routes

### Protection simple (sans restrictions)

```php
Route::post('/endpoint', [Controller::class, 'method'])
    ->middleware('api.key');
```

### Protection avec type d'API

```php
Route::post('/endpoint', [Controller::class, 'method'])
    ->middleware('api.key:excel_update');
```

### Protection avec type d'API et niveau d'accès

```php
Route::post('/endpoint', [Controller::class, 'method'])
    ->middleware('api.key:excel_update,write');
```

### Protection d'un groupe de routes

```php
Route::middleware('api.key:reporting,read')->group(function () {
    Route::get('/reports/list', [ReportController::class, 'list']);
    Route::get('/reports/{id}', [ReportController::class, 'show']);
});
```

## Gestion des clés API

### Vérifier une clé

```php
$apiKey = ApiKey::where('key', ApiKey::hashKey($plainKey))->first();

if ($apiKey && $apiKey->isValid()) {
    echo "Clé valide";
}
```

### Désactiver une clé

```php
$apiKey->update(['is_active' => false]);
```

### Réactiver une clé

```php
$apiKey->update(['is_active' => true]);
```

### Prolonger l'expiration

```php
$apiKey->update(['expires_at' => now()->addMonths(6)]);
```

### Vérifier la dernière utilisation

```php
$apiKey = ApiKey::find($id);
echo "Dernière utilisation : " . $apiKey->last_used_at;
```

### Récupérer les clés d'un utilisateur

```php
$user = User::find(1);
$apiKeys = $user->apiKeys()->active()->get();
```

## Requêtes avancées

### Récupérer les clés valides d'un type spécifique

```php
$keys = ApiKey::valid()
    ->byApiType('excel_update')
    ->get();
```

### Récupérer les clés avec un niveau d'accès minimum

```php
$keys = ApiKey::valid()
    ->byAccessLevel('write')
    ->get();
```

### Récupérer les clés utilisées récemment

```php
$recentKeys = ApiKey::whereNotNull('last_used_at')
    ->where('last_used_at', '>', now()->subDays(7))
    ->get();
```

### Récupérer les clés qui vont expirer

```php
$expiringKeys = ApiKey::active()
    ->whereNotNull('expires_at')
    ->whereBetween('expires_at', [now(), now()->addDays(30)])
    ->get();
```

## Vérification dans le contrôleur

Dans votre contrôleur, vous pouvez accéder à l'objet ApiKey :

```php
public function update(Request $request)
{
    $apiKey = $request->input('api_key_model');

    // Vérifier une permission spécifique
    if ($apiKey->hasPermission('excel.update')) {
        // Effectuer l'opération
    }

    // Vérifier le type d'API
    if ($apiKey->hasApiType('excel_update')) {
        // ...
    }

    // Vérifier le niveau d'accès
    if ($apiKey->hasAccessLevel('write')) {
        // ...
    }
}
```

## Codes d'erreur

| Code | Message | Description |
|------|---------|-------------|
| 401 | API key is missing | Aucune clé API fournie |
| 401 | Invalid API key | La clé API est invalide ou n'existe pas |
| 401 | Invalid API key (désactivée) | La clé API est désactivée |
| 401 | Invalid API key (expirée) | La clé API a expiré |
| 403 | Unauthorized API type | La clé n'a pas accès à ce type d'API |
| 403 | Insufficient access level | La clé n'a pas le niveau d'accès requis |

## Sécurité

### Bonnes pratiques

1. **Hashage** : Les clés sont toujours stockées hashées (SHA-256)
2. **HTTPS** : Toujours utiliser HTTPS en production
3. **Expiration** : Définir une date d'expiration pour les clés
4. **Rotation** : Renouveler régulièrement les clés
5. **Logs** : Monitorer l'utilisation via le champ `last_used_at`
6. **Désactivation** : Désactiver immédiatement les clés compromises

### Exemple de rotation de clé

```php
// Désactiver l'ancienne clé
$oldKey->update(['is_active' => false]);

// Créer une nouvelle clé
$newPlainKey = ApiKey::generateKey();
$newKey = ApiKey::create([
    'name' => $oldKey->name . ' (Renouvelée)',
    'key' => ApiKey::hashKey($newPlainKey),
    'api_type' => $oldKey->api_type,
    'access_level' => $oldKey->access_level,
    'permissions' => $oldKey->permissions,
    'user_id' => $oldKey->user_id,
    'is_active' => true,
    'expires_at' => now()->addYear(),
]);

echo "Nouvelle clé : " . $newPlainKey;
```

## Migration de la base de données

Pour créer la table dans votre base de données :

```bash
php artisan migrate
```

Pour annuler la migration :

```bash
php artisan migrate:rollback
```

## Exemples de scénarios d'utilisation

### Scénario 1 : Application tierce avec accès limité

```php
$apiKey = ApiKey::create([
    'name' => 'Application Externe - Lecture seule',
    'key' => ApiKey::hashKey(ApiKey::generateKey()),
    'api_type' => 'reporting',
    'access_level' => 'read',
    'permissions' => ['reports.list', 'reports.view'],
    'expires_at' => now()->addMonths(3),
    'is_active' => true,
]);
```

### Scénario 2 : Intégration Kizeo avec accès complet

```php
$apiKey = ApiKey::create([
    'name' => 'Kizeo Forms - Mise à jour Excel',
    'key' => ApiKey::hashKey(ApiKey::generateKey()),
    'api_type' => 'excel_update',
    'access_level' => 'write',
    'permissions' => ['excel.update', 'excel.read'],
    'is_active' => true,
    // Pas d'expiration pour une intégration permanente
]);
```

### Scénario 3 : Clé administrateur

```php
$apiKey = ApiKey::create([
    'name' => 'Admin Key',
    'key' => ApiKey::hashKey(ApiKey::generateKey()),
    'api_type' => 'admin',
    'access_level' => 'admin',
    'permissions' => ['*'], // Toutes les permissions
    'user_id' => 1, // Associée à l'admin
    'expires_at' => now()->addMonths(6),
    'is_active' => true,
]);
```

## Extension future

Le système est conçu pour s'adapter à la croissance de l'application :

1. **Nouveaux types d'API** : Ajoutez simplement de nouvelles valeurs dans `api_type`
2. **Niveaux d'accès personnalisés** : Modifiez l'enum dans la migration
3. **Permissions granulaires** : Utilisez le champ JSON `permissions`
4. **Rate limiting** : Ajoutez des champs pour le contrôle du débit
5. **IP whitelisting** : Ajoutez un champ JSON pour les IP autorisées

## Support

Pour toute question ou problème, consultez la documentation principale de l'application ou contactez l'équipe de développement.
