# Referens-PDF:er för testdata

Officiellt tidtabellsunderlag (Lennakatten) för verifiering av testdata och import.

## Källa 2026

| Fil | Beskrivning |
|-----|-------------|
| **`Anslagstidtabell-2026.pdf`** | Samlad anslagstidtabell 2026: trafikdagkalender, GRÖN, RÖD, GUL, ORANGE och anslutningsbussar |

Trafikdagsregler enligt anslagstidtabellen:

| Tidtabell | Trafikdagar 2026 |
|-----------|------------------|
| **GRÖN** | Lördagar 30/5–26/9 (utom 20–27/6 och 12/9), onsdagar och torsdagar 1/7–6/8 |
| **GUL** | Fredagar 29/5–25/9 (utom 19/6) |
| **RÖD** | Söndagar 5/7–16/8 |
| **ORANGE** | Fredagar 3/7–7/8 (kombineras med GUL i kalendern) |
| **GRÖN anslutningsbuss** | 1/7–16/8 på dagar med grön trafik (kalender: ”Bussanslutningar kör”) |

CSV-fixturen (`testdata/fixtures/lennakatten/`) importerar **GRÖN** (tåg), **GRÖN ANSLUTNINGSBUSS**, **GUL**, **RÖD** och **ORANGE**.

| Fixture-kod | PDF-motsvarighet |
|---------------|------------------|
| `green` | GRÖN tidtabell – lördagar |
| `green-vard` | GRÖN tidtabell – onsdagar och torsdagar 1/7–6/8 |
| `green-buss` | Anslutningsbuss Selknä–Fjällnora (blå stjärnrader i GRÖN-tabellen) |
| `yellow` | GUL tidtabell – fredagar |
| `red` | RÖD tidtabell – söndagar 5/7–16/8 |
| `orange` | ORANGE tidtabell – extra tåg fre 3/7–7/8 (utöver GUL) |

På **ons/tors** i sommarfönstret gäller `green-vard` + `green-buss`. På **lördagar** i bussfönstret (1/7–16/8) gäller `green` + `green-buss`; övriga lördagar bara `green`.

## Verifiering

```sh
python scripts/sync-lennakatten-green-yellow.py
python scripts/generate-lennakatten-extra-timetables.py
python scripts/verify-lennakatten-vs-pdf.py
composer csv:validate -- testdata/fixtures/lennakatten
composer test -- tests/Unit/LennakattenJourneySearchTest.php tests/Unit/CsvFixtureTest.php
```

Kanonisk data:

| Innehåll | Modul |
|----------|--------|
| Stopptider GRÖN/GUL/RÖD/ORANGE + GRÖN-buss | `scripts/lennakatten_anslag_tables.py` |
| Bussfönster och `green-buss`-datum | `scripts/lennakatten_calendar.py` |
| P/X-symboler | `scripts/lennakatten_symbols.py` |

**Bussar:** Endast GRÖN-tabellens blå stjärnrader (Selknä* / Fjällnora*). Ingen GUL-buss i PDF.

### Referensresor (automatiskt stickprov)

| Tidtabell | Tåg | Sträcka | Datum i test |
|-----------|-----|---------|--------------|
| GRÖN | 71 | Uppsala Östra → Marielund 10:00–10:35 | 2026-06-06 |
| RÖD | 81 | Uppsala Östra → Marielund 10:00–10:35 | 2026-07-05 |
| ORANGE | 73 | Uppsala Östra → Marielund 11:15–11:47 | (CSV-tider) |

P/X-regler och Ca (fet = fast tid, normal vikt = ungefärlig): `docs/CSV_FORMAT.md`, `docs/STOP_TIME_CA.md`.
