# Museum Railway – public Vue frontend

Builds to `assets/dist/vue/` and is enqueued when `MRT_VUE_FRONTEND` is enabled.

## Apps (`src/apps/`)

| Mount `data-mrt-vue-app` | Component |
|--------------------------|-----------|
| `month` | `MonthCalendarApp.vue` |
| `overview` | `TimetableOverviewApp.vue` |
| `wizard` | `JourneyWizardApp.vue` (async chunk) |

## Wizard (`src/wizard/`)

- `composables/useWizard.ts` – step state machine
- `composables/useWizardDebug.ts` – dev presets from PHP `debugPresets`
- `components/` – one SFC per wizard step
- `utils/` – dates, prices, vehicle icons, calendar grid

AJAX uses `src/api/mrtApi.ts` (`fetch` + `config.nonce`).

## Commands

```bash
npm ci && npm run build
# or from repo root: composer vue:build
```

### Local check (no WordPress)

```bash
cd frontend/vue
npm ci
npm run check    # typecheck + build + verify bundle (IIFE, no wizardStrings, smoke load in Node)
```

From repo root: `composer vue:check`

| Script | What it does |
|--------|----------------|
| `npm run typecheck` | `vue-tsc` — catches typos like undefined imports |
| `npm run build` | Vite production build → `assets/dist/vue/` |
| `npm run verify` | Asserts manifest, IIFE shape, forbidden tokens, Node smoke load |
| `npm run check` | All of the above |
