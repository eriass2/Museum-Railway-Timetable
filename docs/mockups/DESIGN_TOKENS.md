# Journey wizard – design tokens

Komponent-specifika tokens. **Färgpalett (källa):** [docs/design/COLOR_PALETTE.md](../design/COLOR_PALETTE.md) och `assets/mrt-color-tokens.css`.

## Färger (alias)

Wizard-CSS använder `--mrt-wizard-*` som pekar på `--mrt-color-*` i token-filen.

| Token | Mappar till | Användning |
|-------|-------------|------------|
| `--mrt-wizard-green` | `green-600` | Varumärkesgrön |
| `--mrt-wizard-green-dark` | `green-700` | Hero / mörk panel |
| `--mrt-wizard-yellow` | `accent-600` (`#e0b820`) | CTA, aktivt steg, vald restyp — **vit text** |
| `--mrt-wizard-yellow-bright` | `accent-500` | Highlight / klara steg på mörk bakgrund |
| `--mrt-wizard-surface` | `neutral-100` | Formulärfält |
| `--mrt-wizard-text` | `neutral-900` | Brödtext på ljus yta |
| `--mrt-wizard-focus` | `focus-ring` | Fokusring |

## Restyp (steg 1)

- Segmentknappar: `steps-route.css` (`.mrt-journey-wizard__trip-type-segmented`)
- Ikoner: `frontend/vue/src/wizard/components/WizardTripTypeIcon.vue` — SVG, `currentColor` (enkel = pil höger, tur/retur = pilar båda håll)
- Storlek: ~1,85 rem, vertikalt centrerad i knappen (`.mrt-journey-wizard__trip-type-icon`)

## Typografi

- Rubriker steg/hero: ~2.4–3.2rem, font-weight 900 (`.mrt-journey-wizard__step-title`, `__hero-title`)
- Brödtext panel: system/stack via tema; wizard sätter vit text på grön panel

## Layout

- Hero: full viewport width (`100vw`), min-height ~`76dvh`
- Aktivt stegpanel: `max-width` ~`46rem` (sök), ~`54rem` (utresa/retur med `steps-outbound-return.css`)
- Tidtabell i wizard: utökad bredd `86rem` när `search-panel--with-timetable`

## Kalender (steg datum)

- Vit **kalenderkort** på grön panel (`__calendar-card` i `controls-calendar.css`)
- **Guld** (`accent-600`) = trafik för vald resa (`--day--ok`), vit siffra
- **Ljusgrå** = trafik men ej vald resa (`--day--traffic`)
- **Vit + grå text** = ingen trafik (`--day--none`)
- Månadspilar: platta, gröna — inte 3D-grå chevrons

Se `assets/journey-wizard/controls-calendar.css`.

## Utresa / återresa

- Vit turkort med skugga på grön panel (`trips-detail-summary.css`)
- **Välj →**: gul knapp (`controls-calendar.css` `.mrt-journey-wizard__btn-select`)
- Tider med punkt: `10.00 → 10.57`
- Byte: fordon badges med `→` mellan (`mrt-journey-wizard__vehicle-sep`)
- Vald utresa-kort på retursteget: gul kant, mörkgrön bakgrund (`steps-outbound-return.css`)
- Aktivt steg i steglistan: gul bakgrund + fokusring vid `aria-current="step"`
- Expanderat kort: tidslinje med punkt-tider, visa/dölj hållplatser, **Priser**-tabell i kortet

## Sammanfattning (`Din resa`)

- Vit **Utresa** / **Återresa**-kort i lista (`steps-summary.css`)
- **Priser** på grön panel: vit rubrik, vit tabell-yta, markerad tur/retur-rad
- Prisceller med `kr`-suffix
- Gul **Fortsätt till biljetter**-CTA centrerad under tabellen

## Återresa (`valj-aterresa.png`)

- **Vald utresa:** vit kort med gul kant ovanför listan
- Returkort: samma som utresa
- **Byte i detalj:** `%d min byte` från `transfer_wait_minutes` (API)
- **Varning:** `⚠` + fet text vid t.ex. brandrisk (`notice--warning`)
