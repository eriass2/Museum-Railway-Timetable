#!/usr/bin/env bash
# PHPUnit line coverage for inc/ via Docker + PCOV (exploratory; not a CI gate).
set -e
SCRIPTS="$(cd "$(dirname "$0")/.." && pwd)"
ROOT="$(cd "$SCRIPTS/.." && pwd)"
cd "$ROOT"
. "$SCRIPTS/lib/mrt-docker.sh"

if ! mrt_docker_available; then
	echo "Docker is not running. Coverage requires Docker (php-test + PCOV)." >&2
	exit 1
fi

mrt_ensure_vendor
mkdir -p coverage

phpunit_args=(--coverage-clover coverage/clover.xml --colors=never)
for arg in "$@"; do
	phpunit_args+=("$arg")
done

echo "Running PHPUnit with PCOV in Docker (php-test)..."
mrt_tools_run php-test vendor/bin/phpunit "${phpunit_args[@]}"
echo ""
mrt_tools_run php-test scripts/coverage-summary.php coverage/clover.xml
