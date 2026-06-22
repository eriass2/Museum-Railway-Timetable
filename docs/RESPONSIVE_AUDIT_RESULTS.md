# Responsiv granskning — resultat

**Plan:** [RESPONSIVE_AUDIT_PLAN.md](RESPONSIVE_AUDIT_PLAN.md)  
**Status:** T1 klar (2026-06-22)

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
| T1 Wizard | `MrtPriceTable*` | ✅ | fluid | 48rem | Matrix scroll OK | — |

**Legend:** R1–R8 enligt plan (✅ / ⚠️ / ❌).

---

## Fyndlogg

| Datum | Yta | Beskrivning | Status |
|-------|-----|-------------|--------|
| 2026-06-22 | F0 | Infört `assets/mrt-layout-tokens.css`; wizard shell kopplad till skalan | Klar |
| 2026-06-22 | T1 | E2E overflow 320/390 + desktop cap; token-migration route/timeline/detail | Klar |
