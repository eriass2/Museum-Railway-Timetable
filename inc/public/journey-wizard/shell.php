<?php
/**
 * Journey wizard shortcode (Vue mount).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param mixed $value Shortcode boolean attribute.
 */
function MRT_journey_wizard_shortcode_bool( $value ): bool {
	if ( is_bool( $value ) ) {
		return $value;
	}
	$normalized = strtolower( trim( (string) $value ) );
	return in_array( $normalized, array( '1', 'true', 'yes', 'on' ), true );
}

/**
 * Sanitize debug preset key (development only).
 */
function MRT_journey_wizard_sanitize_debug_attr( string $debug ): string {
	if ( ! MRT_is_development_mode() || ! function_exists( 'MRT_journey_wizard_debug_presets' ) ) {
		return '';
	}
	$debug   = sanitize_key( $debug );
	$allowed = array_keys( MRT_journey_wizard_debug_presets() );
	return in_array( $debug, $allowed, true ) ? $debug : '';
}

/**
 * @return array{ticket_url: string, hero_subtitle: string, timetable_id: int, timetable_page_url: string, embedded: bool, debug: string}
 */
function MRT_journey_wizard_parse_shortcode_atts( $atts ): array {
	$atts = shortcode_atts(
		array(
			'ticket_url'         => '',
			'hero_subtitle'      => '',
			'timetable_id'       => '',
			'timetable'          => '',
			'timetable_page_url' => '',
			'embedded'           => '',
			'debug'              => '',
		),
		(array) $atts,
		'museum_journey_wizard'
	);

	$timetable_id = MRT_journey_wizard_resolve_timetable_id( $atts );

	return array(
		'ticket_url'         => esc_url( $atts['ticket_url'] ),
		'hero_subtitle'      => is_string( $atts['hero_subtitle'] ) ? $atts['hero_subtitle'] : '',
		'timetable_id'       => $timetable_id,
		'timetable_page_url' => esc_url( is_string( $atts['timetable_page_url'] ) ? $atts['timetable_page_url'] : '' ),
		'embedded'           => MRT_journey_wizard_shortcode_bool( $atts['embedded'] ),
		'debug'              => MRT_journey_wizard_sanitize_debug_attr( is_string( $atts['debug'] ) ? $atts['debug'] : '' ),
	);
}

/**
 * Render [museum_journey_wizard]
 *
 * @param array|string $atts Shortcode attributes
 * @return string HTML
 */
function MRT_render_shortcode_journey_wizard( $atts ) {
	$parsed        = MRT_journey_wizard_parse_shortcode_atts( $atts );
	$ticket_url    = $parsed['ticket_url'];
	$hero_subtitle = isset( $parsed['hero_subtitle'] ) && is_string( $parsed['hero_subtitle'] ) ? trim( $parsed['hero_subtitle'] ) : '';
	$timetable_id  = isset( $parsed['timetable_id'] ) ? intval( $parsed['timetable_id'] ) : 0;
	$embedded      = ! empty( $parsed['embedded'] );
	$debug         = isset( $parsed['debug'] ) ? (string) $parsed['debug'] : '';

	$stations = MRT_get_all_stations();

	return MRT_render_vue_mount(
		'wizard',
		MRT_vue_wizard_config(
			$stations,
			array(
				'ticket_url'         => $ticket_url,
				'hero_subtitle'      => $hero_subtitle,
				'timetable_id'       => $timetable_id,
				'timetable_page_url' => isset( $parsed['timetable_page_url'] ) ? (string) $parsed['timetable_page_url'] : '',
				'embedded'           => $embedded,
				'debug'              => $debug,
			)
		)
	);
}
