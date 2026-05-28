# Vue UI components (`frontend/vue/src/components/ui/`)

Shared primitives for wizard, month calendar, and overview. Styles: `assets/frontend/ui-components.css` (also imported via `frontend-public.css`).

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

Wizard-only layout/trip CSS remains under `assets/journey-wizard/`. Price matrix helpers live in `frontend/vue/src/shared/prices.ts`.

## Rebuild

After CSS changes: `cd frontend/vue && npm run build`.
