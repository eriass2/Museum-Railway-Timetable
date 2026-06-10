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
| 1 | HH:MM | `Ca 10.13` (Ca direkt före siffrorna) |
| — | ingen tid, P+A | `X` (behovsuppehåll) |

**P/A** styr fortfarande på-/avstigningsrestriktioner och fotnoter (P enbart, A enbart, X utan tid). **Ca** styrs enbart av `approximate_time`.

## P/A vid ändhållplatser

| Position i turen / resebenet | P (enbart påstigning) | A (enbart avstigning) |
|------------------------------|----------------------|------------------------|
| **Start** (Från / första stopp) | Döljs | Visas om relevant |
| **Mellan** | Visas om relevant | Visas om relevant |
| **Slut** (Till / sista stopp) | Visas om relevant | Döljs som **prefix** i cell |

Gäller **P/A-prefix** i tidtabellsöversikt (`from`/`to`-rader). I reseplaneraren visas fortfarande **A-fotnot** vid sista stoppet när hållplatsen är behovsuppehåll endast avstigande (t.ex. Selknä på rälsbuss — säg till konduktören). Underliggande `pickup_allowed` / `dropoff_allowed` ändras inte.

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

I reseplaneraren och tidtabellsöversikt visas Ca **direkt före** klockslaget (`Ca 10.13`, eller `P`/`A` före och `Ca 10.13` efter). Ca ska aldrig separeras visuellt från tidssiffrorna.

Referens: `testdata/reference-pdfs/Anslagstidtabell-2026.pdf`, se [testdata/reference-pdfs/README.md](../testdata/reference-pdfs/README.md).

## Relaterat

- [DATA_MODEL.md](DATA_MODEL.md) § stop times
- [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md) — **skiss:** tidtabell B + anslag, schema v3 (`pickup_mode` / `dropoff_mode`)
- Jesper J4 — Ca vid hållplatser utan exakt tid i tjänstetidtabell
- Validering 2026-06-10 — Marielund ska inte ha Ca; felaktig härledning från P+A borttagen
