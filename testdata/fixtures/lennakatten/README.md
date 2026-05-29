# Lennakatten CSV fixture

Test/demo timetable data for development (`docker-dev-reset`, Import Lennakatten).

**Source of truth:** edit the CSV files here directly. Reference PDFs for verification: `testdata/reference-pdfs/`.

Validate after changes:

```sh
composer csv:validate -- testdata/fixtures/lennakatten
```

Format: [docs/CSV_FORMAT.md](../../docs/CSV_FORMAT.md).
