#!/usr/bin/env sh
# Shared Docker helpers for bash scripts.
# Usage: . "$(dirname "$0")/lib/mrt-docker.sh"

mrt_docker_up() {
	docker compose up -d --build
}

mrt_wait_wordpress() {
	timeout="${1:-120}"
	interval="${2:-5}"
	echo "Waiting for WordPress..."
	i=1
	max=$((timeout / interval))
	while [ "$i" -le "$max" ]; do
		if docker compose run --rm --no-TTY wordpress-init wp --allow-root core is-installed >/dev/null 2>&1; then
			return 0
		fi
		if [ "$i" -eq "$max" ]; then
			echo "WordPress did not become ready within ${timeout}s." >&2
			return 1
		fi
		sleep "$interval"
		i=$((i + 1))
	done
}

mrt_wp_eval() {
	docker compose run --rm --no-TTY wordpress-init wp --allow-root eval "$1"
}

mrt_vue_check() {
	echo "Running Vue check in Docker (node:22-alpine)..."
	docker compose --profile tools run --rm vue sh -c "npm ci && npm run check"
}

mrt_vue_build_verify() {
	docker compose --profile tools run --rm vue sh -c "npm ci && npm run build && npm run verify"
}

mrt_lint_docker() {
	docker compose --profile tools run --rm composer phpstan -- --no-progress
	docker compose --profile tools run --rm composer phpcs
}

mrt_ensure_sv_locale() {
	docker compose run --rm wordpress-init sh /usr/local/bin/mrt-ensure-sv-locale.sh
}

mrt_dev_reset_import() {
	docker compose run --rm wordpress-init wp --allow-root eval \
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
