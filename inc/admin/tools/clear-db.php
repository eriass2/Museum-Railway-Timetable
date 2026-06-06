<?php
/**
 * Plugin data cleanup utilities (REST, CLI, dev tools).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Delete plugin custom post type content.
 */
function MRT_clear_plugin_posts(): void {
	if ( function_exists( 'MRT_clear_timetable_public_pages' ) ) {
		MRT_clear_timetable_public_pages();
	}
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
	if ( function_exists( 'MRT_clear_dev_smoke_pages' ) ) {
		MRT_clear_dev_smoke_pages();
	} else {
		MRT_clear_demo_page_post();
	}
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
	delete_option( 'mrt_brand_tokens' );
	delete_option( 'mrt_price_matrix' );
	delete_option( 'mrt_price_schema' );
	delete_option( 'mrt_components_demo_page_id' );
	delete_option( 'mrt_wizard_smoke_page_id' );
	delete_option( 'mrt_planner_smoke_page_id' );
	delete_option( 'mrt_debug_page_month_id' );
	delete_option( 'mrt_debug_page_overview_id' );
	delete_option( 'mrt_debug_page_wizard_date_id' );
	delete_option( 'mrt_debug_page_wizard_outbound_id' );
	delete_option( 'mrt_debug_page_wizard_return_id' );
	delete_option( 'mrt_debug_page_wizard_summary_id' );
	delete_option( 'mrt_timetables_index_page_id' );
	delete_option( 'mrt_dev_nav_menu_id' );
	delete_option( 'mrt_dev_wp_navigation_id' );
}
