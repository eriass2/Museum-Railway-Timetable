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
| `green` | GRÖN tidtabell – tåg (lördagar + sommar-ons/tors) |
| `green-buss` | Blå anslutningsbussar (Fjällnora) 1/7–16/8 på gröna dagar |
| `yellow` | GUL tidtabell – fredagar |
| `yellow-buss` | Gul anslutningsbuss på fredagar i samma sommarfönster |
| `red` | RÖD tidtabell – söndagar 5/7–16/8 |
| `orange` | ORANGE tidtabell – extra tåg fre 3/7–7/8 (utöver GUL) |

`Gron-tdt-buss-vard.pdf` (buss ersätter tåg vardag) är **inte** en egen tidtabell i fixturen ännu – den skiljer sig från sommar-anslutningsbussarna.

## Detaljtidtabeller (tidigare referenser)

| Fil | Beskrivning |
|-----|-------------|
| `Gron-tdt-buss-vard.pdf` | Grön tidtabell, buss ersätter tåg vardag |
| `Gron-tidtabell-lor.pdf` | Grön tidtabell, lördag |
| `Gul-tidtabell-fre.pdf` | Gul tidtabell, fredag |
| `Tidtabellsboken-del-B.pdf` | Tidtabellsboken del B |

Används för att verifiera tågnummer, hållplatser och stopptider. Tider i `stoptimes.csv` är i första hand avstämda mot lördags-/fredagstabellerna och kontrollerade mot `Anslagstidtabell-2026.pdf` (t.ex. tåg 71/70 GRÖN, 72/76 GUL).

## Vid uppdatering

1. Lägg ny PDF här och uppdatera denna README.
2. Jämför `timetable_dates.csv` mot kalenderreglerna i anslagstidtabellen.
3. Stickprov mot `stoptimes.csv` / `services.csv` om tider ändrats.
4. Kör `composer csv:validate -- testdata/fixtures/lennakatten` och `composer test`.
