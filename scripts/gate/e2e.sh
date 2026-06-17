#!/usr/bin/env bash
# Playwright E2E in Docker (playwright:v1.61-jammy).
set -e
. "$(dirname "$0")/_init.sh"
mrt_gate_parse_args "$@"

if [ "$MRT_GATE_LOCAL" -eq 1 ]; then
	echo "Vue E2E requires Docker (Playwright browsers). Omit --local." >&2
	exit 1
fi

mrt_gate_require_docker
echo "Running Vue E2E in Docker (playwright v1.61 jammy)..."
mrt_step 'Vue E2E (Docker)'

mrt_tools_run vue-e2e sh -c "$(mrt_vue_e2e_shell "${MRT_GATE_FILTERED[@]}")"
mrt_step_done
