#!/usr/bin/env bash
# WordPress + Playwright E2E (component demo page). Used in CI and locally.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
. "$(dirname "$0")/lib/mrt-docker.sh"

echo "=== ci-e2e-wp: starting Docker ==="
mrt_docker_up

echo "=== ci-e2e-wp: waiting for WordPress ==="
mrt_wait_wordpress 300 5

echo "=== ci-e2e-wp: import + demo page ==="
docker compose run --rm --no-TTY wordpress-init wp --allow-root eval \
  "if (function_exists('MRT_run_lennakatten_import')) { echo MRT_run_lennakatten_import(); }" || true

docker compose run --rm --no-TTY wordpress-init wp --allow-root eval \
  'if (function_exists("MRT_dev_cli_set_admin_user")) { MRT_dev_cli_set_admin_user(); }
  if (function_exists("MRT_sync_timetable_public_pages")) { MRT_sync_timetable_public_pages(); }' || true

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

WP_SITE_BASE="$(echo "$DEMO_URL" | sed -E 's#^(https?://[^/]+).*#\1#')"
export MRT_E2E_WP_ADMIN_URL="${WP_SITE_BASE}/wp-admin/admin.php?page=mrt_app"
export MRT_E2E_WP_ADMIN_USER="${WORDPRESS_ADMIN_USER:-admin}"
export MRT_E2E_WP_ADMIN_PASSWORD="${WORDPRESS_ADMIN_PASSWORD:-admin}"
echo "Admin URL: $MRT_E2E_WP_ADMIN_URL"

INDEX_URL="$(
  docker compose run --rm --no-TTY wordpress-init wp --allow-root eval \
    '$id = (int) get_option("mrt_timetables_index_page_id", 0);
    if ($id <= 0) { exit(1); }
    echo get_permalink($id);'
)" || INDEX_URL="${WP_SITE_BASE}/"

export MRT_E2E_WP_INDEX_URL="$INDEX_URL"
echo "Index URL: $MRT_E2E_WP_INDEX_URL"

echo "=== ci-e2e-wp: Vue build + Playwright ==="
npm --prefix frontend/vue ci
npm --prefix frontend/vue run build
npx --prefix frontend/vue playwright install chromium --with-deps
npm --prefix frontend/vue run e2e -- --workers=1 \
  e2e/overview-wp.spec.ts \
  e2e/month-wp.spec.ts \
  e2e/wizard-wp.spec.ts \
  e2e/index-wp.spec.ts \
  e2e/traffic-notices-wp.spec.ts \
  e2e/admin-dashboard.spec.ts \
  e2e/admin-nav-wp.spec.ts \
  e2e/admin-traffic-notices.spec.ts \
  e2e/admin-import-export.spec.ts \
  e2e/admin-timetable-flow.spec.ts
