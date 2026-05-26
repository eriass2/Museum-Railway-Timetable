# Journey wizard – design tokens (från nuvarande CSS)

Extraherat från `assets/journey-wizard/base.css` och relaterade moduler. Uppdatera när mockup-PNG finns i `docs/mockups/`.

## Färger

| Token | Värde | Användning |
|-------|--------|------------|
| `--mrt-jw-green` | `#1c650f` | Primär grön |
| `--mrt-jw-green-dark` | `#15530b` | Panelbakgrund |
| `--mrt-jw-yellow` | `#e3d449` | Accent, aktivt steg, CTA |
| `--mrt-jw-orange` | `#f39813` | (reserverad accent) |
| `--mrt-jw-surface` | `#ffffff` | Ljusa ytor i steg |
| `--mrt-jw-text` | `#141414` | Brödtext på ljus yta |
| `--mrt-jw-focus` | `#fff4a3` | Fokusring |

## Typografi

- Rubriker steg/hero: ~2.4–3.2rem, font-weight 900 (`.mrt-journey-wizard__step-title`, `__hero-title`)
- Brödtext panel: system/stack via tema; wizard sätter vit text på grön panel

## Layout

- Hero: full viewport width (`100vw`), min-height ~`76dvh`
- Aktivt stegpanel: `max-width` ~`46rem` (sök), ~`54rem` (utresa/retur med `steps-outbound-return.css`)
- Tidtabell i wizard: utökad bredd `86rem` när `search-panel--with-timetable`

## Kalender (steg datum)

- Grön = trafik för vald resa (`--ok`)
- Gul/orange traffic = trafik men ej vald resa
- Grå = ingen trafik

Se `assets/journey-wizard/controls-calendar.css`.

## Utresa / återresa

- Vit turkort med skugga på grön panel (`trips-detail-summary.css`)
- **Välj →**: gul knapp (`controls-calendar.css` `.mrt-journey-wizard__btn-select`)
- Tider med punkt: `10.00 → 10.57` (`trip-card.js` `formatTripClock`)
- Byte: fordon badges med `→` mellan (`mrt-journey-wizard__vehicle-sep`)
- Vald utresa-kort på retursteget: gul kant, mörkgrön bakgrund (`steps-outbound-return.css`)
- Aktivt steg i steglistan: gul bakgrund + fokusring vid `aria-current="step"`
- Expanderat kort: tidslinje med punkt-tider, `∨ visa passerade stationer`, **Priser**-tabell i kortet (`connection-detail.js`, `prices.js` `compactTitle`)
