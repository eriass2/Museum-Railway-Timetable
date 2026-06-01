# Lennakatten – UI och varumärke (plugin)

Sammanfattning av [grafisk profil v1](reference/lennakatten-grafisk-manual.pdf) ([webb](https://lennakatten.se/grafisk-profil/)) för **publikt plugin-UI** (wizard, månadskalender, tidtabellsöversikt). Kodstandarder: [STYLE_GUIDE.md](../STYLE_GUIDE.md). Färgtokens: [COLOR_PALETTE.md](COLOR_PALETTE.md).

## Scope

| Följer profilen | Följer inte profilen |
|-----------------|----------------------|
| Shortcodes och Vue-vyer mot resenärer | ULJF/ULJH/ULJm/ULJC, tjänstetidtabeller (historisk layout) |
| Admin som WP-skärmdel (neutralt skal) | Stationsskyltar, fordonsskyltar, personalens historiska dokument |
| Tryck/PDF som ska kännas som nutida Lennakatten-kommunikation | Anslag som medvetet ska se ”på plats” historiska ut |

Profilen ska **inte** användas där den försvagar det historiska intrycket på järnvägen (manual s. 2). Pluginets resenärs-UI räknas som nutida kommunikation.

## Färger

- **Grön** (`--mrt-color-brand-green` / `green-600`) ska vara den dominerande ytfärgen (hero, paneler, stegindikator).
- **Guld** (`accent-600`) sparsamt — CTA, aktivt steg, vald dag/restyp; **svart text** (`--mrt-color-on-accent`).
- **Oliv** (`--mrt-color-brand-olive`) — länkar på ljus bakgrund; **placera inte intill grön** (endast mot vitt eller ljusgrått enligt profil).
- **Trafikfärger** (`--mrt-color-traffic-*`) — funktionella, klara nyanser; GRÖN/GUL från profil, RÖD/ORANGE från anslagstavla. Se [COLOR_PALETTE.md](COLOR_PALETTE.md).
- **Gråskala** — använd endast profilens neutrala tokens; undvik `#787878` som text (uppfyller inte WCAG AA som textfärg).

## Typografi

| Roll | Typsnitt | Token |
|------|----------|--------|
| Rubriker | Open Sans Bold (700–800) | `--mrt-font-heading` |
| Brödtext, tider, formulär | Roboto Regular | `--mrt-font-body` |

Källa: [`assets/mrt-typography.css`](../../assets/mrt-typography.css).

- **Charter** i profilen gäller långa löptexter (t.ex. tidning); plugin-UI behöver normalt inte Charter.
- Skapa hierarki med **storlek**, inte extra teckenvikter utöver angivna.
- **Vänsterställ** text; undvik CAPS utom t.ex. tidtabellshuvuden.
- **Fetstil** för betoning i brödtext, inte skugga/kontur på text över bild.

## Formspråk (publikt UI)

Profilen: platt design, skarpa hörn, solid färg, inga gradienter.

| Regel | Riktlinje i plugin |
|-------|-------------------|
| Hörn | `border-radius: 0` i nya publika komponenter; använd `--mrt-radius-sm` (4px) endast där befintlig komponent kräver det |
| Gradienter | Undvik i nya vyer; legacy gradient i `.mrt-ov-*`-banner dokumenterad avvikelse |
| Skuggor | Undvik; undantag om element måste lyftas från bakgrund |
| Kantlinjer | Sparsamt; separation via färg/yta |
| Ikoner | Enfärgade, enkla 2D-SVG; `currentColor` (t.ex. `WizardTripTypeIcon.vue`) |

## Länkar och kontrast

- **Ljus bakgrund:** länkfärg ≈ oliv (`--mrt-color-brand-olive` / `#807C1C`).
- **Mörk grön bakgrund:** ljus accent-länk (`--mrt-color-on-dark-link`) — läsbarhet på hero; inte samma som profilens ”mörkgul på hemsida” men OK på grön yta.
- **Kontrast:** minst WCAG 2.1 nivå AA (4,5:1) för text.

## Texter och branding

- Skriv **Lennakatten** (endast första bokstaven stor), inte LK, LennaKatten eller LENNAKATTEN i löptext.
- Engelska offentligt namn: **The Lennakatten Heritage Railway**.
- ULJ, Upsala–Lenna Jernväg m.m. endast formellt/internt — inte i resenärs-UI.
- Översättningar: text domain `museum-railway-timetable`.

## Layoutreferens (tryck)

Word-mallar i [`reference/`](reference/README.md):

- **Anslag 1** — allmän information, grönt sidhuvud.
- **Anslag 2** — avdelning informerar, gult sidhuvud.

Använd som referens för PDF/utskrift, inte som pixelperfekt skärm-layout.

## Kända avsteg (legacy)

Vid ny CSS: räta hörn och inga gradienter om möjligt.

| Avvikelse | Var |
|-----------|-----|
| `border-radius: 4px` (`--mrt-radius-sm`) | Delar av `assets/frontend/`, admin |
| Gradient på banner | `frontend/vue/src/styles/timetable-overview.css` |
| Rundade detaljer (t.ex. 1–2px) | Månadskalender, admin |

**Klart (räta hörn):** resesökaren — `journey-wizard/sharp-corners.css`, `price-table.css` (prislistor). Laddningsspinner (`.mrt-empty--loading::before`) behåller cirkel.

**Nästa vy:** månadskalender → tidtabellsöversikt → övriga `assets/frontend/`.

## Relaterat

- [COLOR_PALETTE.md](COLOR_PALETTE.md) — hex och tokens
- [reference/README.md](reference/README.md) — nedladdade profilfiler
- [VUE_UI_COMPONENTS.md](../VUE_UI_COMPONENTS.md) — komponentlista
- [STYLE_GUIDE.md](../STYLE_GUIDE.md) §3 — CSS-filvägar och kodregler
