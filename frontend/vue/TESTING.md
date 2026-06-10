# Manual test checklist (Vue public UI)

Detaljerad per-app-checklista. Översikt, Docker-smoke och automatiska kommandon: **[docs/SMOKE_CHECKLIST.md](../../docs/SMOKE_CHECKLIST.md)**.

Kör efter `npm run check` och hård refresh i webbläsaren (Ctrl+F5).

## Environment

- Demo page: `http://localhost:8080/?page_id=569` (or your local demosida)
- Plugin active; Vue bundle built (`composer vue:build`)

### Automated E2E

Se [SMOKE_CHECKLIST.md](../../docs/SMOKE_CHECKLIST.md) § Vue – detaljerad checklista och E2E. Avbrutna resor (statiska mounts): `/overview-cancelled`, `/wizard?debug=cancelled` — `e2e/cancelled-mount.spec.ts`.

## Month calendar (`data-mrt-vue-app="month"`)

- [ ] Month grid renders with correct weekdays
- [ ] Prev/next month links work
- [ ] Click running day loads timetable panel
- [ ] Error state if REST request fails

## Timetable overview (`overview`)

- [ ] Full timetable JSON renders (`.mrt-ov` groups, branch table if buses imported)
- [ ] No console errors

## Journey wizard (`wizard`)

- [ ] Route step: hero is compact (root has `data-step="route"`)
- [ ] Step nav shows 1–4 (or 5 for return)
- [ ] Route: select from/to, single vs return, search
- [ ] Date: calendar loads, pick day → outbound
- [ ] Outbound: connections list, select trip
- [ ] Return (tur-retur): return leg after outbound
- [ ] Summary: times, prices, ticket CTA if URL set
- [ ] Back buttons work on each step
- [ ] Debug presets (if configured): `debug="date"`, `outbound`, `return`, `summary`

## Timetable index (`index`)

Shortcode: `[museum_timetable_index]`

- [ ] Intro text shows when `intro="1"` (default)
- [ ] List renders timetable links with labels and optional meta (dates)
- [ ] Empty state message when no timetables exist
- [ ] No console errors

## Build

```bash
cd frontend/vue && npm run check
# or (host): composer vue:check
# or (Docker, repo root): .\scripts\vue-check.ps1
```
