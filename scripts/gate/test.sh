#!/usr/bin/env bash
# PHPUnit in Docker by default (PHP 8.2). Pass --local for host PHP 8.2+.
set -e
. "$(dirname "$0")/_init.sh"
mrt_gate_parse_args "$@"

if [ "$MRT_GATE_LOCAL" -eq 1 ]; then
	if ! command -v php >/dev/null 2>&1; then
		echo "Local PHP not in PATH. Omit --local to use Docker." >&2
		exit 1
	fi
	mrt_step 'PHPUnit (local)'
	if [ "${#MRT_GATE_FILTERED[@]}" -gt 0 ]; then
		composer test -- "${MRT_GATE_FILTERED[@]}"
	else
		composer test
	fi
	mrt_step_done
	exit 0
fi

mrt_gate_require_docker
mrt_ensure_vendor
echo "Running PHPUnit in Docker (php-test)..."
mrt_step 'PHPUnit (Docker)'
if [ "${#MRT_GATE_FILTERED[@]}" -gt 0 ]; then
	mrt_tools_run php-test vendor/bin/phpunit "${MRT_GATE_FILTERED[@]}"
else
	mrt_tools_run php-test vendor/bin/phpunit
fi
mrt_step_done
