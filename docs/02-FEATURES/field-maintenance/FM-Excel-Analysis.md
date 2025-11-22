# Analyse du Parc Sites INWI - Excel

**Date:** 2025-11-13 01:05:27

---

## Vue d'ensemble

- **Feuilles:** 8

### Feuille: `PARC_SITES_INWI`

- **Lignes de données:** 8842 (+ 1 ligne d'en-tête)
- **Colonnes utiles:** 7

#### Colonnes

| # | Col | Nom | Description |
|---|-----|-----|-------------|
| 1 | A | SiteID | |
| 2 | B | GSM ID | |
| 3 | C | ZI | |
| 4 | D | Classification Site V. Avril 2025 | |
| 5 | E | Source d'Energie | |
| 6 | F | Typologie  Maintenance | |
| 7 | G | Colloc | |

### Feuille: `Params_Zones`

- **Lignes de données:** 7 (+ 1 ligne d'en-tête)
- **Colonnes utiles:** 4

#### Colonnes

| # | Col | Nom | Description |
|---|-----|-----|-------------|
| 1 | A | Reference | |
| 2 | B | Code | |
| 3 | C | ZI | |
| 4 | D | Zone Geographique | |

### Feuille: `Params_Classifications`

- **Lignes de données:** 5 (+ 1 ligne d'en-tête)
- **Colonnes utiles:** 3

#### Colonnes

| # | Col | Nom | Description |
|---|-----|-----|-------------|
| 1 | A | Reference | |
| 2 | B | Code | |
| 3 | C | Classe | |

### Feuille: `Params_SourceEnergie`

- **Lignes de données:** 15 (+ 1 ligne d'en-tête)
- **Colonnes utiles:** 3

#### Colonnes

| # | Col | Nom | Description |
|---|-----|-----|-------------|
| 1 | A | Reference | |
| 2 | B | Code | |
| 3 | C | Source_Energie | |

### Feuille: `Params_Typologie_Maintenance`

- **Lignes de données:** 5 (+ 1 ligne d'en-tête)
- **Colonnes utiles:** 3

#### Colonnes

| # | Col | Nom | Description |
|---|-----|-----|-------------|
| 1 | A | Reference | |
| 2 | B | Code | |
| 3 | C | Typologie  Maintenance | |

### Feuille: `Params_Site_Shared_With_Tenant`

- **Lignes de données:** 23 (+ 1 ligne d'en-tête)
- **Colonnes utiles:** 3

#### Colonnes

| # | Col | Nom | Description |
|---|-----|-----|-------------|
| 1 | A | ID | |
| 2 | B | Code | |
| 3 | C | Name | |

### Feuille: `Params_Tenants_Config`

- **Lignes de données:** 36 (+ 1 ligne d'en-tête)
- **Colonnes utiles:** 8

#### Colonnes

| # | Col | Nom | Description |
|---|-----|-----|-------------|
| 1 | A | Reference | |
| 2 | B | Code | |
| 3 | C | Colloc | |
| 4 | D | Tenet_1 | |
| 5 | E | Tenet_2 | |
| 6 | F | Tenet_3 | |
| 7 | G | Tenet_4 | |
| 8 | H | Nbr_Tenant | |

### Feuille: `Site_Tenet`

- **Lignes de données:** 0 (+ 1 ligne d'en-tête)
- **Colonnes utiles:** 6

#### Colonnes

| # | Col | Nom | Description |
|---|-----|-----|-------------|
| 1 | A | Code Site | |
| 2 | B | Code Tenet | |
| 3 | C | Tenet Rank | |
| 4 | D | Created At | |
| 5 | E | Updated At | |
| 6 | F | Deleted At | |

## Suggestions de Structure de Base de Données

Basé sur la feuille principale `PARC_SITES_INWI`:

### Table principale: `fm_sites`

```sql
CREATE TABLE fm_sites (
    id SERIAL PRIMARY KEY,
    siteid VARCHAR(50),
    gsm_id VARCHAR(50),
    zi VARCHAR(255),
    classification_site_v_avril_2025 VARCHAR(255),
    source_denergie VARCHAR(255),
    typologie__maintenance VARCHAR(255),
    colloc VARCHAR(255),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);
```

