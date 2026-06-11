# CSV import/export – format och beteende

Specifikation för att flytta tidtabellsdata mellan sajter, redigera i Excel/LibreOffice och importera via WordPress-admin. Gäller **alla järnvägar** som använder pluginet — inte bara Lennakatten-testdata.

**Relaterat:** [DATA_MODEL.md](DATA_MODEL.md) (datamodell), [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) (manuell admin), [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md) (dev-reset med fixture).

**Implementation (planerad):** `inc/import/csv/`, admin under Railway Timetable → Import / Export.

---

## 1. Paketstruktur

Ett **exportpaket** är en mapp eller en `.zip` med samma innehåll.

```
mrt-export/
├── manifest.json
├── stations.csv
├── station_train_changes.csv   (valfri)
├── train_types.csv
├── routes.csv
├── route_stations.csv
├── timetables.csv
├── timetable_dates.csv
├── services.csv
├── service_train_types.csv
├── stoptimes.csv
├── settings.csv          # valfritt
├── brand_tokens.csv      # valfritt (färger + typsnitt)
├── prices.csv            # valfritt
└── icons/                # valfritt, tågtypsikoner
    └── angtag.svg
```

| Regel | Beskrivning |
|-------|-------------|
| **Formatversion** | `manifest.json` → `format_version` (nuvarande: `"1"`) |
| **Teckenkodning** | UTF-8; BOM tolereras vid läsning |
| **Separator** | Komma (`,`), citattecken vid behov (RFC 4180) |
| **Zip** | Rekommenderat för admin-uppladdning; läsare accepterar zip eller uppackad mapp |
| **Priser** | Ingår **inte** som standard; export/import är valfritt |
| **Inställningar** | Samma — valfritt block |

---

## 2. Manifest (`manifest.json`)

```json
{
  "format_version": "1",
  "exported_at": "2026-05-29T12:00:00Z",
  "plugin_version": "0.0.0",
  "locale": "sv_SE",
  "includes": [
    "stations",
    "train_types",
    "routes",
    "timetables",
    "services",
    "stoptimes"
  ]
}
```

| Fält | Beskrivning |
|------|-------------|
| `format_version` | Låser kolumnnamn och beteende. Okänd version → import avbryts. |
| `includes` | Vilka entitetstyper som finns i paketet. Styr **override-scope** (se §6). |
| `exported_at` | ISO 8601, informativt |
| `plugin_version` | Plugin-version vid export, informativt |

Tillåtna värden i `includes`: `stations`, `train_types`, `routes`, `timetables`, `services`, `stoptimes`, `settings`, `brand_tokens`, `prices`.

---

## 3. Stabila nycklar (`*_code`)

WordPress post-ID används **inte** i CSV. Alla kopplingar sker via stabila strängnycklar.

### 3.1 Hybridmodell

| Entitet | Primärnyckel | Om tom vid import |
|---------|--------------|-------------------|
| Station | `station_code` | Auto: `slugify(name)` |
| Rutt | `route_code` | Auto: `slugify(title)` |
| Tidtabell | `timetable_code` | Auto: `slugify(title)` |
| Tur (service) | `service_code` | Auto: `{timetable_code}-{service_number}-{end_station_code}` |

**Export skriver alltid ut codes** så round-trip (export → redigera → import) fungerar även om visningsnamn ändras.

**Rekommendation för operatörer:**

1. **Första import** — fyll namn/titlar/tider; lämna `*_code` tomma.
2. **Efter första export** — använd codes i fortsatt redigering.
3. **Avancerat** — egna codes (t.ex. `uk-ost`, `line-a`) vid kollisionsrisk.

### 3.2 Slugify-regler

- Normalisera Unicode (NFC).
- Versaler → gemener.
- Svenska tecken behålls där möjligt (`å` → `a` valfritt; konsekvent inom en import).
- Mellanslag och interpunktion → bindestreck (`-`).
- Flera bindestreck kollapsas; inledande/avslutande bindestreck tas bort.

Exempel: `Uppsala Östra` → `uppsala-ostra`.

### 3.3 Kollisioner

| Situation | Beteende |
|-----------|----------|
| Auto-slug finns redan med **samma** namn | Uppdatera befintlig post |
| Auto-slug finns med **annat** namn | Import **stoppas** med fel på radnivå |
| Explicit `*_code` i CSV | Används som angivet; användaren ansvarar för unikhet |

### 3.4 Tågtyper

Identifieras via `slug` (WordPress taxonomy). Ingen separat `train_type_code`.

---

## 4. CSV-filer och kolumner

