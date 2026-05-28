# Vue experiment branch

Branch `experiment/vue-public-ui` replaces the three public shortcodes with Vue mount points when enabled:

| Shortcode | Vue app |
|-----------|---------|
| `[museum_timetable_month]` | `MonthCalendarApp.vue` |
| `[museum_timetable_overview]` | `TimetableOverviewApp.vue` |
| `[museum_journey_wizard]` | `JourneyWizardApp.vue` |

Legacy jQuery wizard is **removed** on this branch; all three public shortcodes use the Vue bundle only.

## CSS

Public styles are **bundled by Vite**, not enqueued from WordPress:

- Source entry: `frontend/vue/src/styles/mrt-public.css`
- Imports (unchanged on disk): `assets/train-type-icons.css`, `frontend-public.css`, `frontend-overview.css`, `journey-wizard.css`
- Vue-only shell: `frontend/vue/src/styles/vue-shell.css`

In Vue mode, `mrt-frontend-public`, `mrt-frontend-overview`, and `mrt-journey-wizard` handles are **not** registered.

## Enable on a site

Docker dev (`docker compose up` / `docker-dev-reset.ps1`) sets:

```php
define( 'MRT_VUE_FRONTEND', true );
define( 'MRT_DEVELOPMENT', true );
```

Manually in `wp-config.php`:

```php
define( 'MRT_VUE_FRONTEND', true );
```

Or via filter:

```php
add_filter( 'mrt_use_vue_frontend', '__return_true' );
```

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

- `inc/assets/vue-frontend.php` — flag, enqueue bundled CSS/JS (IIFE), mount HTML
- `inc/public/vue-shortcode-config.php` — JSON config per shortcode
- Shortcodes render via `MRT_render_vue_mount()` (Vue-only)

## Current scope

- **Timetable overview** and **month day panel**: JSON from `mrt_timetable_overview_data` / `mrt_get_timetable_for_date`; UI in `components/overview/` + `styles/timetable-overview.css` (no `v-html`, no public PHP HTML).
- **Month calendar** grid: config JSON in mount; day detail uses shared overview components.
- **Journey wizard**: reactive store; JSON AJAX (`mrt_search_journey`, `mrt_journey_calendar_month`, `mrt_journey_connection_detail`).
- **PHP HTML** timetable renderers: wp-admin preview only (`inc/admin/timetable-html-preview.php`).

## Wizard Vue layout

```
frontend/vue/src/
  config/                    # parseMountConfig, typed configs per app
  composables/               # useMrtAjax, useWizardContext
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
