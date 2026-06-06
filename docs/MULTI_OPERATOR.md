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

### Delvis generiskt (mönster rätt, Lennakatten-fallback kvar)

| Område | Status | Detalj |
|--------|--------|--------|
| **Tågbyte (train change)** | Delvis | Per-station meta `mrt_station_train_change_map` i `inc/domain/journey/train-change.php`. Overview och bytes-hub använder `MRT_get_station_train_change_map()`. **Saknas:** REST-fält, admin-UI, CSV-kolumn. Fallback: hårdkodad Marielund-karta (`MRT_default_train_change_maps_by_station_title()`). |
| **Priszoner** | Delvis | Meta/CSV fungerar. Tom meta → titel-fallback `MRT_default_station_price_zones_by_title()` (Lennakatten 2026). |
| **Byteshubbar** | Delvis | Generella signaler: terminus, `mrt_transfer_priority`, buss-*, train-change-karta. Buss-shuttle (2 hållplatser + buss-flagga) är datamodell, exemplifierad med Selknä–Fjällnora i fixture. |

### Fortfarande Lennakatten-låst (produktion)

| Område | Var | Konfigurerbart idag? |
|--------|-----|----------------------|
| Wizard-rubrik | `inc/public/vue-shortcode-config.php` → `"Planera resa med Lennakatten"` | Nej (shortcode `hero_subtitle` finns, inte `routeTitle`) |
| Publikt tema | `assets/mrt-color-tokens.css`, `assets/mrt-typography.css`, [design/BRAND_UI.md](design/BRAND_UI.md) | Nej |
| Tom prismatris | `inc/domain/pricing/price-matrix-builtin.php` (Taxa 2026) | Belopp ja via admin; **default vid tom DB = Lennakatten** |
| Tom prisstruktur-default | `MRT_get_default_price_schema()` | Struktur redigerbar; nycklar/defaults Lennakatten-typiska |
| Eftermiddags-retur kl 15 | `MRT_qualifies_for_afternoon_return()` — tröskel `900` | Nej (belopp konfigurerbart) |
| Max 2 byten, poängvikter | `engine/constraints.php`, `journey-scoring.php` | Bara WP-filter |
| Tidtabellfärger (5 st) | `inc/domain/timetable/timetable-type.php`, Vue `calendarDay.ts`, CSS | Nej |
| Tågtyp-ikoner (4 PNG + slug-map) | `inc/domain/train-type/icons.php`, `trainTypeIcons.ts` | Ikon per typ i admin; **uppsättningen** hårdkodad |
| Inställd-text | `MRT_CANCEL_TRAFFIC_NOTICE = 'Inställd'` | Nej |

### Avsiktligt Lennakatten (dev only — behåll)

- `POST /dev/import-lennakatten`, DevToolsPage
- `inc/public/journey-wizard/debug-fixtures.php`
- `inc/admin/tools/demo-page.php` m.fl.
- `testdata/fixtures/lennakatten/`

---

## Faser

### Tier A — “Annann förening kan ta det i bruk” (prioritet)

| # | Uppgift | Status | Anteckning |
|---|---------|--------|------------|
| A1 | **Neutral wizard-rubrik** — generisk default + shortcode-attribut `route_title` (eller global `operator_name` i `mrt_settings`) | Todo | Idag: hårdkodad sträng + Vue-fallback |
| A2 | **Tom/neutral pris-default** — builtin-matris tom eller uppenbart “ej konfigurerad”; dashboard-varning tills priser sparats/importerats | Todo | `MRT_get_default_price_matrix()` |
| A3 | **Ta bort titel-fallback för priszoner** — tom meta = inga zoner (eller zon 1), inte Lennakatten-karta | Todo | `MRT_get_station_price_zones()`; ev. behåll karta endast i dev-import |
| A4 | **Ta bort titel-fallback för tågbyte** — tom meta = ingen train-change-rad / ingen hub via karta | Todo | `MRT_get_station_train_change_map()`; Marielund-data → fixture/meta vid Lennakatten-import |
| A5 | **Tema override** — dokumentera `--mrt-*` CSS-variabler; neutral default-palett i tokens (Lennakatten som valfri “brand pack” eller child theme) | Todo | Se [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md) |
| A6 | **Dokumentera onboarding** — “Ny förening”: CSV-import, shortcodes, inställningar | Todo | Kort avsnitt i DEVELOPER.md eller egen sida |

**Definition of done Tier A:** Tom install + eget CSV → ingen Lennakatten-text, inga Lennakatten-priser/zoner/tågbyte i runtime.

### Tier B — “Drift utan utvecklare”

| # | Uppgift | Status | Anteckning |
|---|---------|--------|------------|
| B1 | **Train change i admin + REST + CSV** | Todo | Meta finns; `stations.php` saknar fält; ingen admin-UI |
| B2 | **`operator_name`, `ticket_url`, ev. `brand_accent`** i `mrt_settings` | Todo | Biljett-URL idag bara shortcode |
| B3 | **Inställning: eftermiddagsgräns** (minuter efter midnatt) | Todo | Ersätter hårdkodad 15:00 |
| B4 | **Inställning: max byten** (UI för filter `mrt_journey_max_transfers`) | Todo | Default 2 i kod |
| B5 | **Operatörshandbok** — vilka meta-fält styr buss-hub, bytesprioritet, zoner | Todo | DATA_MODEL + CSV_FORMAT |

### Tier C — “Vilket prissystem som helst” (ej mål nu)

Medvetet **out of scope** tills behov finns: flat taxa utan zoner, helt annorlunda biljettyper, obegränsade tidtabellfärger. Zonmatris + kategorier täcker de flesta svenska museijärnvägar.

---

## Mönster att följa (train change visar vägen)

Det nya lagret i `train-change.php` är rätt riktning:

```
Importerad / admin-data (meta)  →  används i runtime
Tom meta                        →  neutralt (ej Lennakatten-karta)
Lennakatten-exempel             →  endast dev-import / fixture
```

Samma mönster ska gälla **priszoner** och **defaults** generellt.

**Undvik:** titelsträng som primärnyckel i produktion (`'Marielund'`, `'Uppsala Östra'`) — okej i fixture, farligt om station byter namn.

---

## Vad som *inte* behöver generaliseras

- Resemotorns grund (pickup/dropoff, riktning, min/max byte, overshoot) — rimlig för linjebundna museijärnvägar
- Fem tidtabellfärger — branschkonvention i Sverige; dokumentera snarare än obegränsad lista
- Fyra tågtyp-ikoner — täcker normal trafik; nya ikoner = tillägg i kod + PNG
- Lennakatten dev-import — värdefull demo, ska finnas kvar

---

## Nästa steg (rekommenderad ordning)

1. **A3 + A4** — ta bort titel-fallbacks (minimalt diff, störst säkerhetsvinst)
2. **A2** — neutral pris-default + dashboard-varning
3. **A1** — operatornamn / route_title
4. **B1** — train change synlig i admin och CSV (meta finns redan)
5. **A5** — neutral CSS-default

Uppdatera status i denna fil när steg levereras.
