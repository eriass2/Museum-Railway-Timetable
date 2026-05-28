# Journey wizard — UX and components

Wizard domain code: `frontend/vue/src/wizard/`. Shared UI: `frontend/vue/src/components/ui/` — see [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md).

## Wizard-specific pieces

- `WizardRouteStep`, `WizardDateStep`, `WizardTripStep`, `WizardSummaryStep`
- `WizardPanel` — step region wrapper (`data-wizard-step`, `ariaLabel`)
- `WizardTripCard`, `WizardCalendarGrid` (wraps `MrtCalendarGrid`), `WizardStationField` (wraps `MrtCombobox`)
- Summary prices via `MrtPriceTable` directly
- `WizardTripTypeIcon`

Step titles live in `MrtStepProgress` only. Swedish calendar strings from `MRT_journey_wizard_calendar_i18n_arrays()` in PHP.

See also `docs/PRODUCTION_WIZARD.md`.
