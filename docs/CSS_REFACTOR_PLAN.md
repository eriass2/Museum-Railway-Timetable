# Åtgärdsplan: CSS-refaktor + återanvändbara UI-komponenter

**Status:** Klar (2026-06-12) — uppföljning: [CSS_RESPONSIBILITY_PLAN.md](CSS_RESPONSIBILITY_PLAN.md) (klar), [CSS_FOLLOWUP_PLAN.md](CSS_FOLLOWUP_PLAN.md)  
**Relaterat:** [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md), [design/BRAND_UI.md](design/BRAND_UI.md), [STYLE_GUIDE.md](STYLE_GUIDE.md) §3

---

## Syfte

Encapsulation är **klar** — komponent-CSS ligger i Vue-chunks, inte i global barrel. Nästa steg är **underhållbarhet och återanvändning**:

1. Dela upp **god-filer** (wizard shell, overview shell, admin-shell) till **små komponenter** med scoped CSS.
2. Extrahera **nya delade `Mrt*`-primitiver** där samma mönster upprepas (spacing, focus, loading, layout).
3. Minska beroendet på **global utilities** (`mrt-mt-sm`, …) i Vue — ersätt med komponenter eller scoped spacing.
4. Långsiktigt: **admin använder samma publika primitiver** (`MrtButton`, `MrtAlert`, `MrtAsyncState`) där det passar; admin-specifikt kvar i `Admin*`.

**Ledprincip:** *CSS följer komponenten.* Hellre ett nytt `MrtStack` än en ny global utility-klass.

---

## Nuläge (efter refaktor R0–R5)

| Område | Status |
|--------|--------|
| Publika `Mrt*` (~40 st) | Scoped SFC, exporterade från `@/components/ui` |
| Layout | `MrtWizardShell` → `MrtWizardHero`, `MrtWizardShellSurfaces`, `MrtWizardMainCard` |
| Overview | CSS i `MrtOverview*`; shell ~60 rader tokens |
| Admin | `admin-shell.css` **7 rader**; feature-CSS i SFC; `Mrt*` med `context="admin"` |
| App-roots | `JourneyWizardApp.vue` utan `<style>` — wiring only |
| Global publik CSS | Tokens, utilities, PHP legacy (`components-base`, `tables`), `public-layout` |
| Legacy barrel | `assets/frontend/ui/` och `ui-components.css` **borttagna** (validate guardrail) |
| Guardrails | validate + `verify-build.mjs` (admin-markers, style line budget) + layout-E2E |

### Kvarvarande skuld (låg prioritet)

| Område | Not |
|--------|-----|
| Global utilities | ~58 klasser kvar för PHP/demo; Vue använder `MrtStack` |
| `WizardSummaryStep.vue` | 184 rader CSS — dokumenterat undantag (print) |
| Nära style-budget | `MrtOverviewRailGroupGridRow`, `MrtMonthDayCell`, `WizardFeedbackWidget`, `AdminApp` (132–152 rader) |
| Varumärke | Admin `border-radius: 3px`; legacy token-alias i `tokens.css` |
| E2E | Full publik suite + admin smoke ej körd i denna omgång |

---

## Målbild — komponentcentrerad CSS

```
assets/mrt-color-tokens.css          ← tokens
assets/frontend/public-layout.css    ← WP/tema
assets/frontend/utilities.css        ← PHP/demo (Vue → MrtStack)

frontend/vue/src/components/ui/      ← delade Mrt* (+ interna hjälp-SFC t.ex. MrtPriceTableMatrix)
frontend/vue/src/components/layout/  ← MrtPublicAppShell, MrtWizardShell*
frontend/vue/src/components/overview/ ← domän med scoped CSS per komponent
frontend/vue/src/wizard/components/  ← tunna wrappers
frontend/vue/src/admin/components/ui/ ← Admin*; Mrt* där möjligt

apps/*.vue                           ← tunn glue (ingen stor CSS i app-root)
```

**Regler (oförändrade från encapsulation + tillägg):**

1. Max **~50 rader** scoped CSS per komponent/metod — dela upp vid överskridande.
2. **Nya återanvändbara mönster** → ny `Mrt*` i `components/ui/` (eller `layout/` för shell).
3. **App-specifikt som inte ska återanvändas** → wizard/overview/admin feature-mapp, inte global CSS.
4. **`context`-prop** när samma komponent ska fungera i admin och publikt.
5. Uppdatera [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md) vid varje ny export.

---

## Komponentstrategi — vad ska vara `Mrt*`?

### Redan bra (fortsätt använda)

Knappar, alerts, async, formulär, steg, kalender, resor, priser — se [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md).

### Nya delade primitiver (föreslagna)

Prioritet = antal upprepningar × risk vid copy-paste.

