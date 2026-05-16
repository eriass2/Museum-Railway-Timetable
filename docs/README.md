# Dokumentation

**Börja här:** [DEVELOPER.md](DEVELOPER.md) · **Projektstatus:** [PROJECT_HEALTH.md](PROJECT_HEALTH.md) · **Bidrag:** [../CONTRIBUTING.md](../CONTRIBUTING.md)

```
docs/
├── README.md, DEVELOPER.md, PROJECT_HEALTH.md   ← ingång
├── guides/          ← verktyg, deploy, admin
├── domain/          ← arkitektur, datamodell
├── product/         ← shortcodes, reseflöde
├── design/          ← style guide, UI
├── accessibility/   ← WCAG, release-rökning
├── mockups/         ← PNG-referenser
└── archive/         ← avslutade planer
```

---

## guides/

| Fil | Innehåll |
|-----|----------|
| [PHP_INSTALL_WINDOWS.md](guides/PHP_INSTALL_WINDOWS.md) | PHP och Composer på Windows |
| [VALIDATION.md](guides/VALIDATION.md) | Checklista före deploy |
| [COMPILE_TRANSLATIONS.md](guides/COMPILE_TRANSLATIONS.md) | Kompilera `.po` → `.mo` |
| [ADMIN_WORKFLOW.md](guides/ADMIN_WORKFLOW.md) | Admin-arbetsflöde |
| [FUTURE_WORK.md](guides/FUTURE_WORK.md) | Rekommendationer framåt |

---

## domain/

| Fil | Innehåll |
|-----|----------|
| [ARCHITECTURE.md](domain/ARCHITECTURE.md) | Lager, testning, filstruktur `inc/` |
| [DATA_MODEL.md](domain/DATA_MODEL.md) | Post types, relationer, tabeller |
| [TIMETABLE_STRUCTURE.md](domain/TIMETABLE_STRUCTURE.md) | Tidtabellstruktur |

---

## product/

| Fil | Innehåll |
|-----|----------|
| [JOURNEY.md](product/JOURNEY.md) | Wizard + planner (levererat) |
| [SHORTCODES_OVERVIEW.md](product/SHORTCODES_OVERVIEW.md) | Alla shortcodes och parametrar |

---

## design/

| Fil | Innehåll |
|-----|----------|
| [STYLE_GUIDE.md](design/STYLE_GUIDE.md) | PHP, CSS, JS, clean code |
| [COMPONENT_LIBRARY.md](design/COMPONENT_LIBRARY.md) | UI-komponenter (`.mrt-*`) |
| [DESIGN_SYSTEM.md](design/DESIGN_SYSTEM.md) | Tokens, färger, spacing |
| [assets/CSS_STRUCTURE.md](../assets/CSS_STRUCTURE.md) | CSS-moduler i `assets/` |

---

## accessibility/

| Fil | Innehåll |
|-----|----------|
| [WCAG_JOURNEY_WIZARD.md](accessibility/WCAG_JOURNEY_WIZARD.md) | Tillgänglighet – wizard |
| [WCAG_PUBLIC_SHORTCODES.md](accessibility/WCAG_PUBLIC_SHORTCODES.md) | Planner, månad, översikt |
| [RELEASE_A11Y_SMOKE.md](accessibility/RELEASE_A11Y_SMOKE.md) | Manuell a11y-rökning |

---

## archive/

Avslutade planer och engångsgranskningar: [archive/README.md](archive/README.md).
