#!/usr/bin/env bash
# Unified MRT developer CLI (Fas 3 S2). Forwards to existing scripts.
set -euo pipefail

SCRIPTS="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$SCRIPTS/.." && pwd)"
cd "$ROOT"

cmd="${1:-help}"
sub="${2:-}"
shift $(( $# > 0 ? 1 : 0 )) || true
if [ -n "$sub" ] && { [ "$cmd" = dev ] || [ "$cmd" = release ] || [ "$cmd" = vue ]; }; then
	shift $(( $# > 0 ? 1 : 0 )) || true
fi

mrt_help() {
	cat <<'EOF'
MRT developer CLI — forwards to scripts/*.sh / composer

  mrt check
  mrt test [--local]
  mrt lint [--local] [--timings]
  mrt vue-check [--local] [--timings]
  mrt dev reset [--build] [--skip-compose]
  mrt dev watch [--no-up]
  mrt help

Examples:
  bash scripts/mrt.sh check
  bash scripts/mrt.sh dev reset --build
EOF
}

mrt_run_ps1() {
	powershell -NoProfile -ExecutionPolicy Bypass -File "$SCRIPTS/$1" "${@:2}"
}

case "$cmd" in
help) mrt_help ;;
check) mrt_run_ps1 check.ps1 "$@" ;;
test) mrt_run_ps1 test.ps1 "$@" ;;
lint) bash "$SCRIPTS/lint.sh" "$@" ;;
vue-check) bash "$SCRIPTS/vue-check.sh" "$@" ;;
vue)
	case "$sub" in
	check) bash "$SCRIPTS/vue-check.sh" "$@" ;;
	*) echo "Unknown vue subcommand: $sub" >&2; exit 1 ;;
	esac
	;;
dev)
	case "$sub" in
	reset) bash "$SCRIPTS/docker-dev-reset.sh" "$@" ;;
	smoke) mrt_run_ps1 docker-smoke.ps1 "$@" ;;
	watch) bash "$SCRIPTS/docker-watch.sh" "$@" ;;
	*) echo "Unknown dev subcommand: $sub (try: reset, smoke, watch)" >&2; exit 1 ;;
	esac
	;;
release)
	case "$sub" in
	build) mrt_run_ps1 build-release.ps1 "$@" ;;
	*) echo "Unknown release subcommand: $sub (try: build)" >&2; exit 1 ;;
	esac
	;;
*) echo "Unknown command: $cmd (run: mrt help)" >&2; exit 1 ;;
esac
