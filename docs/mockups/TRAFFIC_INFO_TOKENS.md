# Trafikinfo — design tokens (UL 1:1)

**Status:** CSS-fil skapad (`assets/mrt-traffic-info-tokens.css`); enqueue via `MRT_enqueue_traffic_info_tokens()` (Vue + shortcode). E2E: `e2e/serve.mjs` laddar samma fil för statiska Playwright-tester.
**Plan:** [TRAFFIC_INFO_UL_PLAN.md](../TRAFFIC_INFO_UL_PLAN.md)  
**Referens:** UL «Aktuellt trafikläge» / «Planerade avvikelser» (skärmdumpar i `mockups/`)

Tokens använder prefix `--mrt-tf-*` (traffic feed). **Inte** wizard-tokens (`--mrt-wizard-*`).

---

## Färger

| Token | Värde (UL-approx) | Användning |
|-------|-------------------|------------|
| `--mrt-tf-section-bg` | `#e8e8e8` | Sektionsrubrik (Aktuellt / Planerade) |
| `--mrt-tf-section-text` | `#333333` | Rubriktext |
| `--mrt-tf-surface` | `#ffffff` | Kort, alert-lista |
| `--mrt-tf-border` | `#dddddd` | Horisontella linjer, kortkant |
| `--mrt-tf-category-active` | `#f5e642` | Expanderad / aktiv kategori-rad |
| `--mrt-tf-category-hover` | `#f0f0f0` | Hover på kategori (valfritt) |
| `--mrt-tf-badge-bg` | `#e8e8e8` | Pill bakgrund (count badges) |
| `--mrt-tf-info` | `#c9a227` | Info-ikon (gul cirkel) |
| `--mrt-tf-info-on` | `#ffffff` | «i» på info-cirkel |
| `--mrt-tf-warning` | `#c44b33` | Varning-triangel |
| `--mrt-tf-alert-bg` | `#e8a090` | Korall alert-ruta |
| `--mrt-tf-alert-text` | `#1a1a1a` | Text i alert-ruta |
| `--mrt-tf-line-badge-bg` | `#222222` | Tågnummer-badge |
| `--mrt-tf-line-badge-text` | `#ffffff` | Tågnummer-text |
| `--mrt-tf-validity-text` | `#666666` | Giltighetstext under alert |
| `--mrt-tf-muted` | `#505050` | Sekundär text |

Brand-grön (`--mrt-color-brand-green`) används **inte** som primär accent i UL-layouten — endast om produkt explicit vill.

### Lennakatten-profil (Nivå A)

När `MRT_LENNAKATTEN_BRAND` eller filter `mrt_use_lennakatten_traffic_info_tokens` är aktivt laddas `assets/brand/lennakatten-traffic-info-tokens.css` efter bas-tokens.

| Token | Lennakatten | UL (neutral) |
|-------|-------------|--------------|
| `--mrt-tf-section-bg` | `--mrt-color-green-700` | `#e8e8e8` |
| `--mrt-tf-section-text` | vit | `#333333` |
| `--mrt-tf-category-active` | `--mrt-color-accent-600` (svart text) | `#f5e642` |
| `--mrt-tf-alert-bg` | vit ruta | korall `#e8a090` |
| `--mrt-tf-line-badge-bg` | `--mrt-color-green-800` | `#222222` |
| `--mrt-tf-feed-shadow` | `none` | skugga |
| `--mrt-tf-section-text-transform` | `none` | `uppercase` |
| `--mrt-tf-radius-alert` | `0` | `0.25rem` |

Layout-tokens (`--mrt-tf-feed-shadow`, `--mrt-tf-section-text-transform`, …) styrs i bas-filen; Lennakatten-pack overridar värdena.

---

## Typografi

