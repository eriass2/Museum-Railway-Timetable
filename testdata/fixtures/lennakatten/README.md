# Lennakatten CSV fixture

Test/demo timetable data for development (`docker-dev-reset`, Import Lennakatten).

**Source of truth:** edit the CSV files here directly.

**Reference PDFs:** `testdata/reference-pdfs/` — primarily `Anslagstidtabell-2026.pdf` for traffic-day calendar rules; detail tables (`Gron-tidtabell-lor.pdf`, `Gul-tidtabell-fre.pdf`, …) for stop times.

Timetable codes in the CSV package:

| Code | Meaning |
|------|---------|
| `green` | GRÖN rail services |
| `green-buss` | GRÖN Fjällnora connection buses (Jul–Aug) |
| `yellow` / `yellow-buss` | GUL rail / connection buses |
| `red` / `orange` | RÖD Sundays / ORANGE extra Friday trains |

Regenerate rail blocks after PDF edits: `python scripts/generate-lennakatten-extra-timetables.py` (bus split + red/orange services).

Validate after changes:

```sh
composer csv:validate -- testdata/fixtures/lennakatten
```

Format: [docs/CSV_FORMAT.md](../../docs/CSV_FORMAT.md).
