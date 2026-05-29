<?php
/**
 * Shortcode: Timetables index [museum_timetable_index]
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/timetable-pages.php';

/**
 * Mark index shortcode usage (late asset enqueue in block themes).
 */
function MRT_timetable_index_mark_used(): void {
	$GLOBALS['mrt_timetable_index_used'] = true;
}

/**
 * Whether the index shortcode rendered on this request.
 */
function MRT_timetable_index_was_used(): bool {
	return ! empty( $GLOBALS['mrt_timetable_index_used'] );
}

/**
 * Render list of timetables with links to public pages.
 *
 * @param array<string, string> $atts Shortcode attributes.
 * @return string
 */
function MRT_render_shortcode_timetable_index( $atts ) {
	MRT_timetable_index_mark_used();
	MRT_enqueue_timetable_index_styles_if_needed();

	$atts = shortcode_atts(
		array(
			'show_dates' => '1',
			'intro'      => '1',
		),
		$atts,
		'museum_timetable_index'
	);

	$timetables = MRT_get_published_timetables();
	if ( $timetables === array() ) {
		return MRT_render_alert(
			__( 'No timetables are published yet.', 'museum-railway-timetable' ),
			'info'
		);
	}

	$show_dates = $atts['show_dates'] === '1' || $atts['show_dates'] === 'true';
	$show_intro = $atts['intro'] !== '0' && $atts['intro'] !== 'false';
	$items      = array();

	foreach ( $timetables as $timetable ) {
		$timetable_id = (int) $timetable->ID;
		$url          = MRT_timetable_public_page_url( $timetable_id );
		$label        = get_the_title( $timetable );
		if ( $label === '' ) {
			$label = __( 'Timetable', 'museum-railway-timetable' ) . ' #' . $timetable_id;
		}
		$meta = '';
		if ( $show_dates ) {
			$meta = MRT_timetable_traffic_days_summary( $timetable_id );
		}
		$items[] = array(
			'url'       => $url,
			'label'     => $label,
			'meta'      => $meta,
			'modifier'  => MRT_timetable_index_color_modifier( $timetable_id ),
			'aria_hint' => $meta !== '' ? $meta : __( 'View timetable', 'museum-railway-timetable' ),
		);
	}

	return MRT_render_timetable_index_html( $items, $show_intro );
}

/**
 * @param array<int, array{url: string, label: string, meta: string, modifier: string, aria_hint: string}> $items
 */
function MRT_render_timetable_index_html( array $items, bool $show_intro = true ): string {
	$out = '<div class="mrt-timetable-index">';
	if ( $show_intro ) {
		$out .= '<p class="mrt-timetable-index__intro">';
		$out .= esc_html__(
			'Choose a timetable to see departures and traffic days.',
			'museum-railway-timetable'
		);
		$out .= '</p>';
	}
	$out .= '<nav aria-label="' . esc_attr__( 'Timetables', 'museum-railway-timetable' ) . '">';
	$out .= '<ul class="mrt-timetable-index__list">';
	foreach ( $items as $item ) {
		$item_class = 'mrt-timetable-index__item';
		if ( $item['modifier'] !== '' ) {
			$item_class .= ' mrt-timetable-index__item--' . esc_attr( $item['modifier'] );
		}
		$out .= '<li class="' . esc_attr( $item_class ) . '">';
		if ( $item['url'] !== '' ) {
			$aria = $item['label'] . ' — ' . $item['aria_hint'];
			$out .= '<a class="mrt-timetable-index__card" href="' . esc_url( $item['url'] ) . '"';
			$out .= ' aria-label="' . esc_attr( $aria ) . '">';
			$out .= MRT_timetable_index_card_inner_html( $item['label'], $item['meta'] );
			$out .= '</a>';
		} else {
			$out .= '<div class="mrt-timetable-index__card mrt-timetable-index__card--static">';
			$out .= MRT_timetable_index_card_inner_html( $item['label'], $item['meta'] );
			$out .= '</div>';
		}
		$out .= '</li>';
	}
	$out .= '</ul></nav></div>';
	return $out;
}

/**
 * @return string HTML (no outer link wrapper).
 */
function MRT_timetable_index_card_inner_html( string $label, string $meta ): string {
	$out  = '<span class="mrt-timetable-index__swatch" aria-hidden="true"></span>';
	$out .= '<span class="mrt-timetable-index__body">';
	$out .= '<span class="mrt-timetable-index__title">' . esc_html( $label ) . '</span>';
	if ( $meta !== '' ) {
		$out .= '<span class="mrt-timetable-index__meta">' . esc_html( $meta ) . '</span>';
	}
	$out .= '</span>';
	$out .= '<span class="mrt-timetable-index__chevron" aria-hidden="true"></span>';
	return $out;
}
