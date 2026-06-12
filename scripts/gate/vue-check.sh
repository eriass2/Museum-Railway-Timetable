#!/usr/bin/env sh
# Run Vue check in Docker (node:22-alpine). Pass --local to use host npm.
set -e
SCRIPTS="$(cd "$(dirname "$0")/.." && pwd)"
ROOT="$(cd "$SCRIPTS/.." && pwd)"
cd "$ROOT"
. "$SCRIPTS/lib/mrt-docker.sh"

use_local=0
composer_args=
for arg in "$@"; do
	case "$arg" in
		--local) use_local=1 ;;
		--timings) export MRT_SCRIPT_TIMINGS=1 ;;
		*) composer_args="$composer_args $arg" ;;
	esac
done

if [ "$use_local" -eq 1 ]; then
	# shellcheck disable=SC2086
	exec composer vue:check $composer_args
fi

mrt_vue_check
mrt_step_done
