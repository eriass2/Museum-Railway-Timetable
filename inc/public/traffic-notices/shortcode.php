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

require_once MRT_PATH . 'inc/domain/traffic-notices/aggregate.php';

/**
 * @param array<string, string> $atts Shortcode attributes.
 * @return array<string, mixed>
 */
function MRT_traffic_notices_build_context( array $atts ): array {
	$atts = shortcode_atts(
		array(
			'days'            => '1',
			'date'            => '',
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
	$days            = max( 1, min( 2, (int) $atts['days'] ) );
	$show_general    = $atts['show_general'] === '1' || $atts['show_general'] === 'true';
	$show_deviations = $atts['show_deviations'] === '1' || $atts['show_deviations'] === 'true';
	$payload         = MRT_traffic_notices_aggregate( $reference_date, $days, $show_general, $show_deviations );
	if ( is_wp_error( $payload ) ) {
		$payload = array(
			'reference_date' => $reference_date,
			'days'           => $days,
			'general'        => array(),
			'by_date'        => array(),
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
	$context = MRT_traffic_notices_build_context( (array) $atts );
	$mount   = MRT_render_vue_mount( 'traffic_notices', MRT_vue_traffic_notices_config( $context ) );
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
 * @param array<string, mixed> $payload Aggregate payload.
 */
function MRT_render_traffic_notices_html( array $payload, string $title = '' ): string {
	$is_empty = ! empty( $payload['is_empty'] );
	$general  = isset( $payload['general'] ) && is_array( $payload['general'] ) ? $payload['general'] : array();
	$by_date  = isset( $payload['by_date'] ) && is_array( $payload['by_date'] ) ? $payload['by_date'] : array();

	$out = '<div class="mrt-traffic-notices">';
	if ( $title !== '' ) {
		$out .= '<h2 class="mrt-traffic-notices__title">' . esc_html( $title ) . '</h2>';
	}
	if ( $is_empty ) {
		$out .= '<p class="mrt-traffic-notices__empty">' . esc_html__( 'Inga meddelanden', 'museum-railway-timetable' ) . '</p>';
		$out .= '</div>';
		return $out;
	}
	$out .= '<ul class="mrt-traffic-notices__list">';
	foreach ( $general as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$text = trim( (string) ( $item['text'] ?? '' ) );
		if ( $text === '' ) {
			continue;
		}
		$out .= '<li class="mrt-traffic-notices__item mrt-traffic-notices__item--general">' . esc_html( $text ) . '</li>';
	}
	foreach ( $by_date as $group ) {
		if ( ! is_array( $group ) ) {
			continue;
		}
		$show_heading = count( $by_date ) > 1 || (int) ( $payload['days'] ?? 1 ) > 1;
		if ( $show_heading && ! empty( $group['date_label'] ) ) {
			$out .= '<li class="mrt-traffic-notices__day-heading"><span>' . esc_html( (string) $group['date_label'] ) . '</span></li>';
		}
		$deviations = isset( $group['deviations'] ) && is_array( $group['deviations'] ) ? $group['deviations'] : array();
		foreach ( $deviations as $deviation ) {
			if ( ! is_array( $deviation ) ) {
				continue;
			}
			$classes = 'mrt-traffic-notices__item mrt-traffic-notices__item--deviation';
			if ( ! empty( $deviation['is_cancelled'] ) ) {
				$classes .= ' mrt-traffic-notices__item--cancelled';
			}
			$line = MRT_traffic_notice_deviation_line_text( $deviation );
			$out .= '<li class="' . esc_attr( $classes ) . '">' . esc_html( $line ) . '</li>';
		}
	}
	$out .= '</ul></div>';
	return $out;
}
