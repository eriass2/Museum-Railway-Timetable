<?php
/**
 * REST: development tools (dev mode only).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register dev tool routes.
 */
function MRT_rest_register_dev_tools_routes(): void {
	if ( ! MRT_is_development_mode() ) {
		return;
	}

	$routes = array(
		'/dev/clear-db'               => 'MRT_rest_dev_clear_db_handler',
		'/dev/import-lennakatten'     => 'MRT_rest_dev_import_lennakatten_handler',
		'/dev/demo-page'              => 'MRT_rest_dev_demo_page_handler',
		'/dev/setup-navigation'       => 'MRT_rest_dev_setup_navigation_handler',
		'/dev/sync-timetable-pages'   => 'MRT_rest_dev_sync_timetable_pages_handler',
	);

	foreach ( $routes as $path => $callback ) {
		register_rest_route(
			MRT_REST_NAMESPACE,
			$path,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => $callback,
				'permission_callback' => 'MRT_rest_dev_tools_permission',
			)
		);
	}
}

/**
 * Dev tools require manage_options and development mode.
 */
function MRT_rest_dev_tools_permission(): bool {
	return MRT_is_development_mode() && current_user_can( 'manage_options' );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_dev_clear_db_handler( WP_REST_Request $request ) {
	unset( $request );
	if ( ! function_exists( 'MRT_clear_all_plugin_data' ) ) {
		return new WP_Error( 'unavailable', __( 'Clear DB is not available.', MRT_TEXT_DOMAIN ), array( 'status' => 500 ) );
	}
	MRT_clear_all_plugin_data();
	return rest_ensure_response( array( 'cleared' => true ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_dev_import_lennakatten_handler( WP_REST_Request $request ) {
	unset( $request );
	if ( ! function_exists( 'MRT_run_lennakatten_import' ) ) {
		return new WP_Error( 'unavailable', __( 'Import is not available.', MRT_TEXT_DOMAIN ), array( 'status' => 500 ) );
	}
	MRT_run_lennakatten_import();
	return rest_ensure_response( array( 'imported' => true ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_dev_demo_page_handler( WP_REST_Request $request ) {
	unset( $request );
	if ( ! function_exists( 'MRT_ensure_components_demo_page' ) ) {
		return new WP_Error( 'unavailable', __( 'Demo page creation is not available.', MRT_TEXT_DOMAIN ), array( 'status' => 500 ) );
	}
	$result = MRT_ensure_components_demo_page();
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( array( 'page_id' => (int) $result ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_dev_setup_navigation_handler( WP_REST_Request $request ) {
	unset( $request );
	if ( ! function_exists( 'MRT_setup_development_navigation' ) ) {
		return new WP_Error( 'unavailable', __( 'Dev navigation is not available.', MRT_TEXT_DOMAIN ), array( 'status' => 500 ) );
	}
	$result = MRT_setup_development_navigation();
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response(
		array(
			'menu_id' => (int) ( $result['menu_id'] ?? 0 ),
			'added'   => (int) ( $result['added'] ?? 0 ),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_dev_sync_timetable_pages_handler( WP_REST_Request $request ) {
	unset( $request );
	if ( ! function_exists( 'MRT_sync_timetable_public_pages' ) ) {
		return new WP_Error( 'unavailable', __( 'Timetable page sync is not available.', MRT_TEXT_DOMAIN ), array( 'status' => 500 ) );
	}
	$result = MRT_sync_timetable_public_pages();
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response(
		array(
			'index_page_id'      => (int) ( $result['index_page_id'] ?? 0 ),
			'timetable_page_ids' => array_map( 'intval', (array) ( $result['timetable_page_ids'] ?? array() ) ),
		)
	);
}
