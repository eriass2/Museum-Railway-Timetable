#!/usr/bin/env bash
# PHPUnit in Docker by default (PHP 8.2). Pass --local for host PHP 8.2+.
set -e
SCRIPTS="$(cd "$(dirname "$0")/.." && pwd)"
ROOT="$(cd "$SCRIPTS/.." && pwd)"
cd "$ROOT"
. "$SCRIPTS/lib/mrt-docker.sh"

use_local=0
filtered_args=()
for arg in "$@"; do
	case "$arg" in
	--local) use_local=1 ;;
	--timings) export MRT_SCRIPT_TIMINGS=1 ;;
	*) filtered_args+=("$arg") ;;
	esac
done

if [ "$use_local" -eq 1 ]; then
	if ! command -v php >/dev/null 2>&1; then
		echo "Local PHP not in PATH. Omit --local to use Docker." >&2
		exit 1
	fi
	mrt_step 'PHPUnit (local)'
	if [ "${#filtered_args[@]}" -gt 0 ]; then
		composer test -- "${filtered_args[@]}"
	else
		composer test
	fi
	mrt_step_done
	exit 0
fi

if ! mrt_docker_available; then
	echo "Docker is not running." >&2
	exit 1
fi

mrt_ensure_vendor
echo "Running PHPUnit in Docker (php-test)..."
mrt_step 'PHPUnit (Docker)'
if [ "${#filtered_args[@]}" -gt 0 ]; then
	mrt_tools_run php-test vendor/bin/phpunit "${filtered_args[@]}"
else
	mrt_tools_run php-test vendor/bin/phpunit
fi
mrt_step_done
