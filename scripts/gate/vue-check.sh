#!/usr/bin/env sh
# Run Vue check in Docker (node:22-alpine). Pass --local to use host npm.
set -e
. "$(dirname "$0")/_init.sh"
mrt_gate_parse_args "$@"

if [ "$MRT_GATE_LOCAL" -eq 1 ]; then
	exec composer vue:check
fi

mrt_gate_require_docker
mrt_vue_check
mrt_step_done