### 4.1 `stations.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `station_code` | nej* | string | Stabil nyckel (*obligatorisk efter export; kan auto-genereras) |
| `name` | ja | string | Visningsnamn (`post_title`) |
| `station_type` | nej | enum | `station` \| `halt` \| `depot` \| `museum` |
| `display_order` | nej | int | Sortering, default `0` |
| `bus_stop_marker` | nej | 0\|1 | Asterisk i tidtabell (busshållplats) |
| `lat` | nej | float | |
| `lng` | nej | float | |
| `price_zones` | nej | string | Priszoner `1`–`4`, kommaseparerade (max två). Tom = inga zoner. Se [PRICE_ZONES.md](PRICE_ZONES.md). |

### 4.1b `station_train_changes.csv` (valfri)

Importeras tillsammans med `stations`. En rad per anslutning vid bytesstation.

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `station_code` | ja | string | FK → `stations.csv` |
| `from_service` | ja | string | Ankommande tågnummer (t.ex. `71`) |
| `type_name` | ja | string | Anslutande fordons typ (t.ex. `Dieseltåg`) |
| `to_service` | ja | string | Anslutande tågnummer (t.ex. `61`) |

Flera rader med samma `station_code` slås ihop till stationens `train_change_map`. Redigerbart i admin under **Stationer → Tågbyte**.

### 4.2 `train_types.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `slug` | ja | string | t.ex. `angtag`, `buss` |
| `name` | ja | string | t.ex. `Ångtåg` |
| `icon_file` | nej | string | Relativ sökväg i paketet, t.ex. `icons/angtag.svg` |

Saknas `icon_file` eller filen i zip → plugin-default för slug om den finns.

### 4.3 `routes.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `route_code` | nej* | string | Stabil nyckel |
| `title` | ja | string | Ruttens namn |
| `start_station_code` | nej | string | Terminus (kan härledas från `route_stations`) |
| `end_station_code` | nej | string | Terminus |
| `branch_code` | nej | string | Logisk **gren** som flera shuttle-rutter kan dela. Tom på huvudkorridor om ej grupperad. Se [DATA_MODEL.md](DATA_MODEL.md) §1.4b. |

**Exempel (Lennakatten):** `main` (Faringe–Uppsala), `fjallnora` (Selkné–Fjällnora), `linnes-hammarby` (alla Linnés-shuttles inkl. B14 till Uppsala).

### 4.4 `route_stations.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `route_code` | ja | string | FK → `routes.csv` |
| `sequence` | ja | int | 1, 2, 3 … |
| `station_code` | ja | string | FK → `stations.csv` |

En rad per hållplats per rutt. Vid uppdatering av rutt **ersätts** hela stationslistan.

### 4.5 `timetables.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `timetable_code` | nej* | string | Logiskt namn **utan år**, t.ex. `green`, `yellow` |
| `title` | ja | string | t.ex. `GRÖN TIDTABELL` |
| `colour_type` | nej | enum | `green` \| `yellow` \| `red` \| `orange` (översiktsrubrik) |

År och säsong styrs av datum i `timetable_dates.csv`, inte av `timetable_code`.

### 4.6 `timetable_dates.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `timetable_code` | ja | string | FK |
| `date` | ja | date | `YYYY-MM-DD` |

En rad per trafikdag. Vid uppdatering **ersätts** alla datum för tidtabellen.

### 4.7 `services.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `service_code` | nej* | string | Stabil nyckel |
| `timetable_code` | ja | string | FK |
| `route_code` | ja | string | FK |
| `service_number` | nej | string | Tågnummer, t.ex. `71`, `B1` |
| `end_station_code` | ja | string | Destination |
| `title` | nej | string | Om tom: generera `{route title} {service_number}` |
| `highlight_label` | nej | string | Markering i översikten (t.ex. `Thun's-expressen`). Tom = ingen markering. |
| `highlight_color` | nej | string | Bakgrundsfärg för kolumnen, hex t.ex. `#fff9c4`. Standard `#fff9c4` om etikett finns. |
| `highlight_note` | nej | string | Förklaringstext i tidtabellens nyckel (Förklaringar). |

Markering styrs per tur och tidtabell via `timetable_code` — en tur utan `highlight_label` visas utan färgmarkering.

### 4.8 `service_train_types.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `service_code` | ja | string | FK |
| `train_type_slug` | ja | string | FK → `train_types.slug` |

Flera rader per tur om flera tågtyper ska kopplas.

### 4.9 `stoptimes.csv`

