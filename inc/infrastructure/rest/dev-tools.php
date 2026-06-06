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

if ( MRT_is_development_mode() ) {
	require_once MRT_PATH . 'inc/import/lennakatten/importer.php';
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
		'/dev/client-log'             => 'MRT_rest_dev_client_log_handler',
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

/**
 * @param mixed $input Raw client context.
 * @return array<string, scalar|null>
 */
function MRT_rest_sanitize_client_log_context( $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}
	$out = array();
	foreach ( $input as $key => $value ) {
		$clean_key = sanitize_key( (string) $key );
		if ( $clean_key === '' ) {
			continue;
		}
		if ( is_scalar( $value ) || $value === null ) {
			$out[ $clean_key ] = is_string( $value ) ? sanitize_text_field( $value ) : $value;
			continue;
		}
		if ( is_array( $value ) ) {
			$encoded = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
			$out[ $clean_key ] = is_string( $encoded ) ? sanitize_text_field( $encoded ) : '';
		}
	}
	return $out;
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_dev_client_log_handler( WP_REST_Request $request ) {
	$body    = $request->get_json_params();
	$message = is_array( $body ) ? sanitize_text_field( (string) ( $body['message'] ?? '' ) ) : '';
	if ( $message === '' ) {
		return new WP_Error( 'invalid', __( 'Log message is required.', MRT_TEXT_DOMAIN ), array( 'status' => 400 ) );
	}
	$source  = sanitize_key( (string) ( is_array( $body ) ? ( $body['source'] ?? 'admin' ) : 'admin' ) );
	$level   = sanitize_key( (string) ( is_array( $body ) ? ( $body['level'] ?? 'error' ) : 'error' ) );
	$context = is_array( $body ) ? MRT_rest_sanitize_client_log_context( $body['context'] ?? array() ) : array();
	MRT_log( '[vue:' . $source . '] ' . $message, $context, $level !== '' ? $level : 'error' );
	return rest_ensure_response( array( 'logged' => true ) );
}
