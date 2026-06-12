#!/usr/bin/env bash
# Run PHPStan and PHPCS. Default: Docker (matches lint.ps1). Pass --local for host vendor/.
set -e
cd "$(dirname "$0")/.."
. "$(dirname "$0")/lib/mrt-docker.sh"

use_local=0
for arg in "$@"; do
	case "$arg" in
		--local) use_local=1 ;;
		--timings) export MRT_SCRIPT_TIMINGS=1 ;;
	esac
done

if [ "$use_local" -eq 1 ]; then
	if [ ! -d vendor ]; then
		echo "Run 'composer install' first."
		exit 1
	fi
	echo "Using existing vendor/."
	echo "Running composer lint..."
	mrt_step 'composer lint (local)'
	./vendor/bin/phpstan analyse --no-progress
	./vendor/bin/phpcs
	mrt_step_done
	echo "Lint OK."
	exit 0
fi

mrt_lint_docker
echo "Lint OK."
mrt_step_done
