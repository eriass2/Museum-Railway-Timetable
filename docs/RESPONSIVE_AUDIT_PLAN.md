# Plan: Granskning av responsivitet och max-storlekar (Vue)

**Status:** Pågående — F0 klar (2026-06-22); T1–T3 klar (2026-06-22); T4–T8 ej påbörjad  
**Datum:** 2026-06-22  
**Relaterat:** [CSS_RESPONSIBILITY_PLAN.md](CSS_RESPONSIBILITY_PLAN.md), [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md), [STYLE_GUIDE.md](STYLE_GUIDE.md) §3, [mockups/DESIGN_TOKENS.md](mockups/DESIGN_TOKENS.md), [REBUILD_RULES.md](REBUILD_RULES.md) §mobile-first

---

## Syfte

Systematiskt granska **publika Vue-ytor** och **admin mobil** för:

1. **Responsivitet** — layout, overflow, touch targets, läsbarhet från smal mobil till bred desktop.
2. **Max-storlekar** — rimliga caps så innehåll inte blir oläsligt brett; tokens istället för ad hoc `rem` per komponent.

**Inte i scope:** PHP-templates, legacy global CSS i `assets/` (utom tokens som Vue importerar), ren desktop-admin utan mobil-shell.

**Ledprincip (oförändrad):** *Komponenten som äger DOM ska äga CSS.* Layout sätter context (tokens, max-width) — inte barnens padding eller breakpoints via `:deep()`.

---

## Nuläge (baseline)

| Signal | Ungefär |
|--------|---------|
| Vue SFC totalt | ~179 filer |
| Filer med `@media` / `@container` | ~50 |
| Publik breakpoint | `48rem`, `40rem`, `22.5rem` (ofta `max-width`) |
| Admin mobil breakpoint | `782px` (`useMobileAdmin`, WP admin) |
| App-shell max | `--mrt-app-content-max: min(96vw, 80rem)`, wizard `64rem` |
| E2E viewport-snapshots | Wizard timeline, trafikinfo (390×820), wizard front page |
| STYLE_GUIDE mobile-first | Dokumenterat — kod blandar `min-width` och `max-width` |

**Kända riskområden:** overview-grid (horisontell scroll), kalendergrid, pris-tabell, wizard steg-progress, admin-tabeller utan `mrt-admin-responsive-table`.

---

## Fas 0 — Ramverk (gör en gång)

**Mål:** Gemensamma regler så granskning blir repeterbar, inte subjektiv.

### 0.1 Viewport-matris (obligatorisk per yta)

| ID | Bredd | Höjd | Typisk enhet | Syfte |
|----|-------|------|--------------|-------|
| V1 | 320px | 568px | iPhone SE | Minsta stöd, overflow-test |
| V2 | 390px | 820px | Mobil (E2E-baseline) | Primär mobil |
| V3 | 768px | 1024px | Surfplatta | Brytpunkt public/admin |
| V4 | 1280px | 800px | Laptop | Normal desktop |
| V5 | 1920px | 1080px | Bred skärm | Max-width / cap-test |

**Manuell smoke:** DevTools device toolbar + full bredd i webbläsare.  
**Automatiserat:** Playwright `page.setViewportSize()` — utöka befintliga specs vid behov.

### 0.2 Granskningschecklista (per komponent / yta)

| # | Kriterium | Pass | Fail-exempel |
|---|-----------|------|--------------|
| R1 | Ingen oavsiktlig horisontell scroll (V1–V2) | ☐ | Tabell/grid sticker ut |
| R2 | Text radbryts; inga klippta etiketter | ☐ | `white-space: nowrap` utan overflow-hantering |
| R3 | Klickbara ytor ≥ ~44×44px på touch (V2) | ☐ | Små ikonknappar utan padding |
| R4 | `max-width` medveten på V5 (cap eller medvetet fluid) | ☐ | 1200px textblock utan cap |
| R5 | Flex/grid-barn har `min-width: 0` / `minmax(0,1fr)` där det behövs | ☐ | Trunkerad text klipps fel |
| R6 | Breakpoint följer yta (public `48rem` / admin `782px`) | ☐ | Blandade px/rem utan skäl |
| R7 | `@container` över `@media` när komponenten sitter i varierande förälder | ☐ | Bara viewport-query trots smalt kort |
| R8 | `prefers-reduced-motion` respekteras för animationer | ☐ | Endast om komponent animerar |

