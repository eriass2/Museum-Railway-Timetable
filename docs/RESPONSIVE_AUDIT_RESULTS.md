# Responsiv granskning — resultat

**Plan:** [RESPONSIVE_AUDIT_PLAN.md](RESPONSIVE_AUDIT_PLAN.md)  
**Status:** T1–T3 klar (2026-06-22)

---

## T3 — Månadskalender (sammanfattning)

| Viewport | Resultat |
|----------|----------|
| V1 320px | ✅ Ingen sid-overflow |
| V2 390px | ✅ Tabell utan horisontell scroll; nav ≥44px touch |
| V5 1920px | ✅ `--mrt-max-app` cap via `MrtPublicAppShell` |

**Komponenter:** `MonthCalendarApp`, `MrtCalendarNav`, `MrtCalendarGridTable`, `MrtMonthDayCell`.

**Åtgärder:** E2E `month-responsive.spec.ts` — inga CSS-fix krävdes (befintlig `40rem`-breakpoint fungerar).

---

## T2 — Tidtabellsöversikt (sammanfattning)

| Viewport | Resultat |
|----------|----------|
| V1 320px | ✅ Ingen sid-overflow |
| V2 390px | ✅ Grid scroll i `.mrt-ov-grid-scroll` (avsiktlig) |
| V5 1920px | ✅ Bredvid `--mrt-max-app` cap |

**Komponenter:** `MrtTimetableOverviewShell`, `MrtOverviewRailGroupGrid*`, `MrtOverviewBranch*`, `MrtHtmlPanel`.

**Åtgärder:** E2E `overview-responsive.spec.ts` — inga CSS-fix krävdes.

---

## T1 — Reseplanerare (sammanfattning)

| Viewport | Resultat |
|----------|----------|
| V1 320px | ✅ Ingen horisontell overflow (route) |
| V2 390px | ✅ Route, outbound, summary — E2E `wizard-responsive.spec.ts` |
| V5 1920px | ✅ Shell cap verifierad (embedded: `--mrt-max-app`; prod hero: `--mrt-max-wizard`) |

**Komponenter granskade:** `MrtWizardShell*`, `MrtStepPanel`, `MrtStepProgress`, `MrtRouteLayout`, `MrtTripCard`, `MrtDetailPanel`, `MrtSummaryCard`, `MrtTimeline`, `MrtPriceTable*`, `WizardSummaryStep`.

**Åtgärder:** Token-migration (`MrtRouteLayout`, container queries); ny E2E overflow/cap-suite.

**Kvar (låg prioritet):** `MrtConnectionLegList` container `22rem` — ev. egen token vid summary-touch.

---

## Inventering per yta

| Yta | Komponent | R1–R8 | Max-token | `@media` | Åtgärd | PR |
|-----|-----------|-------|-----------|----------|--------|-----|
| T1 Wizard | `MrtRouteLayout` | ✅ | `--mrt-max-feed` | — | Migrerad från `36rem` | T1 |
| T1 Wizard | `MrtDetailPanel` | ✅ | `--mrt-max-narrow` (container) | 48rem | Container token | T1 |
| T1 Wizard | `MrtSummaryCard` | ✅ | `--mrt-max-narrow` (container) | — | Container token | T1 |
| T1 Wizard | `MrtTimeline` | ✅ | `--mrt-max-narrow` (container) | — | Container token | T1 |
| T1 Wizard | Shell (`MrtWizardShell`) | ✅ | `--mrt-max-wizard` / `--mrt-max-app` | 48rem | F0 + E2E cap | F0/T1 |
| T1 Wizard | `MrtStepProgress` | ✅ | fluid | 48rem | Befintlig scroll | — |
| T1 Wizard | `MrtTripCard` | ✅ | fluid | 48/22.5rem | OK | — |
| T3 Calendar | `MonthCalendarApp` + grid | ✅ | `--mrt-max-app` | 40rem | OK | T3 |
| T3 Calendar | `MrtCalendarNav` | ✅ | fluid | 48/40rem | Touch 2.65rem | — |

**Legend:** R1–R8 enligt plan (✅ / ⚠️ / ❌).

---

## Fyndlogg

| Datum | Yta | Beskrivning | Status |
|-------|-----|-------------|--------|
| 2026-06-22 | F0 | Infört `assets/mrt-layout-tokens.css`; wizard shell kopplad till skalan | Klar |
| 2026-06-22 | T3 | E2E month overflow, table scroll, touch targets | Klar |
