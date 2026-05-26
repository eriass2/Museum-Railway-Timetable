# Mockups och visuell referens

PNG-mockups för journey wizard (V1–V5) finns **inte** i repot ännu. Tills de läggs hit gäller följande källor.

## Primära källor

| Källa | Plats | Användning |
|-------|--------|------------|
| Referens-PDF:er | `testdata/reference-pdfs/` | Trafikdagar, tågtyper, tider, specialnoteringar |
| Implementerad wizard-CSS | `assets/journey-wizard/` | Grön hero, gul accent, steg 1–5, kalenderfärger |
| Design tokens (extraherade) | [DESIGN_TOKENS.md](DESIGN_TOKENS.md) | Färger/spacing för finpolish |

## Lägga till mockups

1. Exportera PNG från design (namn t.ex. `journey-v1-route.png` … `journey-v5-summary.png`).
2. Lägg filerna i denna mapp (`docs/mockups/`).
3. Uppdatera `assets/journey-wizard/*.css` mot mockup – behåll befintliga CSS-variabler där möjligt.
4. Notera avvikelser i PR/commit eller i DESIGN_TOKENS.md.

## Relaterat

- [REBUILD_SKETCH.md](../REBUILD_SKETCH.md) – MVP och wizard som primärt flöde
- [ACCESSIBILITY.md](../ACCESSIBILITY.md) – WCAG-rökning
- [SHORTCODES.md](../SHORTCODES.md) – `[museum_journey_wizard]`
