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
	mrt_tools_ensure_shell
	if mrt_tools_service_running "$_svc"; then
		case "$_svc" in
		composer)
			docker compose --profile tools exec -T composer composer "$@"
			;;
		php-test)
			docker compose --profile tools exec -T php-test php "$@"
			;;
		*)
			docker compose --profile tools exec -T "$_svc" "$@"
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
			docker compose --profile tools run --rm --no-deps "$_svc" "$@"
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

mrt_lint_docker() {
	echo "Running composer lint in Docker..."
	mrt_step 'composer lint (Docker)'
	mrt_tools_run composer lint
	mrt_step_done
}
