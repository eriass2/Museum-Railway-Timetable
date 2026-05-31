#!/usr/bin/env bash
# Pack Lennakatten CSV fixture (or custom dir) into an import-ready zip.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SOURCE="${1:-testdata/fixtures/lennakatten}"
OUTPUT="${2:-testdata/fixtures/lennakatten.zip}"

cd "$ROOT"

echo "=== csv-package-zip: validate ==="
docker compose --profile tools run --rm php-test scripts/csv-validate.php "$SOURCE"

echo "=== csv-package-zip: pack ==="
docker run --rm -v "$ROOT:/app" -w /app alpine sh -c "
  apk add --no-cache zip >/dev/null
  rm -f '$OUTPUT'
  cd '$SOURCE'
  zip -qr '/app/$OUTPUT' manifest.json *.csv
"

SIZE="$(du -h "$OUTPUT" | cut -f1)"
echo "OK: $OUTPUT ($SIZE)"
