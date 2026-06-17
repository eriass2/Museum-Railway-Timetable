# TODO – teknisk skuld och förbättringar

Kort lista över **öppna** punkter där **produkt/beslut redan är spikat** — implementation eller verifiering återstår. Genomfört arbete finns i arkivet längst ner och i respektive plan/doc.

Punkter **utan** beslut listas separat. **Mycket senare** — parkerade tills kärnflöden är verifierade och produktbeslut finns.

---

## Beroendeuppgraderingar

**Plan:** [DEPENDENCY_UPGRADE_PLAN.md](DEPENDENCY_UPGRADE_PLAN.md) — steg 0 (baseline) **klar**; nästa: steg 1 (patch), därefter PHP 8.3 + PHPUnit 12.

| Steg | Fokus | Status |
|------|-------|--------|
| 0 | Actions v5/v6, Playwright 1.61, PHPStan 2.2 | ✅ |
| 1 | npm/Composer patch | öppen |
| 2 | PHP 8.3 + PHPUnit 12 | öppen |
| 3–7 | Vite, ESLint, router, TS, html2pdf | öppen |

---

## CSS — uppföljning C6+ och E2E

**Plan:** [CSS_FOLLOWUP_PLAN.md](CSS_FOLLOWUP_PLAN.md) — **klar** (2026-06-13): C6–C9, E0–E1, WP E2E-suite verifierad.

| ID | Punkt | Status |
|----|-------|--------|
| E1 | WP-admin E2E-fixar (specs) | ✅ verifierat |
| E0 | Windows/Docker E2E-dokumentation | ✅ |
| C6–C9 | Focus, tokens, PageHeader, print/surface | ✅ |

---

## Vue — kodkvalitet och teknisk skuld

**Källa:** kodgranskning 2026-06-13 (`frontend/vue/src/`). **V1–V9 klart** (2026-06-13).

| ID | Punkt | Status |
|----|-------|--------|
| V1 | Admin — enhetlig felhantering vid mutationer (`useAdminMutation`) | ✅ |
| V2 | ESLint i Vue-gate (`npm run lint` i `check`) | ✅ |
| V3 | Dela stora composables (`useStationsRoutesData`; `useTimetableEditorPage` mutation-wrap) | ✅ delvis |
| V4 | A11y — feedback-dialog (aria-labelledby, Escape, fokusfälla) | ✅ |
| V5 | A11y — combobox (tangentbord, aria-activedescendant) | ✅ |
| V6 | A11y — admin i e2e (`a11y-smoke` dashboard) | ✅ |
| V7 | Stabila `:key` i wizard (`connectionListKey`) | ✅ |
| V8 | i18n — hårdkodade strängar (feedback legend/close, MrtAsyncState/MrtCalendarNav) | ✅ |
| V9 | Admin scaffold — DRY (`useAdminListEditor`) | ✅ |

---

## Refactor — uppföljning efter Jesper omgång 4 (2026-06-13)

| ID | Punkt | Status |
|----|-------|--------|
| R-J2 | Timeline CSS: tokens på panel, layout i `MrtTimeline` (ej `:deep` nod) | ✅ |
| R-J4 | Delad `parseTimeLabelCaPrefix` (wizard + overview) | ✅ |
| R-T2 | Datumhelpers i `disruption-feed-display.php` | ✅ |
| R-T3 | `useDisruptionFeedItemDetails` + rensa deprecated feed-exports | ✅ |
| R-J3 | Förenkla behov-flaggor i API (`behov_hint`) | ✅ |
| R-J1 | Ca-semantik wizard vs Turvy (beslut D23) + `STOP_TIME_CA.md` | öppen |
| R-J5 | Timeline E2E/screenshot-regression | ✅ |
| R-T1 | Ta bort TS legacy body-fallback när API alltid sätter `detail_intro` | ✅ |
| R-CSS1 | Wizard `:deep` flytta/optimera (summary, trip card, panel tokens) | ✅ |

---

