# Journey wizard — UX and shared components

Production wizard UI is Vue-only (`frontend/vue/src/wizard/`). Styles live in `assets/journey-wizard/` and are bundled via `frontend/vue/src/styles/mrt-public.css`.

## Shared Vue components

| Component | Role |
|-----------|------|
| `WizardSurfaceCard` | White card on green hero; all steps |
| `WizardStepHeader` | Back link + route/date context (no duplicate step title) |
| `WizardProgress` | Non-interactive step pills (`aria-current="step"`) |
| `WizardAccentButton` | Gold CTA / “Välj” (`variant`: `primary` \| `select`) |
| `WizardStationField` | Searchable station combobox |
| `WizardTripTypeIcon` | Enkel / tur-retur icons |

Step titles appear only in the progress nav. Each panel uses `aria-label` from PHP `labels` / `wizard` l10n.

## Swedish calendar

- Month names and weekday abbreviations: `MRT_journey_wizard_calendar_i18n_arrays()` in `inc/assets/frontend.php` (not `date_i18n`, so English WP locale does not leak).
- Display dates: `formatYmdForDisplay` → `15 mars 2026`.
- “Idag” jumps to current month (`goToToday`).

## Empty month

When no `ok` days exist for the loaded month, `WizardDateStep` shows `calendarEmptyMonth` + `calendarEmptyHint`.

## CSS

- `wizard-shell.css` — surface, progress, accent buttons, calendar empty, mobile price table.
- Rebuild after CSS edits: `cd frontend/vue && npm run build`, then hard-refresh.

See also `docs/PRODUCTION_WIZARD.md` for backlog.
