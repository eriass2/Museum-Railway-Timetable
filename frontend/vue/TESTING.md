# Manual test checklist (Vue public UI)

Run after `npm run check` and hard-refresh in the browser (Ctrl+F5).

## Environment

- Demo page: `http://localhost:8080/?page_id=569` (or your local demosida)
- Plugin active; Vue bundle built (`composer vue:build`)

### Automated E2E (no WordPress)

```bash
cd frontend/vue
npm run build
npm run e2e:install   # first time
npm run build         # required before e2e (serve uses dist/)
npm run e2e           # includes admin-nav.spec.ts (static admin tabs)
```

Optional against Docker demo (all four shortcodes on one page):

```bash
MRT_E2E_WP_DEMO_URL=http://127.0.0.1:8080/?page_id=… npm run e2e -- e2e/*-wp.spec.ts
```

Or run the full WP stack script from repo root: `bash scripts/ci-e2e-wp.sh`

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
