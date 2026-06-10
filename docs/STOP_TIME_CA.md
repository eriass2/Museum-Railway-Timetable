# Stopptider — Ca (ungefärlig tid)

**Datum:** 2026-06-10  
**Beslut:** Explicit fält `approximate_time` i stället för att härleda Ca från P/A-kryssrutor.

## Fält

| Kolumn | Typ | Standard | Betydelse |
|--------|-----|----------|-----------|
| `approximate_time` | 0\|1 | 0 | Visa **Ca** före tiden i reseplaneraren och tidtabellsöversikt |

Lagras i `{prefix}_mrt_stoptimes` tillsammans med `arrival_time`, `departure_time`, `pickup_allowed`, `dropoff_allowed`.

## Regler

| `approximate_time` | Tid | Wizard / översikt |
|--------------------|-----|-------------------|
| 0 | HH:MM | `10.35` |
| 1 | HH:MM | `Ca 10.13` |
| — | ingen tid, P+A | `X` (behovsuppehåll) |

**P/A** styr fortfarande på-/avstigningsrestriktioner och fotnoter (P enbart, A enbart, X utan tid). **Ca** styrs enbart av `approximate_time`.

## Slutstopp i reseben

Sista stoppet i ett reseben visar **ankomsttid** (`arrival_time`), inte avgång — t.ex. Marielund 10:35 för tåg 71.

## Admin

- **Stopptider-tabell:** kolumn **Ca**
- **Turvy (grid):** checkbox *Ca — ungefärlig tid i reseplaneraren* i celldialog

## CSV

Se [CSV_FORMAT.md](CSV_FORMAT.md) § stoptimes — valfri kolumn `approximate_time` (default 0).

## Anslagstidtabell (PDF / tryckt anslagstavla)

Vid avskrift från Lennakatten-anslagstidtabellen gäller typografi, inte bara P/X-symboler:

| Utseende i PDF | Betydelse | `approximate_time` |
|----------------|-----------|---------------------|
| **Fet** tid | Fast tid | **0** |
| Normal vikt | Ungefärlig tid (Ca) | **1** |

Stationer är också fetstil i PDF — det gäller **inte** stopptiderna. Titta på själva tidssiffrorna.

I reseplaneraren visas Ca som prefix (`Ca 10.13`). I digital tidtabellsöversikt kan samma skillnad speglas med fet vs normal vikt (ej implementerat ännu).

Referens: `testdata/reference-pdfs/Anslagstidtabell-2026.pdf`, se [testdata/reference-pdfs/README.md](../testdata/reference-pdfs/README.md).

## Relaterat

- [DATA_MODEL.md](DATA_MODEL.md) § stop times
- Jesper J4 — Ca vid hållplatser utan exakt tid i tjänstetidtabell
- Validering 2026-06-10 — Marielund ska inte ha Ca; felaktig härledning från P+A borttagen
