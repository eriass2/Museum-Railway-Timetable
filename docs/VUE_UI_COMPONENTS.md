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
| `MrtLegend` | `mrt-legend-list` | Wizard date, month |
| `MrtSegmentedControl` | `mrt-segmented` | Route trip type |
| `MrtFieldGroup` + `MrtCombobox` | `mrt-field`, `mrt-combobox` | Station search |
| `MrtTripSummary` | `mrt-trip-summary` | Trip cards, summary |
| `MrtExpandTrigger` | `mrt-expand-trigger` | Trip card detail toggle |
| `MrtVehicleRow` | `mrt-vehicle-row` | Trip card |
| `MrtHtmlPanel` | `mrt-html-panel` | Month day timetable, overview |

Wizard-only layout/trip CSS remains under `assets/journey-wizard/`.

## Rebuild

After CSS changes: `cd frontend/vue && npm run build`.
