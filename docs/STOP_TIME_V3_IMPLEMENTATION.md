# Stopptider schema v3 — implementationsplan

**Status:** Fas 4 implementerad (2026-06-10) — se [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md)  
**Beslut:** [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md), [STOP_TIME_CA.md](STOP_TIME_CA.md)

## Faser (körordning)

| Steg | Innehåll | Status |
|------|----------|--------|
| 1 | Kärnmodell + DB v3 | ✓ |
| 2 | B-PDF → fixture (modes/tider) | ✓ |
| 3 | Anslag-overlay (Ca + `in_service_timetable`) | ✓ (justeras i fas 4) |
| 4 | Ca/visning enligt nya beslut | ✓ |

## Fas 4 — Ca, ○/anslag, visning (2026-06-10)

**Produktbeslut (senaste):**

1. **Ca** endast **mellanstationer utan klockslag i tidtabell B** (plus buss / `in_service = 0`).
2. **Skölsta (71):** `Ca 10.09 X` — anslag **X** + tid; inte `\|`.
3. Cellordning: **Ca före** tid, **P/A/X efter**; Turvy-CSS (station normal storlek).

### Data / sync (Python)

- [ ] `anslag_overlay_flags()` / `approximate_time_for_stop()` — Ca endast om mellanstation **och** saknar B-tid (`has_b_time` flagga)
- [ ] `_overlay_missing_times()` — overlay även ○/pass-through; fyll tider; **modes från anslag** när B har ○
- [ ] Regenerera fixture + uppdatera verify-skript (Skölsta: `approximate_time=1`, modes on_request, tid 10:09)
- [ ] Barby m.fl.: Ca endast där B saknar klockslag

### Visning (PHP)

- [ ] `MRT_format_stop_time_display()` — `Ca` + tid + suffix (P/A/X); inte `P Ca …`
- [ ] Ta bort `\|` när tid + X (on_request båda)

### Visning (Vue + CSS)

- [ ] `overviewTimeDisplay.ts` — Ca före, fotnot efter; stöd **X** som suffix
- [ ] `timetable-overview.css` — fix `--mrt-ov-text-size` på stationskolumn

### Tester

- [ ] Tur **71:** Uppsala Ö `10.00`, Barby `Ca 10.23`, Skölsta `Ca 10.09 X`, Marielund `10.35`
- [ ] Vue + PHP unit tests; dev-reset

## Verifiering (referenstur 71)

| Stopp | Förväntat |
|-------|-----------|
| Uppsala Ö | `10.00` (+ P efter tid på from-rad) |
| Barby | `10.23` (B-tid, ingen Ca) |
| Skölsta | **`Ca 10.09 X`** |
| Marielund | `10.35` (to-rad) |

Buss B1: Ca på alla stopp (hel tur utanför B).

## Relaterat (ej fas 4)

- Sammanslagna Turvy-kolumner (71→61) → [TODO.md](TODO.md)
