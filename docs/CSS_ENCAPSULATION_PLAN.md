# Plan: CSS inkapsling (Vue, Angular-liknande)

**Status:** pågår — Fas 0 + Fas 1 (PR 1.1–1.6) + Fas 2 klara 2026-06-12; Fas 3–4 kvar  
**Relaterat:** [STYLE_GUIDE.md](STYLE_GUIDE.md) §3, [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md), [frontend/vue/src/styles/journey-wizard/README.md](../frontend/vue/src/styles/journey-wizard/README.md)

---

## Syfte

Minska **global CSS** som stylar komponenter via lösa klassnamn (särskilt `assets/frontend/ui/`). Flytta stilar till **scoped `<style>` i `.vue`-filer** så beteende följer komponenten — ungefär som Angular `@Component({ styles })`.

**Symptom idag:** samma klass (t.ex. `.mrt-route-layout__stations`) stylas i både legacy `trips.css` (alltid laddad via `main-*.css`) och `controls-form.css` (wizard-chunk). Specificitet och laddordning avgör resultatet — svårt att förutse.

**Mål:** en komponent → en stylesheet-källa. Globalt bara tokens, WP-layout och reset.

---

## Principer

| Ska vara globalt | Ska vara lokalt (scoped) |
|------------------|---------------------------|
| `--mrt-color-*`, `--mrt-wizard-*` tokens | Formulär, steg, knappar, kort |
| `assets/frontend/public-layout.css` (TT5 `.alignwide`) | `MrtSegmentedControl`, `MrtCombobox`, … |
| Minimal reset / typografi-import | Wizard-steg (`WizardRouteStep`, …) |
| Print (ev.) | Trip cards, timeline, detail panel |

**Spelregler (efter Fas 0):**

1. **Inga nya regler** i `assets/frontend/ui/`.
2. **Nya UI-primitiver** → `<style scoped>` i `frontend/vue/src/components/ui/`.
3. **Wizard-specifikt** → scoped i `wizard/components/` eller app-komponent — inte ny rad i `journey-wizard/*.css` om det bara gäller ett steg.
4. **`:deep()`** när parent måste nå barn/slot (t.ex. ikon i segmented option).
5. **Bygg om** efter CSS-ändring: `npm run build` i `frontend/vue/` (se [DEVELOPER.md](DEVELOPER.md)).

---

## Nuläge — var CSS bor idag

### A. Legacy global (laddas alltid via `mrt-public.css` → `main-*.css`)

```
assets/frontend-public.css
  └── ui-components.css
        ├── ui/primitives.css          → .mrt-empty, focus-ring
        ├── ui/calendar-tokens.css     → .mrt-calendar-day--*
        ├── ui/wizard-steps.css        → steg, combobox, segmented, field
        ├── ui/calendar-nav-legend.css → kalendernav, legend, trip-type-icon
        ├── ui/trips.css               → route layout, trip cards, timeline, detail
        ├── ui/panels-headings.css     → headings, actions, field-error, html-panel
        └── ui/price-table.css         → prisblock
```

### B. Vue app-modul (lazy chunk, wizard)

```
JourneyWizardApp.vue → journey-wizard.css
  ├── base.css, hero-layout.css, wizard-shell.css
  ├── wizard-main-card.css
  ├── controls-form.css      ← dubblerar/krockar med A
  ├── controls-calendar.css
  ├── steps-outbound-return.css, steps-summary.css
  ├── trips-detail-summary.css, feedback.css
  ├── responsive.css, sharp-corners.css
```

### C. Scoped SFC (redan OK — förebild)

`MrtAlert`, `MrtButton`, `MrtDot`, `MrtAsyncState`, `MrtSurfaceCard` — **5 av ~35** UI-primitiver.

### D. App-specifika moduler (global inom app, acceptabelt tills migrerat)

`month-calendar.css`, `timetable-overview.css`, `timetable-index.css`, `traffic-notices.css`, `app-shell.css`.

---

## Målbild — filstruktur

```
assets/mrt-color-tokens.css          ← kvar (designsystem)
assets/frontend/public-layout.css    ← kvar (WP/tema)
frontend/vue/src/styles/mrt-public.css  ← bara tokens + tunn reset (inga ui/*.css)

frontend/vue/src/components/ui/
  MrtRouteLayout.vue                 ← scoped (stations staplade här)
  MrtSegmentedControl.vue            ← scoped
  MrtCombobox.vue                    ← scoped
  …

frontend/vue/src/wizard/components/
  WizardRouteStep.vue                ← scoped (route-form, station-field)
  WizardDateStep.vue                 ← scoped
  …

frontend/vue/src/apps/JourneyWizardApp.vue  ← scoped shell (main-card, hero)
frontend/vue/src/components/layout/MrtPublicAppShell.vue  ← scoped bleed

assets/frontend/ui/                  ← tommas / raderas stegvis
```

