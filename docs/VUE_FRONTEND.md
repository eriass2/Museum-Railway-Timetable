# Publikt Vue-frontend

De tre publika shortcodes mountar Vue-appar från `assets/dist/vue/`:

| Shortcode | Vue app |
|-----------|---------|
| `[museum_timetable_month]` | `MonthCalendarApp.vue` |
| `[museum_timetable_overview]` | `TimetableOverviewApp.vue` |
| `[museum_journey_wizard]` | `JourneyWizardApp.vue` |

Legacy jQuery-frontend är borttagen; ingen PHP-toggle krävs.

## CSS

Public styles are **bundled by Vite**, not enqueued from WordPress:

- Source entry: `frontend/vue/src/styles/mrt-public.css`
- Imports (unchanged on disk): `assets/train-type-icons.css`, `frontend-public.css`
- Vue-owned: `frontend/vue/src/styles/timetable-overview.css`, `frontend/vue/src/styles/journey-wizard/`
- Vue-only shell: `frontend/vue/src/styles/vue-shell.css`

Styles ship in the Vite bundle (not separate `mrt-frontend-public` handles).

## Docker / utveckling

Docker dev (`docker compose up` / `docker-dev-reset.ps1`) sätter `MRT_DEVELOPMENT` och bygger Vue-bundeln.

## Build and check

```bash
composer vue:check   # typecheck + vitest + build + bundle smoke test
composer vue:build   # build only
```

Docker (tools profile):

```bash
docker compose --profile tools run --rm vue
```

Output: `assets/dist/vue/` (commit after CSS/JS changes). Single IIFE entry `assets/main-*.js`.

Manual regression: [frontend/vue/TESTING.md](../frontend/vue/TESTING.md).

## PHP integration

- `inc/assets/vue-frontend.php` — enqueue bundled CSS/JS (IIFE), mount HTML
- `inc/public/vue-shortcode-config.php` — JSON config per shortcode
- Shortcodes render via `MRT_render_vue_mount()` (Vue-only)

## Current scope

- **Timetable overview** and **month day panel**: JSON from `mrt_timetable_overview_data` / `mrt_get_timetable_for_date`; UI in `components/overview/` + `styles/timetable-overview.css` (no `v-html`, no public PHP HTML).
- **Month calendar** grid: config JSON in mount; day detail uses shared overview components.
- **Journey wizard**: reactive store; JSON AJAX (`mrt_search_journey`, `mrt_journey_calendar_month`, `mrt_journey_connection_detail`).
- **PHP HTML** timetable renderers removed; Vue-admin editor uses the same overview component as the public site.

## Wizard Vue layout

```
frontend/vue/src/
  config/                    # parseMountConfig, typed configs per app
  composables/               # useMrtRest, useWizardContext
  components/ui/             # shared Mrt* primitives (see VUE_UI_COMPONENTS.md)
  utils/mrtStrings.ts        # resolveMrtString (strings / wizard / labels)
  wizard/
    store/                   # createWizardStore, wizardStoreGetters, route/steps/selections
    composables/             # useTripConnections, useWizardCalendar, useConnectionDetail, useWizardDebug
    components/              # step SFCs
    utils/
```

## E2E (Playwright)

```bash
cd frontend/vue
npm run build
npm run e2e:install   # once per machine
npm run e2e           # static wizard mount on :5199
```

Optional WordPress demo (Docker):

```bash
MRT_E2E_WP_URL=http://127.0.0.1:8080/?page_id=569 npm run e2e
```
