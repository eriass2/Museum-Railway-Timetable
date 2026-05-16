<?php
/**
 * Dashboard data/demo actions.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Handle dashboard tool actions.
 */
function MRT_handle_dashboard_tool_actions(): void {
	if ( ! isset( $_POST['mrt_action'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$action = sanitize_key( wp_unslash( $_POST['mrt_action'] ) );
	if ( $action === 'clear_db' ) {
		MRT_handle_clear_db_action();
	}
	if ( $action === 'import_demo_data' ) {
		MRT_handle_import_demo_data_action();
	}
	if ( $action === 'create_demo_page' ) {
		MRT_handle_create_demo_page_action();
	}
}

add_action( 'admin_init', 'MRT_handle_dashboard_tool_actions' );

/**
 * Clear all plugin-owned data.
 */
function MRT_handle_clear_db_action(): void {
	MRT_verify_dashboard_action_nonce( 'mrt_clear_db', 'mrt_clear_db_nonce' );
	MRT_clear_plugin_posts();
	MRT_clear_plugin_terms();
	MRT_clear_plugin_tables();
	MRT_clear_plugin_options();
	MRT_redirect_dashboard_notice( array( 'mrt_cleared' => '1' ) );
}

/**
 * Run demo/test data import from the dashboard.
 */
function MRT_handle_import_demo_data_action(): void {
	MRT_verify_dashboard_action_nonce( 'mrt_import_lennakatten', 'mrt_import_nonce' );
	if ( ! function_exists( 'MRT_run_lennakatten_import' ) ) {
		wp_die( esc_html__( 'Import is not available.', 'museum-railway-timetable' ) );
	}
	MRT_run_lennakatten_import();
	MRT_redirect_dashboard_notice( array( 'mrt_imported' => '1' ) );
}

/**
 * Create or update the all-shortcodes demo page from the dashboard.
 */
function MRT_handle_create_demo_page_action(): void {
	MRT_verify_dashboard_action_nonce( 'mrt_components_demo', 'mrt_components_demo_nonce' );
	if ( ! function_exists( 'MRT_ensure_components_demo_page' ) ) {
		wp_die( esc_html__( 'Demo page creation is not available.', 'museum-railway-timetable' ) );
	}
	$result = MRT_ensure_components_demo_page();
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ) );
	}
	MRT_redirect_dashboard_notice( array( 'mrt_demo_page' => (int) $result ) );
}

/**
 * Verify a dashboard tool nonce.
 */
function MRT_verify_dashboard_action_nonce( string $action, string $field ): void {
	$nonce = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, $action ) ) {
		wp_die( esc_html__( 'Security check failed.', 'museum-railway-timetable' ) );
	}
}

/**
 * Delete plugin custom post type content.
 */
function MRT_clear_plugin_posts(): void {
	foreach ( MRT_POST_TYPES as $post_type ) {
		$ids = get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		foreach ( $ids as $id ) {
			wp_delete_post( (int) $id, true );
		}
	}
	MRT_clear_demo_page_post();
}

/**
 * Delete the generated demo page if it exists.
 */
function MRT_clear_demo_page_post(): void {
	$page_id = defined( 'MRT_OPTION_COMPONENTS_DEMO_PAGE_ID' ) ? (int) get_option( MRT_OPTION_COMPONENTS_DEMO_PAGE_ID, 0 ) : 0;
	if ( $page_id > 0 && get_post( $page_id ) && get_post_type( $page_id ) === 'page' ) {
		wp_delete_post( $page_id, true );
	}
}

/**
 * Delete plugin taxonomy terms.
 */
function MRT_clear_plugin_terms(): void {
	$terms = get_terms(
		array(
			'taxonomy'   => MRT_TAXONOMY_TRAIN_TYPE,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return;
	}
	foreach ( $terms as $term ) {
		wp_delete_term( (int) $term->term_id, MRT_TAXONOMY_TRAIN_TYPE );
	}
}

/**
 * Empty plugin custom tables.
 */
function MRT_clear_plugin_tables(): void {
	global $wpdb;
	$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}mrt_stoptimes" );
}

/**
 * Delete plugin options.
 */
function MRT_clear_plugin_options(): void {
	delete_option( 'mrt_settings' );
	delete_option( 'mrt_price_matrix' );
	if ( defined( 'MRT_OPTION_COMPONENTS_DEMO_PAGE_ID' ) ) {
		delete_option( MRT_OPTION_COMPONENTS_DEMO_PAGE_ID );
	}
}

/**
 * Redirect to dashboard with notice args.
 *
 * @param array<string, int|string> $args Query args
 */
function MRT_redirect_dashboard_notice( array $args ): void {
	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php?page=mrt_settings' ) ) );
	exit;
}

/**
 * Render dashboard notices for tool actions.
 */
function MRT_render_dashboard_tool_notices(): void {
	$notices = MRT_dashboard_tool_notices();
	foreach ( $notices as $query_arg => $message ) {
		if ( isset( $_GET[ $query_arg ] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
		}
	}
}

add_action( 'admin_notices', 'MRT_render_dashboard_tool_notices' );

/**
 * Success messages keyed by redirect query arg.
 *
 * @return array<string, string>
 */
function MRT_dashboard_tool_notices(): array {
	return array(
		'mrt_cleared'   => __( 'All plugin timetable data, demo page, train types, stop times, and settings have been cleared.', 'museum-railway-timetable' ),
		'mrt_imported'  => __( 'Demo/test data has been imported.', 'museum-railway-timetable' ),
		'mrt_demo_page' => __( 'Demo page has been created or updated.', 'museum-railway-timetable' ),
	);
}