---

## Fas 0 — Spelregler och docs (liten PR)

**Ändra:**

| Fil | Åtgärd |
|-----|--------|
| `docs/STYLE_GUIDE.md` §3 | Länka hit; skärp “inga nya regler i assets/frontend/ui” |
| `docs/VUE_UI_COMPONENTS.md` | Uppdatera “Medvetet kvar som global CSS” → peka på migrationsplan |
| `.cursor/rules/` (ev.) | Kort regel: nya primitiver = scoped |
| `frontend/vue/src/styles/journey-wizard/README.md` | Notera att modulerna är **deprecated** — flyttas till komponenter |

**Definition of done:** team har en regel; inga kodändringar utom docs.

---

## Fas 1 — Legacy `assets/frontend/ui/` → Vue-komponenter

Prioritet = det som krockar eller används brett.

### PR 1.1 — Route search (högsta ROI)

| Från | Till | Radera ur legacy |
|------|------|------------------|
| `trips.css` `.mrt-route-layout*` | `MrtRouteLayout.vue` scoped | route-layout block |
| `wizard-steps.css` segmented/field/combobox (bas) | `MrtSegmentedControl.vue`, `MrtCombobox.vue`, `MrtFieldGroup.vue` scoped | motsvarande block |
| `controls-form.css` (hela filen) | `WizardRouteStep.vue` scoped | filen kan tömmas efter flytt |
| `panels-headings.css` `.mrt-heading*` | `MrtHeading.vue` scoped | heading block |
| `calendar-nav-legend.css` `.mrt-trip-type-icon` | `WizardTripTypeIcon.vue` scoped | trip-type-icon |

**Test:** `wizard-mount.spec.ts`, manuell localhost söksteg, E2E front page.

### PR 1.2 — Steg-navigation

| Från | Till |
|------|------|
| `wizard-steps.css` `.mrt-step-*` | `MrtStepProgress.vue`, `MrtStepHeader.vue` scoped |
| `panels-headings.css` `.mrt-actions`, `.mrt-field-error` | `WizardRouteStep.vue` / dela till komponenter |

### PR 1.3 — Kalender (delad month + wizard)

| Från | Till |
|------|------|
| `calendar-nav-legend.css` | `MrtCalendarNav.vue`, `MrtLegend.vue` scoped |
| `calendar-tokens.css` `.mrt-calendar-day--*` | `MrtWizardCalendarDayCell.vue`, `MrtMonthDayCell.vue` scoped (tokens som CSS vars kan referera globalt) |

**OBS:** Månadskalender och wizard delar primitiver — scoped i primitiven, inte i wizard-CSS.

### PR 1.4 — Resor / detalj (stor)

| Från | Till |
|------|------|
| `trips.css` trip-card, timeline, detail, expand, vehicle | `MrtTripCard`, `MrtTimeline`, `MrtDetailPanel`, `MrtDetailSegment`, `MrtExpandTrigger`, `MrtVehicleRow`, `MrtConnectionLegList`, `MrtTripSummary`, `MrtSelectedTrip`, `MrtTripList`, `MrtSummaryCard` scoped |

### PR 1.5 — Priser

| Från | Till |
|------|------|
| `price-table.css` | `MrtPriceTable.vue` scoped |

### PR 1.6 — Städa barrel

| Fil | Åtgärd |
|-----|--------|
| `assets/frontend/ui-components.css` | Ta bort `@import` en i taget när legacy tom |
| `assets/frontend-public.css` | Ta bort `ui-components.css`-import när barrel tom |
| `assets/frontend/ui/*.css` | Radera tomma filer |

**Fas 1 klar när:** `main-*.css` inte innehåller wizard/trip/form-regler; inga krockar mellan legacy och wizard-chunk.

---

## Fas 2 — `journey-wizard/*.css` → komponenter

| CSS-modul idag | Flytta till |
|----------------|-------------|
| `wizard-main-card.css` | `JourneyWizardApp.vue` scoped |
| `hero-layout.css` | `JourneyWizardApp.vue` + `MrtPublicAppShell.vue` |
| `wizard-shell.css` | relevant UI-primitiv eller steg-komponent |
| `controls-calendar.css` | `WizardDateStep.vue` |
| `steps-outbound-return.css` | `WizardTripStep.vue` |
| `steps-summary.css` | `WizardSummaryStep.vue` |
| `trips-detail-summary.css` | `WizardTripStep` / detail wrappers |
| `feedback.css` | `WizardFeedbackWidget.vue` scoped |
| `sharp-corners.css` | `JourneyWizardApp.vue` eller tokens |
| `responsive.css` | **dela upp** — media queries in i respektive komponent som behöver dem; ev. kvar en tunn `journey-wizard-responsive.css` till allt är flyttat |
| `base.css` | `JourneyWizardApp.vue` (root `.mrt-journey-wizard`, embedded, fokus) |

