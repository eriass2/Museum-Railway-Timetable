# Vue experiment branch

Branch `experiment/vue-public-ui` replaces the three public shortcodes with Vue mount points when enabled:

| Shortcode | Vue app |
|-----------|---------|
| `[museum_timetable_month]` | `MonthCalendarApp.vue` |
| `[museum_timetable_overview]` | `TimetableOverviewApp.vue` |
| `[museum_journey_wizard]` | `JourneyWizardApp.vue` |

Legacy PHP/HTML and jQuery wizard modules are **not** loaded in Vue mode.

## Enable on a site

In `wp-config.php` (or Docker env):

```php
define( 'MRT_VUE_FRONTEND', true );
```

Or via filter:

```php
add_filter( 'mrt_use_vue_frontend', '__return_true' );
```

## Build

```bash
cd frontend/vue
npm install
npm run build
```

Output: `assets/dist/vue/` (committed on this branch so Docker works without Node).

Dev server (optional, for component work only — still needs WP for shortcodes):

```bash
cd frontend/vue
npm run dev
```

## PHP integration

- `inc/assets/vue-frontend.php` — flag, enqueue, mount HTML
- `inc/public/vue-shortcode-config.php` — JSON config per shortcode
- Shortcodes branch early to `MRT_render_vue_mount()` when `MRT_use_vue_frontend()`

## Current scope

Placeholder shells show received config. Port UI step by step; reuse existing AJAX actions from `MRTFrontendApi` via `fetch` + `config.ajaxurl` / `config.nonce`.

## Switch back

Remove `MRT_VUE_FRONTEND` or return `false` from the filter. No code changes on `main` required.