## Trafikinfo UL 1:1 (visuell paritet)

**Plan:** [TRAFFIC_INFO_UL_PLAN.md](TRAFFIC_INFO_UL_PLAN.md) · **Tokens:** [mockups/TRAFFIC_INFO_TOKENS.md](mockups/TRAFFIC_INFO_TOKENS.md) · **Kontext:** J11 v2 feed klar — denna omgång är layout, data, fixtures, acceptans. **Ingen inline styling** — färger endast i `assets/mrt-traffic-info-tokens.css`, BEM `.mrt-tf-*`.

**Rekommenderad ordning:** 0 → A → (B + C parallellt) → D → E → F → H → G.

### Fas 0 — Designreferens & tokens

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-0.1 | Token-spec `mockups/TRAFFIC_INFO_TOKENS.md` | Design | ✅ spec |
| TF-0.2 | `assets/mrt-traffic-info-tokens.css` (`--mrt-tf-*`) | Frontend | ✅ enqueue |
| TF-0.3 | UL-referensbild `mockups/ul-trafikinfo-reference.png` (UL + målbild) | Produkt | öppen |
| TF-0.4 | Side-by-side UL vs snapshot i token-doc | Design | ✅ |

### Fas A — Data & presentation (PHP)

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-A1 | Fält `summary` (korall-text, utan datum) | Backend | ✅ |
| TF-A2 | Fält `validity_label` («Gäller …», separat från rubrik) | Backend | ✅ |
| TF-A3 | Fält `line_label`, `severity`, `category_key`, `category_label`, `icon_key` | Backend | ✅ |
| TF-A4 | Sluta dubblera datum i `headline` när datum visas separat | Backend | ✅ |
| TF-A5 | Svenskt datum i `validity_label` («14 juni», inte `2026-09-03`) | Backend | ✅ |
| TF-A6 | Avvikelse: `summary` = händelse, `line_label` = tågnummer (inte allt i headline) | Backend | ✅ |
| TF-A7 | i18n: «Aktuellt trafikläge», «Planerade avvikelser», giltighet-mall | Backend | ✅ |

### Fas B — API `panels` (hierarki)

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-B1 | `panels[]` med `categories[]` + `counts` (info/warning) | Backend | ✅ |
| TF-B2 | `MRT_disruption_feed_build_panels()` — gruppering Tåg/Buss/Information | Backend | ✅ |
| TF-B3 | TypeScript-typer i `disruptionFeed.ts` | Frontend | ✅ |
| TF-B4 | Legacy `ongoing`/`upcoming` kvar tills noscript migrerat | Backend | ✅ |

### Fas B (valfritt) — schema admin trafikmeddelanden

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-B5 | `valid_from_time` / `valid_to_time` på meddelanden («kl. 10:41 t.o.m. …») | Backend + Admin | öppen (valfritt) |
| TF-B6 | Valfri `headline` + `category` på generella meddelanden | Backend + Admin | öppen (valfritt) |

### Fas C — Vue UI (`MrtTf*`, ingen inline CSS)

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-C1 | `traffic-info-layout.css` — BEM layout, inga färger | Frontend | ✅ |
| TF-C2 | Importera `mrt-traffic-info-tokens.css` i trafikinfo-chunk | Frontend | ✅ enqueue |
| TF-C3 | `MrtTfPanels` → `Panel` → `CategoryRow` → `AlertList` → `AlertCard` | Frontend | ✅ |
| TF-C4 | Primitiver: `MrtTfCountBadge`, `MrtTfLineBadge`, ikoner (klocka, kalender, varning) | Frontend | ✅ |
| TF-C5 | `MrtTfCountBadge` (UL pill); `MrtInfoMark` finns för wizard — **ej** trafikbadge än | Frontend | ✅ |
| TF-C6 | State: `expandedCategoryKey`; valfritt `expandedAlertId` | Frontend | ✅ |
| TF-C7 | Deprecate: `MrtDisruptionFeedSections`, `MrtDisruptionFeedItemCard`, «Mer information» | Frontend | ✅ |
| TF-C8 | Edge: tom `ongoing`, bara `upcoming`; mobil radbrytning giltighet | Frontend | ✅ |
| TF-C9 | DoD: `grep style=` i `traffic-notices/` → tomt | Frontend | ✅ |
| TF-C10 | Uppdatera [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md) med `MrtTf*` | Frontend | ✅ |