**Efter Fas 2:**

- `journey-wizard.css` → antingen borttagen eller bara `@import` av det som **verkligen** är app-root (embedded + reduced-motion).
- Uppdatera `journey-wizard/README.md`.

---

## Fas 3 — Övriga appar (lägre prioritet)

Samma mönster för:

| App | CSS idag | Mål |
|-----|----------|-----|
| Month calendar | `month-calendar.css` | scoped i app + `MrtMonthDayCell` m.fl. |
| Overview | `timetable-overview.css` | scoped i `MrtTimetableOverviewView` + overview-komponenter |
| Index | `timetable-index.css` | scoped i `TimetableIndexApp.vue` |
| Traffic notices | `traffic-notices.css` | scoped i `TrafficNoticesApp.vue` |

---

## Fas 4 — Guardrails

| Åtgärd | Syfte |
|--------|--------|
| CI: grep/check att nya `.mrt-segmented` etc. inte läggs i `assets/frontend/ui/` | förhindra regression |
| Playwright screenshot eller layout-assertions för wizard söksteg | fånga visuella regressioner |
| `npm run verify` + befintliga E2E i PR-checklista | bygg + chunks OK |

---

## Vue-mönster (referens)

```vue
<!-- Parent med slot-barn -->
<style scoped>
.route-form :deep(.mrt-segmented__option) { min-height: 2.5rem; }
</style>
```

```vue
<!-- Primitiv — all styling här, ingen global .mrt-route-layout i trips.css -->
<style scoped>
.mrt-route-layout__stations {
  display: grid;
  grid-template-columns: 1fr; /* staplat — beslut i komponenten */
  gap: 1rem;
}
</style>
```

**Variant:** `variant`-prop + scoped modifier-klass (t.ex. `compact` på `MrtSegmentedControl`) i stället för wizard-specifika overrides i `controls-form.css`.

---

## Prioriterad backlog (konkret ordning)

1. Fas 0 — docs + regler  
2. `MrtRouteLayout` + rensa `trips.css` route-block  
3. `MrtSegmentedControl` + rensa dubletter i `wizard-steps.css` / `controls-form.css`  
4. `MrtCombobox` / `MrtFieldGroup`  
5. `WizardRouteStep` — flytta resten av `controls-form.css`, radera filen  
6. `MrtStepProgress` / `MrtStepHeader`  
7. `JourneyWizardApp` — `wizard-main-card.css`, `base.css` (shell)  
8. Kalender-primitiver  
9. Trip/detail-komponenter (`trips.css` rest)  
10. Ta bort `ui-components.css` från `frontend-public.css`  
11. Övriga appar (Fas 3)

---

## Risker och mitigering

| Risk | Mitigering |
|------|------------|
| Scoped träffar inte slot-innehåll | `:deep()` eller flytta stil till barn-komponent |
| Month + wizard delar primitiv | Styla primitiven; använd props för varianter |
| WP-tema slår igenom | Behåll `.mrt-vue-root` reset i tunn global fil |
| Glömd `npm run build` | CI + tydlig PR-text |
| Stora PR | En komponent / en legacy-fil per PR |

---

## Definition of done (hela initiativet)

- [x] `assets/frontend/ui/` innehåller inga komponentregler (trips, price-table, panels-headings tomma; barrel = tokens + primitives)
- [ ] `frontend-public.css` importerar inte `ui-components.css` (kvar: primitives + calendar tokens)
- [x] Alla `frontend/vue/src/components/ui/*.vue` har scoped styles (eller medvetet undantag dokumenterat)
- [x] Wizard: inga steg-specifika regler kvar i `journey-wizard/*.css` (stub tom; styles i SFC)
- [ ] E2E wizard + manuell smoke på localhost OK
- [x] STYLE_GUIDE + VUE_UI_COMPONENTS uppdaterade (via tidigare PR)

---

## Nästa steg

1. ~~Godkänn plan (denna fil).~~  
2. ~~Fas 0–2 (legacy ui + wizard shell).~~  
3. Fas 3: `month-calendar.css`, `timetable-overview.css`, … → scoped app/SFC  
4. Fas 4: utöka Playwright layout-checks; ev. ta bort `ui-components.css` helt när primitives flyttats
