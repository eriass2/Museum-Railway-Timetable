# Journey wizard (frontend modules)

Modular JS for `[museum_journey_wizard]`. Loaded in order via `MRT_register_journey_wizard_script_modules()` in `inc/assets/frontend.php`.

| Module | Responsibility |
|--------|----------------|
| `namespace.js` | `window.MRTJourneyWizard` |
| `constants.js` | Price matrix keys |
| `connection.js` | Departure/arrival time helpers |
| `context.js` | Route context line in step headers |
| `prices.js` | Price table HTML (summary) |
| `vehicle.js` | Train type icons on cards |
| `calendar.js` | Step 2 calendar grid |
| `trip-card.js` | Outbound/return trip cards (mockup: valj-utresa) |
| `connection-detail.js` | Expandable stop timeline |
| `summary.js` | Summary step |
| `runtime.js` | Step nav, panels, AJAX loaders |
| `events.js` | Click/change handlers |
| `bootstrap.js` | Init on `DOMContentLoaded` |

Entry handle for WordPress: `mrt-journey-wizard` → `bootstrap.js`. Config: `mrtJourneyWizard` (localized).

Regenerate after string changes: not required (strings come from PHP). For structural work, smoke-test wizard in Docker.

**Development debug pages** (one component per page): Railway Timetable → Component demo page → list after **Set up development menu**. Shortcode attribute `debug="date|outbound|return|summary"` loads fixture trips without AJAX (see `debug.js`, `debug-fixtures.php`).