### Fas D — Noscript (shortcode)

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-D1 | `shortcode.php` — samma BEM `.mrt-tf-*` som Vue | Backend | ✅ |
| TF-D2 | Inga `style=` i noscript-HTML | Backend | ✅ |
| TF-D3 | Ta bort legacy `mrt-traffic-notices__feed-item` i noscript | Backend | ✅ |

### Fas E — Admin

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-E1 | `TrafficNoticesFeedPreview` — samma `MrtTf*` som publik vy | Admin FE | ✅ |
| TF-E2 | Operatörguide i admin-hjälp (första rad = meddelande, datum automatiskt) | Produkt + Admin | ✅ |

### Fas F — Test

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-F1 | PHPUnit: `panels`, counts, `summary`/`validity_label` | Test | ✅ |
| TF-F2 | Vitest: badge-räkning, severity, gruppering; städa **R-T1** legacy body | Test | ✅ panels |
| TF-F3 | E2E: expand kategori → alert synlig (UL-flöde) | Test | ✅ |
| TF-F4 | Playwright screenshots desktop + mobil (`traffic-notices-ul-*.png`) | Test | ✅ |
| TF-F5 | UL-lik fixture `e2e/fixtures/traffic-notices-payload.mjs` (`panels`, Tåg+Buss, upcoming) | Test | ✅ |
| TF-F6 | `traffic-demo-data.php` — texter via nya fält efter TF-A | Backend + Test | ✅ |

### Fas H — Slutpolish (output på test3)

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-H1 | Enqueue `mrt-traffic-info-tokens.css` — mönster `brand-tokens.php` / hook i `vue-frontend.php` + shortcode | Backend | ✅ |
| TF-H2 | Verifiera `alignwide` (`vue-mount-layout.php`) + `MrtPublicAppShell` på Lennakatten-tema (test3) | Test + Produkt | manuell — se [TRAFFIC_INFO_ACCEPTANCE.md](TRAFFIC_INFO_ACCEPTANCE.md) |

### Fas G — Acceptans

| ID | Punkt | Ansvar | Status |
|----|-------|--------|--------|
| TF-G1 | Jesper-checklista §14 i planen (8 punkter, mobil + desktop) | Produkt | manuell — [TRAFFIC_INFO_ACCEPTANCE.md](TRAFFIC_INFO_ACCEPTANCE.md) |
| TF-G2 | Jesper OK på UL 1:1 målbild (uppföljning J11) | Produkt | manuell — [TRAFFIC_INFO_ACCEPTANCE.md](TRAFFIC_INFO_ACCEPTANCE.md) |

**Nästa steg (acceptans):** Manuell TF-H2 + TF-G på test3 med Jesper. Kod + E2E klart (2026-06-15).

---

## Reseplanerare — copy & biljettinfo (Jesper omgång 3)

**Källa:** [feedback/2026-06-11-jesper-reseplanerare.md](feedback/2026-06-11-jesper-reseplanerare.md)  
**Status:** J16–J18 ✅ (2026-06-11). J15 (admin-redigerbar biljettcopy) öppen.

| ID | Punkt | Insats |
|----|-------|--------|
| J16 | Enklare zonförklaring + pensionär separat rad | ✅ |
| J17 | CTA «Mer information om biljettköp» | ✅ |
| J18 | Dela-knapp (Web Share) | ✅ |

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

| ID | Punkt | Referens |
|----|-------|----------|
| TF-G2 | Jesper OK på UL 1:1 visuell målbild (uppföljning J11) | [TRAFFIC_INFO_UL_PLAN.md](TRAFFIC_INFO_UL_PLAN.md), [svar till Jesper](feedback/2026-06-11-svar-till-jesper.md) |

