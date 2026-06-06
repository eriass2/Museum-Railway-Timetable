# Publikt Vue-frontend

De fyra publika shortcodes mountar Vue-appar från `assets/dist/vue/`:

| Shortcode | Vue app |
|-----------|---------|
| `[museum_timetable_month]` | `MonthCalendarApp.vue` |
| `[museum_timetable_overview]` | `TimetableOverviewApp.vue` |
| `[museum_journey_wizard]` | `JourneyWizardApp.vue` |
| `[museum_timetable_index]` | `TimetableIndexApp.vue` |

Legacy jQuery-frontend är borttagen; ingen PHP-toggle krävs.

## CSS

Public styles are **bundled by Vite**, not enqueued from WordPress:

- Source entry: `frontend/vue/src/styles/mrt-public.css`
- Imports (unchanged on disk): `assets/train-type-icons.css`, `frontend-public.css`
- Vue-owned: `frontend/vue/src/styles/month-calendar.css`, `frontend/vue/src/styles/timetable-overview.css`, `frontend/vue/src/styles/timetable-index.css`, `frontend/vue/src/styles/journey-wizard/`
- Vue-only shell: `frontend/vue/src/styles/vue-shell.css`

Styles ship in the Vite bundle (not separate `mrt-frontend-public` handles).

## Docker / utveckling

Docker dev (`docker compose up` / `docker-dev-reset.ps1`) sätter `MRT_DEVELOPMENT` och bygger Vue-bundeln.

## Build and check

Host (Node/npm i PATH):

```bash
composer vue:check   # typecheck + vitest + build + bundle smoke test
composer vue:build   # build only
```

Docker (Windows rekommenderat — samma Node 22 som CI):

```powershell
.\scripts\vue-check.ps1
```

```bash
docker compose --profile tools run --rm vue sh -c "npm ci && npm run check"
docker compose --profile tools run --rm vue sh -c "npm ci && npm run build && npm run verify"
```

**Kör inte** `docker compose … run composer vue:check` — `composer`-imaget saknar npm.

Output: `assets/dist/vue/` (commit after CSS/JS changes). ES module entry `assets/main-*.js` with lazy chunks per app (`month`, `overview`, `wizard`, `index`).

Manual regression: [frontend/vue/TESTING.md](../frontend/vue/TESTING.md).

## PHP integration

- `inc/assets/vue-frontend.php` — enqueue ES module entry + CSS from manifest, mount HTML
- `inc/public/vue-shortcode-config.php` — JSON config per shortcode
- Shortcodes render via `MRT_render_vue_mount()` (Vue-only)

## Current scope

- **Timetable index**: static list from PHP config (`items`, `labels`); no REST. See `[museum_timetable_index]` in `inc/public/timetable-index/`.
- **Timetable overview** and **month day panel**: JSON from `GET /timetables/{id}/overview` and `GET /timetables/day`; UI in `components/overview/` + `styles/timetable-overview.css` (no `v-html`, no public PHP HTML).
- **Month calendar** grid: config JSON in mount; day detail uses shared overview components.
- **Journey wizard**: reactive store; REST via `mrtRestRequest` (`journey/search`, `journey/calendar`, `journey/connection-detail`). Client-side caches for calendar month and trip search avoid redundant refetch on back navigation — see [WIZARD_PERFORMANCE_PLAN.md](WIZARD_PERFORMANCE_PLAN.md).
- **PHP HTML** timetable renderers removed; Vue-admin editor uses the same overview component as the public site.

## Wizard Vue layout

```
frontend/vue/src/
  config/                    # parseMountConfig, typed configs per app
  composables/               # useMrtRest, useWizardContext, useMonthCalendar, useTimetableOverview
  components/ui/             # shared Mrt* primitives (see VUE_UI_COMPONENTS.md)
  components/timetable-index/
  utils/calendarDate.ts      # addCalendarMonths (month + wizard)
  utils/mrtStrings.ts        # resolveMrtString (strings / wizard / labels)
  wizard/
    store/                   # createWizardStore, wizardStoreGetters, route/steps/selections
    composables/             # useTripConnections, useWizardCalendar, useConnectionDetail, useWizardDebug
    components/              # step SFCs
    utils/                   # wizardCalendarCache, tripConnectionsCache, …
```

Wizard performance roadmap: [WIZARD_PERFORMANCE_PLAN.md](WIZARD_PERFORMANCE_PLAN.md).

## E2E (Playwright)

```bash
cd frontend/vue
npm run build
npm run e2e:install   # once per machine
npm run e2e           # static wizard mount on :5199
```

Optional WordPress demo (Docker):

```bash
MRT_E2E_WP_DEMO_URL=http://127.0.0.1:8080/?page_id=569 npm run e2e
```
