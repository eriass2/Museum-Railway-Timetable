# Journey wizard CSS (Vue)

Modular styles for `[museum_journey_wizard]`, imported from `JourneyWizardApp.vue` via `../styles/journey-wizard.css`.

**Konventioner:** [docs/STYLE_GUIDE.md](../../../docs/STYLE_GUIDE.md) §3 CSS, [docs/VUE_UI_COMPONENTS.md](../../../docs/VUE_UI_COMPONENTS.md), [docs/CSS_ENCAPSULATION_PLAN.md](../../../docs/CSS_ENCAPSULATION_PLAN.md).

> **Migrering:** Steg- och formulärstilar flyttas till scoped CSS i `.vue`-komponenter. Modulerna nedan är **app-shell** (layout, embedded, responsive) — inte nya komponentregler här.

| Modul | Innehåll |
|-------|----------|
| `base.css` | Shell, hero, embedded, fokus |
| `hero-layout.css` | Desktop fullbredd, valfri hero-bakgrundsbild (J10) |
| `wizard-main-card.css` | Grön huvudpanel (J19) |
| `wizard-shell.css` | Wizard-specifika overrides på delade primitiver |
| `controls-form.css` | **Endast** embedded-overrides (söksteg → scoped SFC) |
| `controls-calendar.css` | Datumsteg, kalender |
| `steps-outbound-return.css` | Utresa/återresa |
| `steps-summary.css` | Sammanfattning |
| `responsive.css` | Mobile-first breakpoints |

Tokens: `assets/mrt-color-tokens.css` (via `mrt-public.css`).
