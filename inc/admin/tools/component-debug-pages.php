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
		esc_html__( 'Development UI page', 'museum-railway-timetable' ) .
		'</strong> — ' .
		esc_html__(
			'Fixture data for fast layout work. Run Import Lennakatten for real month/overview data. Wizard debug presets use hardcoded sample trips.',
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
			'title'      => __( 'Debug – Month calendar', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Month', 'museum-railway-timetable' ),
			'content'    => static function () use ( $date ): string {
				return MRT_component_debug_intro_html() .
					'[museum_timetable_month month="' . esc_attr( $date ) . '" show_counts="1" legend="1"]';
			},
		),
		array(
			'option'     => 'mrt_debug_page_overview_id',
			'title'      => __( 'Debug – Timetable overview', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Overview', 'museum-railway-timetable' ),
			'content'    => static function () use ( $tt ): string {
				return MRT_component_debug_intro_html() .
					'[museum_timetable_overview timetable="' . esc_attr( $tt ) . '"]';
			},
		),
		array(
			'option'     => 'mrt_debug_page_wizard_date_id',
			'title'      => __( 'Debug – Wizard (date)', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Wizard date', 'museum-railway-timetable' ),
			'content'    => '[museum_journey_wizard embedded="1" debug="date" hero_subtitle="Fixture: calendar step"]',
		),
		array(
			'option'     => 'mrt_debug_page_wizard_outbound_id',
			'title'      => __( 'Debug – Wizard (outbound)', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Wizard outbound', 'museum-railway-timetable' ),
			'content'    => '[museum_journey_wizard embedded="1" debug="outbound" hero_subtitle="Fixture: trip cards (direct + transfer)"]',
		),
		array(
			'option'     => 'mrt_debug_page_wizard_return_id',
			'title'      => __( 'Debug – Wizard (return)', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Wizard return', 'museum-railway-timetable' ),
			'content'    => '[museum_journey_wizard embedded="1" debug="return" hero_subtitle="Fixture: return step"]',
		),
		array(
			'option'     => 'mrt_debug_page_wizard_summary_id',
			'title'      => __( 'Debug – Wizard (summary)', 'museum-railway-timetable' ),
			'menu_label' => __( 'Debug: Wizard summary', 'museum-railway-timetable' ),
			'content'    => '[museum_journey_wizard embedded="1" debug="summary" ticket_url="https://example.com/biljetter" hero_subtitle="Fixture: summary + prices"]',
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
		$result = MRT_ensure_dev_smoke_page( $spec['option'], $spec['title'], $spec['content'] );
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
				esc_html__( 'not created yet (run Set up development menu)', 'museum-railway-timetable' ) .
				'</li>';
			continue;
		}
		$url = get_permalink( $page_id );
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $spec['menu_label'] ) . '</a></li>';
	}
	echo '</ul>';
}
