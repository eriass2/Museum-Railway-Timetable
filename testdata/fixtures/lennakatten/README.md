# Lennakatten CSV fixture

Test/demo timetable data for development (`docker-dev-reset`, Import Lennakatten).

Regenerate from `reference-data.php`:

```sh
composer csv:fixture
composer csv:validate -- testdata/fixtures/lennakatten
```

Reference PDFs: `testdata/reference-pdfs/`. Format: [docs/CSV_FORMAT.md](../../docs/CSV_FORMAT.md).
