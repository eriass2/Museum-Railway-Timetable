# Museum Railway – public Vue frontend

Builds to `assets/dist/vue/` as an **ES module** entry with **lazy chunks per app** (WordPress enqueues `type="module"`).

WordPress-integration, enqueue och REST: **[docs/VUE_FRONTEND.md](../../docs/VUE_FRONTEND.md)**.

## Apps (`src/apps/`)

| Mount `data-mrt-vue-app` | Component |
|--------------------------|-----------|
| `month` | `MonthCalendarApp.vue` |
| `overview` | `TimetableOverviewApp.vue` |
| `wizard` | `JourneyWizardApp.vue` |
| `index` | `TimetableIndexApp.vue` |

## Structure

```
src/
  config/       # Typed mount config + parseMountConfig
  api/          # restUrl, mrtRestRequest
  composables/  # useMrtRest, useWizardContext, useTimetableOverview
  utils/        # calendarDate, calendarGrid, monthGrid, mrtStrings (resolveMrtString)
  components/ui/   # Shared UI primitives (MrtAlert, MrtSurfaceCard, …)
  components/timetable-index/  # TimetableIndexApp view
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
| `npm run verify` | Manifest + lazy-chunk smoke check |
| `npm run check` | All of the above |
| `npm run e2e` | Playwright smoke (static wizard mount; run `build` first) |
| `npm run e2e:install` | Install Chromium for Playwright (once) |

See [TESTING.md](./TESTING.md) for manual WP regression. Utils guide: [../docs/VUE_UTILS.md](../docs/VUE_UTILS.md). Server HTML via `v-html`: [TRUSTED_HTML.md](./TRUSTED_HTML.md).
