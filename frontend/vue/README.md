# Museum Railway – public Vue frontend

Builds to `assets/dist/vue/` as a **single IIFE bundle** (classic WordPress `<script>` enqueue).

## Apps (`src/apps/`)

| Mount `data-mrt-vue-app` | Component |
|--------------------------|-----------|
| `month` | `MonthCalendarApp.vue` |
| `overview` | `TimetableOverviewApp.vue` |
| `wizard` | `JourneyWizardApp.vue` |

## Structure

```
src/
  config/       # Typed mount config + parseMountConfig
  api/          # mrtPost
  composables/  # useMrtAjax, useWizardContext
  components/   # Shared UI (MrtStepShell)
  apps/
  wizard/       # Store, steps, utils
  utils/        # calendarGrid, monthGrid
```

## Commands

```bash
npm ci && npm run build
# or from repo root: composer vue:build
```

### Local check (no WordPress)

```bash
cd frontend/vue
npm ci
npm run check    # typecheck + test + build + verify
```

From repo root: `composer vue:check`

| Script | What it does |
|--------|----------------|
| `npm run typecheck` | `vue-tsc` |
| `npm test` | Vitest (pure utils) |
| `npm run build` | Vite → `assets/dist/vue/` |
| `npm run verify` | IIFE smoke load in Node |
| `npm run check` | All of the above |

See [TESTING.md](./TESTING.md) for manual WP regression.
