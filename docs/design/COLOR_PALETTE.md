# Färgpalett – Museum Railway (neutral default + valfri Lennakatten-profil)

**Standard (alla operatörer):** [`assets/mrt-color-tokens.css`](../../assets/mrt-color-tokens.css) — neutral blå/brass-profil.

**Lennakatten (valfritt):** [`assets/brand/lennakatten-color-tokens.css`](../../assets/brand/lennakatten-color-tokens.css) — enqueues när `MRT_LENNAKATTEN_BRAND` eller filter `mrt_use_lennakatten_brand_tokens` är aktivt. Profil: [lennakatten.se/grafisk-profil](https://lennakatten.se/grafisk-profil/).

Override i child theme: sätt `--mrt-*` på `:root`. Se [OPERATOR_ONBOARDING.md](../OPERATOR_ONBOARDING.md).

## Typsnitt

| Token | Neutral default | Lennakatten (brand pack) |
|-------|-----------------|--------------------------|
| `--mrt-font-body` | system-ui stack | Roboto |
| `--mrt-font-heading` | system-ui stack | Open Sans |

Källa neutral: [`assets/mrt-typography.css`](../../assets/mrt-typography.css). Lennakatten: [`assets/brand/lennakatten-typography.css`](../../assets/brand/lennakatten-typography.css).

## Varumärkesfärger (Lennakatten-profil)

| Token | Hex | Text ovanpå |
|-------|-----|-------------|
| `--mrt-color-brand-green` | `#296310` | vit (`--mrt-color-on-green`) |
| `--mrt-color-brand-gold` | `#DDD24C` | **svart** (`--mrt-color-on-accent`) |
| `--mrt-color-brand-olive` | `#807C1C` | vit (`--mrt-color-on-olive`) |

Neutral default använder `#1e4d6b` / `#c9a227` / `#4a6670` för motsvarande roller — se `mrt-color-tokens.css`.

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

- [BRAND_UI.md](BRAND_UI.md) — scope, formspråk, länkar, branding i texter
- [DESIGN_TOKENS.md](../mockups/DESIGN_TOKENS.md)
- [STYLE_GUIDE.md](../STYLE_GUIDE.md)
- [VUE_UI_COMPONENTS.md](../VUE_UI_COMPONENTS.md)
