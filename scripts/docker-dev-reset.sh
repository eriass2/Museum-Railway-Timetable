#!/usr/bin/env sh
# Reset plugin data, re-import Lennakatten, setup dev smoke pages + menu.
# Usage: ./scripts/docker-dev-reset.sh

set -e
cd "$(dirname "$0")/.."
. "$(dirname "$0")/lib/mrt-docker.sh"

echo ""
echo "=== MRT dev reset (clear + import + smoke menu) ==="

mrt_docker_up
mrt_wait_wordpress

echo ""
echo "--- Build Vue public bundle (CSS + JS) ---"
mrt_vue_build_verify

echo ""
mrt_ensure_sv_locale

echo ""
mrt_dev_reset_import

echo ""
echo "Done. Front: http://localhost:8080  Admin: http://localhost:8080/wp-admin (admin / admin)"
echo ""
