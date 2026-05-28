# Trusted HTML (`v-html`)

Vue escapes `{{ }}` output by default. **`v-html` does not** — avoid it for timetable content.

## Timetable (overview + month day panel)

Public timetable UIs use **JSON** from:

- `mrt_timetable_overview_data`
- `mrt_get_timetable_for_date` (returns `{ overview: … }`)

Rendered by `components/overview/` (`MrtTimetableOverviewView.vue` and child SFCs) — no `v-html` for timetables.

## Other `v-html`

Do not add new `v-html` bindings without updating this file and a security review.

## PHP HTML

Timetable **HTML** renderers (`MRT_render_timetable_*`) are for **wp-admin preview** only, not public AJAX.
