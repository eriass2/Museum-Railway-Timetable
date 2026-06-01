# Färgpalett – Lennakatten / Museum Railway

Källa i kod: [`assets/mrt-color-tokens.css`](../../assets/mrt-color-tokens.css). Officiell profil: [lennakatten.se/grafisk-profil](https://lennakatten.se/grafisk-profil/) och [`docs/design/reference/`](reference/).

## Typsnitt (Lennakatten profil)

| Token | Värde | Användning |
|-------|--------|------------|
| `--mrt-font-body` | Roboto | Brödtext, tidtabellstider, formulär |
| `--mrt-font-heading` | Open Sans | Rubriker, tidtabellsbanner, route-titel |
| `--mrt-font-weight-heading` | 700 | Standard rubrik |
| `--mrt-font-weight-heading-strong` | 800 | Stora rubriker (wizard, route) |

Källa: [`assets/mrt-typography.css`](../../assets/mrt-typography.css)

## Varumärkesfärger (profil)

| Token | Hex | Text ovanpå |
|-------|-----|-------------|
| `--mrt-color-brand-green` | `#296310` | vit (`--mrt-color-on-green`) |
| `--mrt-color-brand-gold` | `#DDD24C` | **svart** (`--mrt-color-on-accent`) |
| `--mrt-color-brand-olive` | `#807C1C` | vit (`--mrt-color-on-olive`) |

## Grönskala (600 = varumärkesgrön)

| Token | Hex | Användning |
|-------|-----|------------|
| `--mrt-color-green-900` | `#183809` | Djup skugga |
| `--mrt-color-green-800` | `#214F0C` | Mörk panel / gradient |
| `--mrt-color-green-700` | `#245610` | Hero, rubrikfält |
| `--mrt-color-green-600` | `#296310` | Primär varumärkesgrön |
| `--mrt-color-green-500` | `#358015` | Hover på grön yta |
| `--mrt-color-green-400` | `#42961A` | Ljusare markering (t.ex. kalender) |

## Guld / accent (600 = varumärkesguld)

| Token | Hex | Användning |
|-------|-----|------------|
| `--mrt-color-accent-700` | `#C5BD44` | Hover på guld-CTA |
| `--mrt-color-accent-600` | `#DDD24C` | Knappar, aktivt steg, bokningsbar dag |
| `--mrt-color-accent-500` | `#E3DC65` | Highlight på mörk bakgrund |
| `--mrt-color-accent-400` | `#EAE483` | Ljus guldmarkering |

**Profilregel:** svart text på guld — använd `--mrt-color-on-accent` (`#000000`), inte vit.

## Gråskala (Lennakatten-profil)

| Token | Hex |
|-------|-----|
| `--mrt-color-neutral-100` | `#FFFFFF` |
| `--mrt-color-neutral-200` | `#B4B4B4` |
| `--mrt-color-neutral-500` / `600` | `#787878` |
| `--mrt-color-neutral-700` | `#505050` |
| `--mrt-color-neutral-900` | `#000000` |

## Trafikfärger (tidtabellstyper)

Samma som admin `mrt_timetable_type`. GRÖN/GUL följer profilen; RÖD/ORANGE från anslagstavla (ej i grafisk manual).

| Token | Typ | Hex |
|-------|-----|-----|
| `--mrt-color-traffic-green` | green | `#296310` |
| `--mrt-color-traffic-yellow` | yellow | `#DDD24C` |
| `--mrt-color-traffic-red` | red | `#B42318` |
| `--mrt-color-traffic-orange` | orange | `#C9740C` |

## Övrigt semantiskt

| Token | Hex | Användning |
|-------|-----|------------|
| `--mrt-color-olive-600` | `#807C1C` | Trafik utan matchande rutt |
| `--mrt-color-error-bg` | `#FDE8E8` | Felruta |
| `--mrt-color-error-text` | `#7A1212` | Feltext |

## Text på ytor

| Token | Värde | Yta |
|-------|--------|-----|
| `--mrt-color-on-dark` | `#FFFFFF` | Grön/oliv hero |
| `--mrt-color-on-accent` | `#000000` | Guld/accent |
| `--mrt-color-on-green` | `#FFFFFF` | Varumärkesgrön |

## Wizard-alias

`--mrt-wizard-*` pekar på tokens ovan (se `mrt-color-tokens.css`).

## Riktlinjer

1. **Hero och paneler:** `green-700`–`green-800`, vit rubrik.
2. **Primär handling / aktivt steg:** `accent-600` + **svart** text.
3. **Inaktiva steg:** mörkgrön bakgrund, vit text.
4. **Tidtabellstyp:** `--mrt-color-traffic-*` — inte hårdkoda hex i komponenter.
5. Nya färger: lägg i `mrt-color-tokens.css` och denna tabell.

## Relaterat

- [DESIGN_TOKENS.md](../mockups/DESIGN_TOKENS.md)
- [STYLE_GUIDE.md](../STYLE_GUIDE.md)
- [VUE_UI_COMPONENTS.md](../VUE_UI_COMPONENTS.md)
