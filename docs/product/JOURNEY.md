# Publikt reseflöde (wizard och planner)

Översikt över **levererad** funktionalitet för besökare. Detaljer om shortcodes: [SHORTCODES_OVERVIEW.md](SHORTCODES_OVERVIEW.md).

## Shortcodes

| Shortcode | Syfte |
|-----------|--------|
| `[museum_journey_wizard]` | Flersstegsflöde: rutt, enkel/retur, kalender, utresa, retur, sammanfattning (prismatris, notiser) |
| `[museum_journey_planner]` | En skärm: sök anslutning mellan stationer på ett datum |

## Backend och tillgångar

- **Domän:** `inc/functions/journey-*.php` (kalender, sök, retur, prismatris, normalisering, m.m.)
- **AJAX:** `inc/admin-ajax/journey.php`, `journey-parse.php`, `journey-render.php`
- **Wizard:** `inc/shortcodes/shortcode-journey-wizard.php`, `assets/journey-wizard.js`, `assets/journey-wizard.css`
- **Planner:** `inc/shortcodes/shortcode-journey.php`, `assets/frontend.js`
- **Delade JS:** `mrt-string-utils.js`, `mrt-date-utils.js`, `mrt-frontend-api.js` (se [design/STYLE_GUIDE.md](../design/STYLE_GUIDE.md) §4)

## Tillgänglighet och test

- [accessibility/WCAG_JOURNEY_WIZARD.md](../accessibility/WCAG_JOURNEY_WIZARD.md) – wizard
- [accessibility/WCAG_PUBLIC_SHORTCODES.md](../accessibility/WCAG_PUBLIC_SHORTCODES.md) – planner, månad, översikt
- [accessibility/RELEASE_A11Y_SMOKE.md](../accessibility/RELEASE_A11Y_SMOKE.md) – manuell rökning inför release
- `composer test` – enhetstester för journey-hjälpare (utan full WP-DB)

## Designreferens

Mockup-bilder: `docs/mockups/` (`sok-din-resa.png`, `valj-datum.png`, `valj-utresa.png`, `valj-aterresa.png`). Tokens och komponenter: [design/DESIGN_SYSTEM.md](../design/DESIGN_SYSTEM.md), [design/COMPONENT_LIBRARY.md](../design/COMPONENT_LIBRARY.md).

## Valfritt framåt (ej blockerande för MVP)

- Rate limiting på publika AJAX-anrop
- Integrationstester mot databas / full WordPress
- UI-polish (hero, autocomplete stationer, flerben tydligare i planner)
- E2E (Playwright/Cypress) – se [guides/FUTURE_WORK.md](../guides/FUTURE_WORK.md)

Historiska planer och gap-analyser: [archive/planning/](../archive/planning/).
