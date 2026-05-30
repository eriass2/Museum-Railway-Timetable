<?php
/**
 * Enqueue Vue admin bundle on app screens.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin page slugs that mount Vue.
 *
 * @return string[]
 */
function MRT_admin_vue_page_slugs(): array {
	$slugs = array(
		MRT_ADMIN_APP_SLUG,
		'mrt_app_timetables',
		'mrt_app_stations_routes',
		'mrt_app_settings',
		'mrt_app_prices',
		'mrt_app_train_types',
		'mrt_app_import_export',
	);
	if ( MRT_is_development_mode() ) {
		$slugs[] = 'mrt_app_dev_tools';
	}
	return $slugs;
}

/**
 * Whether current admin screen is the Vue app.
 *
 * @param string $hook Page hook.
 */
function MRT_is_admin_vue_screen( string $hook ): bool {
	unset( $hook );
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
	return in_array( $page, MRT_admin_vue_page_slugs(), true );
}

/**
 * @return array<string, mixed>
 */
function MRT_admin_vue_client_config(): array {
	return array(
		'restUrl'      => esc_url_raw( rest_url( MRT_REST_NAMESPACE ) ),
		'restNonce'    => wp_create_nonce( 'wp_rest' ),
		'initialRoute' => MRT_admin_app_initial_route(),
		'adminBase'    => admin_url( 'admin.php?page=' . MRT_ADMIN_APP_SLUG ),
		'canManage'    => current_user_can( 'manage_options' ),
		'canOperate'   => current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ),
		'isDevMode'    => MRT_is_development_mode(),
	);
}

/**
 * Enqueue admin Vue bundle (fixed path assets/admin.js).
 */
function MRT_enqueue_admin_vue_assets(): void {
	$base_url = MRT_assets_base_url() . 'dist/vue/assets/admin.js';
	if ( ! is_readable( MRT_PATH . 'assets/dist/vue/assets/admin.js' ) ) {
		return;
	}
	wp_enqueue_script(
		'mrt-vue-admin',
		$base_url,
		array(),
		MRT_VERSION,
		true
	);
	wp_localize_script( 'mrt-vue-admin', 'mrtAdminVue', MRT_admin_vue_client_config() );
}

/**
 * @param string $hook Admin hook.
 */
function MRT_maybe_enqueue_admin_vue( string $hook ): void {
	if ( ! MRT_is_admin_vue_screen( $hook ) ) {
		return;
	}
	MRT_enqueue_admin_css( $hook );
	MRT_enqueue_admin_vue_assets();
}

add_action( 'admin_enqueue_scripts', 'MRT_maybe_enqueue_admin_vue', 20 );
