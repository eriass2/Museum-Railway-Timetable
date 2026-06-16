#!/usr/bin/env sh
# Docker tools-shell helpers for bash MRT scripts.

mrt_tools_service_running() {
	docker compose --profile tools ps --status running -q "$1" 2>/dev/null | grep -q .
}

mrt_tools_ensure_shell() {
	for _svc in composer php-test vue; do
		if ! mrt_tools_service_running "$_svc"; then
			docker compose --profile tools up -d "$_svc"
		fi
	done
}

mrt_tools_run() {
	_svc="$1"
	shift
	if [ "$_svc" != "vue-e2e" ]; then
		mrt_tools_ensure_shell
	fi
	if mrt_tools_service_running "$_svc"; then
		case "$_svc" in
		composer)
			docker compose --profile tools exec --no-TTY composer composer "$@"
			;;
		php-test)
			docker compose --profile tools exec --no-TTY php-test php "$@"
			;;
		*)
			docker compose --profile tools exec --no-TTY "$_svc" "$@"
			;;
		esac
	else
		case "$_svc" in
		composer)
			docker compose --profile tools run --rm --no-deps --entrypoint composer "$_svc" "$@"
			;;
		php-test)
			docker compose --profile tools run --rm --no-deps --entrypoint php "$_svc" "$@"
			;;
		*)
			docker compose --profile tools run --rm --no-deps -e "CI=${CI:-}" "$_svc" "$@"
			;;
		esac
	fi
}

mrt_vue_shell() {
	mode="$1"
	case "$mode" in
	Check) printf '%s && npm run check' "$MRT_NPM_CI_SNIPPET" ;;
	Build) printf '%s && npm run build' "$MRT_NPM_CI_SNIPPET" ;;
	BuildVerify) printf '%s && npm run build && npm run verify' "$MRT_NPM_CI_SNIPPET" ;;
	*) return 1 ;;
	esac
}

mrt_vue_e2e_shell() {
	_cmd="$MRT_NPM_CI_SNIPPET && npm run e2e"
	if [ "$#" -gt 0 ]; then
		_extra=""
		for _arg in "$@"; do
			_extra="$_extra '$(printf '%s' "$_arg" | sed "s/'/'\\\\''/g")'"
		done
		_cmd="$_cmd -- $_extra"
	fi
	printf '%s' "$_cmd"
}

mrt_lint_docker() {
	echo "Running composer lint in Docker..."
	mrt_step 'composer lint (Docker)'
	mrt_tools_run composer lint
	mrt_step_done
}
