# Trusted HTML (`v-html`)

Vue escapes `{{ }}` output by default. **`v-html` does not** — any script or event handlers in the string can run in the browser.

## Where we use `v-html`

| Component | Source | AJAX action |
|-----------|--------|-------------|
| `TimetableOverviewApp.vue` | WordPress timetable overview | `mrt_timetable_overview_html` |
| `MonthCalendarApp.vue` | Day panel | `mrt_get_timetable_for_date` |
| `WizardRouteStep.vue` | Optional timetable drawer | `mrt_timetable_overview_html` |

## Contract

1. **PHP must sanitize** all HTML returned by these actions (`wp_kses_post` or equivalent).
2. **Do not** pass user input into HTML generation without escaping on the server.
3. **Do not** add new `v-html` bindings without updating this file and a security review.

## Client helpers

- `useTimetableHtml` / `loadOverviewHtml` — fetch only; they do not sanitize HTML.
- Prefer `{{ }}` for labels, errors, and station names from config.
