#!/usr/bin/env bash
# PHP quality gate in Docker (validate + PHPStan + PHPUnit; optional PHPCS).
set -e
. "$(dirname "$0")/_init.sh"
mrt_gate_parse_args "$@"

if ! mrt_docker_available; then
	echo "Docker is not running." >&2
	exit 1
fi

mrt_ensure_vendor

composer_script=check:all
if [ "$MRT_GATE_SKIP_PHPCS" -eq 1 ]; then
	composer_script=check
fi

mrt_step "composer ${composer_script} (Docker)"
mrt_tools_run composer "$composer_script"
mrt_step_done

echo "PHP check OK."

if [ "$MRT_GATE_VUE" -eq 1 ]; then
	vue_args=()
	if [ "${MRT_SCRIPT_TIMINGS:-}" = 1 ]; then
		vue_args+=(--timings)
	fi
	bash "$SCRIPTS/gate/vue-check.sh" "${vue_args[@]}"
fi

mrt_step_done
