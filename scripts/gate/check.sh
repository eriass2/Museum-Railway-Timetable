#!/usr/bin/env bash
# PHP quality gate in Docker (validate + PHPStan + PHPUnit; optional PHPCS).
set -e
SCRIPTS="$(cd "$(dirname "$0")/.." && pwd)"
ROOT="$(cd "$SCRIPTS/.." && pwd)"
cd "$ROOT"
. "$SCRIPTS/lib/mrt-docker.sh"

skip_phpcs=0
run_vue=0
for arg in "$@"; do
	case "$arg" in
	--skip-phpcs) skip_phpcs=1 ;;
	--vue) run_vue=1 ;;
	--timings) export MRT_SCRIPT_TIMINGS=1 ;;
	esac
done

if ! mrt_docker_available; then
	echo "Docker is not running." >&2
	exit 1
fi

mrt_ensure_vendor

composer_script=check:all
if [ "$skip_phpcs" -eq 1 ]; then
	composer_script=check
fi

mrt_step "composer ${composer_script} (Docker)"
mrt_tools_run composer "$composer_script"
mrt_step_done

echo "PHP check OK."

if [ "$run_vue" -eq 1 ]; then
	vue_args=()
	if [ "${MRT_SCRIPT_TIMINGS:-}" = 1 ]; then
		vue_args+=(--timings)
	fi
	bash "$SCRIPTS/gate/vue-check.sh" "${vue_args[@]}"
fi

mrt_step_done
