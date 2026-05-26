# Journey wizard (frontend modules)

Modular JS for `[museum_journey_wizard]`. Loaded in order via `MRT_register_journey_wizard_script_modules()` in `inc/assets/frontend.php`.

| Module | Responsibility |
|--------|----------------|
| `namespace.js` | `window.MRTJourneyWizard` |
| `constants.js` | Price matrix keys |
| `render.js` | `JW.render` helpers + `mrt-jw-*` class map |
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

**UI components (`mrt-jw-*`)**

Shared building blocks in `components.css` + `render.js` (`JW.render`):

| # | Component | Classes |
|---|-----------|---------|
| 1 | Trip head | `mrt-jw-trip-head`, `__copy`, `__side` |
| 2 | Prices | `mrt-jw-prices`, `__table`, `__row--active` |
| 3 | Notice | `mrt-jw-notice`, `__dot`, `__warn` |
| 4 | Step head | `mrt-jw-step-head`, `__context` |
| 5 | Timeline | `mrt-jw-timeline`, `__row`, `__transfer` |
| 6 | Calendar | `mrt-jw-calendar__*`, `mrt-jw-btn--day` |
| 7 | Panel | `mrt-jw-panel` (alias: `mrt-journey-wizard__panel`) |
| 8 | Typography | `mrt-jw-typo--time`, `--route`, `--step-title`, … |
| 9 | Expand | `mrt-jw-expand`, `mrt-jw-btn--expand` |

Legacy `mrt-journey-wizard__*` classes remain on markup for compatibility.

**Development debug pages** (one component per page): Railway Timetable → Component demo page → list after **Set up development menu**. Shortcode attribute `debug="date|outbound|return|summary"` loads fixture trips without AJAX (see `debug.js`, `debug-fixtures.php`).
