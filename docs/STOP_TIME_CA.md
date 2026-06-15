# Stopptider — Ca (ungefärlig tid)

**Datum:** 2026-06-10 (uppdaterad fas 4)  
**Beslut:** Explicit fält `approximate_time`. Programmet tolkar **inte** fet/normal typografi från anslag-PDF.

## Fält

| Kolumn | Typ | Standard | Betydelse |
|--------|-----|----------|-----------|
| `approximate_time` | 0\|1 | 0 | Visa **Ca** före klockslaget i reseplaneraren och tidtabellsöversikt |

Lagras i `{prefix}_mrt_stoptimes` tillsammans med tider och fyra mode-fält (schema v3).

## Regler

| `approximate_time` | Tid | Wizard / översikt |
|--------------------|-----|-------------------|
| 0 | HH:MM | `10.35` |
| 1 | HH:MM | `Ca 10.13` (Ca direkt före siffrorna) |
| — | ingen tid, P+A | `X` (behovsuppehåll) |

**P/A/X** styrs av modes/fotnoter. **Ca** styrs enbart av `approximate_time`.

## När sätts Ca? (sync)

| Stopp | Ca? |
|-------|-----|
| **Start/slut** med klockslag i **tidtabell B** | **Nej** (`0`) |
| **Mellanstation** med klockslag i **B** | **Nej** (`0`) |
| **Mellanstation utan** klockslag i **B**, tid från anslag | **Ja** (`1`) |
| Stopp saknas i B (`in_service_timetable = 0`) | **Ja** (`1`) |
| **Anslutningsbuss** (hel tur utanför B) | **Ja** (`1`) på alla stopp |

**Fet text** i anslag-PDF används **inte**.

Se [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md) §5 och beslut #14.

## ○ i B + anslag (Skölsta, tur 71)

- **B:** ○ — inga klockslag i tabell B (mellanstation).
- **Anslag:** **10:09** + symbol **X**.
- **Data:** tider + `approximate_time = 1` + modes från anslag (**on_request** / **X**).
- **Visning:** **`Ca 10.09 X`** — **inte** `\|`.

`\|` gäller fortfarande när **ingen tid** finns och inget trafikutbyte (○ utan anslagstid).

## Cellordning och typografi

| Del | Placering | Storlek |
|-----|-----------|---------|
| **Ca** | Direkt **före** klockslaget | Liten |
| **Klockslag** | Efter Ca (om Ca) | Normal i tidcellen |
| **P / A / X** | **Efter** klockslaget | Liten (superscript-lik) |
| **Stationsnamn** (Turvy) | Stationskolumn | **Normal** |

**Mål:** `Ca 10.23 P`, `Ca 10.09 X`, `10.00 P` (start utan Ca).

## P/A vid ändhållplatser

| Position | P | A / X |
|----------|---|-------|
| **Start** (`from`) | Döljs som prefix | Efter tid om relevant |
| **Mellan** | Efter tid | Efter tid |
| **Slut** (`to`) | Efter tid | Döljs som prefix |

## Slutstopp i reseben

Sista stoppet visar **ankomsttid** — t.ex. Marielund **`10.35`** (ingen Ca om tid finns i B).

## Admin

- **Stopptider-tabell:** kolumn **Ca**
- **Turvy (grid):** checkbox *Ca — ungefärlig tid i reseplaneraren*

## CSV

Se [CSV_FORMAT.md](CSV_FORMAT.md) § stoptimes.

## Relaterat

- [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md)
- [STOP_TIME_V3_IMPLEMENTATION.md](STOP_TIME_V3_IMPLEMENTATION.md) — fas 4

## Journey detail API — `behov_hint`

`POST /journey/connection-detail` stoppar exponerar ett fält **`behov_hint`** (`''` | `pickup` | `dropoff` | `both`) i stället för tre booleans. Värden sätts i `MRT_journey_stop_wizard_time_meta()` utifrån passagerarens position i segmentet (ändpunkt vs genomfart). Vue tidslinje och fotnoter läser endast `behov_hint`.
