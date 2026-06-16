# Lennakatten CSV fixture

Test/demo timetable data for development (`docker-dev-reset`, Import Lennakatten).

**Source of truth:** edit the CSV files here directly.

**Lines:** `lines.csv` + `line_stations.csv` + `branch_junctions.csv` define `main`, `fjallnora`, `linnes-marielund`, and pattern `linnes-uppsala` (B14). Services use `line_code` only; directed `route_code` is derived at import. PDF corridor for B14 comes from the line registry.

**Reference PDF:** `testdata/reference-pdfs/Anslagstidtabell-2026.pdf` (kalender + alla tidtabeller).

Timetable codes in the CSV package:

| Code | Meaning |
|------|---------|
| `green` | GRÖN rail – Saturdays |
| `green-vard` | GRÖN rail – Wed/Thu summer (1/7–6/8) |
| `green-buss` | GRÖN anslutningsbuss Selknä–Fjällnora (1/7–16/8, gröna trafikdagar) |
| `red-buss` | RÖD anslutningsbuss Marielund–Linnés Hammarby (1/7–16/8, **söndagar**) |
| `yellow` | GUL rail – Fridays |
| `red` / `orange` | RÖD Sundays / ORANGE extra Friday trains |

Regenerate rail blocks after PDF edits: `python scripts/fixtures/lennakatten/generate-lennakatten-extra-timetables.py` (green-vard clone + red/orange).

Sync GRÖN/GUL rail and bus stop times from Anslagstidtabell (splits tågbyte at Marielund into separate services):

```sh
python scripts/fixtures/lennakatten/sync-lennakatten-rail-fixture.py
python scripts/fixtures/lennakatten/generate-lennakatten-extra-timetables.py
```

Verify against Anslagstidtabell:

```sh
python scripts/fixtures/lennakatten/verify-lennakatten-vs-pdf.py
```

Validate after changes:

```sh
composer csv:validate -- testdata/fixtures/lennakatten
composer csv:zip
```

Import zip (same data): `../lennakatten.zip` — regenerate with `composer csv:zip` after CSV edits.

**Linnés Hammarby:** buss B9–B14 under `red-buss` (söndagar) — importguide [docs/LINNES_HAMMARBY.md](../../../docs/LINNES_HAMMARBY.md).

**Also included:** `settings.csv`, `price_schema.csv`, and `prices.csv` (Lennakatten taxa 2026 + operatörsinställningar). `settings.csv` sätter `hero_background_url` till `testdata/images/wizard-hero-bosshus.jpg` (Bosshus-bild i plugin-mappen). See `inc/import/lennakatten/reference-data.php` for the PHP mirror used in PHPUnit.

**Traffic demo (not in CSV):** After import, `MRT_lennakatten_apply_traffic_demo_data()` seeds trafikmeddelanden (B) and tur-avvikelser (A) for manual/PHPUnit testing of the UL-like disruption feed — see `inc/import/lennakatten/traffic-demo-data.php`. Reference date **2026-06-06**: sommarinfo (jun–sep) + glassrea + inställda turer 71/97, ersättningsbuss tur 75, försenad buss B3; upcoming baninfo Jul–Aug and inställd tur 71 on **2026-07-04**. **Rolling:** tåg 71 + buss B3 får även avvikelse på **dagens datum** vid varje import/reset så Docker alltid visar Tåg/Buss under «Aktuellt trafikläge». On **today within summer** (e.g. 2026-06-15) sommarinfo still shows under «Aktuellt trafikläge» → Information. First line of each notice becomes `summary` in the feed API.

Format: [docs/CSV_FORMAT.md](../../docs/CSV_FORMAT.md).
