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
	if ( function_exists( 'MRT_enqueue_traffic_info_tokens' ) ) {
		MRT_enqueue_traffic_info_tokens();
	}
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
	$panels   = MRT_render_traffic_notices_resolve_panels( $payload );

	$out = '<div class="mrt-traffic-notices">';
	if ( $title !== '' ) {
		$out .= '<h2 class="mrt-traffic-notices__title">' . esc_html( $title ) . '</h2>';
	}
	if ( $is_empty || $panels === array() ) {
		$out .= '<p class="mrt-traffic-notices__empty">' . esc_html__( 'Inga meddelanden', 'museum-railway-timetable' ) . '</p>';
		$out .= '</div>';
		return $out;
	}
	$out .= '<div class="mrt-tf-feed">';
	foreach ( $panels as $panel ) {
		if ( ! is_array( $panel ) ) {
			continue;
		}
		$out .= MRT_render_tf_panel_html( $panel );
	}
	$out .= '</div></div>';
	return $out;
}

/**
 * @param array<string, mixed> $payload
 * @return list<array<string, mixed>>
 */
function MRT_render_traffic_notices_resolve_panels( array $payload ): array {
	$panels = isset( $payload['panels'] ) && is_array( $payload['panels'] ) ? $payload['panels'] : array();
	if ( $panels !== array() ) {
		return $panels;
	}
	$ongoing  = isset( $payload['ongoing'] ) && is_array( $payload['ongoing'] ) ? $payload['ongoing'] : array();
	$upcoming = isset( $payload['upcoming'] ) && is_array( $payload['upcoming'] ) ? $payload['upcoming'] : array();
	$built    = array();
	if ( $ongoing !== array() ) {
		$built[] = MRT_disruption_feed_build_panel( 'ongoing', $ongoing );
	}
	if ( $upcoming !== array() ) {
		$built[] = MRT_disruption_feed_build_panel( 'upcoming', $upcoming );
	}
	return $built;
}

/**
 * @param array<string, mixed> $panel
 */
function MRT_render_tf_panel_html( array $panel ): string {
	$title      = trim( (string) ( $panel['title'] ?? '' ) );
	$icon       = (string) ( $panel['icon'] ?? 'clock' );
	$categories = isset( $panel['categories'] ) && is_array( $panel['categories'] ) ? $panel['categories'] : array();
	$out        = '<section class="mrt-tf-panel" aria-label="' . esc_attr( $title ) . '">';
	$out       .= '<h3 class="mrt-tf-panel__header">';
	$out       .= '<span class="mrt-tf-panel__icon" aria-hidden="true">';
	$out       .= $icon === 'calendar' ? '▦' : '◷';
	$out       .= '</span><span>' . esc_html( $title ) . '</span></h3>';
	foreach ( $categories as $category ) {
		if ( ! is_array( $category ) ) {
			continue;
		}
		$out .= MRT_render_tf_category_html( $category );
	}
	$out .= '</section>';
	return $out;
}

/**
 * @param string $key Category key (train, bus, or other).
 */
function MRT_render_tf_category_icon_html( string $key ): string {
	$class = 'mrt-tf-category__icon';
	if ( $key === 'train' ) {
		return '<span class="' . esc_attr( $class ) . '" aria-hidden="true">'
			. '<svg viewBox="0 0 24 24" fill="none" width="20" height="20">'
			. '<rect x="5" y="8" width="14" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/>'
			. '<path d="M8 17v2M16 17v2M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
			. '</svg></span>';
	}
	if ( $key === 'bus' ) {
		return '<span class="' . esc_attr( $class ) . '" aria-hidden="true">'
			. '<svg viewBox="0 0 24 24" fill="none" width="20" height="20">'
			. '<rect x="4" y="6" width="16" height="11" rx="2" stroke="currentColor" stroke-width="2"/>'
			. '<path d="M4 11h16" stroke="currentColor" stroke-width="2"/>'
			. '</svg></span>';
	}
	return '<span class="' . esc_attr( $class ) . '" aria-hidden="true">'
		. '<svg viewBox="0 0 24 24" fill="none" width="20" height="20">'
		. '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>'
		. '<path d="M12 10v6M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
		. '</svg></span>';
}

/**
 * @param array<string, mixed> $category
 */
