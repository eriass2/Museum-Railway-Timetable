# Vue experiment branch

Branch `experiment/vue-public-ui` replaces the three public shortcodes with Vue mount points when enabled:

| Shortcode | Vue app |
|-----------|---------|
| `[museum_timetable_month]` | `MonthCalendarApp.vue` |
| `[museum_timetable_overview]` | `TimetableOverviewApp.vue` |
| `[museum_journey_wizard]` | `JourneyWizardApp.vue` |

Legacy PHP/HTML and jQuery wizard modules are **not** loaded in Vue mode.

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

## Build

Local:

```bash
composer vue:build
# or
cd frontend/vue && npm ci && npm run build
```

Docker (tools profile):

```bash
docker compose --profile tools run --rm vue
```

Output: `assets/dist/vue/` (commit after CSS/JS changes so hosts without Node still work). Entry chunk `main-*.js` plus async chunk `JourneyWizardApp-*.js` when wizard shortcode is used.

Dev server (component work only — WP still serves shortcodes):

```bash
cd frontend/vue
npm run dev
```

## PHP integration

- `inc/assets/vue-frontend.php` — flag, enqueue bundled CSS/JS, mount HTML
- `inc/public/vue-shortcode-config.php` — JSON config per shortcode
- Shortcodes branch early to `MRT_render_vue_mount()` when `MRT_use_vue_frontend()`

## Current scope

- **Month calendar** and **timetable overview**: Vue SFCs (`MonthCalendarApp`, `TimetableOverviewApp`).
- **Journey wizard**: Vue step flow (`wizard/components/*`, `useWizard` composable). Same AJAX actions as legacy (`mrt_journey_calendar_month`, `mrt_search_journey`, `mrt_journey_connection_detail`).
- Journey wizard is **lazy-loaded** (`defineAsyncComponent`) so month/overview pages avoid the wizard chunk.
- Legacy jQuery modules were removed from `frontend/vue/`; behaviour lives in `wizard/components/` and `wizard/utils/`.

## Wizard Vue layout

```
frontend/vue/src/wizard/
  composables/useWizard.ts   — step state, route/date/trip selection
  components/                — one SFC per wizard step
  utils/                     — dates, connections, prices, vehicle icons
```

## Switch back

Remove `MRT_VUE_FRONTEND` or return `false` from the filter. Legacy enqueue path in `inc/assets/frontend.php` loads PHP CSS again.
