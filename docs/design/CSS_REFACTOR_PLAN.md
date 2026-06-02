# CSS-refactor — plan och status

Städning efter Vue-migrering: tydligare lager, mindre duplicering, bättre återanvändbarhet.

## Mål

1. En import-kedja utan duplicering i Vue-bundlen
2. Inga döda `@import` (admin overview-PHP)
3. En primitives-fil istället för `components-ui.css` + överlapp i `ui-components.css`
4. Delade kalender-tokens (månad + wizard)
5. Modulära UI-CSS-filer under `assets/frontend/ui/`

## Lager (målbild)

```
mrt-color-tokens.css, mrt-typography.css
  → tokens.css (spacing, legacy aliases)
  → components-base.css (card, alert PHP)
  → ui-components.css (barrel @import)
      → ui/primitives.css
      → ui/calendar-tokens.css
      → ui/*.css (alerts, wizard, calendar, trips, …)
  → app-CSS i frontend/vue/src/styles/ (wizard, overview)
```

## Steg

| # | Åtgärd | Commit |
|---|--------|--------|
| 1 | Ta bort dubbel `ui-components.css`-import i `mrt-public.css` | ✓ `1406f16` |
| 2 | Ta bort döda overview-`@import` i `admin.css` | ✓ `9d511b4` |
| 3 | Flytta `components-ui.css` → `ui/primitives.css`; uppdatera kedjor | ✓ `d66ca02` |
| 4 | Inför `ui/calendar-tokens.css`; koppla month + wizard | ✓ `ef062a4` |
| 5 | Dela `ui-components.css` i moduler + barrel-import | ✓ (denna branch) |
| 6 | Flytta `month-calendar.css` → Vue (`MonthCalendarApp.vue`) | ✓ |
| 7 | Ta bort döda meta-box CSS (`meta-boxes-*.css`) + doc-referenser | ✓ |

## Regler efter refactor

- Nya Vue-primitives → `assets/frontend/ui/<område>.css` + rad i barrel
- App-specifikt → `frontend/vue/src/styles/<app>/` eller `<app>.css` eller `<app>.css` (t.ex. `month-calendar.css`, `journey-wizard/`)
- Tokens → `mrt-color-tokens.css` / `mrt-typography.css` — aldrig nya hex i komponenter
- Se [STYLE_GUIDE.md](../STYLE_GUIDE.md) §3 och [VUE_UI_COMPONENTS.md](../VUE_UI_COMPONENTS.md)
