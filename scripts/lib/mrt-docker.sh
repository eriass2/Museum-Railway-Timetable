#!/usr/bin/env sh
# Shared Docker helpers for bash scripts.
# Usage: . "$(dirname "$0")/lib/mrt-docker.sh"

MRT_DEV_SITE_URL="${MRT_DEV_SITE_URL:-http://localhost:8080}"

MRT_NPM_CI_SNIPPET='if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ] || ! cmp -s package-lock.json node_modules/.package-lock.json 2>/dev/null; then npm ci; fi'

mrt_npm_ci_snippet="$MRT_NPM_CI_SNIPPET"

mrt_wordpress_running() {
	docker compose ps --status running -q wordpress 2>/dev/null | grep -q .
}

mrt_compose_run_no_deps_if_up() {
	if mrt_wordpress_running; then
		docker compose run --rm --no-deps "$@"
	else
		docker compose run --rm "$@"
	fi
}

mrt_tools_run() {
	docker compose --profile tools run --rm --no-deps "$@"
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

mrt_docker_up() {
	docker compose up -d "$@"
}

mrt_wait_until() {
	deadline="$1"
	interval="$2"
	shift 2

	while [ "$(date +%s)" -lt "$deadline" ]; do
		if "$@"; then
			return 0
		fi
		sleep "$interval"
	done
	return 1
}

mrt_http_ready() {
	curl -sf -o /dev/null --max-time 5 "$1" 2>/dev/null
}

mrt_wp_installed() {
	mrt_compose_run_no_deps_if_up --no-TTY wordpress-init wp --allow-root core is-installed >/dev/null 2>&1
}

mrt_wait_wordpress() {
	timeout="${1:-120}"
	interval="${2:-2}"
	login_url="${MRT_DEV_SITE_URL}/wp-login.php"
	deadline=$(( $(date +%s) + timeout ))

	echo "Waiting for WordPress..."
	if ! mrt_wait_until "$deadline" "$interval" mrt_http_ready "$login_url"; then
		echo "WordPress did not respond at ${login_url} within ${timeout}s." >&2
		return 1
	fi
	if ! mrt_wait_until "$deadline" "$interval" mrt_wp_installed; then
		echo "WordPress did not become ready within ${timeout}s." >&2
		return 1
	fi
}

mrt_wp_eval() {
	mrt_compose_run_no_deps_if_up --no-TTY wordpress-init wp --allow-root eval "$1"
}

mrt_vue_check() {
	echo "Running Vue check in Docker (node:22-alpine)..."
	mrt_tools_run vue sh -c "$(mrt_vue_shell Check)"
}

mrt_vue_build_verify() {
	mrt_tools_run vue sh -c "$(mrt_vue_shell BuildVerify)"
}

mrt_lint_docker() {
	echo "Running composer lint in Docker..."
	mrt_tools_run composer lint
}

mrt_ensure_sv_locale() {
	mrt_compose_run_no_deps_if_up wordpress-init sh /usr/local/bin/mrt-ensure-sv-locale.sh
}

mrt_dev_reset_import() {
	mrt_compose_run_no_deps_if_up --no-TTY wordpress-init wp --allow-root eval \
		"if (!function_exists('MRT_dev_reset_and_import_cli')) { fwrite(STDERR, 'Plugin not active or dev-cli not loaded'.PHP_EOL); exit(1); } MRT_dev_reset_and_import_cli();"
}

mrt_import_lennakatten() {
	mrt_wp_eval "if (function_exists('MRT_run_lennakatten_import')) { echo MRT_run_lennakatten_import(); }" || true
}

mrt_e2e_prepare_site() {
	mrt_import_lennakatten
	mrt_wp_eval 'if (function_exists("MRT_dev_cli_set_admin_user")) { MRT_dev_cli_set_admin_user(); }
  if (function_exists("MRT_sync_timetable_public_pages")) { MRT_sync_timetable_public_pages(); }' || true
}

mrt_demo_page_url() {
	mrt_wp_eval '$r = MRT_ensure_components_demo_page_cli();
    if (is_wp_error($r)) { fwrite(STDERR, $r->get_error_message() . "\n"); exit(1); }
    wp_update_post(array("ID" => (int) $r, "post_status" => "publish"));
    echo get_permalink((int) $r);'
}

mrt_index_page_url() {
	mrt_wp_eval '$id = (int) get_option("mrt_timetables_index_page_id", 0);
    if ($id <= 0) { exit(1); }
    echo get_permalink($id);'
}
