# Design tokens (public Vue UI)

CSS custom properties used across `ui-components.css` and `journey-wizard/`. Defined in `assets/mrt-color-tokens.css` and wizard overrides in `assets/journey-wizard/base.css`.

## Shared (`--mrt-color-*`, `--mrt-font-*`, `--mrt-spacing-*`)

| Token | Typical use |
|-------|-------------|
| `--mrt-color-green-700` | Headings on light surfaces |
| `--mrt-color-accent-500` / `--mrt-color-accent-700` | CTAs, calendar “ok” days |
| `--mrt-color-on-dark` / `--mrt-color-on-dark-link` | Text on wizard green hero |
| `--mrt-color-neutral-*` | Borders, muted text |
| `--mrt-font-lg` / `--mrt-font-xl` | `MrtHeading` sizes |

## Wizard shell (`--mrt-wizard-*`)

| Token | Typical use |
|-------|-------------|
| `--mrt-wizard-green-dark` | Hero + step panel background |
| `--mrt-wizard-surface` | White cards inside wizard |
| `--mrt-wizard-text` | Body on white surfaces |
| `--mrt-wizard-yellow` | Selected trip border, calendar ok |
| `--mrt-wizard-focus` | Focus rings inside wizard |

Prefer these tokens in new wizard CSS instead of hard-coded hex values.
