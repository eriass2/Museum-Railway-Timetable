# TODO – teknisk skuld och förbättringar

Kort lista över **öppna** punkter där **produkt/beslut redan är spikat** — implementation eller verifiering återstår. Genomfört arbete finns i arkivet längst ner och i respektive plan/doc.

Punkter **utan** beslut listas separat. **Mycket senare** — parkerade tills kärnflöden är verifierade och produktbeslut finns.

---

## Reseplanerare — copy & biljettinfo (J15–J18)

**Källa:** [feedback/2026-06-11-jesper-reseplanerare.md](feedback/2026-06-11-jesper-reseplanerare.md)  
**Status:** Väntar produktbeslut D19–D22 innan implementation.

| ID | Punkt | Insats |
|----|-------|--------|
| J17 | CTA «Mer information om biljettköp» | Liten — kan göras direkt |
| J16 | Enklare zonförklaring | Liten |
| J15 | Biljettinfo + admin-redigerbar copy | Medel–stor |
| J18 | Dela-knapp (Web Share) | Medel |

---

## Mycket senare (förutsättningar / produktbeslut saknas)

Plocka **inte** upp förrän kärnflöden är stabila och ev. produktbeslut är tagna.

| ID | Punkt | Varför senare |
|----|-------|----------------|
| R11 | Gångvägar + karta (Selkné–Fjällnora m.fl.) | Ny resetypsmodell + kart-UI; Jesper: «stort extrajobb» — [feedback juni 2026](feedback/2026-06-05-reseplanerare-beta.md#r11-gångvägar-framtida) |
| A0 / D8 | Onboarding-friktion (en vs två rutter per linje) | Kräver D8-domänbeslut (en rutt vs returrutt-knapp vs CSV-first); A1/A7/A10/Turvy redan levererat — [diskussioner D8](feedback/2026-06-09-jesper-diskussioner.md#d8-rutt-en-eller-två-rutter-per-linje-) |
| A9 / D11 | Publicera-knapp / utkast tidtabell | Kräver D11 (`draft` vs meta vs staging) **och** stabil admin/onboarding innan utkast/publicera kan designas säkert — [diskussioner D11](feedback/2026-06-09-jesper-diskussioner.md#d11-publicera-knapp--utkast-a9-) |
| D2b v2 | Feedback — e-postnotis vid ny rapport | `wp_mail` + konfigurerbar adress; låg prioritet när admin-lista räcker — [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md) |

---

## Saknar produktbeslut (ej i backlog tills beslut)

*(Tomt — D16 har beslutad riktning i [TRAFFIC_DISRUPTIONS_PLAN.md](TRAFFIC_DISRUPTIONS_PLAN.md). Jesper OK J11 väntar svar i [svar till Jesper](feedback/2026-06-11-svar-till-jesper.md).)*

---

## Genomfört (arkiv)

| Område | Klar | Referens |
|--------|------|----------|
| Vue — gemensamma datetime/tid-utils | 2026-06-09 | [VUE_UTILS.md](VUE_UTILS.md), `frontend/vue/src/utils/datetime.ts` |
| PHP — utils-snabbguide i dokumentation | 2026-06-10 | [STYLE_GUIDE.md](STYLE_GUIDE.md) |
| Reseplanerare — feedback-widget v1 | 2026-06-10 | [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md), J13/D2b |
| Reseplanerare — feedback CSV-export | 2026-06-11 | `#/feedback`, `GET /feedback/export` |
| Tidtabellsöversikt — typografi (Turvy) | 2026-06-10 | `timetable-overview.css`, commit `6d91237` |
| Tidtabellsöversikt — kolumnsammanslagning (kärna) | 2026-06-10 | commit `9a44dda`, `OverviewColumnMergeTest` |
| Tidtabellsöversikt — bussrader per tågkolumn (Selkné) | 2026-06-10 | commit `8c8a30a`, `TimetableOverviewHelpersTest` |
| Jesper beta — reseplanerare + admin (J1–J10, A1–A8, A10) | 2026-06-10 | [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) |
| Turvy — highlight-kolumn (Thun's-expressen, J1) | 2026-06-01 | Smal vertikal etikett + fast kolumnbredd — [granskning J1](feedback/2026-06-01-granskning.md), `MrtOverviewRailGroupGrid.vue`, `timetable-overview.css` |
| Trafikstörningar J11 — UL-lik feed (fas 1–4, 90 d, admin preview) | 2026-06-11 | [TRAFFIC_DISRUPTIONS_PLAN.md](TRAFFIC_DISRUPTIONS_PLAN.md), `/trafikstorningar`, `#/traffic-notices` |
| Stopptider schema v3 — drift + tur 71 GRÖN | 2026-06-11 | [STOP_TIME_CA.md](STOP_TIME_CA.md), [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md) |
| Tidtabellsöversikt — sammanslagna kolumner (Marielund) | 2026-06-11 | Manuell check Faringe→Uppsala 2026-06-06 |
| Tidtabellsöversikt — buss vid knutpunkt (Selkné) | 2026-06-11 | Manuell check GRÖN + RÖD mot PDF |
| Linjer och grenar — refactor (main + 2+1) | 2026-06-11 | [LINES_REFACTOR_PLAN.md](LINES_REFACTOR_PLAN.md), [LINES_ADMIN_PLAN.md](LINES_ADMIN_PLAN.md) |
| Reseplanerare — smoke efter buggfixar (B1–B3) | 2026-06-11 | [feedback/2026-06-09-jesper-buggar-plan.md](feedback/2026-06-09-jesper-buggar-plan.md) |
| Linnés Hammarby — operatörsdata + verifiering | 2026-06-11 | [LINNES_HAMMARBY.md](LINNES_HAMMARBY.md) |
| Admin — utökad datakvalitet (dashboard) | 2026-06-11 | `dashboard-warnings.php`, `dashboard-warnings-quality.php` |
| Reseplanerare J14 — holistisk cache (R1–R4, prefetch, warm) | 2026-06-11 | [WIZARD_CACHE_REFACTOR.md](WIZARD_CACHE_REFACTOR.md), `journey-cache.php`, `resourceCache.ts`, `scripts/warm-journey-cache.php`, commits `fb52993`, `8f3228e` |
| Reseplanerare — enhetlig stepper (pill-storlek/typografi) | 2026-06-11 | `wizard-steps.css`, `MrtStepProgress.vue`, commit `6cf1c64` |
| Docker/skript — Fas 0 (optimering + refaktor) | 2026-06-11 | [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md), commit `b3934a4` |

---

## Docker och utvecklarverktyg (Fas 1–3)

**Plan:** [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md)

| ID | Punkt | Fas |
|----|-------|-----|
| D1–D7 | Docs, bash-paritet, timings, smoke-URL:er | 1 |
| P1–P7 | Volumes, tools-image, WP-CLI exec, tools-shell | 2 |
| S1–S5 | En CLI, CI-strategi, devcontainer | 3 |