| Komponent | Ersätter | Användning |
|-----------|----------|------------|
| **`MrtStack`** | `mrt-mt-sm`, `mrt-mt-lg`, `mrt-mb-*`, vertikal spacing i templates | Props: `gap`, `marginTop`; slot för barn. Publik + admin. |
| **`MrtVisuallyHidden`** | Duplicerad `.mrt-sr-only` / clip-css | A11y-etiketter; ersätt inline sr-only i wizard shell. |
| **`MrtInlineSpinner`** | `.mrt-empty--loading::before`, admin async spinner | Inline laddning utan full `MrtAsyncState`-block. |
| **`MrtFocusRing`** (eller mixin i befintliga) | Utspridd `:focus-visible` i wizard/overview | Wrapper eller dokumenterad scoped mixin importerad i SFC — **inte** global CSS. |
| **`MrtWizardShell`** | Hero + panels + embedded/normal i `JourneyWizardApp` | Layout-komponent i `components/layout/`; appen bara wiring + steg. |
| **`MrtWizardMainCard`** | Grön vit kort-yta i wizard | Beta + step nav + step slot; används bara av wizard men isolerar ~80 rader CSS. |
| **`MrtOverviewBanner`** | `.mrt-ov-banner` + typ-varianter i shell | Props: `timetableType`, `label`. |
| **`MrtOverviewPrintKey`** | CSS i shell (logik finns redan som komponent) | Flytta `.mrt-ov-print-key*` CSS till befintlig `MrtOverviewPrintKey.vue`. |

### Domänkomponenter (overview) — CSS ner i barn

Shell ska **orkestrera**, inte styla grid:

| Befintlig komponent | CSS att flytta hit från shell |
|---------------------|------------------------------|
| `MrtOverviewRailGroupGrid.vue` | Grid tracks, scroll, row head |
| `MrtOverviewRailGroupGridRow.vue` | Station/time/transfer/bus celler |
| `MrtOverviewRailGroupGridHead.vue` | Kolumnhuvuden, ikoner |
| `MrtOverviewBranchGroup.vue` | Branch-tabell/kort |
| `MrtOverviewPrintKey.vue` | Print key tabell |

Mål: `MrtTimetableOverviewShell.vue` **< 80 rader CSS** (tokens + layout-wrapper).

### Admin — återanvänd publikt där det går

| Idag | Mål |
|------|-----|
| `AdminLoadState` | Bort — endast `MrtAsyncState context="admin"` |
| `AdminStatusMessage` | Bort — endast `MrtAlert context="admin"` |
| `AdminPanel` | Behåll; ev. dela visuella tokens med `MrtSurfaceCard` (border/radius policy) |
| Spinner i `admin-shell.css` | `MrtAsyncState` / `MrtInlineSpinner` |
| Feature-CSS i `admin-shell.css` | Flytta till respektive `Admin*.vue` scoped |

**Ny admin-primitiv (om behov):** `AdminPageHeader` (h1 + lead) — samma mönster på många sidor.

---

## Faser och PR-backlog

Varje rad = **en reviewbar PR**. Kör `npm run build`, relevant E2E, uppdatera docs.

### Fas R0 — Spelregler (docs, liten PR) ✅

| # | Åtgärd |
|---|--------|
| R0.1 | Länka denna plan från `CSS_ENCAPSULATION_PLAN.md`, `VUE_UI_COMPONENTS.md`, `STYLE_GUIDE.md` |
| R0.2 | Dokumentera **tillåtna globala lager**: tokens \| layout \| utilities (legacy) \| PHP alerts |
| R0.3 | Checklista “ny `Mrt*`”: props, scoped CSS, tokens, Vitest, VUE_UI_COMPONENTS |

**DoD:** team har gemensam regel — uppfyllt via länkar i STYLE_GUIDE och VUE_UI_COMPONENTS.

---

### Fas R1 — Delade layout-komponenter (wizard)

| PR | Innehåll | Nya/ändrade komponenter |
|----|----------|-------------------------|
| **R1.1** | Extrahera `MrtWizardMainCard` | `components/layout/MrtWizardMainCard.vue` |
| **R1.2** | Extrahera hero + embedded/normal | `components/layout/MrtWizardShell.vue` |
| **R1.3** | Flytta responsive trip-card `:deep()` till `MrtTripCard` / `MrtTripList` | Mindre `JourneyWizardApp.vue` |
| **R1.4** | `MrtVisuallyHidden` + ersätt sr-only i wizard | `components/ui/MrtVisuallyHidden.vue` |

**DoD:** `JourneyWizardApp.vue` **< 150 rader CSS**; wizard layout-E2E gröna.

---

### Fas R2 — Overview som komponentträd

| PR | Innehåll |
|----|----------|
| **R2.1** | Grid + row CSS → `MrtOverviewRailGroupGrid*` |
| **R2.2** | Branch + print key CSS → `MrtOverviewBranchGroup`, `MrtOverviewPrintKey` |
| **R2.3** | `MrtOverviewBanner` + typ-tokens som props |
| **R2.4** | Shell endast wrapper + `:root`-liknande `--mrt-ov-*` på root element |

**DoD:** `MrtTimetableOverviewShell.vue` **< 80 rader CSS**; `overview-mount` + `overview-wp` E2E gröna.

---

### Fas R3 — Spacing & loading (`MrtStack`, spinner)

