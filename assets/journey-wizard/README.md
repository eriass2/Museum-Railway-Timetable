# Journey wizard styles

Publik reseplanerare använder **Vue** (`frontend/vue`) och Vite-bundlad CSS från denna mapp.

- Klasser: `.mrt-journey-wizard__*`
- Design tokens: `--mrt-wizard-*` (definieras i `base.css`)
- Paneler: `data-wizard-step`; rot: `data-step` för hero-layout

Bygg om efter CSS/TS-ändringar: `composer vue:build` från repo-roten.

E2E (statisk mount): `cd frontend/vue && npm run build && npm run e2e`.
