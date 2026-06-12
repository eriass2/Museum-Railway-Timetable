#!/usr/bin/env sh
# Step headers and optional timings for bash MRT scripts.

mrt_timings_on() {
	case "${MRT_SCRIPT_TIMINGS:-}" in 1|true|yes) return 0 ;; esac
	return 1
}

mrt_timing_finish() {
	if ! mrt_timings_on || [ -z "${MRT_TIMING_TITLE:-}" ]; then
		return 0
	fi
	_now=$(date +%s)
	_elapsed=$(( _now - MRT_TIMING_START ))
	printf '  [timing] %s - %ss\n' "$MRT_TIMING_TITLE" "$_elapsed"
	unset MRT_TIMING_TITLE MRT_TIMING_START
}

mrt_step() {
	mrt_timing_finish
	printf '\n--- %s ---\n' "$1"
	if mrt_timings_on; then
		MRT_TIMING_TITLE=$1
		MRT_TIMING_START=$(date +%s)
	fi
}

mrt_step_done() {
	mrt_timing_finish
}
