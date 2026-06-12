<?php
/**
 * Shortcode: Traffic notices [museum_traffic_notices]
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/traffic-notices/disruption-feed.php';

/**
 * @param array<string, string> $atts Shortcode attributes.
 * @return array<string, mixed>
 */
function MRT_traffic_notices_build_context( array $atts ): array {
	$atts = shortcode_atts(
		array(
			'horizon_days'    => '90',
			'date'            => '',
			'days'            => '1',
			'show_general'    => '1',
			'show_deviations' => '1',
			'title'           => '',
		),
		$atts,
		'museum_traffic_notices'
	);

	$reference_date = trim( (string) $atts['date'] );
	if ( $reference_date === '' ) {
		$reference_date = MRT_get_current_datetime()['date'];
	}
	$horizon_days = max( 1, min( MRT_DISRUPTION_FEED_MAX_HORIZON, (int) $atts['horizon_days'] ) );
	$payload      = MRT_disruption_feed_build( $reference_date, $horizon_days );
	if ( is_wp_error( $payload ) ) {
		$payload = array(
			'reference_date' => $reference_date,
			'horizon_days'   => $horizon_days,
			'end_date'       => $reference_date,
			'ongoing'        => array(),
			'upcoming'       => array(),
			'items'          => array(),
			'is_empty'       => true,
		);
	}

	return array(
		'atts'    => $atts,
		'payload' => $payload,
	);
}

/**
 * @param array<string, string> $atts Shortcode attributes.
 * @return string
 */
function MRT_render_shortcode_traffic_notices( $atts ): string {
	$context  = MRT_traffic_notices_build_context( (array) $atts );
	$mount    = MRT_render_vue_mount( 'traffic_notices', MRT_vue_traffic_notices_config( $context ) );
	$noscript = MRT_render_traffic_notices_noscript( $context );
	return $mount . $noscript;
}

/**
 * Server-rendered fallback when JavaScript is disabled.
 *
 * @param array<string, mixed> $context Built context.
 */
function MRT_render_traffic_notices_noscript( array $context ): string {
	$payload = isset( $context['payload'] ) && is_array( $context['payload'] ) ? $context['payload'] : array();
	$title   = trim( (string) ( $context['atts']['title'] ?? '' ) );
	$inner   = MRT_render_traffic_notices_html( $payload, $title );
	if ( $inner === '' ) {
		return '';
	}
	return '<noscript>' . $inner . '</noscript>';
}

/**
 * @param array<string, mixed> $payload Disruption feed payload.
 */
function MRT_render_traffic_notices_html( array $payload, string $title = '' ): string {
	$is_empty = ! empty( $payload['is_empty'] );
	$ongoing  = isset( $payload['ongoing'] ) && is_array( $payload['ongoing'] ) ? $payload['ongoing'] : array();
	$upcoming = isset( $payload['upcoming'] ) && is_array( $payload['upcoming'] ) ? $payload['upcoming'] : array();

	$out = '<div class="mrt-traffic-notices">';
	if ( $title !== '' ) {
		$out .= '<h2 class="mrt-traffic-notices__title">' . esc_html( $title ) . '</h2>';
	}
	if ( $is_empty ) {
		$out .= '<p class="mrt-traffic-notices__empty">' . esc_html__( 'Inga meddelanden', 'museum-railway-timetable' ) . '</p>';
		$out .= '</div>';
		return $out;
	}
	$out .= '<div class="mrt-traffic-notices__feed">';
	$out .= MRT_render_disruption_feed_section_html(
		__( 'Pågår nu', 'museum-railway-timetable' ),
		$ongoing
	);
	$out .= MRT_render_disruption_feed_section_html(
		__( 'Kommande', 'museum-railway-timetable' ),
		$upcoming
	);
	$out .= '</div></div>';
	return $out;
}

/**
 * @param list<array<string, mixed>> $items Feed items.
 */
function MRT_render_disruption_feed_section_html( string $heading, array $items ): string {
	if ( $items === array() ) {
		return '';
	}
	$out = '<section class="mrt-traffic-notices__section">';
	$out .= '<h3 class="mrt-traffic-notices__section-title">' . esc_html( $heading ) . '</h3>';
	$out .= '<ul class="mrt-traffic-notices__list">';
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$out .= MRT_render_disruption_feed_item_html( $item );
	}
	$out .= '</ul></section>';
	return $out;
}

/**
 * @param array<string, mixed> $item Feed item.
 */
function MRT_render_disruption_feed_item_html( array $item ): string {
	$kind     = (string) ( $item['kind'] ?? 'info' );
	$classes  = 'mrt-traffic-notices__feed-item mrt-traffic-notices__feed-item--' . sanitize_html_class( $kind );
	$headline = trim( (string) ( $item['headline'] ?? '' ) );
	$body     = trim( (string) ( $item['body'] ?? '' ) );
	$date     = trim( (string) ( $item['date_label'] ?? '' ) );
	$out      = '<li class="' . esc_attr( $classes ) . '">';
	if ( $date !== '' ) {
		$out .= '<p class="mrt-traffic-notices__date">' . esc_html( $date ) . '</p>';
	}
	if ( $headline !== '' ) {
		$out .= '<p class="mrt-traffic-notices__headline">' . esc_html( $headline ) . '</p>';
	}
	$body_display = MRT_disruption_feed_item_body_display( $item );
	if ( $body_display !== '' ) {
		$out .= '<p class="mrt-traffic-notices__body">' . esc_html( $body_display ) . '</p>';
	}
	$out .= '</li>';
	return $out;
}
