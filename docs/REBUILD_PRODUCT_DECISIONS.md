# Produktbeslut (rebuild)

Beslut för MVP enligt [REBUILD_SKETCH.md](REBUILD_SKETCH.md). Uppdatera vid ändring.

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

## 8. Öppna punkter (ej blockerande)

- Wizard finpolish mot PNG i `docs/mockups/` när designfiler finns.
- [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md) – manuell WCAG-genomgång före ”live”.
