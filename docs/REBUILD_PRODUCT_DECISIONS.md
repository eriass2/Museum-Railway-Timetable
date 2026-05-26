# Produktbeslut (rebuild)

Beslut för MVP enligt [REBUILD_SKETCH.md](REBUILD_SKETCH.md). Uppdatera vid ändring.

**Datum:** 2026-05 (arbete på `main`, stegvis i nuvarande plugin)

---

## 1. `[museum_journey_planner]` vs wizard

**Beslut:** Behåll **båda**.

- **`[museum_journey_wizard]`** – primärt publikt bokningsflöde (mockup-led).
- **`[museum_journey_planner]`** – enkel sökform + resultattabell (legacy/”snabb sök”), kvar på demosidan tills vidare.

**Motivering:** Låg underhållskostnad; användbart för enkel integration och test. Ta bort planner först när produkt uttryckligen vill wizard-only.

---

## 2. Månadsvy som shortcode

**Beslut:** Behåll **fristående** `[museum_timetable_month]`.

- Används för trafikdagar och klick till dags tidtabell.
- Wizard har egen kalender för resval; månadsvyn är **inte** ersatt av wizard.

---

## 3. Admin: import vs manuell inmatning

**Beslut:** **Import + manuell korrigering.**

- Lennakatten-import (`inc/admin/tools/import-lennakatten.php`) för test/demo-data.
- Meta boxes kvar för stationer, rutter, tidtabeller, turer, stopptider.

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
| `[museum_journey_wizard]` | Behåll – primär resa |
| `[museum_journey_planner]` | Behåll – enkel sök |
| `[museum_timetable_month]` | Behåll – månad/kalender |

---

## 6. Buss Selknä* ↔ Fjällnora* (reseplanerare)

**Beslut:** Modellera som **egen bussrutt** (`Selknä – Fjällnora` / retur) med turer typ `Buss` i importen (`reference-data.php`).

- Resesök hittar **direkt buss** Selknä–Fjällnora och **byte** (tåg till Selknä + buss till Fjällnora).
- Tider i importen är **anslutningstider** (~8 min, avgång efter tåg vid Selknä) – justera mot PDF vid behov.
- Tidtabellsöversikt visar inte längre raden ”INGA BUSSANSLUTNINGAR…” (ersatt av riktiga bussturer).
- **Byte:** min 5 min, max **120 min** väntetid (`mrt_min_transfer_minutes` / `mrt_max_transfer_minutes`). Bytesstationer med **Bus stop marker** (t.ex. Selknä) prioriteras före andra hållplatser på samma resa.

**Efter ändring:** Kör **Import Lennakatten** igen (eller rensa + import) så rutter/turer skapas i databasen.

## 7. Utvecklingsläge vs live

**Beslut:** Verktyg för testdata, clear DB, demosidor och front-meny styrs av `MRT_is_development_mode()` (`WP_DEBUG` eller `MRT_DEVELOPMENT`).

- **Utveckling:** import Lennakatten, development tools på dashboard, *Set up development menu*.
- **Live:** shortcodes, CPT, inställningar, manuell data – inga automatiska smoke-menyer.

Se [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).

---

## 8. Öppna punkter (ej blockerande)

- Wizard finpolish mot PNG i `docs/mockups/` när designfiler finns.
- Planner wizard-only: om beslut tas, uppdatera demosida och SHORTCODES.md.
- [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md) – manuell WCAG-genomgång före ”live”.
