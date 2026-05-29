<?php
/**
 * WP-CLI / Docker dev reset: clear plugin data, import, smoke pages + menu.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether automated dev reset is allowed (not on a typical live site).
 *
 * @return bool
 */
function MRT_dev_cli_allowed(): bool {
	if ( MRT_is_development_mode() ) {
		return true;
	}
	return defined( 'WP_CLI' ) && WP_CLI;
}

/**
 * Clear all plugin-owned data (no admin nonce; for CLI).
 */
function MRT_clear_all_plugin_data(): void {
	MRT_clear_plugin_posts();
	MRT_clear_plugin_terms();
	MRT_clear_plugin_tables();
	MRT_clear_plugin_options();
}

/**
 * Set current user for CLI operations (admin user).
 */
function MRT_dev_cli_set_admin_user(): void {
	$admin = get_user_by( 'login', 'admin' );
	$user_id = ( $admin instanceof WP_User ) ? (int) $admin->ID : 1;
	wp_set_current_user( $user_id );
}

/**
 * Permalinks for dev smoke pages after setup.
 *
 * @return array<string, string> Keys: component_demo, wizard
 */
function MRT_dev_smoke_page_permalinks(): array {
	$out = array();
	foreach ( MRT_dev_smoke_page_specs() as $spec ) {
		$key     = str_contains( $spec['option'], 'wizard' ) ? 'wizard'
			: ( str_contains( $spec['option'], 'planner' ) ? 'planner' : 'component_demo' );
		$page_id = (int) get_option( $spec['option'], 0 );
		if ( $page_id > 0 ) {
			$url = get_permalink( $page_id );
			if ( is_string( $url ) && $url !== '' ) {
				$out[ $key ] = $url;
			}
		}
	}
	return $out;
}

/**
 * Clear plugin data, import Lennakatten, create smoke pages and menu links.
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_dev_reset_and_import() {
	if ( ! MRT_dev_cli_allowed() ) {
		return new WP_Error(
			'mrt_not_dev',
			'Dev reset requires WP_DEBUG, MRT_DEVELOPMENT, or WP-CLI.'
		);
	}
	if ( ! function_exists( 'MRT_run_lennakatten_import_package' ) ) {
		require_once MRT_PATH . 'inc/import/lennakatten/importer.php';
	}

	MRT_dev_cli_set_admin_user();
	MRT_ensure_pretty_permalinks();
	MRT_clear_all_plugin_data();

	$import_result = MRT_run_lennakatten_import_package();
	if ( is_wp_error( $import_result ) ) {
		return $import_result;
	}
	$import_message = sprintf(
		'Stations: %d, Routes: %d, Timetables: %d, Services: %d.',
		(int) ( $import_result['stations'] ?? 0 ),
		(int) ( $import_result['routes'] ?? 0 ),
		(int) ( $import_result['timetables'] ?? 0 ),
		(int) ( $import_result['services'] ?? 0 )
	);

	$nav_result = null;
	if ( function_exists( 'MRT_setup_development_navigation' ) ) {
		$nav_result = MRT_setup_development_navigation();
		if ( is_wp_error( $nav_result ) ) {
			return $nav_result;
		}
	} elseif ( function_exists( 'MRT_ensure_components_demo_page_cli' ) ) {
		$demo = MRT_ensure_components_demo_page_cli();
		if ( is_wp_error( $demo ) ) {
			return $demo;
		}
	}

	if ( function_exists( 'MRT_sync_timetable_public_pages' ) ) {
		$pages_result = MRT_sync_timetable_public_pages();
		if ( is_wp_error( $pages_result ) ) {
			return $pages_result;
		}
	}

	return array(
		'cleared'        => true,
		'import_message' => is_string( $import_message ) ? $import_message : '',
		'navigation'     => $nav_result,
		'pages'          => MRT_dev_smoke_page_permalinks(),
		'admin'          => admin_url( 'admin.php?page=mrt_settings' ),
		'front'          => home_url( '/' ),
	);
}

/**
 * Echo JSON result for `wp eval` (exits 1 on error).
 */
function MRT_dev_reset_and_import_cli(): void {
	$result = MRT_dev_reset_and_import();
	if ( is_wp_error( $result ) ) {
		if ( defined( 'STDERR' ) ) {
			fwrite( STDERR, $result->get_error_message() . PHP_EOL );
		}
		exit( 1 );
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI JSON
	echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
}
