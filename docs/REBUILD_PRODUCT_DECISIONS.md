# Produktbeslut (rebuild)

Beslut för MVP och produktinriktning. Uppdatera vid ändring.

**Datum:** 2026-05 (arbete på `main`, stegvis i nuvarande plugin)

---

## 1. Publikt reseflöde

**Beslut:** Endast **`[museum_journey_wizard]`**.

- Flerstegsflöde enligt mockup (rutt, datum, utresa, retur, priser).
- Den tidigare enkelsidiga shortcoden `[museum_journey_planner]` är **borttagen** (ej live ännu).

---

## 2. Månadsvy som shortcode

**Beslut:** Behåll **fristående** `[museum_timetable_month]`.

- Används för trafikdagar och klick till dags tidtabell.
- Wizard har egen kalender för resval; månadsvyn är **inte** ersatt av wizard.

---

## 3. Admin: import vs manuell inmatning

**Beslut:** **Import + Vue-admin för korrigering** (ersätter CPT/meta boxes).

- Lennakatten-import och CSV kvar; dev-verktyg enligt [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).
- Vue-admin (klar 2026-05): [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md), REST enligt [REST_API.md](REST_API.md).

---

## 3b. API: REST, ingen AJAX

**Beslut:** All klient–server-kommunikation via **WordPress REST API**. AJAX-lagret är borttaget (2026-05).

- Policy och route-tabell: [REST_API.md](REST_API.md).

---

## 4. Ny struktur: stegvis eller parallell `inc-next/`

**Beslut:** **Stegvis i nuvarande plugin** (`inc/domain/`, `inc/admin/`, `inc/infrastructure/`, `inc/public/`).

- Ingen parallell `inc-next/`.
- Legacy loaders borttagna; bootstrap laddar domän direkt.

---

## 5. Övriga MVP-shortcodes

| Shortcode | Status |
|-----------|--------|
| `[museum_timetable_overview]` | Behåll – tryckt tidtabellsöversikt |
| `[museum_journey_wizard]` | Behåll – enda publika resa |
| `[museum_timetable_month]` | Behåll – månad/kalender |

---

## 6. Buss Selknä* ↔ Fjällnora* (reseplanerare)

**Beslut:** Modellera som **egen bussrutt** (`Selknä – Fjällnora` / retur) med turer typ `Buss` i Lennakatten CSV-fixturen (`testdata/fixtures/lennakatten/`).

Se [DATA_MODEL.md](DATA_MODEL.md) och import-PDF.

---

## 7. Utvecklingsverktyg

**Beslut:** Dev-only via `MRT_is_development_mode()` / `WP_DEBUG`.

- Component demo (3 block), wizard smoke test, Lennakatten-import, clear DB.
- Se [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).

---

## 9. Admin Vue (2026-05)

**Beslut:** Vue **ersätter** WordPress CPT-admin. **Status: implementerat** (dashboard, tidtabeller, stationer/rutter, priser, import/export, dev-verktyg).

| Punkt | Värde |
|-------|--------|
| Arbetsflöde | [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) |
| API | [REST_API.md](REST_API.md) |
| Routing | Hash (`#/dashboard`) under `admin.php?page=mrt_app` |
| Behörighet | `manage_options` fullt; `edit_posts` begränsat (avvikelser, en avgångstid) |
| Språk | Svenska primärt |
| E2E | Playwright (CI + `frontend/vue/e2e/`) |

---

## 8. Öppna punkter (ej blockerande)

- Wizard finpolish mot PNG i `docs/mockups/` när designfiler finns.
- [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md) – manuell WCAG-genomgång före ”live”.

---

## 10. Resesökmotor (journey engine, 2026-06)

**Beslut:** Ny BFS-motor i `inc/domain/journey/engine/` bakom oförändrat REST-API.

| Punkt | Värde |
|-------|--------|
| Max antal byten | **2** (3 ben). Filter: `mrt_journey_max_transfers`. |
| Progressiv djupning | Kalender: direkt först, sedan 1 byte, sedan 2. Wizard: alla träffar upp till max. |
| Bytesstation | Hub (terminus, buss-*, `mrt_transfer_priority`) vid mellanliggande byte; slutstation kräver inte hub. |
| Regler | Befintliga: pickup/dropoff, riktning mot mål, min/max bytestid, overshoot-filter. |
| Avvikelser i poäng | **Ej ännu** — TODO kvar i `journey-scoring.php`. |
| Presentation | Befintlig normalisering, wizard-filter och sortering; `legs[]` stöder N ben. |

---

## 11. Multi-operator (2026-06)

**Beslut:** Pluginet ska kunna användas av **andra föreningar** utan Lennakatten i runtime. Lennakatten är **referensoperatör** — dev-import, fixture, designreferens — inte default vid tom databas.

| Punkt | Värde |
|-------|--------|
| Dataplattform | Redan generisk: CSV + Vue-admin + REST |
| Problem idag | Branding, builtin-priser, titel-fallbacks (zoner, tågbyte), affärsregler utan UI |
| Tågbyte (2026-06) | Per-station meta `mrt_station_train_change_map` — **delvis**; REST/admin/CSV saknas; Marielund-default kvar i kod |
| Plan och status | [MULTI_OPERATOR.md](MULTI_OPERATOR.md) (Tier A → B) |
| Tier A (2026-06) | **Klart:** A1–A6 (neutral defaults, onboarding, brand pack) |
| Tier B | **B1 klart:** train change admin/REST/CSV. Kvar: B2–B5 |

**Princip:** *Fail empty* — saknas operatörsdata ska systemet vara neutralt eller varna, inte gissa Lennakatten.