D16 / J11 v2-feed har beslutad riktning i [TRAFFIC_DISRUPTIONS_PLAN.md](TRAFFIC_DISRUPTIONS_PLAN.md).

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
| Trafikinfo UL 1:1 — plan, token-spec, CSS-fil (ej UI-implementation) | 2026-06-14 | [TRAFFIC_INFO_UL_PLAN.md](TRAFFIC_INFO_UL_PLAN.md), [TRAFFIC_INFO_TOKENS.md](mockups/TRAFFIC_INFO_TOKENS.md), `assets/mrt-traffic-info-tokens.css`, backlog **TF-*** i [TODO.md](TODO.md) |
| Trafikinfo UL 1:1 — implementation + visual regression (TF-A–H, F4) | 2026-06-15 | `MrtTf*`, `traffic-notices-ul-layout.spec.ts`, snapshots `e2e/traffic-notices-ul-layout.spec.ts-snapshots/` |
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
| Docker/skript — Fas 1 D1–D2 (docs → skript, bash `-Build`) | 2026-06-12 | [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) |
| Docker/skript — Fas 1 D3–D4 (timings, npm/vendor-logg) | 2026-06-12 | [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) |
| Docker/skript — Fas 1 D5–D7 (csv-package, host npm ci, smoke-URL:er) | 2026-06-12 | [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) |
| Docker/skript — Fas 2 P1–P4 (volumes, tools-image, coverage) | 2026-06-12 | [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) |
| Docker/skript — Fas 2 P5 (wpcli exec sidecar) | 2026-06-12 | [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) |
| Docker/skript — Fas 2 P6 (tools-shell exec) | 2026-06-12 | [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) |
| Docker/skript — Fas 2 P7 (compose watch overlay) | 2026-06-12 | `docker-compose.watch.yml`, `scripts/docker-watch.ps1` |
| Docker/skript — Fas 3 S1–S5 (bash gates, setup-dev, devcontainer, init poll) | 2026-06-12 | [CI_AND_DEV_MODEL.md](CI_AND_DEV_MODEL.md), `.devcontainer/` |
| Docker/skript — Fas 3 S2 (`mrt` CLI) | 2026-06-12 | `scripts/mrt.ps1`, `scripts/mrt.sh` |
| Docker/skript — scripts-organisation (gate, php, csv, i18n, dev, release) | 2026-06-12 | [scripts/README.md](../scripts/README.md) |
| Reseplanerare J15–J17 + D19–D21 — biljettcopy (fotnoter, station, eftermiddag) | 2026-06-11 | `ticket-copy.php`, `PricesTicketCopyPanel.vue`, `MrtPriceTable.vue`, `#/prices` + stationfält |
| CSS refaktor R0–R5 (encapsulation, layout-split, admin, style budget) | 2026-06-12 | [CSS_REFACTOR_PLAN.md](CSS_REFACTOR_PLAN.md), commits `de3e3bd`–`6b8c2c1` |
| CSS ansvar C1–C5 (StepPanel, focus ring, admin mobil shell, month calendar, docs/tokens) | 2026-06-13 | [CSS_RESPONSIBILITY_PLAN.md](CSS_RESPONSIBILITY_PLAN.md) |
| CSS uppföljning C6–C9 + E0–E1 (tokens, PageHeader, E2E-fixar, print/surface) | 2026-06-13 | [CSS_FOLLOWUP_PLAN.md](CSS_FOLLOWUP_PLAN.md) |
| Vue — kodkvalitet V1–V9 (mutation errors, ESLint, a11y, i18n, admin scaffold) | 2026-06-13 | `useAdminMutation`, `useAdminListEditor`, `eslint.config.js` |

---

## Docker och utvecklarverktyg (Fas 1–3)

**Plan:** [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) — **Fas 0–3 klara** (2026-06-12).
