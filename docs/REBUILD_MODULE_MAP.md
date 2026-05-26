# Rebuild module map

Syfte: definiera vilka moduler den nya rebuild-versionen ska bestå av innan kod purgas eller flyttas. Modulerna är härledda från `REBUILD_SKETCH.md`, mockups och referens-PDF:er.

---

## 1. Källor

| Källa | Användning |
|-------|------------|
| `docs/mockups/sok-din-resa.png` | Startsteg för journey wizard: hero, ruttval, restyp, CTA |
| `docs/mockups/valj-datum.png` | Kalendersteg: trafikdagar, legend, månadsväxling |
| `docs/mockups/valj-utresa.png` | Utresesteg: trip cards, tågtyp, tider, detaljer, priser |
| `docs/mockups/valj-aterresa.png` | Retursteg: vald utresa, returval, varningar/detaljer |
| `testdata/reference-pdfs/` | Stationer, tågtyper, tidtabeller, trafikdagar, stopptider |
| `REBUILD_SKETCH.md` | Produktmål och MVP |
| `REBUILD_RULES.md` | Kod-, design- och testregler |

---

## 2. Modulöversikt

```text
inc/
├── domain/
│   ├── datetime/
│   ├── station/
│   ├── route/
│   ├── train-type/
│   ├── timetable/
│   ├── service/
│   ├── journey/
│   └── pricing/
├── import/
│   └── lennakatten/
├── admin/
│   ├── dashboard/
│   ├── timetable-editor/
│   ├── service-editor/
│   ├── route-editor/
│   └── tools/
├── public/
│   ├── journey-wizard/
│   ├── timetable-overview/
│   └── month-calendar/
├── infrastructure/
│   ├── wordpress/
│   ├── database/
│   └── ajax/
├── assets/
└── bootstrap.php
```

---

## 3. Domain modules

### `domain/datetime`

**Ansvar:** datum- och tidsvalidering, HH:MM-parsing, jämförelser och duration.

**Input:** datumsträngar, HH:MM-strängar, minutjusteringar.

**Output:** valideringsresultat, minuter, jämförelser, formaterade tidvärden.

**Stöder:** stopptider, journey search, kalenderstatus, returfiltrering.

**Får inte:** läsa WordPress request-data eller rendera HTML.

**Minsta tester:** giltigt/ogiltigt datum, HH:MM-validering, duration, jämförelse, minutaddition.

**Nuvarande start:** `inc/domain/datetime/datetime.php` (via `inc/bootstrap/domain.php`).

### `domain/station`

**Ansvar:** stationer, display order, bus suffix, station title lookup, station collections.

**Input:** station IDs, imported station rows, WordPress station posts via repository.

**Output:** station DTO/array, ordered station lists, validation results.

**Stöder:** alla tidtabeller, route editing, journey station dropdowns.

**Får inte:** rendera HTML, läsa `$_POST`, göra admin redirects.

**Minsta tester:** station ordering, missing station handling, bus suffix flag.

**Nuvarande start:** `inc/domain/station/stations.php`.

### `domain/route`

**Ansvar:** route station sequence, start/end station, route labels, direction inference.

**Input:** route ID, route station IDs, destination/end station.

**Output:** ordered route model, route label, inferred direction.

**Stöder:** admin route editor, service destination, timetable overview grouping.

**Får inte:** rendera route meta boxes.

**Minsta tester:** route direction, shared end station label, missing route stations.

**Nuvarande start:** `inc/domain/route/routes.php`.

### `domain/train-type`

**Ansvar:** train type taxonomy semantics and icon mapping rules.

**Input:** train type name/slug, service train type.

**Output:** normalized train type, icon key, display label.

**Stöder:** PDF train type rows, timetable overview, journey cards.

**Får inte:** hardcode UI markup in domain. Tågikonerna är assets och får användas via icon key.

**Minsta tester:** `Ångtåg`, `Dieseltåg`, `Rälsbuss`, `Buss`, fallback.

**Nuvarande start:** `inc/domain/train-type/icons.php`; WP-meta/helpers i `inc/infrastructure/wordpress/helpers-utils.php`.

### `domain/timetable`

**Ansvar:** timetable dates, traffic days, timetable grouping, printable overview data.

**Input:** timetable ID/type, date list, service list.

**Output:** timetable model, calendar day status, grouped overview rows.

**Stöder:** `Gron-tidtabell-lor.pdf`, `Gul-tidtabell-fre.pdf`, month calendar, timetable overview.

