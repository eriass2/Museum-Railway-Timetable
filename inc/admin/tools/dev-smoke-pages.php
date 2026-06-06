<?php
/**
 * Development smoke page specs and lifecycle.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smoke page definitions (content callback for component demo).
 *
 * @return array<int, array{option: string, title: string, menu_label: string, content: string|callable}>
 */
function MRT_dev_smoke_page_specs(): array {
	$tt = function_exists( 'MRT_demo_lennakatten_timetable_title' )
		? MRT_demo_lennakatten_timetable_title()
		: 'GRÖN TIDTABELL 2026';

	return array(
		array(
			'option'     => MRT_OPTION_COMPONENTS_DEMO_PAGE_ID,
			'title'      => __( 'Museijärnvägens tidtabell – komponentdemo', 'museum-railway-timetable' ),
			'menu_label' => __( 'Komponentdemo', 'museum-railway-timetable' ),
			'content'    => static function (): string {
				return MRT_get_components_demo_page_content();
			},
		),
		array(
			'option'     => MRT_OPTION_WIZARD_SMOKE_PAGE_ID,
			'title'      => __( 'Wizard-smoketest', 'museum-railway-timetable' ),
			'menu_label' => __( 'Wizard-smoketest', 'museum-railway-timetable' ),
			'content'    => sprintf(
				'[museum_journey_wizard timetable="%s"]',
				esc_attr( $tt )
			),
		),
	);
}

/**
 * Create or update all development smoke pages.
 *
 * @return array{page_ids: int[], errors: WP_Error[]}
 */
function MRT_ensure_dev_smoke_pages(): array {
	$page_ids = array();
	$errors   = array();

	foreach ( MRT_dev_smoke_page_specs() as $spec ) {
		$result = MRT_ensure_option_backed_page( $spec['option'], $spec['title'], $spec['content'] );
		if ( is_wp_error( $result ) ) {
			$errors[] = $result;
			continue;
		}
		$page_ids[] = (int) $result;
	}

	$debug_pages = MRT_ensure_component_debug_pages();
	foreach ( $debug_pages['errors'] as $error ) {
		$errors[] = $error;
	}
	$page_ids = array_merge( $page_ids, $debug_pages['page_ids'] );

	return array(
		'page_ids' => $page_ids,
		'errors'   => $errors,
	);
}

/**
 * Smoke page IDs used for front-menu sync.
 *
 * @return int[]
 */
function MRT_dev_smoke_page_ids(): array {
	$ids = array();
	foreach ( MRT_dev_smoke_page_specs() as $spec ) {
		$page_id = (int) get_option( $spec['option'], 0 );
		if ( $page_id > 0 && get_post( $page_id ) ) {
			$ids[] = $page_id;
		}
	}
	return $ids;
}

/**
 * Delete plugin-owned smoke + debug pages.
 */
function MRT_clear_dev_smoke_pages(): void {
	$keys = array(
		MRT_OPTION_COMPONENTS_DEMO_PAGE_ID,
		MRT_OPTION_WIZARD_SMOKE_PAGE_ID,
	);
	if ( function_exists( 'MRT_component_debug_page_specs' ) ) {
		foreach ( MRT_component_debug_page_specs() as $spec ) {
			$keys[] = $spec['option'];
		}
	}
	foreach ( $keys as $key ) {
		$page_id = (int) get_option( $key, 0 );
		if ( $page_id > 0 && get_post( $page_id ) && get_post_type( $page_id ) === 'page' ) {
			wp_delete_post( $page_id, true );
		}
		delete_option( $key );
	}
}