**Status per rad:** ✅ Pass · ⚠️ Acceptabelt med dokumenterat undantag · ❌ Fix krävs

### 0.3 Max-width-skala (förslag — tokenisera i Fas 0)

Centralisera i `assets/mrt-layout-tokens.css` (importeras via `mrt-public.css`):

| Token | Värde | Användning |
|-------|-------|------------|
| `--mrt-max-narrow` | `28rem` | Formulärfält, admin inline-form, smala paneler |
| `--mrt-max-feed` | `36rem` | Trafikinfo-feed (`--mrt-tf-panel-max-width`) |
| `--mrt-max-content` | `42rem` | Index, texttunga kort |
| `--mrt-max-step` | `46rem` | Wizard sök / fel / beta-banner |
| `--mrt-max-step-wide` | `54rem` | Wizard utresa/retur (enligt DESIGN_TOKENS) |
| `--mrt-max-wizard` | `64rem` | Wizard shell content |
| `--mrt-max-app` | `80rem` | Publik app-shell cap |

**Shell-derived:** `--mrt-app-content-max: min(96vw, var(--mrt-max-app))`, `--mrt-wizard-content-max: min(76.8vw, var(--mrt-max-wizard))`.

**Breakpoint-referenser:** `--mrt-bp-public-md` (48rem), `--mrt-bp-public-sm` (40rem), `--mrt-bp-public-xs` (22.5rem), `--mrt-bp-admin-mobile` (782px).

**Regel:** Komponenter refererar tokens — hårdkodade `42rem`/`36rem` migreras vid granskning (T1–T8).

**Mobil:** `max-width: 100%` eller token `none` under respektive breakpoint (redan mönster i wizard/admin).

### 0.4 Inventeringsmall

Resultat loggas i [RESPONSIVE_AUDIT_RESULTS.md](RESPONSIVE_AUDIT_RESULTS.md):

| Yta | Komponent | R1–R8 | Max-token | `@media` | Åtgärd | PR |
|-----|-----------|-------|-----------|----------|--------|-----|

---

## Fas 1 — Ytor (prioriterad ordning)

Granska **yta för yta** (shell → feature-vy → layout → UI med egen grid). Hoppa över primitiver som bara är fluid markup (`MrtDot`, `MrtVisuallyHidden`, `MrtStack`).

### Tier 1 — Publik, hög trafik

| ID | Yta | Ingående komponenter (huvudsakliga) | Viewports | E2E idag |
|----|-----|--------------------------------------|-----------|----------|
| T1 | **Reseplanerare** | `JourneyWizardApp`, `MrtWizardShell*`, `MrtStepProgress`, `MrtStepPanel`, `MrtTripCard`, `MrtDetailPanel`, `MrtPriceTable*`, `WizardSummaryStep` | V1–V5 | timeline, front-page-wp, performance |
| T2 | **Tidtabellsöversikt** | `MrtTimetableOverviewShell`, `MrtOverviewRailGroupGrid*`, `MrtOverviewBranch*`, `MrtHtmlPanel` | V1–V5 | begränsad |
| T3 | **Månadskalender** | `MonthCalendarApp`, `MrtCalendarNav`, `MrtCalendarGridTable`, `MrtMonthDayCell` | V1–V5 | begränsad |

### Tier 2 — Publik, medel

| ID | Yta | Ingående komponenter | Viewports |
|----|-----|----------------------|-----------|
| T4 | **Trafikinfo** | `MrtTrafficNoticesView`, `MrtTfPanels`, `MrtTfAlertCard` | V2, V5 |
| T5 | **Tidtabellsindex** | `MrtTimetableIndexView`, `MrtTimetableIndexCard` | V2, V5 |

### Tier 3 — Admin mobil

| ID | Yta | Ingående | Viewports |
|----|-----|----------|-----------|
| T6 | **Admin shell + dashboard** | `AdminApp`, `AdminMobilePageShell`, `AdminNav`, `DashboardPage` | V2 (782px), V4 |
| T7 | **Tidtabellseditor mobil** | `MobileTimetablePanel`, `TimetableTripFieldsBlock`, `StopTimesEditor` | V2 |
| T8 | **Priser / stationer / import** | Responsiva tabeller, `AdminInlineForm`, `PricesPage` | V2 |

### Tier 4 — Delade UI-primitiver (efter ytor)

Granska endast om Tier 1–3 hittar återkommande problem:

