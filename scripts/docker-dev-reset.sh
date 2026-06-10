#!/usr/bin/env sh
# Reset plugin data, re-import Lennakatten, setup dev smoke pages + menu.
# Usage: ./scripts/docker-dev-reset.sh

set -e
cd "$(dirname "$0")/.."

echo ""
echo "=== MRT dev reset (clear + import + smoke menu) ==="

docker compose up -d --build
echo "Waiting for WordPress..."
sleep 12

echo ""
echo "--- Build Vue public bundle (CSS + JS) ---"
docker compose --profile tools run --rm vue sh -c "npm ci && npm run build && npm run verify"

echo ""
echo "--- Swedish locale (sv_SE) ---"
docker compose run --rm wordpress-init sh /usr/local/bin/mrt-ensure-sv-locale.sh

echo ""
echo "--- Reset and import ---"
docker compose run --rm wordpress-init wp --allow-root eval \
  "if (!function_exists('MRT_dev_reset_and_import_cli')) { fwrite(STDERR, 'Plugin not active'.PHP_EOL); exit(1); } MRT_dev_reset_and_import_cli();"

echo ""
echo "Done. Front: http://localhost:8080  Admin: http://localhost:8080/wp-admin (admin / admin)"
echo ""
