#!/usr/bin/env bash
# Shared gate bootstrap for bash quality scripts.
# Usage: . "$(dirname "$0")/_init.sh"

SCRIPTS="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ROOT="$(cd "$SCRIPTS/.." && pwd)"
cd "$ROOT"
. "$SCRIPTS/lib/mrt-docker.sh"

mrt_gate_parse_args() {
	MRT_GATE_LOCAL=0
	MRT_GATE_SKIP_PHPCS=0
	MRT_GATE_VUE=0
	MRT_GATE_FILTERED=()
	for arg in "$@"; do
		case "$arg" in
		--local|-Local|--Local) MRT_GATE_LOCAL=1 ;;
		--skip-phpcs|-SkipPhpcs) MRT_GATE_SKIP_PHPCS=1 ;;
		--vue|-Vue) MRT_GATE_VUE=1 ;;
		--timings|-Timings) export MRT_SCRIPT_TIMINGS=1 ;;
		*) MRT_GATE_FILTERED+=("$arg") ;;
		esac
	done
}

mrt_gate_require_docker() {
	if ! mrt_docker_available; then
		echo "Docker is not running." >&2
		exit 1
	fi
}