`MrtButton`, `MrtSegmentedControl`, `MrtHeading`, `MrtCalendarNav`, `MrtVehicleRow`, `MrtExpandTrigger`, `MrtCombobox`.

---

## Fas 2 — Åtgärder per fynd

| Typ | Exempel | Var fixas |
|-----|---------|-----------|
| **Token** | Ersätt `max-width: 42rem` → `var(--mrt-max-content)` | Shell eller komponent |
| **Overflow** | `overflow-x: auto` + fokusbar scroll-container | Grid/tabell-komponent |
| **Container query** | `MrtDetailPanel`, `MrtSummaryCard` (redan delvis) | Komponent SFC |
| **Breakpoint-konsolidering** | `40rem` vs `48rem` — dokumentera eller slå ihop | Feature + STYLE_GUIDE |
| **Mobile-first refactor** | `@media (max-width: 48rem)` → bas mobil + `min-width` | Vid touch av fil |
| **E2E snapshot** | Ny screenshot-spec för overview/calendar V2 | `frontend/vue/e2e/` |

**Scope per PR:** En yta (T1–T8) eller en token-migration — inte hela biblioteket.

---

## Fas 3 — Verifiering och dokumentation

| ID | Uppgift | Acceptans |
|----|---------|-----------|
| V-DOC | Uppdatera [STYLE_GUIDE.md](STYLE_GUIDE.md) §3 med breakpoint- och max-width-kontrakt | Tokens + public vs admin dokumenterade |
| V-VUE | Uppdatera [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md) med responsiva props/variants | t.ex. `MrtVehicleRow layout`, container-namn |
| V-E2E | Minst en ny eller utökad Playwright-spec per Tier-1-yta (V2) | CI grön |
| V-MAN | [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) — 2 rader per publik app (mobil + desktop) | Manuell release-check |

---

## Arbetsflöde per yta (checklista)

1. **Öppna app** i dev (`mrt dev` / WP shortcode enligt [DEVELOPER.md](DEVELOPER.md)).
2. **Kör viewport-matris** V1–V5; notera overflow (DevTools → Elements → scrollWidth > clientWidth).
3. **Fyll inventeringstabell** för shell + komponenter med egen layout.
4. **Klassificera:** ✅ / ⚠️ / ❌ per R1–R8.
5. **Implementera fix** i samma komponent som äger DOM (inte `:deep` i app-root).
6. **Lägg till/uppdatera E2E** om layout är regression-känslig.
7. **Kör gate:** `.\scripts\mrt.ps1 vue-check` och relevant E2E.
8. **Markera yta klar** i status-tabell nedan.

---

## Statusöversikt

| ID | Uppgift | Status |
|----|---------|--------|
| F0 | Ramverk: tokens, checklista, inventeringsmall | ✅ 2026-06-22 |
| T1 | Reseplanerare | ✅ 2026-06-22 |
| T2 | Tidtabellsöversikt | ✅ 2026-06-22 |
| T3 | Månadskalender | ✅ 2026-06-22 |
| T4 | Trafikinfo | ☐ |
| T5 | Tidtabellsindex | ☐ |
| T6 | Admin shell + dashboard | ☐ |
| T7 | Tidtabellseditor mobil | ☐ |
| T8 | Priser / stationer / import | ☐ |
| V-DOC | STYLE_GUIDE + VUE_UI_COMPONENTS | ☐ |
| V-E2E | Tier-1 Playwright viewport | ⚠️ wizard-responsive (T1); overview/calendar kvar |

---

## Vad vi medvetet **inte** granskar fil-för-fil

| Kategori | Varför |
|----------|--------|
| Ikoner, badges utan layout | Fluid by default |
| `MrtVisuallyHidden`, `MrtDot` | Ingen visuell layout |
| Admin desktop-only tabeller med horisontell scroll | Acceptabelt om dokumenterat |
| Komponenter utan `<style>` som bara wrappar barn | Ansvar ligger på barn/shell |

---

## Snabbstart

```powershell
# Vue gate efter ändringar
.\scripts\mrt.ps1 vue-check

# E2E (mock / WP enligt behov)
.\scripts\mrt.ps1 e2e
.\scripts\mrt.ps1 dev e2ewp
```

**Första rekommenderade steg:** ~~F0~~ ✅ ~~T1–T3~~ ✅ → **T4** (trafikinfo) med V2 + V5.
