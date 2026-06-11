# Admin: linjer istället för rutter — plan

**Status:** Klar för Lennakatten (2026-06-11)  
**Relaterat:** [LINES_REFACTOR_PLAN.md](LINES_REFACTOR_PLAN.md) Fas 4 / D8

## Problem

Domänen använder **4 linjer** (`main`, `fjallnora`, `linnes-marielund`, `linnes-uppsala`), men admin **Stationer & rutter → Rutter** visar **7 legacy-rutter** (dubbelriktade import-alias). Det förvirrar operatörer.

## Mål

| Lager | Mål |
|-------|-----|
| **Operatör** | Se 4 linjer med stationer, knutpunkt och typ — inte 7 rutter |
| **Turvy** | Redan linje + riktning (Fas 4) |
| **Import** | `lines.csv` + `line_stations.csv`; `routes.csv` valfritt (legacy) |
| **Legacy** | Rutter-flik kvar endast utan line registry |

## Faser

### Fas A — REST + domän (denna leverans)

- [x] `GET /lines` — lista från `mrt_line_registry` med `station_ids`
- [x] Delad formatter `MRT_rest_format_lines_list()`
- [x] Turvy `line_options` återanvänder formatter

### Fas B — Admin Vue (denna leverans)

- [x] Flik **Linjer** när registry inte är tom
- [x] Read-only tabell: namn, typ, knutpunkt, stationer (RoutePreview)
- [x] Hjälptext: linjer styrs via CSV-import
- [x] ~~Legacy rutter i dev-läge~~ — borttaget när registry finns (Fas D)

### Fas C — import + redigering ✓

- [x] `PATCH /lines/{code}` — uppdatera visningsnamn i registry
- [x] Admin: redigera linjetitel (stationer fortfarande via CSV)
- [x] Import härleder riktade rutter från `lines.csv` + `line_stations.csv` (`routes.csv` valfritt)
- [x] Sidtitel «Stationer & linjer» när registry finns
- [x] Lennakatten-fixture: `routes.csv` + `route_stations.csv` borttagna (rutter härleds vid import)
### Fas D — städning ✓

- [x] Lennakatten utan `routes.csv` / `route_stations.csv`
- [x] Admin: inga legacy-rutter när line registry finns (ingen dev-disclosure)
- [x] `listRoutes` hämtas bara utan registry (bakåtkompatibilitet)

## Acceptance

| Check | Förväntat |
|-------|-----------|
| Lennakatten efter import | Flik **Linjer** med 4 rader |
| Utan `lines.csv` | Flik **Rutter** som idag (bakåtkompatibilitet) |
| Med line registry | Ingen rutt-flik, ingen legacy-lista |
| Turvy | Oförändrat (linje + riktning) |
