#!/usr/bin/env bash
# PHPUnit line coverage for inc/ via Docker + PCOV (exploratory; not a CI gate).
set -e
. "$(dirname "$0")/_init.sh"
mrt_gate_parse_args "$@"

mrt_gate_require_docker
mrt_ensure_vendor
mkdir -p coverage

phpunit_args=(--coverage-clover coverage/clover.xml --colors=never)
if [ "${#MRT_GATE_FILTERED[@]}" -gt 0 ]; then
	phpunit_args+=("${MRT_GATE_FILTERED[@]}")
fi

echo "Running PHPUnit with PCOV in Docker (php-test)..."
mrt_tools_run php-test vendor/bin/phpunit "${phpunit_args[@]}"
echo ""
mrt_tools_run php-test scripts/coverage-summary.php coverage/clover.xml
