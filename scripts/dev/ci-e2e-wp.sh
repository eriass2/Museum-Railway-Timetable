#!/usr/bin/env bash
# WordPress + Playwright E2E (component demo page). Used in CI and locally.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"
. "$(dirname "$0")/../lib/mrt-docker.sh"

mrt_ci_e2e_wp_on_fail() {
	echo "=== ci-e2e-wp: failure diagnostics ===" >&2
	docker compose ps 2>/dev/null || true
	docker compose logs --tail=80 db wordpress 2>/dev/null || true
}
trap mrt_ci_e2e_wp_on_fail ERR

echo "=== ci-e2e-wp: starting Docker ==="
mrt_docker_up

echo "=== ci-e2e-wp: waiting for WordPress ==="
mrt_wait_wordpress 300 5

echo "=== ci-e2e-wp: import + demo page ==="
mrt_e2e_prepare_site

DEMO_URL="$(mrt_demo_page_url)"
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

INDEX_URL="$(mrt_index_page_url)" || INDEX_URL="${WP_SITE_BASE}/"
export MRT_E2E_WP_INDEX_URL="$INDEX_URL"
echo "Index URL: $MRT_E2E_WP_INDEX_URL"

WP_E2E_SPECS=(
	e2e/overview-wp.spec.ts
	e2e/month-wp.spec.ts
	e2e/wizard-wp.spec.ts
	e2e/wizard-front-page-wp.spec.ts
	e2e/index-wp.spec.ts
	e2e/traffic-notices-wp.spec.ts
	e2e/admin-dashboard.spec.ts
	e2e/admin-nav-wp.spec.ts
	e2e/admin-traffic-notices.spec.ts
	e2e/admin-import-export.spec.ts
	e2e/admin-timetable-flow.spec.ts
)

echo "=== ci-e2e-wp: Vue build + Playwright ==="
npm --prefix frontend/vue ci
npm --prefix frontend/vue run build
npx --prefix frontend/vue playwright install chromium --with-deps
e2e_exit=0
npm --prefix frontend/vue run e2e -- --workers=1 "${WP_E2E_SPECS[@]}" || e2e_exit=$?

echo "=== ci-e2e-wp: restoring site (Lennakatten override + demo traffic) ==="
mrt_e2e_prepare_site

exit "$e2e_exit"