| Kolumn | Oblig | Typ | Beskrivning |
|--------|-------|-----|-------------|
| `service_code` | ja | string | FK |
| `sequence` | ja | int | 1-baserad ordning längs rutten |
| `station_code` | ja | string | FK |
| `arrival_time` | nej | time | `HH:MM` eller tomt |
| `departure_time` | nej | time | `HH:MM` eller tomt |
| `pickup_allowed` | ja | 0\|1 | Får stiga på |
| `dropoff_allowed` | ja | 0\|1 | Får stiga av |
| `approximate_time` | nej | 0\|1 | Visa **Ca** före tiden i reseplaneraren (default 0) |

Vid uppdatering av tur **ersätts** alla stoptider för den turen.

**Hjälp vid avskrift från anslagstidtabell (PDF):**

**Ca (ungefärlig tid):**

| Situation | `approximate_time` |
|-----------|---------------------|
| Start/slut med klockslag i tidtabell B | **0** |
| Mellanstation med klockslag i B | **0** |
| Mellanstation **utan** klockslag i B, tid från anslag | **1** |
| Stopp saknas i B / hel anslutningsbuss | **1** |

Programmet tolkar **inte** fet vs normal typografi. Se [STOP_TIME_CA.md](STOP_TIME_CA.md).

**P/X-symboler (på-/avstigning):**

| Symbol i PDF | `pickup_allowed` | `dropoff_allowed` | `approximate_time` |
|--------------|------------------|-------------------|---------------------|
| P | 1 | 0 | **0** om B-tid (start); **1** om mellanstation utan B-tid |
| X (utan tid) | 1 | 1 | 0 |
| X **med** tid (anslag, t.ex. Skölsta) | 1 | 1 | **1** (mellanstation utan B-tid) |
| Tid + P/A enligt tabell | 1 | 1 | **0** om B-tid; **1** om enbart anslag |

### 4.10 `settings.csv` (valfritt)

| Kolumn | Oblig | Beskrivning |
|--------|-------|-------------|
| `key` | ja | Se tabell nedan |
| `value` | ja | Sträng, bool eller heltal enligt nyckel |

**Tillåtna nycklar:**

| Nyckel | Typ | Beskrivning |
|--------|-----|-------------|
| `enabled` | bool | Plugin aktivt |
| `note` | string | Intern anteckning |
| `operator_name` | string | Operatörsnamn (wizard-rubrik om shortcode saknar `route_title`) |
| `ticket_url` | URL | Global biljett-URL i reseplaneraren |
| `min_transfer_minutes` | int | Min väntetid vid byte |
| `max_transfer_minutes` | int | Max väntetid vid byte |
| `max_transfers` | int | Max antal byten (0–5) |
| `afternoon_return_threshold_minutes` | int | Eftermiddagsgräns som minuter från midnatt (900 = kl 15:00) |

### 4.11 `brand_tokens.csv` (valfritt)

Operatörens färger och typsnitt som CSS custom properties (`--mrt-*`). Exporteras tillsammans med inställningar när sparade tokens finns.

| Kolumn | Oblig | Beskrivning |
|--------|-------|-------------|
| `token` | ja | `google_fonts` eller `--mrt-…` (prefix kan utelämnas i CSV) |
| `value` | ja | Hex-färg, `var(--mrt-…)`, font-stack eller Google Fonts-URL |

**Specialrad:**

| Token | Värde |
|-------|-------|
| `google_fonts` | HTTPS-URL till `fonts.googleapis.com/css2…` (valfritt) |

**Exempel:**

```csv
token,value
google_fonts,https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap
--mrt-color-brand-green,#296310
--mrt-font-body,"Roboto", system-ui, sans-serif
--mrt-font-heading,"Open Sans", system-ui, sans-serif
```

Importerade tokens ersätter neutral standard-CSS i frontend. Om både CSV-tokens och `MRT_LENNAKATTEN_BRAND` finns vinner CSV.

### 4.12 `prices.csv` (valfritt)

| Kolumn | Oblig | Beskrivning |
|--------|-------|-------------|
| `ticket_type` | ja | Nyckel från `price_schema.csv` (t.ex. `single`) |
| `category` | ja | Nyckel från `price_schema.csv` (t.ex. `adult`) |
| `zone` | ja | Zonnummer från schemat |
| `amount_sek` | nej | Heltal; tomt = ej tillgänglig |

### 4.13 `price_schema.csv` (valfritt, följer med priser-export)

Definierar biljettyper, kategorier, zoner och specialpriser. Importeras **före** `prices.csv`.

| Kolumn | Oblig | Beskrivning |
|--------|-------|-------------|
| `kind` | ja | `ticket_type` \| `category` \| `zone` \| `zone_cap` \| `afternoon_return` |
| `key` | nej | Nyckel (biljettyp/kategori) eller tom för `zone` / `zone_cap` |
| `label` | nej | Visningsnamn för biljettyp/kategori |
| `value` | nej | Zonnummer, zone_cap, eller belopp (eftermiddags-retur) |

