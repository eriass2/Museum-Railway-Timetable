# Arkitektur: ansvar, testning, affärslogik vs UI

Kort riktlinje för **Museum Railway Timetable** så att ansvar fördelas tydligt, kod kan testas, och **affärskritisk logik** inte låses in i presentation.

**Relaterat:** [REBUILD_RULES.md](REBUILD_RULES.md) (primär regelbok), [SHORTCODES.md](SHORTCODES.md), [STYLE_GUIDE.md](STYLE_GUIDE.md), [ACCESSIBILITY.md](ACCESSIBILITY.md). **Pull requests:** [`.github/pull_request_template.md`](../.github/pull_request_template.md).

---

## 1. Ansvarsfördelning (lager)

| Lager | Roll | Exempel |
|--------|------|---------|
| **Domän** | Regler oberoende av WordPress och UI | Prismatris, datum/tid, connection-sökning, normalisering, tidtabellsrutnät |
| **Infrastruktur** | WP-adapters: CPT, REST, inställningar, helpers | `inc/infrastructure/rest/`, `inc/infrastructure/post-types/` |
| **Admin / public** | Vue-admin, dev-verktyg (REST), shortcodes, enqueue | `inc/admin/`, `frontend/vue/src/admin/`, `inc/public/journey-wizard/` |

**Vid ändringar:** Om en funktion kan beskrivas och testas utan `echo` och utan `$_POST` ska den ligga i **`inc/domain/`** (prefix `MRT_*`), inte i template-strängar eller JS.

---

## 2. Bootstrap och laddning

`museum-railway-timetable.php` → `inc/bootstrap.php`:

1. **`MRT_bootstrap_load_domain()`** – `inc/bootstrap/domain.php` laddar domänmoduler (journey, timetable view, service, route, station, pricing, datetime).
2. **`MRT_bootstrap_load_app()`** – miljö, inställningar, CPT, assets, admin, REST, shortcodes.

Inga legacy-loaders (`inc/functions/`, `inc/cpt/`, …) – allt går via bootstrap.

---

## 3. Testning

- **Enhetstester (PHPUnit):** Ren PHP i `tests/Unit/` mot `inc/domain/`; se `phpunit.xml.dist` och `composer test`.
- **Ny affärsregel:** Lägg test i samma leverans när logiken är ren nog.
- **CI:** `.github/workflows/ci.yml` kör `composer check`, `composer vue:check`, static Playwright E2E, och `e2e-wp` (publikt + Vue-admin mot Docker).
- **Refaktor:** Validering som inte behöver `$_POST` ska vara namngivna `MRT_*`-funktioner (t.ex. `MRT_journey_validate_station_pair_ids` i `journey-parse.php`).

---

## 4. Affärskritisk kod och UI

- **PHP:** Shortcode och REST ska **samla input → anropa domän → returnera JSON/HTML**.
- **JavaScript:** Servern är sanning för sökning, priser och giltiga datum; klienten visar svar och fel.
- **Gemensam regel:** En implementation i PHP, inte copy-paste mellan admin och publikt.

**Checklista för ny funktion:** (1) Logik i `inc/domain/…` (2) Tester i `tests/Unit/` (3) Tunt lager i shortcode/REST (4) UI visar och skickar parametrar. Se [REST_API.md](REST_API.md) och [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md).

---

## 5. Filstruktur (`inc/`)

Max **50 rader per funktion** (se [STYLE_GUIDE.md](STYLE_GUIDE.md)).

```
inc/
├── bootstrap.php           # MRT_bootstrap_load()
├── bootstrap/domain.php    # MRT_load_domain_modules()
├── constants.php
├── domain/
│   ├── datetime/
│   ├── journey/            # sökning, byte, kalender, normalisering, visningshjälpare
│   ├── pricing/
│   ├── route/
│   ├── service/            # services, stop-times, connections
│   ├── station/
│   ├── timetable/view/     # overview-data (JSON), group-view, grid-merge, grid-connections
│   ├── train-type/         # ikon-slugs
│   └── admin/              # dashboard-data, deviations (REST backing)
├── infrastructure/
│   ├── post-types/         # CPT + taxonomier
│   ├── rest/               # admin + publikt REST
│   └── wordpress/          # environment, plugin-settings, helpers-utils
├── admin/
│   ├── app.php, menu.php   # Vue shell + legacy redirects
│   ├── meta-boxes/         # save-hooks (UI borttaget)
│   └── tools/              # demo, import, clear-db, dev-navigation, timetable-pages
├── public/
│   ├── month-calendar/
│   ├── timetable-overview/
│   └── journey-wizard/
├── import/lennakatten/     # referensdata + importer
├── assets/                 # enqueue (anropas från inc/assets.php)
├── admin.php               # admin-bootstrap
└── shortcodes.php          # registrerar tre shortcodes
```

### Publika shortcodes

| Shortcode | Modul |
|-----------|--------|
| `[museum_timetable_month]` | `inc/public/month-calendar/` |
| `[museum_timetable_overview]` | `inc/public/timetable-overview/` |
| `[museum_journey_wizard]` | `inc/public/journey-wizard/` |

Rese-UI är endast wizard; `[museum_journey_planner]` finns inte längre (se [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md)).

### Admin (Vue)

Vue-admin under `admin.php?page=mrt_app` (`frontend/vue/src/admin/`). REST via `inc/infrastructure/rest/` och `adminRest.ts`. Dev-verktyg (clear DB, import, tidtabellssidor) i Vue `#/dev-tools` via `POST /dev/*` (dev-läge). Legacy `?page=mrt_settings` redirectar till Vue.

### Timetable overview (Vue)

`inc/domain/timetable/view/overview-data.php` bygger JSON (`MRT_get_timetable_overview_data`). Vue renderar i `frontend/vue/src/components/overview/`; admin meta box och shortcode mountar samma komponent (`inc/admin/timetable-vue-preview.php`).

---

## 6. Varför det spelar roll

Lös koppling mellan domän och UI gör det möjligt att byta layout eller API-format utan att röra kärnreglerna, och tvärtom att testa regler utan webbläsare.
