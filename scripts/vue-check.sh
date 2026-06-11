#!/usr/bin/env sh
# Run Vue check in Docker (node:22-alpine). Pass --local to use host npm.
set -e
cd "$(dirname "$0")/.."
. "$(dirname "$0")/lib/mrt-docker.sh"

use_local=0
if [ "${1:-}" = "--local" ]; then
	use_local=1
	shift
fi

if [ "$use_local" -eq 1 ]; then
	exec composer vue:check "$@"
fi

exec mrt_vue_check