Exempel:

```csv
kind,key,label,value
ticket_type,single,Enkelbiljett,
category,adult,Vuxen,
zone,,,1
zone_cap,,,3
afternoon_return,adult,,160
```

---

## 5. Validering

Validering körs **alltid före** någon databasändring.

### 5.1 Schema och referenser

- Alla obligatoriska kolumner finns.
- FK (`station_code`, `route_code`, …) finns i refererad fil eller redan i databasen (vid delimport).
- `sequence` unik per `service_code` respektive `route_code`.
- `station_code` i `stoptimes` ingår i turens rutt.
- Datum: giltigt `YYYY-MM-DD`.
- Tider: giltigt `HH:MM` eller tomt.

### 5.2 Delimport

Om paketet **inte** innehåller `stations` / `routes` i `includes`:

- Stationer och rutter som refereras måste **redan finnas** i databasen (match på `*_code`).
- Saknas referens → valideringsfel, ingen import.

### 5.3 Vid valideringsfel

- **Ingen** databasändring (transaktion eller rollback per importkörning).
- Felrapport med filnamn, radnummer och meddelande.

### 5.4 Utan WordPress

```sh
composer csv:validate -- path/to/package
```

Samma regler som vid admin-import (utom kontroll mot befintlig DB vid delimport).

---

## 6. Importlägen (admin)

Tillgängligt för alla med `manage_options` under **Railway Timetable → Import / Export**.

### 6.1 Lägg till / uppdatera (merge)

- Poster med matchande `*_code` **uppdateras**; barn (datum, route_stations, stoptimes) **ersätts** helt för den posten.
- Poster i databasen som **saknas** i CSV **lämnas kvar**.

### 6.2 Ersätt omfång (override)

- Samma uppdatering som merge för allt i paketet.
- Dessutom: poster av entitetstyper som listas i `manifest.includes` men vars `*_code` **inte** finns i CSV **tas bort** från plugin-databasen.

**Override-scope:** Endast entitetstyper i `includes` — inte hela plugin-databasen om paketet bara innehåller t.ex. `timetables` + `services` + `stoptimes`.

**Varning i UI:** Override kan radera tidtabeller/turer som inte finns i filen.

### 6.3 Importordning

```
stations → train_types → routes + route_stations
  → timetables + timetable_dates
  → services + service_train_types
  → stoptimes
  → settings → prices
```

---

## 7. Export (admin)

- **Exportera tidtabellsdata** → nedladdning som `.zip`.
- Checkboxar: ☐ Inkludera priser, ☐ Inkludera inställningar (default av).
- `manifest.json` och `includes` speglar faktiskt innehåll.
- Alla `*_code` fylls i.

---

## 8. Utvecklarflöde (Lennakatten)

Dev-only auto-setup (rensa DB, smoke-meny) är **separat** från operatörens import.

| Artefakt | Plats |
|----------|-------|
| Referens-PDF | `testdata/reference-pdfs/` |
| CSV-fixture | `testdata/fixtures/lennakatten/` |
| CSV-fixture (zip) | `testdata/fixtures/lennakatten.zip` — `composer csv:zip` |
| Dev-reset | `scripts/docker-dev-reset.ps1` |

Dev-reset anropar import med **override** mot fixturen, sedan smoke-navigation. Se [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).

Planerade kommandon:

| Kommando | Syfte |
|----------|--------|
| `composer csv:validate -- <path>` | Validera utan WordPress |
| `wp mrt csv validate <path>` | Validera i WP-miljö |
| `wp mrt csv export <path>` | Exportera till mapp/zip |
| `wp mrt csv import <path> --mode=merge\|override` | CLI-import |

---

## 9. Exempel

### `route_stations.csv` (utdrag)

```csv
route_code,sequence,station_code
uppsala-faringe,1,uppsala-ostra
uppsala-faringe,2,fyrislund
uppsala-faringe,14,faringe
```

### `stoptimes.csv` (utdrag)

```csv
service_code,sequence,station_code,arrival_time,departure_time,pickup_allowed,dropoff_allowed
green-71-out,1,uppsala-ostra,,10:00,1,0
green-71-out,2,fyrislund,10:03,10:03,1,0
```

### `train_types.csv`

```csv
slug,name,icon_file
angtag,Ångtåg,icons/angtag.svg
buss,Buss,icons/buss.svg
```

---

## 10. Versionshistorik

| `format_version` | Ändring |
|------------------|---------|
| `1` | Initial spec (2026-05) |

Vid breaking changes: öka `format_version`; importör stödjer äldre versioner endast om explicit dokumenterat.
