# Stopptider schema v3 — implementationsplan

**Status:** Fas 1 klar (2026-06-10)  
**Beslut:** [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md)

## Faser (körordning)

| Steg | Innehåll | Filer |
|------|----------|-------|
| 1 | Kärnmodell + DB v3 | `stop-time-modes.php`, `db-schema.php` |
| 2 | CSV import/export/validate | `schema.php`, `import-entities-services.php`, `exporter-entities.php`, `validate-references.php` |
| 3 | Persist + display + journey | `stoptimes-persist.php`, `stop-time-display.php`, `constraints.php`, `journey-detail.php`, `stop-time-wizard-display.php` |
| 4 | REST + editor + overview | `stop-times.php`, `route-stoptimes-editor.php`, `overview-rail-cells.php` |
| 5 | Vue admin (effective modes) | `types.ts`, `stopTimesPayload.ts`, `StopTimesEditor.vue`, grid edit |
| 6 | Fixture sync | `lennakatten_symbols.py`, `stoptimes.csv` |
| 7 | Tester + `.\scripts\check.ps1` | PHPUnit + Vitest |

## Tekniska regler (implementerade i kod)

- **Effective:** `max(avg, ank)` per riktning (`none` < `scheduled` < `on_request`).
- **Resesök:** `allows_*` = effective ≠ `none` (både `scheduled` och `on_request` giltiga).
- **Visning P/A:** prefix/fotnot endast vid `on_request`.
- **`in_service_timetable = 0`:** tvingar `approximate_time = 1`.
- **Admin:** förenklad redigering via `pickup_mode` / `dropoff_mode` → expanderas till fyra kolumner vid spar.
- **Anslutningsbuss:** `in_service_timetable = 0` på hel tur i CSV-sync.

## Verifiering

- Tur **71** GRÖN: tider, modes, Ca
- Buss B1: Ca på alla stopp, P på Selknä
- `.\scripts\check.ps1 -Vue`
