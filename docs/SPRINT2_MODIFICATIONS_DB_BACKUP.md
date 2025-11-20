# 🔧 Sprint 2 - Modifications pour Compatibilité DB Backup

**Date :** 20 novembre 2025
**Objectif :** Adapter le code du Sprint 2 (RLS) pour qu'il soit compatible avec le backup de la base de données réelle
**Statut :** ✅ Complété

---

## 📊 Problème Identifié

Le code du Sprint 2 a été implémenté en supposant que la table `organizations` avait une colonne `type` avec les valeurs `'Internal'`, `'Client'`, `'Partner'`. Cependant, le backup de la DB réelle (`db_backup.sql`) utilise la **nouvelle architecture contextuelle** où :

- ❌ **Pas de colonne `type`** dans `organizations`
- ✅ **Rôles contextuels** définis par projet dans `project_organizations`
- ✅ Une organisation peut avoir différents rôles selon les projets

### Architecture Avant (Sprint 2 initial)
```
organizations
├── id
├── name
├── type → 'Internal' | 'Client' | 'Partner'  ← N'EXISTE PAS dans DB réelle
└── status
```

### Architecture Réelle (DB Backup)
```
organizations
├── id
├── name
├── status
└── (pas de colonne type)

project_organizations
├── project_id
├── organization_id
├── role → 'sponsor' | 'moa' | 'moe' | 'subcontractor'
└── status
```

---

## 🔄 Modifications Apportées

### 1. Migration : Ajouter flag `is_internal`

**Fichier :** `database/migrations/2025_11_20_222500_add_is_internal_to_organizations_table.php`

**But :** Identifier l'organisation interne (SAMSIC) pour le bypass RLS sans utiliser une colonne `type`

```sql
ALTER TABLE organizations
ADD COLUMN is_internal BOOLEAN DEFAULT FALSE;

UPDATE organizations
SET is_internal = TRUE
WHERE id = 1; -- SAMSIC MAINTENANCE MAROC
```

**Avantage :**
- ✅ Simple flag booléen (pas de contraintes complexes)
- ✅ Compatible avec l'architecture contextuelle
- ✅ SAMSIC conserve son bypass RLS

---

### 2. Model `User` : Méthodes adaptées à l'architecture contextuelle

**Fichier :** `app/Models/User.php`

#### Méthode `isInternal()`

```php
/**
 * Vérifier si l'utilisateur appartient à une organisation interne (SAMSIC)
 */
public function isInternal(): bool
{
    return $this->organization?->is_internal === true;
}
```

**Logique :** Vérifie le flag `is_internal` au lieu de `type === 'Internal'`

#### Méthode `isClient()`

```php
/**
 * Vérifier si l'utilisateur est un client
 * Note: Avec l'architecture contextuelle, vérifie si l'org a AU MOINS UN projet en tant que sponsor
 */
public function isClient(): bool
{
    if (!$this->organization_id) {
        return false;
    }

    return \DB::table('project_organizations')
        ->where('organization_id', $this->organization_id)
        ->where('role', 'sponsor')
        ->where('status', 'active')
        ->exists();
}
```

**Logique :** Une organisation est "cliente" si elle a au moins un projet où elle est sponsor

#### Méthode `isPartner()`

```php
/**
 * Vérifier si l'utilisateur est un partenaire
 * Note: Vérifie si l'org participe à des projets sans être interne
 */
public function isPartner(): bool
{
    if (!$this->organization_id || $this->isInternal()) {
        return false;
    }

    return \DB::table('project_organizations')
        ->where('organization_id', $this->organization_id)
        ->where('status', 'active')
        ->exists();
}
```

**Logique :** Une organisation est "partenaire" si elle participe à des projets et n'est pas interne

---

### 3. Model `Organization` : Support du flag `is_internal`

**Fichier :** `app/Models/Organization.php`

```php
protected $fillable = [
    'name',
    'address',
    'ville',
    'contact_info',
    'logo',
    'status',
    'is_internal', // ← Ajouté
];

protected $casts = [
    'contact_info' => 'array',
    'is_internal' => 'boolean', // ← Ajouté
];

/**
 * Vérifier si l'organisation est interne (SAMSIC)
 */
public function isInternal(): bool
{
    return $this->is_internal === true;
}
```

---

### 4. `TenantScope` : Logique simplifiée pour architecture contextuelle

**Fichier :** `app/Scopes/TenantScope.php`

**Changement majeur :** Simplification de la logique de filtrage

#### Avant (4 cas complexes)
```php
if ($user->isSystemAdmin()) return;        // Bypass
if ($user->isInternal()) return;           // Bypass
if ($user->isClient()) applyClientFilter(); // Filtre client_organization_id
if ($user->isPartner()) applyPartnerFilter(); // Filtre project_organizations
```

#### Après (3 cas simples)
```php
if ($user->isSystemAdmin()) return;     // Bypass
if ($user->isInternal()) return;        // Bypass
applyParticipationFilter();             // Filtre sur participations (tous les autres)
```

**Nouvelle méthode `applyParticipationFilter()`**

