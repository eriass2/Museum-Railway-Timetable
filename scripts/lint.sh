#!/usr/bin/env bash
# Run PHPStan and PHPCS. Default: Docker (matches lint.ps1). Pass --local for host vendor/.
set -e
cd "$(dirname "$0")/.."
. "$(dirname "$0")/lib/mrt-docker.sh"

use_local=0
if [ "${1:-}" = "--local" ]; then
	use_local=1
	shift
fi

if [ "$use_local" -eq 1 ]; then
	if [ ! -d vendor ]; then
		echo "Run 'composer install' first."
		exit 1
	fi
	echo "Running composer lint..."
	./vendor/bin/phpstan analyse --no-progress
	./vendor/bin/phpcs
	echo "Lint OK."
	exit 0
fi

mrt_lint_docker
echo "Lint OK."
