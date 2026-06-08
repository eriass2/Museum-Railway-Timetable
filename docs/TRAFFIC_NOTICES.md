# Åtgärdsplan – trafikmeddelanden (shortcode)

Shortcode på **startsidan** som visar generella trafikmeddelanden och tur-avvikelser för **idag**. Besökare ser en kompakt lista; tom vy visar «Inga meddelanden».

**Relaterat:** [SHORTCODES.md](SHORTCODES.md), [REST_API.md](REST_API.md), [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md), [DATA_MODEL.md](DATA_MODEL.md)

---

## Status

| Fas | Beskrivning | Status |
|-----|-------------|--------|
| 0 | Plan och produktbeslut (detta dokument) | Klar |
| 1 | Domän: `public-notices.php`, `aggregate.php`, PHPUnit | Klar |
| 2 | REST: public + admin routes, loader, PHPUnit | Klar |
| 3 | Admin: `TrafficNoticesPage.vue`, route, top-level-meny | Klar |
| 4 | Shortcode + Vue + CSS + tester | Klar |
| 5 | Docs: SHORTCODES, REST_API, ADMIN_WORKFLOW, admin help | Klar |

---

## Beslut (spikade 2026-06-06)

| Fråga | Beslut |
|-------|--------|
| Rubrik | Ingen standardrubrik — shortcode visar bara listan (valfri `title`-parameter) |
| Datumomfång | `days="1"` som standard (endast idag); `days="2"` opt-in för idag + imorgon |
| Admin-placering | Ny **top-level**-meny: **Trafikmeddelanden** (`#/traffic-notices`) |
| Max längd meddelande | **500 tecken** (ren text) |
| Sortering generella meddelanden | **`sort_order`** + **Upp/Ner-knappar** i listan (ingen drag-and-drop) |

---

## Mål

1. **Generella meddelanden** — flera sparade texter (t.ex. glassrea, baninfo), hanterade i admin.
2. **Tur-avvikelser** — aggregerade från befintlig avvikelsedata per tur och datum.
3. **Startsida** — placeras ovanför månadskalendern.
4. **Tom vy** — «Inga meddelanden» när inget är aktivt.

Tur-avvikelser redigeras fortsatt i **Tidtabell → Avvikelser** (oförändrat arbetsflöde).

---

## Shortcode

```
[museum_traffic_notices]
```

| Parameter | Standard | Beskrivning |
|-----------|----------|-------------|
| `days` | `1` | `1` = idag, `2` = idag + imorgon |
| `date` | *(WP-tid idag)* | Referensdatum `YYYY-MM-DD` (test) |
| `show_general` | `1` | Visa generella meddelanden |
| `show_deviations` | `1` | Visa tur-avvikelser |
| `title` | *(tom)* | Valfri rubrik ovanför listan |

**Exempel (startsida):**

```
[museum_traffic_notices]
[museum_timetable_month ...]
```

**Med imorgon och rubrik:**

```
[museum_traffic_notices days="2" title="Trafikinfo"]
```

---

## Publikt utseende

**Med innehåll (standard, utan `title`):**

```
• Glassrean i caféet kl 14–16!
• Banan stängd vid Fjällnora pga evenemang

• Inställd — Tåg 71, Uppsala Ö → Marielund
• Ersättningsbuss — Tåg 73, Marielund → Selknä
```

Med `days="2"` grupperas avvikelser under dagsrubriker («Idag …», «Imorgon …»). Generella meddelanden visas alltid överst (gäller valda dagar enligt `active_from` / `active_to`).

**Tom:**

```
Inga meddelanden
```

**Design:**

- Kompakt lista — passar ovanför kalendern.
- Inställda turer: samma visuella språk som tidtabell/reseplanerare (badge/genomstrykning).
- Generella meddelanden utan turkoppling först, därefter avvikelser.
- `<noscript>`-fallback med server-renderad lista (samma mönster som övriga shortcodes).

---

## Datamodell

### A. Generella meddelanden (nytt)

**Option:** `mrt_public_notices` — array:

```php
[
  'id'          => 'uuid',
  'text'        => 'Glassrean ...',  // max 500 tecken, sanitize_textarea_field
  'enabled'     => true,
  'active_from' => '2026-06-06',     // valfritt — tom = gäller direkt
  'active_to'   => '2026-06-06',     // valfritt — tom = tills vidare
  'sort_order'  => 10,
]
```

**Visningsfilter:** `enabled === true` och referensdatum ∈ `[active_from, active_to]` (saknad gräns = öppen).

**Sortering:** `sort_order` stigande, sedan `id` som tiebreaker.

**Ny post:** `sort_order = max(befintliga) + 10` (glapp underlättar infogning).

**Obs:** Plugin-inställningen har fältet `note` («Anteckning») som inte visas publikt. Det **ersätts inte** i v1; ny struktur är separat.

### B. Tur-avvikelser (befintligt)

Meta per tur:

- `mrt_service_notices_by_date`
- `mrt_service_train_types_by_date`

Domän att återanvända:

- `MRT_collect_timetable_deviation_rows()`
- `MRT_timetable_deviation_row_data()`
- Inställd-detektering (samma som admin: «Inställd» i notice)

Aggregering: alla publicerade tidtabeller → filtrera datum ∈ valt intervall → sortera datum, sedan tur.

---

## REST API

### Publikt

