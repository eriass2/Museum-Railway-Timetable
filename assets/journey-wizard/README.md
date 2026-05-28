# Journey wizard styles

Publik reseplanerare använder **Vue** (`frontend/vue`) och Vite-bundlad CSS från denna mapp.

- Shell: `.mrt-journey-wizard__hero`, `__panels` (Vue `JourneyWizardApp`)
- Steg: `MrtStepPanel` (`mrt-step-panel`, `data-wizard-step`)
- Delad UI-CSS: `assets/frontend/ui-components.css`
- Design tokens: se `docs/DESIGN_TOKENS.md`
- Inline tidtabell i wizard borttagen; route-steg länkar till `timetablePageUrl`

Bygg om efter CSS/TS-ändringar: `composer vue:build` från repo-roten.

E2E (statisk mount): `cd frontend/vue && npm run build && npm run e2e`.
