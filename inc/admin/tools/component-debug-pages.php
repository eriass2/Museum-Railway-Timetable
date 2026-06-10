<?php
/**
 * Per-component debug pages with fixture / import-backed data (development only).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string
 */
function MRT_component_debug_intro_html(): string {
	return '<div class="mrt-alert mrt-alert-warning mrt-mb-lg"><p><strong>' .
		esc_html__( 'Utvecklingssida för UI', 'museum-railway-timetable' ) .
		'</strong> — ' .
		esc_html__(
			'Fixturdata för snabb layout. Kör Importera Lennakatten för riktiga månads-/översiktsdata. Wizard-debug använder hårdkodade exempelturer.',
			'museum-railway-timetable'
		) .
		'</p></div>';
}

/**
 * Page specs for component debug (one shortcode per page).
 *
 * @return array<int, array{option: string, title: string, menu_label: string, content: string|callable(): string}>
 */
function MRT_component_debug_page_specs(): array {
	if ( ! MRT_is_development_mode() ) {
		return array();
	}

	$tt   = function_exists( 'MRT_demo_lennakatten_timetable_title' )
		? MRT_demo_lennakatten_timetable_title()
		: 'GRÖN TIDTABELL 2026';
	$date = function_exists( 'MRT_debug_lennakatten_sample_date' )
		? MRT_debug_lennakatten_sample_date()
		: '2026-05-30';

	return array(
		array(
			'option'     => 'mrt_debug_page_month_id',
			'title'      => __( 'Debug – Månadskalender', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Månad', 'museum-railway-timetable' ),
			'content'    => static function () use ( $date ): string {
				return MRT_component_debug_intro_html() .
					'[museum_timetable_month month="' . esc_attr( $date ) . '" show_counts="1" legend="1"]';
			},
		),
		array(
			'option'     => 'mrt_debug_page_overview_id',
			'title'      => __( 'Debug – Tidtabellsöversikt', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Översikt', 'museum-railway-timetable' ),
			'content'    => static function () use ( $tt ): string {
				return MRT_component_debug_intro_html() .
					'[museum_timetable_overview timetable="' . esc_attr( $tt ) . '"]';
			},
		),
		array(
			'option'     => 'mrt_debug_page_wizard_date_id',
			'title'      => __( 'Debug – Wizard (datum)', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Wizard datum', 'museum-railway-timetable' ),
			'content'    => '[museum_journey_wizard embedded="1" debug="date"]',
		),
		array(
			'option'     => 'mrt_debug_page_wizard_outbound_id',
			'title'      => __( 'Debug – Wizard (utresa)', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Wizard utresa', 'museum-railway-timetable' ),
			'content'    => '[museum_journey_wizard embedded="1" debug="outbound"]',
		),
		array(
			'option'     => 'mrt_debug_page_wizard_return_id',
			'title'      => __( 'Debug – Wizard (retur)', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Wizard retur', 'museum-railway-timetable' ),
			'content'    => '[museum_journey_wizard embedded="1" debug="return"]',
		),
		array(
			'option'     => 'mrt_debug_page_wizard_summary_id',
			'title'      => __( 'Debug – Wizard (sammanfattning)', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Wizard sammanfattning', 'museum-railway-timetable' ),
			'content'    => '[museum_journey_wizard embedded="1" debug="summary"]',
		),
	);
}

/**
 * Create or update all component debug pages.
 *
 * @return array{page_ids: int[], errors: WP_Error[]}
 */
function MRT_ensure_component_debug_pages(): array {
	$page_ids = array();
	$errors   = array();
	foreach ( MRT_component_debug_page_specs() as $spec ) {
		$result = MRT_ensure_option_backed_page( $spec['option'], $spec['title'], $spec['content'] );
		if ( is_wp_error( $result ) ) {
			$errors[] = $result;
			continue;
		}
		$page_ids[] = (int) $result;
	}
	return array(
		'page_ids' => $page_ids,
		'errors'   => $errors,
	);
}

/**
 * Admin list of debug page permalinks (after setup).
 */
function MRT_render_component_debug_page_admin_links(): void {
	if ( ! function_exists( 'MRT_component_debug_page_specs' ) ) {
		return;
	}
	echo '<ul class="ul-disc">';
	foreach ( MRT_component_debug_page_specs() as $spec ) {
		$page_id = (int) get_option( $spec['option'], 0 );
		if ( $page_id <= 0 || ! get_post( $page_id ) ) {
			echo '<li>' . esc_html( $spec['menu_label'] ) . ' — ' .
				esc_html__( 'Inte skapad än (kör Sätt upp utvecklingsmeny)', 'museum-railway-timetable' ) .
				'</li>';
			continue;
		}
		$url = get_permalink( $page_id );
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $spec['menu_label'] ) . '</a></li>';
	}
	echo '</ul>';
}
