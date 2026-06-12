#!/usr/bin/env bash
# Run PHPStan and PHPCS. Default: Docker. Pass --local for host vendor/.
set -e
. "$(dirname "$0")/_init.sh"
mrt_gate_parse_args "$@"

if [ "$MRT_GATE_LOCAL" -eq 1 ]; then
	if [ ! -d vendor ]; then
		echo "Run 'composer install' first."
		exit 1
	fi
	echo "Using existing vendor/."
	mrt_step 'composer lint (local)'
	./vendor/bin/phpstan analyse --no-progress
	./vendor/bin/phpcs
	mrt_step_done
	echo "Lint OK."
	exit 0
fi

mrt_gate_require_docker
mrt_ensure_vendor
mrt_lint_docker
echo "Lint OK."
mrt_step_done
