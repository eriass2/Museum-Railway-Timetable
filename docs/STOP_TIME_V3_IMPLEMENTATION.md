# Stopptider schema v3 — implementationsplan

**Status:** Fas 2 klar (2026-06-10) — se [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md)  
**Beslut:** [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md)

## Faser (körordning)

| Steg | Innehåll | Status |
|------|----------|--------|
| 1 | Kärnmodell + DB v3 | ✓ |
| 2 | B-PDF → fixture (modes/tider) | ✓ `lennakatten_b_pdf.py`, `sync-lennakatten-rail-fixture.py` |
| 3 | Anslag-overlay (Ca + `in_service_timetable`) | Kvar |

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
