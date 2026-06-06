# Ny förening – kom igång

Kort onboarding för en museijärnväg som ska använda pluginet **utan** Lennakatten-data i produktion.

Se även [MULTI_OPERATOR.md](MULTI_OPERATOR.md) för produktplan och [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) för daglig admin.

---

## 1. Installera pluginet

1. Ladda upp/aktivera **Museum Railway Timetable** i WordPress.
2. Tom databas ger **neutralt** publikt UI och **inga** priser/zoner/tågbyte tills du konfigurerar dem.

---

## 2. Importera er data (rekommenderat)

1. Gå till **Railway Timetable → Import / export** i Vue-admin.
2. Bygg ett CSV-zip enligt [CSV_FORMAT.md](CSV_FORMAT.md) med minst:
   - `stations.csv` — stationer + `price_zones`
   - `routes.csv` + `route_stations.csv`
   - `timetables.csv` + `timetable_dates.csv`
   - `services.csv` + `stoptimes.csv`
   - `train_types.csv`
3. Valfritt: `prices.csv` + `price_schema.csv`, `settings.csv`
4. Valfritt: `station_train_changes.csv` — anslutande tåg i tidtabellsöversikten (t.ex. byte Marielund 71 → Dieseltåg 61)
5. Importera i läge **merge** (första gången) eller **override** (ersätt fixture-data).

Mallen exporteras från admin eller via `composer csv:template`.

---

## 3. Priser och inställningar

| Uppgift | Var |
|---------|-----|
| Prismatris och prisstruktur | Admin → **Priser** |
| Min/max bytestid | Admin → **Inställningar** |
| Priszoner per station | Admin → **Stationer & rutter** (eller CSV) |
| Tågbyte per station | Admin → **Stationer & rutter** → Tågbyte (eller CSV) |

Saknas priser i databasen visas inga belopp i reseplaneraren (fail empty).

---

## 4. Publikt UI

### Shortcodes

| Shortcode | Syfte |
|-----------|--------|
| `[museum_journey_wizard route_title="Planera resa med …"]` | Reseplanerare |
| `[museum_timetable_month]` | Månadskalender |
| `[museum_timetable_overview timetable_id="…"]` | Tryckt tidtabellsöversikt |

Se [SHORTCODES.md](SHORTCODES.md).

### Utseende (tema)

- **Standard:** neutral blå/brass-profil i [`assets/mrt-color-tokens.css`](../assets/mrt-color-tokens.css) och systemtypsnitt.
- **Override:** sätt egna `--mrt-*`-variabler i child theme eller sajt-CSS.
- **Lennakatten-profil (valfritt):** i `wp-config.php`:

```php
define( 'MRT_LENNAKATTEN_BRAND', true );
```

Det laddar [`assets/brand/lennakatten-color-tokens.css`](../assets/brand/lennakatten-color-tokens.css) och Lennakatten-typsnitt. Filter: `mrt_use_lennakatten_brand_tokens`.

Färgreferens: [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md).

---

## 5. Dev/demo (Lennakatten)

Under utveckling: **Dev tools → Importera Lennakatten-demo** fyller databasen med referensdata från `testdata/fixtures/lennakatten/` (inkl. `station_train_changes.csv`). Det ska **inte** användas som produktionsdefault.

Docker dev har `MRT_LENNAKATTEN_BRAND` aktiverat i `docker-compose.yml` så demo behåller Lennakatten-look.

---

## 6. Checklista före go-live

- [ ] Stationer, rutter, tidtabeller importerade och granskade i admin
- [ ] Priser sparade eller importerade
- [ ] `route_title` satt på wizard-shortcode
- [ ] Ev. eget tema/ `--mrt-*` om standardprofilen inte räcker
- [ ] Manuell rökning: [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md)
