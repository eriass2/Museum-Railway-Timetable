# Multi-operator – plan och status

**Datum:** 2026-06  
**Princip:** Lennakatten är **referensoperatör** (dev-fixture, tester, designexempel), inte runtime-default för andra föreningar.

Beslut sammanfattat i [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md) §11.

---

## Mål

En annan museijärnvägsförening ska kunna:

1. Installera pluginet utan Lennakatten-data i produktion
2. Importera eget CSV-paket (stationer, rutter, tidtabeller, priser)
3. Få neutralt publikt UI (namn, färger) utan kodändring
4. Inte riskera fel priser/zoner/tågbyte om deras data saknas

Testdata (`testdata/fixtures/lennakatten/`) och Lennakatten-tester **behålls** som referens — de ska inte styra tom databas.

---

## Nuvarande läge (kodbas 2026-06)

### Redan generiskt (data-driven)

| Område | Mekanism | Kod |
|--------|----------|-----|
| Tidtabell, turer, stopptider | CSV + Vue-admin | `inc/import/csv/`, REST |
| Tågtyper | Taxonomi + admin REST | `inc/infrastructure/rest/train-types.php` |
| Prisstruktur och belopp | Admin → Priser, CSV | `mrt_price_schema`, `mrt_price_matrix` |
| Priszoner per station | Meta / CSV `price_zones` | `inc/domain/pricing/station-zones.php` |
| Min/max bytestid | Admin → Inställningar, CSV | `mrt_settings` |
| REST / klient-config | PHP → Vue mount JSON | `inc/assets/frontend.php`, `mrtRest.ts` |
| Resesökmotor | BFS på importerad trafik | `inc/domain/journey/engine/` |

### Delvis generiskt

| Område | Status | Detalj |
|--------|--------|--------|
| **Tågbyte (train change)** | Delvis | Meta `mrt_station_train_change_map`; **ingen titel-fallback**. Lennakatten-import sår meta via `MRT_lennakatten_seed_train_change_maps()`. **Saknas:** REST, admin-UI, CSV. |
| **Priszoner** | Delvis | Meta/CSV; **ingen titel-fallback**. Tom meta = inga zoner. |
| **Wizard-rubrik** | Delvis | Neutral default ”Planera resa”; shortcode `route_title` för operatörsnamn. |
| **Prismatris** | Delvis | Tom default (`MRT_build_empty_price_matrix()`). Lennakatten-belopp kvar i `price-matrix-builtin.php` för referens/import. |
| **Byteshubbar** | Delvis | Terminus, `mrt_transfer_priority`, buss-*, train-change-meta. |

### Fortfarande Lennakatten-låst (produktion)

| Område | Var | Konfigurerbart idag? |
|--------|-----|----------------------|
| Publikt tema | `assets/mrt-color-tokens.css`, [design/BRAND_UI.md](design/BRAND_UI.md) | Nej |
| Eftermiddags-retur kl 15 | `price-rules-matrix.php` — tröskel `900` | Nej (belopp i schema) |
| Eftermiddags-retur-belopp (default schema) | `MRT_get_default_afternoon_return_prices()` | Via admin efter spar/import |
| Max 2 byten, poängvikter | `engine/constraints.php`, `journey-scoring.php` | Bara WP-filter |
| Tidtabellfärger (5 st) | `timetable-type.php`, Vue, CSS | Nej |
| Tågtyp-ikoner (4 PNG + slug-map) | `train-type/icons.php` | Ikon per typ; uppsättning hårdkodad |

### Avsiktligt Lennakatten (dev only)

- `POST /dev/import-lennakatten`, `inc/import/lennakatten/reference-data.php`
- `inc/public/journey-wizard/debug-fixtures.php`
- `testdata/fixtures/lennakatten/`

---

## Faser

### Tier A — “Annann förening kan ta det i bruk”

| # | Uppgift | Status |
|---|---------|--------|
| A1 | Neutral wizard-rubrik + shortcode `route_title` | **Klar** |
| A2 | Tom pris-default (`MRT_build_empty_price_matrix`) | **Klar** |
| A3 | Ingen titel-fallback för priszoner | **Klar** |
| A4 | Ingen titel-fallback för tågbyte; Lennakatten-import sår meta | **Klar** |
| A5 | Tema override (neutral `--mrt-*` default) | Todo |
| A6 | Onboarding-dokumentation för ny förening | Todo |

**Definition of done Tier A:** Tom install + eget CSV → ingen Lennakatten-text, inga Lennakatten-priser/zoner/tågbyte i runtime. *(Kvar: A5–A6, CSS-branding.)*

### Tier B — “Drift utan utvecklare”

| # | Uppgift | Status |
|---|---------|--------|
| B1 | Train change i admin + REST + CSV | Todo |
| B2 | `operator_name`, global `ticket_url` i `mrt_settings` | Todo |
| B3 | Inställning: eftermiddagsgräns | Todo |
| B4 | Inställning: max byten (UI) | Todo |
| B5 | Operatörshandbok (meta-fält) | Todo |

### Tier C — Out of scope

Flat taxa utan zoner, obegränsade tidtabellfärger, vilket prissystem som helst.

---

## Mönster

```
Importerad / admin-data (meta)  →  runtime
Tom meta                        →  neutralt (tomt)
Lennakatten-exempel             →  reference-data.php + dev-import
```

---

## Nästa steg

1. **A5** — neutral CSS-default (Lennakatten som valfri profil)
2. **A6** — kort onboarding i DEVELOPER.md
3. **B1** — train change i admin/REST/CSV (meta finns)

Uppdatera status i denna fil när steg levereras.