```
GET /museum-railway-timetable/v1/traffic-notices?date=2026-06-06&days=1
```

**Svar (förenklad):**

```json
{
  "reference_date": "2026-06-06",
  "days": 1,
  "general": [{ "id": "...", "text": "Glassrean kl 14–16!" }],
  "by_date": [{
    "date": "2026-06-06",
    "date_label": "lördag 6 juni",
    "deviations": [{
      "service_id": 71,
      "trip_label": "71 → Marielund",
      "notice": "Inställd",
      "is_cancelled": true,
      "train_type_id": 3
    }]
  }],
  "is_empty": false
}
```

- `permission_callback`: `MRT_rest_can_read_public`
- Domänlogik i `inc/domain/traffic-notices/`

### Admin (generella meddelanden)

```
GET /traffic-notices/messages
PUT /traffic-notices/messages
```

- `permission_callback`: `MRT_rest_can_edit_operations` (`edit_posts` + admin)
- Body vid PUT: hela listan (validerad, max 500 tecken/text, datum ISO)

Tur-avvikelser redigeras **inte** här.

---

## Admin (Vue)

### Sida: Trafikmeddelanden

- Route: `#/traffic-notices`
- Meny: **Railway Timetable → Trafikmeddelanden** (synlig för `edit_posts`)
- List ↔ detalj (samma mönster som stationer/rutter)

**Listvy:**

| Ordning | Text | Gäller från | Gäller till | Aktiv | Åtgärder |
|---------|------|-------------|-------------|-------|----------|
| ↑ ↓ | Glassrean … | 2026-06-06 | 2026-06-06 | ✓ | Redigera / Ta bort |

**Detaljvy:**

- Textarea (max 500 tecken, teckenräknare)
- Datumfält (valfria)
- Checkbox «Aktiv»
- Hjälptext: «Visas idag» / «Visas inte idag» (beräknat från datum + enabled)

---

## Frontend (Vue shortcode)

| Lager | Plats |
|-------|--------|
| PHP shortcode | `inc/public/traffic-notices/shortcode.php` |
| Vue config | `MRT_vue_traffic_notices_config()` |
| Vue app | `frontend/vue/src/apps/TrafficNoticesApp.vue` |
| Komponenter | `frontend/vue/src/components/traffic-notices/` |
| CSS | `frontend/vue/src/styles/traffic-notices.css` |
| REST-klient | `frontend/vue/src/api/mrtRest.ts` |
| Registrering | `inc/shortcodes.php`, `main.ts` loader `traffic_notices` |

Flöde: mount → `GET /traffic-notices` → render → tom = «Inga meddelanden».

---

## Startsida

```
┌─────────────────────────────────┐
│  [museum_traffic_notices]       │
├─────────────────────────────────┤
│  [museum_timetable_month]       │
└─────────────────────────────────┘
```

Dev-verktyg: uppdatera «Synka tidtabellssidor» så startsidan får shortcoden (eller dokumentera manuellt steg).

---

## Behörigheter

| Åtgärd | Vem |
|--------|-----|
| Se shortcode publikt | Alla |
| Generella meddelanden | `edit_posts` + admin |
| Tur-avvikelser | Oförändrat — tidtabellseditorn |
| Plugin-inställningar | `manage_options` |

---

## Implementation — faser

### Fas 1 — Domän (PHP)

- `inc/domain/traffic-notices/public-notices.php` — läs/skriv/sanera/filtrera
- `inc/domain/traffic-notices/aggregate.php` — slå ihop generella + avvikelser
- Tester: datumfilter, tom lista, 500-tecken-gräns, inställd-flagga

### Fas 2 — REST

- `inc/infrastructure/rest/traffic-notices-public.php`
- `inc/infrastructure/rest/traffic-notices-admin.php`
- Registrera i `loader.php`

### Fas 3 — Admin UI

- `TrafficNoticesPage.vue`, API-modul, route, top-level-meny
- Upp/Ner för `sort_order`, teckenräknare

### Fas 4 — Publikt shortcode

- PHP + Vue app + styling (Lennakatten tokens)
- Shortcode- och komponenttester

### Fas 5 — Dokumentation

- Uppdatera SHORTCODES.md, REST_API.md, ADMIN_WORKFLOW.md
- Admin help (shortcodes + ny sida)
- Ev. component demo

**Status:** Component demo inkluderar `[museum_traffic_notices]` som block 1 (uppdateras vid «Skapa/uppdatera demo-sida»).

---

## Medvetet utanför v1

| Idé | Anledning |
|-----|-----------|
| Gutenberg-block | Shortcode räcker |
| Widget | Senare |
| HTML i meddelanden | Säkerhet — ren text |
| Deduplicering «allt inställt» | Visa generellt + per tur |
| Migrera `note` i inställningar | Separat uppgift |

---

## Filöversikt (ny kod)

```
inc/domain/traffic-notices/
  public-notices.php
  aggregate.php
inc/infrastructure/rest/
  traffic-notices-public.php
  traffic-notices-admin.php
inc/public/traffic-notices/
  shortcode.php
frontend/vue/src/
  apps/TrafficNoticesApp.vue
  components/traffic-notices/
  admin/pages/TrafficNoticesPage.vue
  admin/api/adminRestTrafficNotices.ts
tests/Unit/
  TrafficNoticesDomainTest.php
  TrafficNoticesShortcodeTest.php
  RestTrafficNoticesTest.php
```
