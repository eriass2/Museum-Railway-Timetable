# Färgpalett – Museum Railway (publik UI)

Startpunkt för visuell style guide. Källan i kod: `assets/mrt-color-tokens.css`.

Grönt är varumärkesfärg (Lennakatten). Guld/gul accent används för aktivt steg, CTA och markeringar.

## Primär grön

| Token | Hex | Användning |
|-------|-----|------------|
| `--mrt-color-green-900` | `#0a2e06` | Djup skugga, sällan |
| `--mrt-color-green-800` | `#0f3d08` | Alternativ hero-bakgrund |
| `--mrt-color-green-700` | `#15530b` | **Hero / mörk panel** (vit text) |
| `--mrt-color-green-600` | `#1c650f` | **Primär varumärkesgrön** |
| `--mrt-color-green-500` | `#2a7a1c` | Hover på mörk yta, länkar |
| `--mrt-color-green-400` | `#3d9430` | Kalender “trafik OK” |

Vit text på `--mrt-color-green-700` uppfyller AA för normal text (~7:1+).

## Accent (guld)

| Token | Hex | Användning |
|-------|-----|------------|
| `--mrt-color-accent-700` | `#9a7614` | Tryckt/hover på guld-CTA |
| `--mrt-color-accent-600` | `#c9a02a` | **Knappar, aktivt steg** (varm guld) |
| `--mrt-color-accent-500` | `#dbb83a` | Highlight, “klar” steg i steglistan |
| `--mrt-color-accent-400` | `#e8cc5c` | Ljus guldmarkering |
| `--mrt-color-on-accent` | `#ffffff` | Text på guldfyll (knappar, aktivt steg) |

Använd **`accent-600` + `--mrt-color-on-accent`** på knappar, aktivt steg och vald restyp — inte mörk text på guld.

## Neutrala

| Token | Hex | Användning |
|-------|-----|------------|
| `--mrt-color-neutral-900` | `#141414` | Brödtext på vit yta |
| `--mrt-color-neutral-700` | `#3d3d3d` | Sekundär text |
| `--mrt-color-neutral-600` | `#525252` | **Placeholder** på vit yta |
| `--mrt-color-neutral-500` | `#6b6b6b` | Hjälptext |
| `--mrt-color-neutral-200` | `#e5e5e5` | Inaktiv yta (ersätter grå `#d5d5d5`) |
| `--mrt-color-neutral-100` | `#ffffff` | Formulärfält, kort |

## Semantiska

| Token | Hex | Användning |
|-------|-----|------------|
| `--mrt-color-olive-600` | `#6b6f0f` | Kalender: trafik men ej vald rutt |
| `--mrt-color-orange-600` | `#c9740c` | Varningar, buss |
| `--mrt-color-error-bg` | `#fde8e8` | Felruta bakgrund |
| `--mrt-color-error-text` | `#7a1212` | Feltext |

## Text på mörkgrön

| Token | Värde | Användning |
|-------|--------|------------|
| `--mrt-color-on-dark` | `#ffffff` | Rubriker, etiketter |
| `--mrt-color-on-dark-muted` | `#e4efe2` | Sekundärtext |
| `--mrt-color-on-dark-link` | `#fff6c8` | Länkar |

## Kantlinjer & placeholder

| Token | Värde | Användning |
|-------|--------|------------|
| `--mrt-color-placeholder` | `#525252` | Placeholder i vita fält |
| `--mrt-color-border-on-surface` | `#767676` | Kant runt vita fält |
| `--mrt-color-border-on-dark` | vit ~62 % | Kant på grön yta |

Använd **solid** färger på grön bakgrund — undvik `rgba()` för brödtext och placeholders.

## Fokus (tangentbord)

| Token | Värde |
|-------|--------|
| `--mrt-color-focus-ring` | `#fff8c4` |
| `--mrt-color-focus-offset` | `var(--mrt-color-green-700)` |

## Wizard-alias

Komponenter använder fortfarande `--mrt-wizard-*` i CSS; dessa pekar på tokens ovan (se `mrt-color-tokens.css`).

## Riktlinjer

1. **Hero och paneler:** `green-700` bakgrund, vit rubrik.
2. **Primär handling:** `accent-600` bakgrund, vit text (`on-accent`), fetstil.
3. **Inaktiva steg:** `green-800` på hero (`green-700`), vit text, kant `border-on-dark`.
4. **Aktivt steg i steglista:** `accent-600` + vit text.
5. **Klara steg:** `accent-500` som textfärg på mörk bakgrund.
6. Nya färger: lägg till i `mrt-color-tokens.css` och denna tabell — inte hårdkoda hex i komponenter.

## Relaterat

- [DESIGN_TOKENS.md](../mockups/DESIGN_TOKENS.md) — komponent-specifika tokens (uppdateras mot denna palett)
- [STYLE_GUIDE.md](../STYLE_GUIDE.md) — kodkonventioner