function MRT_render_tf_category_html( array $category ): string {
	$label  = trim( (string) ( $category['label'] ?? '' ) );
	$counts = isset( $category['counts'] ) && is_array( $category['counts'] ) ? $category['counts'] : array();
	$items  = isset( $category['items'] ) && is_array( $category['items'] ) ? $category['items'] : array();
	$out    = '<div class="mrt-tf-category">';
	$out   .= '<div class="mrt-tf-category__row">';
	$out   .= MRT_render_tf_category_icon_html( (string) ( $category['key'] ?? '' ) );
	$out   .= '<span class="mrt-tf-category__label">' . esc_html( $label ) . '</span>';
	$out   .= '<span class="mrt-tf-category__badges">';
	$info    = (int) ( $counts['info'] ?? 0 );
	$warning = (int) ( $counts['warning'] ?? 0 );
	if ( $info > 0 ) {
		$out .= MRT_render_tf_count_badge_html( 'info', $info );
	}
	if ( $warning > 0 ) {
		$out .= MRT_render_tf_count_badge_html( 'warning', $warning );
	}
	$out .= '</span></div>';
	if ( $items !== array() ) {
		$out .= '<div class="mrt-tf-category__alerts"><ul class="mrt-tf-alert-list">';
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$out .= MRT_render_tf_alert_html( $item );
		}
		$out .= '</ul></div>';
	}
	$out .= '</div>';
	return $out;
}

function MRT_render_tf_count_badge_html( string $variant, int $count ): string {
	$mark = $variant === 'info' ? 'i' : '!';
	$out  = '<span class="mrt-tf-count-badge mrt-tf-count-badge--' . esc_attr( $variant ) . '">';
	$out .= '<span class="mrt-tf-count-badge__mark" aria-hidden="true">' . esc_html( $mark ) . '</span>';
	$out .= '<span class="mrt-tf-count-badge__count">' . esc_html( (string) $count ) . '</span>';
	$out .= '</span>';
	return $out;
}

/**
 * @param array<string, mixed> $item
 */
function MRT_render_tf_alert_html( array $item ): string {
	$summary        = trim( (string) ( $item['summary'] ?? $item['headline'] ?? '' ) );
	$validity_label = trim( (string) ( $item['validity_label'] ?? '' ) );
	$line_label     = trim( (string) ( $item['line_label'] ?? '' ) );
	$intro          = trim( (string) ( $item['detail_intro'] ?? '' ) );
	if ( $intro === '' ) {
		$intro = MRT_disruption_feed_item_body_display( $item );
	}
	$out = '<li class="mrt-tf-alert"><div class="mrt-tf-alert__main">';
	if ( $line_label !== '' ) {
		$out .= '<span class="mrt-tf-line-badge">' . esc_html( $line_label ) . '</span>';
	}
	$out .= '<div class="mrt-tf-alert__body">';
	$out .= '<div class="mrt-tf-alert__summary">' . esc_html( $summary ) . '</div>';
	if ( $validity_label !== '' ) {
		$out .= '<p class="mrt-tf-alert__validity"><span>' . esc_html( $validity_label ) . '</span></p>';
	}
	$out .= '</div></div>';
	if ( $intro !== '' || MRT_disruption_feed_item_has_expandable_content( $item ) ) {
		$out .= '<div class="mrt-tf-alert__detail">';
		if ( $intro !== '' ) {
			$out .= '<p>' . esc_html( $intro ) . '</p>';
		}
		$out .= MRT_render_disruption_feed_item_sections_html( $item );
		$out .= '</div>';
	}
	$out .= '</li>';
	return $out;
}

/**
 * @param array<string, mixed> $item Feed item.
 */
function MRT_render_disruption_feed_item_sections_html( array $item ): string {
	$sections = $item['detail_sections'] ?? array();
	if ( ! is_array( $sections ) || $sections === array() ) {
		return '';
	}
	$out = '';
	foreach ( $sections as $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}
		$title = trim( (string) ( $section['title'] ?? '' ) );
		$lines = isset( $section['lines'] ) && is_array( $section['lines'] )
			? array_values( array_filter( array_map( 'strval', $section['lines'] ) ) )
			: array();
		if ( $lines === array() ) {
			continue;
		}
		$out .= '<div class="mrt-tf-alert__detail-section">';
		if ( $title !== '' ) {
			$out .= '<h4 class="mrt-tf-alert__detail-title">' . esc_html( $title ) . '</h4>';
		}
		$out .= '<ul class="mrt-tf-alert__detail-lines">';
		foreach ( $lines as $line ) {
			$out .= '<li>' . esc_html( $line ) . '</li>';
		}
		$out .= '</ul></div>';
	}
	return $out;
}