**Får inte:** decide frontend layout or admin HTML.

**Minsta tester:** date parsing/sorting, traffic/no-traffic status, grouping by route/direction.

**Nuvarande start:** `inc/domain/timetable/view/`.

### `domain/service`

**Ansvar:** trips/services, service number, stop times, pickup/dropoff flags, service notice.

**Input:** service ID, stop time rows, route, timetable, train type.

**Output:** service model, ordered stop times, display-ready time facts.

**Stöder:** import, service editor, timetable overview, journey search.

**Får inte:** own raw `$_POST` save logic.

**Minsta tester:** stop order, null/fixed times, pickup/dropoff semantics for `P`/`X`.

**Nuvarande start:** `inc/domain/service/services.php`, `stop-times.php`, `connections.php`.

### `domain/journey`

**Ansvar:** journey search, direct trips, one-transfer trips, return filtering, connection detail, normalization.

**Input:** from station, to station, date, trip type, outbound arrival.

**Output:** normalized journey options, leg list, duration, notices, transfer station.

**Stöder:** `sok-din-resa`, `valj-datum`, `valj-utresa`, `valj-aterresa`.

**Får inte:** build HTML cards or read browser state.

**Minsta tester:** direct trip, transfer via shared station, min transfer time, return after arrival, invalid station pair, no traffic.

**Nuvarande start:** `inc/domain/journey/` (ordnad laddning i `inc/bootstrap/domain.php`).

### `domain/pricing`

**Ansvar:** zone calculation, ticket category matrix, active price row.

**Input:** from/to station, ticket type, stored/default matrix.

**Output:** active zone count, price table data.

**Stöder:** journey wizard summary/prismatris.

**Får inte:** render price table HTML.

**Minsta tester:** zone span, empty price matrix, single/return/day ticket rows.

**Nuvarande start:** `inc/domain/pricing/prices.php`.

---

## 4. Import modules

### `import/lennakatten/reference-data`

**Ansvar:** static data transcribed from reference PDFs.

**Input:** maintained PHP arrays or future parsed intermediate format.

**Output:** station definitions, timetable definitions, service definitions.

**Stöder:** GRÖN, GUL and future PDF files in similar format.

**Får inte:** write to WordPress directly.

**Minsta tester:** tågnummer, tågtyp, date count, row length per route.

**Nuvarande start:** `inc/import/lennakatten/reference-data.php`.

### `import/lennakatten/importer`

**Ansvar:** convert reference data into domain objects/repositories.

**Input:** reference data definitions.

**Output:** created/updated station, route, timetable, service and stop time records.

**Stöder:** admin import tool and demo setup.

**Får inte:** render admin forms.

**Minsta tester:** idempotent import, expected counts, train type assignment.

**Nuvarande start:** `inc/import/lennakatten/importer.php` (UI via `inc/import-lennakatten/loader.php`).

---

## 5. Admin modules

### `admin/dashboard`

**Ansvar:** WordPress-native dashboard with status, settings links and tool entry points.

**Input:** counts and tool URLs.

**Output:** admin HTML.

**Stöder:** admin overview and quality/debug workflow.

**Får inte:** contain import/search business logic.

**Minsta tester:** smoke/manual, capability visibility for tools.

**Nuvarande start:** `inc/admin/dashboard.php` + `inc/admin/dashboard/*`; laddas via `inc/admin.php`.

**Nuvarande start:** `inc/admin/meta-boxes/` (timetable, service, route, station) via `inc/admin/meta-boxes.php`.

### `admin/timetable-editor`

**Ansvar:** edit timetable dates and trips/services linked to timetable.

**Input:** timetable post, route/service options.

**Output:** meta boxes and save operations via adapter.

**Stöder:** admin workflow for creating printed timetable data.

**Får inte:** duplicate journey search logic.

**Minsta tester:** save dates, add/remove trip adapter validation.

### `admin/service-editor`

**Ansvar:** edit service details and stop times.

**Input:** service post, route stations, train types.

**Output:** meta boxes and save actions.

**Stöder:** stop times, pickup/dropoff, destination station.

**Får inte:** know frontend wizard layout.

**Minsta tester:** save service metadata, save stop times, permission failure.

### `admin/route-editor`

**Ansvar:** route station sequence and route endpoints.

**Input:** route post, station list.

**Output:** route meta boxes and AJAX save actions.

**Stöder:** route setup and service destination inference.

**Får inte:** compute journey options.

