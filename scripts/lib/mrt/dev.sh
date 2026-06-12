#!/usr/bin/env sh
# Dev reset, locale, and smoke helpers for bash MRT scripts.

mrt_vue_check() {
	echo "Running Vue check in Docker (node:22-alpine)..."
	mrt_step 'Vue check (Docker)'
	mrt_tools_run vue sh -c "$(mrt_vue_shell Check)"
	mrt_step_done
}

mrt_vue_build_verify() {
	mrt_step 'Vue build + verify (Docker)'
	mrt_tools_run vue sh -c "$(mrt_vue_shell BuildVerify)"
	mrt_step_done
}

mrt_ensure_sv_locale() {
	mrt_wp_sh /usr/local/bin/mrt-ensure-sv-locale.sh
}

mrt_set_wp_debug() {
	enabled="${1:-1}"
	value="false"
	if [ "$enabled" -eq 1 ]; then
		value="true"
	fi
	mrt_wp_cli config set WP_DEBUG "$value" --raw
	mrt_wp_cli config set WP_DEBUG_LOG "$value" --raw
}

mrt_dev_reset_import() {
	mrt_wp_cli eval \
		"if (!function_exists('MRT_dev_reset_and_import_cli')) { fwrite(STDERR, 'Plugin not active or dev-cli not loaded'.PHP_EOL); exit(1); } MRT_dev_reset_and_import_cli();"
}

mrt_import_lennakatten() {
	mrt_wp_eval "if (function_exists('MRT_run_lennakatten_import')) { echo MRT_run_lennakatten_import(); }" || true
}

mrt_e2e_prepare_site() {
	mrt_import_lennakatten
	mrt_wp_eval 'if (function_exists("MRT_dev_cli_set_admin_user")) { MRT_dev_cli_set_admin_user(); }
  if (function_exists("MRT_sync_timetable_public_pages")) { MRT_sync_timetable_public_pages(); }
  if (function_exists("MRT_dev_activate_twentytwentyfive_theme")) { MRT_dev_activate_twentytwentyfive_theme(); }' || true
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
