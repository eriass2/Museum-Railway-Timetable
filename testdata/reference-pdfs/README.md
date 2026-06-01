# Referens-PDF:er för testdata

Officiella tidtabellsunderlag (Lennakatten) som källa när testdata eller import ska verifieras mot tryckta tabeller.

## Primär källa 2026

| Fil | Beskrivning |
|-----|-------------|
| **`Anslagstidtabell-2026.pdf`** | Samlad anslagstidtabell 2026: trafikdagkalender + GRÖN, RÖD, GUL, ORANGE m.m. |

Trafikdagsregler enligt anslagstidtabellen (sammanfattning):

| Tidtabell | Trafikdagar 2026 |
|-----------|------------------|
| **GRÖN** | Lördagar 30/5–26/9 (utom 20–27/6 och 12/9), onsdagar och torsdagar 1/7–6/8 |
| **GUL** | Fredagar 29/5–25/9 (utom 19/6) |
| **RÖD** | Söndagar 5/7–16/8 |
| **ORANGE** | Fredagar 3/7–7/8 (kombineras med GUL i kalendern) |

CSV-fixturen (`testdata/fixtures/lennakatten/`) importerar **GRÖN** (tåg), **GRÖN ANSLUTNINGSBUSS**, **GUL**, **GUL ANSLUTNINGSBUSS**, **RÖD** och **ORANGE** enligt tabellen ovan.

| Fixture-kod | PDF-motsvarighet |
|---------------|------------------|
| `green` | GRÖN tidtabell – **lördagar** (`Gron-tidtabell-lor.pdf` / blad A) |
| `green-vard` | GRÖN tidtabell – **onsdagar och torsdagar** 1/7–6/8 (`Gron-tdt-buss-vard.pdf`, blad B) |
| `green-buss` | Anslutningsbussar Fjällnora 1/7–16/8 (på gröna dagar i fönstret) |
| `yellow` | GUL tidtabell – fredagar |
| `yellow-buss` | Gul anslutningsbuss på fredagar i samma sommarfönster |
| `red` | RÖD tidtabell – söndagar 5/7–16/8 |
| `orange` | ORANGE tidtabell – extra tåg fre 3/7–7/8 (utöver GUL) |

På **ons/tors** gäller `green-vard` (tåg) tillsammans med `green-buss` när anslutningar kör – samma innehåll som `Gron-tdt-buss-vard.pdf`. Lördagar utan buss använder bara `green`; lördagar med buss använder `green` + `green-buss`.

## Detaljtidtabeller (tidigare referenser)

| Fil | Beskrivning |
|-----|-------------|
| `Gron-tdt-buss-vard.pdf` | Grön tidtabell, ons/tors sommar (med bussanslutningar; blad B) |
| `Gron-tidtabell-lor.pdf` | Grön tidtabell, lördag |
| `Gul-tidtabell-fre.pdf` | Gul tidtabell, fredag |
| `Tidtabellsboken-del-B.pdf` | Tidtabellsboken del B |

Används för att verifiera tågnummer, hållplatser och stopptider. Tider i `stoptimes.csv` är i första hand avstämda mot lördags-/fredagstabellerna och kontrollerade mot `Anslagstidtabell-2026.pdf` (t.ex. tåg 71/70 GRÖN, 72/76 GUL).

## Vid uppdatering

1. Lägg ny PDF här och uppdatera denna README.
2. Jämför `timetable_dates.csv` mot kalenderreglerna i anslagstidtabellen.
3. Stickprov mot `stoptimes.csv` / `services.csv` om tider ändrats — **inklusive P/X-symboler** (`pickup_allowed` / `dropoff_allowed`).
4. Kör verifiering mot referensresor:

```sh
python scripts/sync-lennakatten-green-yellow.py
python scripts/generate-lennakatten-extra-timetables.py
python scripts/verify-lennakatten-vs-pdf.py
composer csv:validate -- testdata/fixtures/lennakatten
composer test -- tests/Unit/LennakattenJourneySearchTest.php tests/Unit/CsvFixtureTest.php
```

Kanonisk GRÖN/GUL/RÖD/ORANGE-data: `scripts/lennakatten_anslag_tables.py` (tåg + anslutningsbussar B1–B8).

**Bussar:** Selknä*/Fjällnora*-tider från PDF; Uppsala-ben (+28 / −27 min) beräknas enligt etablerad offset (ej i huvudtabellen). B5 (in) saknas i anslagstavlan – oförändrad tills `Gron-tdt-buss-vard.pdf` verifieras. **GUL buss:** bara B1 ut (17:22 Selknä) och B3 in (16:33 Uppsala) — inga kvällsbussar efter ~18:00.

### Referensresor (automatiskt stickprov)

| Tidtabell | Tåg | Sträcka | Datum i test |
|-----------|-----|---------|--------------|
| GRÖN | 71 | Uppsala Östra → Marielund 10:00–10:35 | 2026-06-06 |
| RÖD | 81 | Uppsala Östra → Marielund 10:00–10:35 | 2026-07-05 |
| ORANGE | 73 | Uppsala Östra → Marielund 11:15–11:47 | (CSV-tider) |

P/X-regler: `docs/CSV_FORMAT.md` och `scripts/lennakatten_symbols.py`.
