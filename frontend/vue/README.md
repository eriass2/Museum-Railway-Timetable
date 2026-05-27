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
  utils/        # calendarGrid, monthGrid, mrtStrings (resolveMrtString)
  components/   # Shared UI (MrtStepShell)
  apps/
  wizard/       # store/, composables/, components/, utils/ (typed WizardCfg)
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
| `npm test` | Vitest (12 files: utils, store, parseMount) |
| `npm run build` | Vite → `assets/dist/vue/` |
| `npm run verify` | IIFE smoke load in Node |
| `npm run check` | All of the above |
| `npm run e2e` | Playwright smoke (static wizard mount; run `build` first) |
| `npm run e2e:install` | Install Chromium for Playwright (once) |

See [TESTING.md](./TESTING.md) for manual WP regression.
