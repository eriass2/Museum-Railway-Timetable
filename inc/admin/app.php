<?php
/**
 * Vue admin app mount page.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map legacy admin page slugs to Vue initial routes.
 *
 * @return array<string, string>
 */
function MRT_admin_app_initial_routes(): array {
	$routes = array(
		MRT_ADMIN_APP_SLUG             => 'dashboard',
		'mrt_app_timetables'           => 'timetables',
		'mrt_app_stations_routes'      => 'stations-routes',
		'mrt_app_help'                 => 'help',
		'mrt_app_settings'             => 'settings',
		'mrt_app_prices'               => 'prices',
		'mrt_app_train_types'          => 'train-types',
		'mrt_app_import_export'        => 'import-export',
	);
	if ( MRT_is_development_mode() ) {
		$routes['mrt_app_dev_tools'] = 'dev-tools';
	}
	return $routes;
}

/**
 * Current admin page slug for Vue boot.
 */
function MRT_admin_app_current_slug(): string {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
	return $page !== '' ? $page : MRT_ADMIN_APP_SLUG;
}

/**
 * Initial Vue route from WP submenu slug.
 */
function MRT_admin_app_initial_route(): string {
	$slug  = MRT_admin_app_current_slug();
	$routes = MRT_admin_app_initial_routes();
	return $routes[ $slug ] ?? 'dashboard';
}

/**
 * Render Vue admin shell.
 */
function MRT_render_admin_app(): void {
	if ( ! MRT_rest_can_read() ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'museum-railway-timetable' ) );
	}

	$initial = MRT_admin_app_initial_route();
	?>
	<div class="wrap mrt-admin-vue-wrap">
		<div
			id="mrt-admin-app"
			data-mrt-admin-app="1"
			data-initial-route="<?php echo esc_attr( $initial ); ?>"
		></div>
	</div>
	<?php
}

/**
 * Vue hash for a service CPT edit redirect.
 */
function MRT_admin_service_editor_hash( int $service_id ): string {
	$timetable_id = (int) get_post_meta( $service_id, 'mrt_service_timetable_id', true );
	if ( $timetable_id > 0 ) {
		return '#/timetables/' . $timetable_id;
	}
	return '#/timetables';
}

/**
 * Redirect legacy CPT edit screens to Vue admin.
 */
function MRT_admin_redirect_legacy_cpt_screens(): void {
	global $pagenow;
	if ( ! is_admin() || $pagenow !== 'post.php' ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	if ( $post_id <= 0 ) {
		return;
	}
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	$targets = array(
		MRT_POST_TYPE_TIMETABLE => array(
			'page' => 'mrt_app_timetables',
			'hash' => '#/timetables/' . $post_id,
		),
		MRT_POST_TYPE_STATION   => array(
			'page' => 'mrt_app_stations_routes',
			'hash' => '#/stations-routes',
		),
		MRT_POST_TYPE_ROUTE     => array(
			'page' => 'mrt_app_stations_routes',
			'hash' => '#/stations-routes',
		),
		MRT_POST_TYPE_SERVICE   => array(
			'page' => 'mrt_app_timetables',
			'hash' => MRT_admin_service_editor_hash( $post_id ),
		),
	);
	if ( ! isset( $targets[ $post->post_type ] ) ) {
		return;
	}
	$target = $targets[ $post->post_type ];
	wp_safe_redirect( admin_url( 'admin.php?page=' . $target['page'] . $target['hash'] ) );
	exit;
}

add_action( 'admin_init', 'MRT_admin_redirect_legacy_cpt_screens' );

/**
 * Redirect legacy CPT list screens.
 */
function MRT_admin_redirect_legacy_cpt_lists(): void {
	global $pagenow;
	if ( ! is_admin() || $pagenow !== 'edit.php' ) {
		return;
	}
	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( (string) $_GET['post_type'] ) ) : '';
	$map       = array(
		MRT_POST_TYPE_TIMETABLE => 'mrt_app_timetables',
		MRT_POST_TYPE_STATION   => 'mrt_app_stations_routes',
		MRT_POST_TYPE_ROUTE     => 'mrt_app_stations_routes',
		MRT_POST_TYPE_SERVICE   => 'mrt_app_timetables',
	);
	if ( ! isset( $map[ $post_type ] ) ) {
		return;
	}
	wp_safe_redirect( admin_url( 'admin.php?page=' . $map[ $post_type ] ) );
	exit;
}

add_action( 'admin_init', 'MRT_admin_redirect_legacy_cpt_lists' );