```php
protected function applyParticipationFilter(Builder $builder, $user): void
{
    if (!$user->organization_id) {
        $builder->whereRaw('1 = 0');
        return;
    }

    $tableName = $builder->getModel()->getTable();

    if ($tableName === 'projects') {
        // Filtre direct sur project_organizations
        $builder->whereExists(function ($query) use ($user) {
            $query->select(\DB::raw(1))
                  ->from('project_organizations')
                  ->whereColumn('project_organizations.project_id', 'projects.id')
                  ->where('project_organizations.organization_id', $user->organization_id)
                  ->where('project_organizations.status', 'active');
        });
    } elseif ($this->hasColumn($tableName, 'project_id')) {
        // Tables liées aux projets
        $builder->whereHas('project', function ($query) use ($user) {
            $query->whereExists(function ($subQuery) use ($user) {
                $subQuery->select(\DB::raw(1))
                         ->from('project_organizations')
                         ->whereColumn('project_organizations.project_id', 'projects.id')
                         ->where('project_organizations.organization_id', $user->organization_id)
                         ->where('project_organizations.status', 'active');
            });
        });
    } else {
        $builder->whereRaw('1 = 0');
    }
}
```

**Avantages :**
- ✅ **Plus simple** : Un seul filtre pour tous les types d'organisations (hors bypass)
- ✅ **Plus flexible** : Les organisations voient tous leurs projets, quel que soit leur rôle
- ✅ **Plus réaliste** : Reflète la vraie logique métier

**Note importante :**
Avec l'architecture contextuelle, une organisation peut être **à la fois** sponsor sur un projet et MOE sur un autre. Le filtre unique basé sur les participations gère automatiquement tous ces cas.

---

## 🧪 Script de Test Adapté

**Fichier :** `test_sprint2_rls_updated.php`

Le script de test a été adapté pour :
- ✅ Vérifier que la colonne `is_internal` existe
- ✅ Tester le bypass pour System Admin et Internal
- ✅ Tester le filtrage pour organisations avec participations
- ✅ Tester qu'une org sans participations voit 0 projets
- ✅ Tester `withoutTenantScope()`

---

## 📋 Résumé des Changements

| Composant | Changement | Impact |
|-----------|------------|--------|
| **DB** | Ajout colonne `is_internal` | Migration nécessaire |
| **User Model** | Méthodes `isInternal()`, `isClient()`, `isPartner()` adaptées | Compatible architecture contextuelle |
| **Organization Model** | Support `is_internal` | Identification SAMSIC |
| **TenantScope** | Logique simplifiée (3 cas au lieu de 4) | Code plus simple et maintenable |
| **Tests** | Script adapté à la nouvelle architecture | Validation complète |

---

## ✅ Avantages de Cette Approche

### 1. **Compatibilité avec DB Réelle**
- ✅ Fonctionne avec le backup `db_backup.sql`
- ✅ Respecte l'architecture contextuelle en place
- ✅ Pas besoin de restaurer la colonne `type` supprimée

### 2. **Simplicité**
- ✅ Un seul flag booléen `is_internal` au lieu d'une colonne `type` avec 3+ valeurs
- ✅ Logique RLS simplifiée : bypass ou participations
- ✅ Moins de code à maintenir

### 3. **Flexibilité**
- ✅ Les organisations peuvent avoir plusieurs rôles selon les projets
- ✅ Pas de contraintes artificielles (type fixe)
- ✅ Reflète la vraie vie métier

### 4. **Sécurité**
- ✅ Filtrage par défaut sur participations actives
- ✅ Bypass uniquement pour System Admin et SAMSIC
- ✅ Protection `whereRaw('1 = 0')` pour les cas non gérés

---

## 🚀 Migration et Déploiement

### Étapes de Déploiement

1. **Exécuter la migration**
   ```bash
   php artisan migrate
   ```

2. **Vérifier que SAMSIC est marquée comme interne**
   ```sql
   SELECT id, name, is_internal FROM organizations WHERE id = 1;
   -- Devrait retourner: 1 | SAMSIC MAINTENANCE MAROC | true
   ```

3. **Exécuter les tests**
   ```bash
   php test_sprint2_rls_updated.php
   ```

4. **Vérifier que tous les tests passent** ✅

---

## 📝 Notes pour l'Équipe

### Comportement Important à Comprendre

Avec l'architecture contextuelle :
- Une organisation **n'est plus** exclusivement Cliente, MOE ou Partenaire
- Les méthodes `isClient()` et `isPartner()` retournent `true` si l'org a **AU MOINS UN** projet avec ce rôle
- Une organisation peut être `isClient() = true` ET `isPartner() = true` en même temps

**Exemple concret :**
```php
// Organisation XYZ participe à 3 projets :
// - Projet A : role = 'sponsor'
// - Projet B : role = 'moe'
// - Projet C : role = 'subcontractor'

$user->isClient()   // TRUE (car sponsor sur Projet A)
$user->isPartner()  // TRUE (car participe à des projets)

// RLS : L'utilisateur voit les 3 projets (A, B, C)
// Car il filtre sur TOUTES les participations actives
```

---

## 🎯 Conclusion

Les modifications apportées permettent au code du Sprint 2 de fonctionner avec la structure réelle de la base de données tout en :
- ✅ Conservant la fonctionnalité RLS (Row-Level Security)
- ✅ Respectant l'architecture contextuelle
- ✅ Simplifiant la logique de filtrage
- ✅ Gardant la sécurité multi-tenant

Le système est maintenant **prêt pour la production** et compatible avec le backup DB réel.

---

**Document créé :** 20 novembre 2025
**Version :** 1.0
**Auteur :** Équipe Dev MDF Access
**Status :** ✅ Modifications complétées et testées
