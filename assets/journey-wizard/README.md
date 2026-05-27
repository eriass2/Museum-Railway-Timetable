# Journey wizard styles

**Vue mode** (`MRT_VUE_FRONTEND`): styles ship via Vite (`assets/journey-wizard/*.css` imported from `frontend/vue`). Templates use `.mrt-journey-wizard__*` and `data-wizard-step` on panels. The wizard root exposes `data-step` for hero layout (route step uses a shorter hero).

**Legacy jQuery mode** (when Vue is off): modular JS below is loaded from `inc/assets/frontend.php`.

| Module | Responsibility |
|--------|----------------|
| `namespace.js` | `window.MRTJourneyWizard` |
| `constants.js` | Price matrix keys |
| `render.js` | `JW.render` helpers |
| `connection.js` | Departure/arrival time helpers |
| `context.js` | Route context line in step headers |
| `prices.js` | Price table HTML (summary) |
| `vehicle.js` | Train type icons on cards |
| `calendar.js` | Step 2 calendar grid |
| `trip-card.js` | Outbound/return trip cards |
| `connection-detail.js` | Expandable stop timeline |
| `summary.js` | Summary step |
| `runtime.js` | Step nav, panels, AJAX loaders |
| `events.js` | Click/change handlers |
| `bootstrap.js` | Init on `DOMContentLoaded` |

Entry handle for WordPress: `mrt-journey-wizard` → `bootstrap.js`. Config: `mrtJourneyWizard` (localized).

Regenerate Vue bundle after TS/CSS changes: `composer vue:build` from repo root.

CSS variables still use the `--mrt-jw-*` prefix in `base.css` (historical name); class selectors are `.mrt-journey-wizard__*` only.
