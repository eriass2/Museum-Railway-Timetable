# Vue UI components (`frontend/vue/src/components/ui/`)

Shared primitives for wizard, month calendar, and overview. Wizard-specific wrappers live in `frontend/vue/src/wizard/components/`. Styles: `assets/frontend/ui-components.css` (imported via `mrt-public.css`).

## Components

| Component | CSS prefix | Used by |
|-----------|------------|---------|
| `MrtAlert` | `mrt-ui-alert` | All apps |
| `MrtAsyncState` | `mrt-async`, `mrt-empty` | Trip steps, calendar grid, overview, month day panel |
| `MrtAccentButton` | `mrt-accent-btn` | Wizard CTAs |
| `MrtSurfaceCard` | `mrt-surface` | Wizard steps, month HTML panel |
| `MrtStepHeader` | `mrt-step-header` | Wizard steps 2–4 |
| `MrtStepProgress` | `mrt-step-progress` | Journey wizard |
| `MrtCalendarNav` | `mrt-calendar-nav` | Wizard date, month shortcode |
| `MrtCalendarGrid` | `mrt-calendar-grid` | Wizard date (`WizardCalendarGrid`), month table |
| `MrtMonthDayCell` | `mrt-day`, `is-selected` | Month calendar running days |
| `MrtWizardCalendarDayCell` | `mrt-calendar-day` | Wizard date grid (`WizardCalendarGrid`) |
| `MrtSummaryCard` | `mrt-summary-card` | Wizard summary step |
| `MrtDetailPanel` | `mrt-detail-panel` | Expanded trip connection detail |
| `MrtDetailSegment` | `mrt-detail-segment` | One leg in connection detail |
| `MrtLegend` | `mrt-legend-list`, `mrt-legend__hint` | Wizard date, month |
| `MrtTimeline` | `mrt-timeline` | Wizard detail (`WizardTimeline` wrapper) |
| `MrtTripCard` | `mrt-trip-card` | Wizard trip list (`WizardTripCard` wrapper) |
| `MrtHeading` | `mrt-heading` | Month title, wizard route/summary/detail, price block |
| `MrtStatusMessage` | `mrt-status-message` | Wizard empty calendar month |
| `MrtPriceTable` | `mrt-price-block` | Wizard summary (`shared/prices.ts` + `PriceTableLabels` props) |
| `MrtSegmentedControl` | `mrt-segmented` | Route trip type |
| `MrtFieldGroup` + `MrtCombobox` | `mrt-field`, `mrt-combobox` | Station search |
| `MrtTripSummary` | `mrt-trip-summary` | Trip cards, summary |
| `MrtExpandTrigger` | `mrt-expand-trigger` | Trip card detail toggle |
| `MrtVehicleRow` | `mrt-vehicle-row` | Trip card, detail segment |
| `MrtHtmlPanel` | `mrt-html-panel` | Month day timetable, overview |
| `MrtStepPanel` | `mrt-step-panel` | Wizard step regions (`data-wizard-step`) |
| `MrtRouteLayout` | `mrt-route-layout` | Route search form layout |
| `MrtSelectedTrip` | `mrt-selected-trip` | Return step outbound recap |
| `MrtTripList` | `mrt-trip-list` | Outbound/return connection list |

Wizard shell CSS: `frontend/vue/src/styles/journey-wizard/`. Visuell wizard-referens: [mockups/DESIGN_TOKENS.md](mockups/DESIGN_TOKENS.md). Price matrix: `frontend/vue/src/shared/prices.ts`.

## Alerts

| Class | Where | Component |
|-------|--------|-----------|
| `mrt-ui-alert`, `mrt-ui-alert--error`, … | Public Vue apps | `MrtAlert` |
| `mrt-alert`, `mrt-alert--error`, … | Legacy PHP templates | PHP (inga nya i Vue) |

New Vue code must use **`MrtAlert`** (`mrt-ui-alert`).

## Design tokens (CSS)

Defined in `assets/mrt-color-tokens.css`; wizard overrides in `frontend/vue/src/styles/journey-wizard/base.css`. Prefer tokens over hard-coded hex.

**Shared:** `--mrt-color-green-700`, `--mrt-color-accent-500/700`, `--mrt-color-on-dark`, `--mrt-color-neutral-*`, `--mrt-font-lg/xl`.

**Wizard shell:** `--mrt-wizard-green-dark`, `--mrt-wizard-surface`, `--mrt-wizard-text`, `--mrt-wizard-yellow`, `--mrt-wizard-focus`.

**Overview (`.mrt-ov-*`):** `frontend/vue/src/styles/timetable-overview.css` — `--mrt-ov-green`, `--mrt-ov-highlight`, `--mrt-ov-transfer`, `--mrt-ov-stripe`. Legacy aliases in `assets/frontend/tokens.css`.

See also [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md).

## Rebuild

After CSS changes: `cd frontend/vue && npm run build`.
