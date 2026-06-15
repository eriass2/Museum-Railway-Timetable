#!/usr/bin/env bash
# Unified MRT developer CLI — canonical on Linux/macOS/WSL (Fas 3 S1/S2).
set -euo pipefail

SCRIPTS="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$SCRIPTS/.." && pwd)"
cd "$ROOT"

cmd="${1:-help}"
sub="${2:-}"
shift $(( $# > 0 ? 1 : 0 )) || true
if [ -n "$sub" ] && { [ "$cmd" = dev ] || [ "$cmd" = release ] || [ "$cmd" = csv ] || [ "$cmd" = vue ]; }; then
	shift $(( $# > 0 ? 1 : 0 )) || true
fi

mrt_help() {
	cat "$SCRIPTS/lib/mrt-help.txt"
}

mrt_run_ps1() {
	if ! command -v powershell >/dev/null 2>&1; then
		echo "PowerShell required for this command on this host." >&2
		exit 1
	fi
	powershell -NoProfile -ExecutionPolicy Bypass -File "$SCRIPTS/$1" "${@:2}"
}

case "$cmd" in
help|-h|--help) mrt_help ;;
check) bash "$SCRIPTS/gate/check.sh" "$@" ;;
test) bash "$SCRIPTS/gate/test.sh" "$@" ;;
lint) bash "$SCRIPTS/gate/lint.sh" "$@" ;;
vue-check) bash "$SCRIPTS/gate/vue-check.sh" "$@" ;;
e2e) bash "$SCRIPTS/gate/e2e.sh" "$@" ;;
coverage) bash "$SCRIPTS/gate/coverage.sh" "$@" ;;
setup-dev) bash "$SCRIPTS/setup-dev.sh" "$@" ;;
vue)
	case "$sub" in
	check) bash "$SCRIPTS/gate/vue-check.sh" "$@" ;;
	*) echo "Unknown vue subcommand: $sub (try: check)" >&2; exit 1 ;;
	esac
	;;
dev)
	case "$sub" in
	reset) bash "$SCRIPTS/dev/docker-dev-reset.sh" "$@" ;;
	smoke) mrt_run_ps1 dev/docker-smoke.ps1 "$@" ;;
	watch) bash "$SCRIPTS/dev/docker-watch.sh" "$@" ;;
	e2e-wp) bash "$SCRIPTS/dev/e2e-wp.sh" "$@" ;;
	*) echo "Unknown dev subcommand: $sub (try: reset, smoke, watch, e2e-wp)" >&2; exit 1 ;;
	esac
	;;
release)
	case "$sub" in
	build) mrt_run_ps1 release/build-release.ps1 "$@" ;;
	deploy) mrt_run_ps1 release/live-deploy.ps1 "$@" ;;
	*) echo "Unknown release subcommand: $sub (try: build, deploy)" >&2; exit 1 ;;
	esac
	;;
csv)
	case "$sub" in
	validate) composer csv:validate -- "$@" ;;
	zip) bash "$SCRIPTS/csv/csv-package-zip.sh" "$@" ;;
	*) echo "Unknown csv subcommand: $sub (try: validate, zip)" >&2; exit 1 ;;
	esac
	;;
i18n) mrt_run_ps1 i18n/make-i18n.ps1 "$@" ;;
*) echo "Unknown command: $cmd (run: mrt help)" >&2; exit 1 ;;
esac
