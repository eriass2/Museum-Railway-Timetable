# Admin: linjer istället för rutter — plan

**Status:** Fas A–B påbörjad (2026-06-11)  
**Relaterat:** [LINES_REFACTOR_PLAN.md](LINES_REFACTOR_PLAN.md) Fas 4 / D8

## Problem

Domänen använder **4 linjer** (`main`, `fjallnora`, `linnes-marielund`, `linnes-uppsala`), men admin **Stationer & rutter → Rutter** visar **7 legacy-rutter** (dubbelriktade import-alias). Det förvirrar operatörer.

## Mål

| Lager | Mål |
|-------|-----|
| **Operatör** | Se 4 linjer med stationer, knutpunkt och typ — inte 7 rutter |
| **Turvy** | Redan linje + riktning (Fas 4) |
| **Import** | `lines.csv` fortsätter vara sanning; `routes.csv` alias tills full migrering |
| **Dev** | Legacy-rutter synliga under utvecklingsläge (import/debug) |

## Faser

### Fas A — REST + domän (denna leverans)

- [x] `GET /lines` — lista från `mrt_line_registry` med `station_ids`
- [x] Delad formatter `MRT_rest_format_lines_list()`
- [x] Turvy `line_options` återanvänder formatter

### Fas B — Admin Vue (denna leverans)

- [x] Flik **Linjer** när registry inte är tom
- [x] Read-only tabell: namn, typ, knutpunkt, stationer (RoutePreview)
- [x] Hjälptext: linjer styrs via CSV-import
- [x] **Legacy rutter** i hopfällbar sektion (endast `isDevMode`)

### Fas C — senare

- [ ] Redigera linjer i admin (utan CSV)
- [ ] Import skapar färre `mrt_route`-poster (en per linje + riktning härledd)
- [ ] Ta bort Rutter-fliken helt när alla installationer migrerat
- [ ] Sidtitel «Stationer & linjer»

## Acceptance

| Check | Förväntat |
|-------|-----------|
| Lennakatten efter import | Flik **Linjer** med 4 rader |
| Utan `lines.csv` | Flik **Rutter** som idag (bakåtkompatibilitet) |
| Dev-läge | Legacy-rutter hopfällbara under Linjer |
| Turvy | Oförändrat (linje + riktning) |
