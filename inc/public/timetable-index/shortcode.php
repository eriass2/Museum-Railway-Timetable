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
 * Render list of timetables with links to public pages.
 *
 * @param array<string, string> $atts Shortcode attributes.
 * @return string
 */
function MRT_render_shortcode_timetable_index( $atts ) {
	$atts = shortcode_atts(
		array(
			'show_dates' => '1',
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
			$dates = MRT_get_timetable_dates( $timetable_id );
			if ( $dates !== array() ) {
				$meta = sprintf(
					/* translators: %d: number of traffic days */
					_n( '%d traffic day', '%d traffic days', count( $dates ), 'museum-railway-timetable' ),
					count( $dates )
				);
			}
		}
		$items[] = array(
			'url'   => $url,
			'label' => $label,
			'meta'  => $meta,
		);
	}

	return MRT_render_timetable_index_html( $items );
}

/**
 * @param array<int, array{url: string, label: string, meta: string}> $items
 */
function MRT_render_timetable_index_html( array $items ): string {
	$out  = '<nav class="mrt-timetable-index" aria-label="' . esc_attr__( 'Timetables', 'museum-railway-timetable' ) . '">';
	$out .= '<ul class="mrt-timetable-index__list">';
	foreach ( $items as $item ) {
		$out .= '<li class="mrt-timetable-index__item">';
		if ( $item['url'] !== '' ) {
			$out .= '<a class="mrt-timetable-index__link" href="' . esc_url( $item['url'] ) . '">';
			$out .= esc_html( $item['label'] );
			$out .= '</a>';
		} else {
			$out .= '<span class="mrt-timetable-index__label">' . esc_html( $item['label'] ) . '</span>';
		}
		if ( $item['meta'] !== '' ) {
			$out .= '<span class="mrt-timetable-index__meta">' . esc_html( $item['meta'] ) . '</span>';
		}
		$out .= '</li>';
	}
	$out .= '</ul></nav>';
	return $out;
}