**Minsta tester:** station order update, endpoint update, duplicate station rejection.

### `admin/tools`

**Ansvar:** clear plugin data, import demo data, create demo page.

**Input:** admin form actions.

**Output:** redirects/notices.

**Stöder:** development and QA.

**Får inte:** bypass nonce/capability.

**Minsta tester:** capability/nonce, clear/import/create actions.

**Nuvarande start:** `inc/admin/tools/clear-db.php`, `inc/admin/tools/demo-page.php`.

---

## 6. Public modules

### `public/journey-wizard`

**Ansvar:** mockup-led frontend journey flow.

**Input:** station options, AJAX endpoints, ticket URL, optional hero and timetable overview.

**Output:** shortcode HTML, JS state machine, frontend CSS based on mockup.

**Stöder:** all four mockup screens.

**Får inte:** compute journey validity locally in JS.

**Minsta tester:** render smoke, JS utility tests, AJAX/domain tests.

**Nuvarande start:** shortcode-renderingen ligger i `inc/public/journey-wizard/`; den gamla shortcodefilen är loader under övergången.

### `public/timetable-overview`

**Ansvar:** printed-style timetable display.

**Input:** timetable overview domain data.

**Output:** shortcode HTML.

**Stöder:** reference PDF timetable comparison.

**Får inte:** query services directly from templates.

**Minsta tester:** grouping/render data, empty timetable.

**Nuvarande start:** `inc/public/timetable-overview/shortcode.php`; den gamla shortcodefilen är loader under övergången.

### `public/month-calendar`

**Ansvar:** public monthly traffic calendar.

**Input:** month, filters, traffic day status.

**Output:** shortcode HTML and optional AJAX day detail.

**Stöder:** traffic-day discovery before journey selection.

**Får inte:** duplicate journey calendar domain rules.

**Minsta tester:** month navigation, status classes, filtering.

**Nuvarande start:** `inc/public/month-calendar/shortcode.php`; den gamla shortcodefilen är loader under övergången.

### `public/journey-planner`

**Ansvar:** enkel legacy-reseplanerare om den överlever MVP-beslutet.

**Input:** from/to/date.

**Output:** shortcode HTML och resultatcontainer.

**Stöder:** enkel test-/fallbackvy för journey backend.

**Får inte:** duplicera wizardens domänregler.

**Minsta tester:** render smoke och station/date input.

**Nuvarande start:** `inc/public/journey-planner/shortcode.php`; kan tas bort senare om MVP väljer wizard-only.

---

## 7. Infrastructure modules

### `infrastructure/wordpress`

**Ansvar:** hooks, post type/taxonomy registration, settings registration.

**Input:** module definitions.

**Output:** WordPress hooks registered.

**Får inte:** contain domain algorithms.

### `infrastructure/database`

**Ansvar:** custom table creation, DB repositories and `$wpdb` access.

**Input:** repository queries.

**Output:** rows/DTOs for domain or import.

**Får inte:** render HTML or read `$_POST`.

### `infrastructure/ajax`

**Ansvar:** AJAX endpoint adapters.

**Input:** `$_POST`, nonce, current user.

**Output:** JSON/HTML response.

**Får inte:** implement domain rules inline.

**Nuvarande start:** `inc/infrastructure/ajax/*.php` via `inc/infrastructure/ajax.php` (admin + publika `wp_ajax_*`).

---

## 8. Asset modules

### `assets/frontend`

**Ansvar:** enqueue and frontend JS/CSS bundles.

**Input:** detected shortcode/module usage.

**Output:** registered/enqueued scripts and styles.

**Får inte:** load old appearance implementation after purge.

### `assets/admin`

**Ansvar:** minimal admin assets only where WordPress-native UI is insufficient.

**Input:** admin screen/context.

**Output:** registered/enqueued scripts/styles.

**Får inte:** globally load heavy frontend styling in admin.

---

## 9. First implementation order

1. `infrastructure/wordpress` + `infrastructure/database`
2. `domain/datetime`, `domain/station`, `domain/route`, `domain/train-type`
3. `domain/timetable`, `domain/service`
4. `import/lennakatten/reference-data` + `import/lennakatten/importer`
5. `admin/tools`
6. `public/timetable-overview`
7. `public/month-calendar`
8. `domain/journey`, `domain/pricing`
9. `public/journey-wizard`
10. new minimal `assets/frontend` and `assets/admin`

This order preserves data and testability before rebuilding appearance.
