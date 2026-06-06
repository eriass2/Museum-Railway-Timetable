# Lennakatten CSV fixture

Test/demo timetable data for development (`docker-dev-reset`, Import Lennakatten).

**Source of truth:** edit the CSV files here directly.

**Reference PDF:** `testdata/reference-pdfs/Anslagstidtabell-2026.pdf` (kalender + alla tidtabeller).

Timetable codes in the CSV package:

| Code | Meaning |
|------|---------|
| `green` | GRÖN rail – Saturdays |
| `green-vard` | GRÖN rail – Wed/Thu summer (1/7–6/8) |
| `green-buss` | GRÖN anslutningsbuss Selknä–Fjällnora (1/7–16/8, gröna trafikdagar) |
| `yellow` | GUL rail – Fridays |
| `red` / `orange` | RÖD Sundays / ORANGE extra Friday trains |

Regenerate rail blocks after PDF edits: `python scripts/generate-lennakatten-extra-timetables.py` (green-vard clone + red/orange).

Sync GRÖN/GUL rail and bus stop times from Anslagstidtabell (splits tågbyte at Marielund into separate services):

```sh
python scripts/sync-lennakatten-rail-fixture.py
python scripts/generate-lennakatten-extra-timetables.py
```

Verify against Anslagstidtabell:

```sh
python scripts/verify-lennakatten-vs-pdf.py
```

Validate after changes:

```sh
composer csv:validate -- testdata/fixtures/lennakatten
composer csv:zip
```

Import zip (same data): `../lennakatten.zip` — regenerate with `composer csv:zip` after CSV edits.

**Also included:** `settings.csv`, `price_schema.csv`, and `prices.csv` (Lennakatten taxa 2026 + operatörsinställningar). See `inc/import/lennakatten/reference-data.php` for the PHP mirror used in PHPUnit.

Format: [docs/CSV_FORMAT.md](../../docs/CSV_FORMAT.md).