| PR | Innehåll |
|----|----------|
| **R3.1** | Introducera `MrtStack` (gap/margin props) |
| **R3.2** | Ersätt `mrt-mt-*` i publika SFC (MonthCalendar, PriceTable, HtmlPanel, …) |
| **R3.3** | `MrtInlineSpinner` eller delad spinner-helper; admin-shell spinner bort |
| **R3.4** | (Valfritt) Migrera admin spacing till `MrtStack` |

**DoD:** inga nya `class="mrt-mt-*"` i `components/ui/`; utilities kvar för PHP.

---

### Fas R4 — Admin encapsulation

| PR | Innehånd |
|----|----------|
| **R4.1** | Migrera `AdminLoadState` / `AdminStatusMessage` → `Mrt*` (sök ersätt i pages) |
| **R4.2** | Flytta route preview, mobile panels, timetable meta från `admin-shell.css` → feature SFC |
| **R4.3** | Flytta shell-nav till `AdminApp.vue` scoped |
| **R4.4** | `verify-build.mjs`: admin bundle ska innehålla förväntade markers |

**DoD:** `admin-shell.css` **< 200 rader** (generellt skal); admin E2E stabilare.

---

### Fas R5 — Städning & varumärke (lägre prioritet)

| PR | Innehåll |
|----|----------|
| **R5.1** | Ta bort tomma mappar, död `.mrt-vue-experiment` i `vue-shell.css` |
| **R5.2** | Skärp `public-layout.css` mot `MrtPublicAppShell` (undvik dubletter) |
| **R5.3** | BRAND_UI: overview-banner utan gradient; räta hörn där profilen kräver |
| **R5.4** | Tokens: fasa ut legacy-alias i `tokens.css` till förmån för `--mrt-color-*` |

---

## Prioriterad ordning (rekommendation)

Om fokus är **återanvändbara UI-komponenter** som ni kan bygga vidare på:

1. **R0** — regler (snabbt)  
2. **R1** — `MrtWizardShell` + `MrtWizardMainCard` (tydliga layout-komponenter)  
3. **R3.1–R3.2** — `MrtStack` (mest generell återanvändning i hela kodbasen)  
4. **R2** — overview ned i befintliga `MrtOverview*`  
5. **R4** — admin efter publik modell  
6. **R5** — städning

```mermaid
flowchart LR
  R0[Regler] --> R1[Wizard layout Mrt*]
  R1 --> R3[MrtStack + spinner]
  R3 --> R2[Overview split]
  R2 --> R4[Admin]
  R4 --> R5[Städ + brand]
```

---

## Definition of done (hela refaktor-initiativet)

- [x] Inga app-root/SFC med **> 150 rader** scoped CSS (undantag dokumenterat: `WizardSummaryStep` print block)
- [x] `admin-shell.css` **< 200 rader** (resten i feature SFC) — **7 rader** efter R4
- [x] Nya spacing-mönster via **`MrtStack`**, inte nya utilities (publik Vue; PHP utilities kvar)
- [x] **`AdminLoadState` / `AdminStatusMessage` borttagna** — endast `MrtAsyncState` / `MrtAlert`
- [x] [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md) listar nya `Mrt*` (R1–R3)
- [x] Guardrails utökade — `verify-build.mjs` admin-markers + Vue style line budget (soft warning)
- [ ] E2E: publik suite grön; admin-WP-flak fixad eller isolerad

---

## Status (2026-06-12)

| Fas | Status |
|-----|--------|
| R0 | Klar — länkat från STYLE_GUIDE, VUE_UI_COMPONENTS, encapsulation-plan |
| R0–R3 | Klar (commit `de3e3bd`) |
| R4 | Klar (commit `3fb873f`) |
| R5 | Klar — städning, brand-dok, tokens-kommentar; `assets/frontend/ui/` borttagen |
| Wizard shell split | Klar (commit `4873289`) — `MrtWizardHero`, `MrtWizardShellSurfaces` |
| PriceTable split | Klar — `MrtPriceTableList`, `MrtPriceTableMatrix` |
| CI style budget | Soft warning i validate + verify-build (150 rader) |

---

## Nästa steg

Uppföljning i **[CSS_RESPONSIBILITY_PLAN.md](CSS_RESPONSIBILITY_PLAN.md)** (C1–C5). Kort:

1. **C1** — `MrtStepPanel` äger panel-CSS (ta bort layout `:deep`)
2. **C2** — focus ring i `Mrt*` + `mrtFocusRing.css`
3. E2E — full publik suite + admin smoke
4. **C3–C5** — admin mobil, månadskalender, alert dual track, `AdminPanel` tokens

---

## Risker

| Risk | Mitigering |
|------|------------|
| För många små `Mrt*` | Krav: minst **2 användningsställen** eller tydlig layout-roll innan ny primitiv |
| `:deep()` vid layout-split | Flytta `:deep` till layout-komponent som äger slot-strukturen |
| Admin ser annorlunda ut | `context="admin"` + WP-klasser i `MrtButton`/`MrtAlert` — visuell regression via E2E |
| PR blir stora | En komponent / en CSS-källa per PR |
