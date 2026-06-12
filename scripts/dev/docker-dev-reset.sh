#!/usr/bin/env sh
# Reset plugin data, re-import Lennakatten, setup dev smoke pages + menu.
# Usage:
#   ./scripts/docker-dev-reset.sh
#   ./scripts/docker-dev-reset.sh --build
#   ./scripts/docker-dev-reset.sh --skip-compose
#   ./scripts/docker-dev-reset.sh --timings

set -e
cd "$(dirname "$0")/../.."
. "$(dirname "$0")/../lib/mrt-docker.sh"

BUILD=0
SKIP_COMPOSE=0
for arg in "$@"; do
	case "$arg" in
		-Build|--build|-build) BUILD=1 ;;
		-SkipCompose|--skip-compose|-skip-compose) SKIP_COMPOSE=1 ;;
		--timings) export MRT_SCRIPT_TIMINGS=1 ;;
		-h|--help)
			echo "Usage: $0 [--build] [--skip-compose] [--timings]"
			exit 0
			;;
	esac
done

echo ""
echo "=== MRT dev reset (clear + import + smoke menu) ==="

if [ "$SKIP_COMPOSE" -eq 0 ]; then
	mrt_step 'docker compose up'
	if [ "$BUILD" -eq 1 ]; then
		mrt_docker_up --build
	else
		mrt_docker_up
	fi
	mrt_wait_wordpress
	mrt_step_done
fi

mrt_vue_build_verify

mrt_step 'Swedish locale (sv_SE)'
mrt_ensure_sv_locale
mrt_step_done

mrt_step 'Enable WP_DEBUG (development)'
mrt_set_wp_debug 1
mrt_step_done

mrt_step 'Reset and import'
mrt_dev_reset_import
mrt_step_done

echo ""
echo "Done. Front: ${MRT_DEV_SITE_URL}  Admin: ${MRT_DEV_SITE_URL}/wp-admin (admin / admin)"
echo ""
mrt_step_done