| Token | Värde | Användning |
|-------|-------|------------|
| `--mrt-tf-font-size-body` | `0.9375rem` (15px) | Kategori, alert-text |
| `--mrt-tf-font-size-small` | `0.8125rem` (13px) | Giltighet, detalj |
| `--mrt-tf-font-size-section` | `0.875rem` (14px) | Sektionsrubrik |
| `--mrt-tf-font-weight-bold` | `600` | Rubriker, alert |
| `--mrt-tf-font-weight-normal` | `400` | Datum, giltighet |

Font stack: samma som publik app (system / tema), inte UL:s exakta font.

---

## Spacing & layout

| Token | Värde | Användning |
|-------|-------|------------|
| `--mrt-tf-panel-max-width` | `36rem` | Max bredd feed (nuvarande kort) |
| `--mrt-tf-pad-row` | `0.65rem 0.9rem` | Kategori-rad, alert-kort |
| `--mrt-tf-pad-section` | `0.55rem 0.9rem 0.35rem` | Sektionsrubrik |
| `--mrt-tf-gap-badge` | `0.35rem` | Mellan count-badges |
| `--mrt-tf-gap-inline` | `0.5rem` | Ikon + text |
| `--mrt-tf-radius-alert` | `0.25rem` | Korall-ruta |
| `--mrt-tf-radius-badge` | `999px` | Count pill |
| `--mrt-tf-line-badge-min-width` | `1.75rem` | Linjenummer-badge |

---

## Ikoner

| Komponent | Storlek | Färg |
|-----------|---------|------|
| Sektion (klocka/kalender) | `1.25rem` | `currentColor` → section text |
| Kategori (tåg/buss) | `1.25rem` | `currentColor` |
| Count badge ikon | `0.875rem` | `--mrt-tf-info` / `--mrt-tf-warning` |
| Giltighet klocka | `0.875rem` | `--mrt-tf-validity-text` |

SVG med `currentColor`; inga hårdkodade `fill` i path.

---

## BEM-prefix

Alla klasser: `mrt-tf-*` (traffic feed). Exempel:

- `mrt-tf-panel`, `mrt-tf-panel__header`
- `mrt-tf-category`, `mrt-tf-category--active`
- `mrt-tf-count-badge`, `mrt-tf-count-badge--info`, `mrt-tf-count-badge--warning`
- `mrt-tf-alert`, `mrt-tf-alert__summary`, `mrt-tf-alert__validity`
- `mrt-tf-line-badge`

Vue och PHP noscript delar samma klassnamn.

---

## Visuell referens (TF-0.4)

Playwright-baseline (Linux CI) för UL-layout — jämför hierarki, badges och färger mot UL-skärmdump när `mockups/ul-trafikinfo-reference.png` finns (TF-0.3).

| Vy | Snapshot | Viewport |
|----|----------|----------|
| Desktop (alla kategorier expanderade) | `frontend/vue/e2e/traffic-notices-ul-layout.spec.ts-snapshots/traffic-notices-ul-desktop-linux.png` | 900×900 |
| Mobil | `frontend/vue/e2e/traffic-notices-ul-layout.spec.ts-snapshots/traffic-notices-ul-mobile-linux.png` | 390×820 |

**Kontrollpunkter vid side-by-side:**

1. Två sektioner med ikon i rubrik (klocka / kalender).
2. Kategori-rader med count-badges före expand; gul aktiv rad.
3. Svart line-badge + korall summary-ruta + giltighet under.
4. Ingen grön vänsterkant eller flat «Mer information»-lista.

Uppdatera snapshots efter avsiktliga layoutändringar:

```bash
docker run --rm -v "$PWD:/app" -w /app/frontend/vue mcr.microsoft.com/playwright:v1.60.0-jammy \
  sh -c "npm run build && npm run e2e -- traffic-notices-ul-layout.spec.ts --update-snapshots"
```

---

## Godkännande

| Steg | Ansvar |
|------|--------|
| Token-värden vs UL-skärmdump | Produkt |
| Implementering i CSS-fil | Frontend |
| Inga hex utanför denna spec + CSS-fil | Alla |
