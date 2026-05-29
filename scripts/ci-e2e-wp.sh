#!/usr/bin/env bash
# WordPress + Playwright E2E (component demo page). Used in CI and locally.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "=== ci-e2e-wp: starting Docker ==="
docker compose up -d --build

echo "=== ci-e2e-wp: waiting for WordPress ==="
for i in $(seq 1 60); do
  if docker compose run --rm --no-TTY wordpress-init wp --allow-root core is-installed >/dev/null 2>&1; then
    break
  fi
  if [ "$i" -eq 60 ]; then
    echo "WordPress did not become ready in time" >&2
    exit 1
  fi
  sleep 5
done

echo "=== ci-e2e-wp: import + demo page ==="
docker compose run --rm --no-TTY wordpress-init wp --allow-root eval \
  "if (function_exists('MRT_run_lennakatten_import')) { echo MRT_run_lennakatten_import(); }" || true

DEMO_URL="$(
  docker compose run --rm --no-TTY wordpress-init wp --allow-root eval \
    '$r = MRT_ensure_components_demo_page_cli();
    if (is_wp_error($r)) { fwrite(STDERR, $r->get_error_message() . "\n"); exit(1); }
    wp_update_post(array("ID" => (int) $r, "post_status" => "publish"));
    echo get_permalink((int) $r);'
)"

if [ -z "$DEMO_URL" ]; then
  echo "Failed to resolve demo page URL" >&2
  exit 1
fi

echo "Demo URL: $DEMO_URL"
export MRT_E2E_WP_DEMO_URL="$DEMO_URL"

echo "=== ci-e2e-wp: Vue build + Playwright ==="
npm --prefix frontend/vue ci
npm --prefix frontend/vue run build
npx --prefix frontend/vue playwright install chromium --with-deps
npm --prefix frontend/vue run e2e -- e2e/overview-wp.spec.ts e2e/month-wp.spec.ts e2e/wizard-wp.spec.ts
