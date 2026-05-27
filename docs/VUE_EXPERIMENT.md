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
- Shortcodes branch early to `MRT_render_vue_mount()` when `MRT_use_vue_frontend()`

## Current scope

- **Month calendar** and **timetable overview**: Vue SFCs with typed config (`src/config/types.ts`).
- **Journey wizard**: reactive store (`wizard/store/createWizardStore.ts`), step SFCs, `useWizardContext()`.
- Same AJAX actions as before (`mrt_journey_calendar_month`, `mrt_search_journey`, `mrt_journey_connection_detail`).
- Wizard panels set `data-wizard-step` for step-scoped CSS in `assets/journey-wizard/`.

## Wizard Vue layout

```
frontend/vue/src/
  config/                    # parseMountConfig, typed configs per app
  composables/               # useMrtAjax, useWizardContext
  components/MrtStepShell.vue
  wizard/
    store/                   # createWizardStore, route/steps/selections
    composables/             # useTripConnections, useWizardDebug
    components/              # step SFCs
    utils/
```

## Switch back

Remove `MRT_VUE_FRONTEND` or return `false` from the filter. Legacy enqueue path in `inc/assets/frontend.php` loads PHP CSS again.
