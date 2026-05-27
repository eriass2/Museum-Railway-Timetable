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
