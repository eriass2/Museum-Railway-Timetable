# Vue UI components (`frontend/vue/src/components/ui/`)

Delat komponentbibliotek för wizard, månadskalender, översikt, tidtabellsindex och Vue-admin. Wizard-specifika wrappers ligger i `frontend/vue/src/wizard/components/`. Colocated primitiver har scoped CSS i SFC (`MrtAlert`, `MrtButton`, `MrtDot`, `MrtSurfaceCard`). Kvarvarande global modul-CSS: `assets/frontend/ui/` (wizard-steg, trips, kalender — barrel `ui-components.css`).

## Shared primitives (admin + public)

| Component | `context` | Notes |
|-----------|-----------|--------|
| `MrtButton` | `public` \| `admin` | Public: `.mrt-accent-btn`; admin: WP `.button` |
| `MrtAlert` | `public` \| `admin` | Public: `.mrt-ui-alert`; admin: WP `.notice` |
| `MrtAsyncState` | `public` \| `admin` | Loading, error, empty; admin retry via `@retry` |
| `MrtDot` | — | Traffic / timetable type dots |

Admin wrappers (deprecated gradually): `AdminLoadState` → `MrtAsyncState context="admin"`; `AdminStatusMessage` → `MrtAlert context="admin"`. `MrtAccentButton` delegates to `MrtButton context="public"`.

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
| `MrtMonthDayCell` | `mrt-month-day`, `is-selected` | Month calendar running days |
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

## Lager

```
assets/mrt-color-tokens.css     ← designsystem
frontend/vue/src/components/
├── ui/                         ← delade primitiver
├── overview/                   ← domän (delat)
├── timetable-index/            ← domän (publikt)
└── admin/components/ui/        ← admin-specifikt
```

**Medvetet kvar som global CSS:** `timetable-overview.css`, wizard-shell, `.mrt-empty` (månadskalender).

## Regler för nya komponenter

1. **Nya primitiver** → `frontend/vue/src/components/ui/` med scoped CSS.
2. **Tokens** → `assets/mrt-color-tokens.css` (aldrig nya hex i komponenter).
3. **App-specifikt** → `frontend/vue/src/styles/<app>/`.
4. **Admin-skals** → `admin/styles/admin-shell.css`.
5. **Import** → `@/components/ui` (publik + admin).

```vue
<MrtButton context="public" variant="primary">Sök resa</MrtButton>
<MrtButton context="admin" variant="primary">Spara</MrtButton>
```

### Checklista per ny primitiv

- [ ] `context`-prop om admin + publik
- [ ] Scoped CSS i SFC (inte ny fil i `assets/frontend/ui/`)
- [ ] Tokens — inga nya hex
- [ ] Vitest om klass-logik är icke-trivial
- [ ] Uppdatera denna fil

## Rebuild

After CSS changes: `cd frontend/vue && npm run build`.
