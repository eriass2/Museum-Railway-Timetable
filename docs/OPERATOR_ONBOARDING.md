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
3. Valfritt: `prices.csv` + `price_schema.csv`, `settings.csv`, `brand_tokens.csv`
4. Valfritt: `station_train_changes.csv` — anslutande tåg i tidtabellsöversikten (t.ex. byte Marielund 71 → Dieseltåg 61)
5. Importera i läge **merge** (första gången) eller **override** (ersätt fixture-data).

Mallen exporteras från admin eller via `composer csv:template`.

---

## 3. Priser och inställningar

| Uppgift | Var |
|---------|-----|
| Operatörsnamn och global biljett-URL | Admin → **Inställningar** |
| Prismatris och prisstruktur | Admin → **Priser** |
| Min/max bytestid, max antal byten, eftermiddagsgräns | Admin → **Inställningar** |
| Priszoner per station | Admin → **Stationer & rutter** (eller CSV) |
| Tågbyte per station | Admin → **Stationer & rutter** → Tågbyte (eller CSV) |

Saknas priser i databasen visas inga belopp i reseplaneraren (fail empty).

---

## 4. Publikt UI

### Shortcodes

| Shortcode | Syfte |
|-----------|--------|
| `[museum_journey_wizard]` | Reseplanerare (rubrik från Inställningar → operatörsnamn, eller `route_title="…"`) |
| `[museum_journey_wizard ticket_url="…"]` | Ev. sid-specifik biljett-URL (annars global URL i Inställningar) |
| `[museum_timetable_month]` | Månadskalender |
| `[museum_timetable_overview timetable_id="…"]` | Tryckt tidtabellsöversikt |

Se [SHORTCODES.md](SHORTCODES.md).

### Utseende (tema)

- **Standard:** neutral blå/brass-profil i [`assets/mrt-color-tokens.css`](../assets/mrt-color-tokens.css) och systemtypsnitt.
- **CSV:** `brand_tokens.csv` i importpaketet (färger + typsnitt, se [CSV_FORMAT.md](CSV_FORMAT.md)).
- **Override:** sätt egna `--mrt-*`-variabler i child theme eller sajt-CSS.
- **Lennakatten-profil (valfritt, dev):** i `wp-config.php`:

```php
define( 'MRT_LENNAKATTEN_BRAND', true );
```

Det laddar [`assets/brand/lennakatten-color-tokens.css`](../assets/brand/lennakatten-color-tokens.css) och Lennakatten-typsnitt. Filter: `mrt_use_lennakatten_brand_tokens`.

Färgreferens: [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md).

---

## 5. Dev/demo (Lennakatten)

Under utveckling: **Dev tools → Importera Lennakatten-demo** fyller databasen med referensdata från `testdata/fixtures/lennakatten/` (tidtabeller, priser, inställningar, `station_train_changes.csv`). Det ska **inte** användas som produktionsdefault.

PHP-referens för tester: `MRT_lennakatten_reference_*()` i `inc/import/lennakatten/reference-data.php`; PHPUnit-trait `MRT_Lennakatten_Test_Fixture`.

Docker dev har `MRT_LENNAKATTEN_BRAND` aktiverat i `docker-compose.yml` så demo behåller Lennakatten-look.

---

## 6. Checklista före go-live

- [ ] Operatörsnamn och biljett-URL i Inställningar (eller `route_title` / `ticket_url` på shortcode)
- [ ] Stationer, rutter, tidtabeller importerade och granskade i admin
- [ ] Priser sparade eller importerade
- [ ] Ev. eget tema/ `--mrt-*` om standardprofilen inte räcker
- [ ] Manuell rökning: [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md)

---

## 7. Stationmeta och byteslogik (handbok)

Fält som styr priser, tidtabellsöversikt och reseplanerarens byten.

### Priszoner (`price_zones`)

| Var | CSV `stations.csv` kolumn `price_zones` | Admin → Station |
|-----|----------------------------------------|-----------------|
| Meta | `mrt_station_price_zones` (array 1–4) | Priszoner |

Kommaseparerade zoner (max två per station). Tom = inga zoner → prislookup använder zon-tak. Se [PRICE_ZONES.md](PRICE_ZONES.md).

### Tågbyte (`train_change_map`)

| Var | CSV `station_train_changes.csv` | Admin → Tågbyte |
|-----|--------------------------------|-----------------|
| Meta | `mrt_station_train_change_map` | Anslutande tåg per avgående typ |

Används i tidtabellsöversikten (”byte till …”). Se [CSV_FORMAT.md](CSV_FORMAT.md) §4.1b.

### Busshållplats (`bus_stop_marker` / `bus_suffix`)

| Var | CSV `bus_stop_marker` (0\|1) | Admin |
|-----|------------------------------|-------|
| Meta | `mrt_station_bus_suffix` | Busshållplats |

**Effekt:**

- Asterisk i tryckt tidtabell
- Station tillåter byte i reseplaneraren
- Tåg→buss vid busshubb: minsta väntetid **0 min** (tåg som ankommer + buss som avgår)
- Högre prioritet som bytesstation (före andra hubbar)

Modellera busslinje som egen rutt med turer typ `Buss` (inte som del av tågrutt).

### Stationstyp (`station_type`)

| Värde | Betydelse |
|-------|-----------|
| `station` | Vanlig station (standard) |
| `halt` | Hållplats |
| `depot` | Depå |
| `museum` | Museiplats |

Påverkar visning i admin; påverkar inte resesökning direkt.

### Bytesstationer (hub) — implicit regler

En station får användas som **mellanliggande byte** om något av följande gäller:

1. `bus_stop_marker` = 1
2. Meta `mrt_transfer_priority` satt (tal) — **ej i admin/CSV idag**; filter `mrt_transfer_priority` / `mrt_journey_station_allows_transfer`
3. Station är **ruttändpunkt** (första/sista på någon rutt)
4. Station har **tågbyte-karta** (`train_change_map`)

Saknas hub-markering hittar reseplaneraren inte flerbenade resor via den stationen.

### Inställningar som påverkar resesökning

| Inställning | Nyckel | Standard |
|-------------|--------|----------|
| Min väntetid vid byte | `min_transfer_minutes` | 0 |
| Max väntetid vid byte | `max_transfer_minutes` | 120 |
| Max antal byten | `max_transfers` | 2 (tre ben) |
| Eftermiddagsgräns retur | `afternoon_return_threshold_minutes` | 900 (kl 15:00) |

Alla kan exporteras/importeras via `settings.csv`. Filter `mrt_journey_max_transfers` kan fortfarande override max byten.
