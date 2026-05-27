# Manual test checklist (Vue public UI)

Run after `npm run check` and hard-refresh in the browser (Ctrl+F5).

## Environment

- Demo page: `http://localhost:8080/?page_id=569` (or your local demosida)
- `MRT_VUE_FRONTEND` enabled; plugin active

## Month calendar (`data-mrt-vue-app="month"`)

- [ ] Month grid renders with correct weekdays
- [ ] Prev/next month links work
- [ ] Click running day loads timetable panel
- [ ] Error state if AJAX fails

## Timetable overview (`overview`)

- [ ] Full timetable HTML loads
- [ ] No console errors

## Journey wizard (`wizard`)

- [ ] Step nav shows 1–4 (or 5 for return)
- [ ] Route: select from/to, single vs return, search
- [ ] Date: calendar loads, pick day → outbound
- [ ] Outbound: connections list, select trip
- [ ] Return (tur-retur): return leg after outbound
- [ ] Summary: times, prices, ticket CTA if URL set
- [ ] Back buttons work on each step
- [ ] Debug presets (if configured): `debug="date"`, `outbound`, `return`, `summary`

## Build

```bash
cd frontend/vue && npm run check
# or: composer vue:check
```
