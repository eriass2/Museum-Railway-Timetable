# Alert class names

Two families exist for historical reasons:

| Class | Where | Component |
|-------|--------|-----------|
| `mrt-ui-alert`, `mrt-ui-alert--error`, … | Public Vue apps | `MrtAlert` |
| `mrt-alert`, `mrt-alert--error`, … | Legacy PHP shortcodes / admin | PHP templates |

New Vue code must use **`MrtAlert`** (`mrt-ui-alert`). Do not add new `mrt-alert` usage in Vue.
