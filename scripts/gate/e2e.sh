#!/usr/bin/env bash
# Playwright E2E in Docker (playwright:v1.60-jammy).
set -e
. "$(dirname "$0")/_init.sh"
mrt_gate_parse_args "$@"

if [ "$MRT_GATE_LOCAL" -eq 1 ]; then
	echo "Vue E2E requires Docker (Playwright browsers). Omit --local." >&2
	exit 1
fi

mrt_gate_require_docker
echo "Running Vue E2E in Docker (playwright v1.60 jammy)..."
mrt_step 'Vue E2E (Docker)'

if ! docker compose --profile tools ps --status running -q vue-e2e 2>/dev/null | grep -q .; then
	docker compose --profile tools up -d vue-e2e
fi

if [ "${#MRT_GATE_FILTERED[@]}" -gt 0 ]; then
	mrt_tools_run vue-e2e sh -c "$(mrt_vue_e2e_shell "${MRT_GATE_FILTERED[@]}")"
else
	mrt_tools_run vue-e2e sh -c "$(mrt_vue_e2e_shell)"
fi
mrt_step_done
