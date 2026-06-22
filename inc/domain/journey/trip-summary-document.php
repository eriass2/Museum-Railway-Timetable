<?php
/**
 * Trip summary HTML for server-side PDF rendering.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<int, array<string, mixed>> $segments Leg segment rows.
 */
function MRT_trip_summary_should_show_leg_list( array $segments ): bool {
	if ( $segments === array() ) {
		return false;
	}
	foreach ( $segments as $segment ) {
		if ( ( $segment['type'] ?? '' ) === 'transfer' ) {
			return true;
		}
	}
	return count( $segments ) > 1;
}

/**
 * @param array<int, array{label: string, value: string}> $rows Price rows.
 */
function MRT_trip_summary_price_table_html( array $rows ): string {
	if ( $rows === array() ) {
		return '';
	}

	$body = '';
	foreach ( $rows as $row ) {
		$body .= '<tr><td class="price-label">' . esc_html( $row['label'] ) . '</td>';
		$body .= '<td class="price-value">' . esc_html( $row['value'] ) . '</td></tr>';
	}

	return '<table class="price-table"><tbody>' . $body . '</tbody></table>';
}

/**
 * @param array<int, array{label: string, value: string}> $rows Price rows.
 */
function MRT_trip_summary_price_column_html( string $heading, array $rows ): string {
	if ( $rows === array() ) {
		return '';
	}

	return '<td class="price-column"><h3>' . esc_html( $heading ) . '</h3>'
		. MRT_trip_summary_price_table_html( $rows ) . '</td>';
}

/**
 * @param array<string, mixed> $prices Price section.
 */
function MRT_trip_summary_price_section_html( array $prices ): string {
	$rows = $prices['rows'] ?? array();
	if ( ! is_array( $rows ) || $rows === array() ) {
		return '';
	}

	$day_rows = array();
	if ( ! empty( $prices['dayTicketRows'] ) && is_array( $prices['dayTicketRows'] ) ) {
		$day_rows = $prices['dayTicketRows'];
	}

	$ticket_label = (string) ( $prices['ticketTypeLabel'] ?? '' );
	$heading      = (string) ( $prices['heading'] ?? '' );
	$trip_heading = $ticket_label !== '' ? $ticket_label : $heading;

	if ( $day_rows !== array() ) {
		$day_heading = (string) ( $prices['dayTicketHeading'] ?? __( 'Heldagsbiljett', 'museum-railway-timetable' ) );
		$price_body  = '<table class="price-columns-table"><tbody><tr>'
			. MRT_trip_summary_price_column_html( $trip_heading, $rows )
			. MRT_trip_summary_price_column_html( $day_heading, $day_rows )
			. '</tr></tbody></table>';
	} else {
		$price_body = ( $ticket_label !== '' ? '<h3>' . esc_html( $ticket_label ) . '</h3>' : '' )
			. MRT_trip_summary_price_table_html( $rows );
	}

	$note_blocks = '';
	$notes       = $prices['notes'] ?? array();
	if ( is_array( $notes ) ) {
		foreach ( $notes as $note ) {
			$text = trim( (string) $note );
			if ( $text !== '' ) {
				$note_blocks .= '<p class="note">' . esc_html( $text ) . '</p>';
			}
		}
	}

	return '<section class="prices"><h2>' . esc_html( $heading ) . '</h2>' . $price_body . $note_blocks . '</section>';
}

/**
 * @param array<string, mixed> $leg Trip leg.
 */
function MRT_trip_summary_leg_segments_html( array $leg ): string {
	$segments = $leg['segments'] ?? array();
	if ( ! is_array( $segments ) || ! MRT_trip_summary_should_show_leg_list( $segments ) ) {
		return '';
	}

	$items = '';
	foreach ( $segments as $segment ) {
		if ( ( $segment['type'] ?? '' ) === 'transfer' ) {
			$items .= '<li class="transfer">' . esc_html( (string) ( $segment['label'] ?? '' ) ) . '</li>';
			continue;
		}
		if ( ( $segment['type'] ?? '' ) !== 'leg' || ! isset( $segment['leg'] ) || ! is_array( $segment['leg'] ) ) {
			continue;
		}
		$detail     = $segment['leg'];
		$route_part = (string) ( $detail['route'] ?? '' ) !== ''
			? ' (' . esc_html( (string) $detail['route'] ) . ')'
			: '';
		$items     .= '<li>' . esc_html( (string) ( $detail['vehicleLabel'] ?? '' ) )
			. ' · ' . esc_html( (string) ( $detail['timeRange'] ?? '' ) )
			. $route_part . '</li>';
	}

	return '<ul class="segments">' . $items . '</ul>';
}

/**
 * @param array<string, mixed> $leg Trip leg.
 */
function MRT_trip_summary_leg_card_html( array $leg ): string {
	return '<section class="card"><h2>' . esc_html( (string) ( $leg['heading'] ?? '' ) ) . '</h2>'
		. '<p class="time">' . esc_html( (string) ( $leg['timeRange'] ?? '' ) ) . '</p>'
		. '<p class="route">' . esc_html( (string) ( $leg['route'] ?? '' ) )
		. ' · ' . esc_html( (string) ( $leg['date'] ?? '' ) ) . '</p>'
		. MRT_trip_summary_leg_segments_html( $leg ) . '</section>';
}

/**
 * @param array<int, array<string, mixed>> $legs Trip legs.
 */
function MRT_trip_summary_legs_layout_html( array $legs ): string {
	if ( count( $legs ) <= 1 ) {
		$html = '';
		foreach ( $legs as $leg ) {
			$html .= MRT_trip_summary_leg_card_html( $leg );
		}
		return $html;
	}

	$cells = '';
	foreach ( $legs as $leg ) {
		$cells .= '<td class="legs-cell">' . MRT_trip_summary_leg_card_html( $leg ) . '</td>';
	}

	return '<table class="legs-table"><tbody><tr class="legs-row">' . $cells . '</tr></tbody></table>';
}

/**
 * @param array<string, mixed> $input Parsed trip summary input.
 */
function MRT_trip_summary_build_html( array $input ): string {
	$legs = $input['legs'] ?? array();
	if ( ! is_array( $legs ) ) {
		$legs = array();
	}

	$meta = '';
	$trip_type_label = (string) ( $input['tripTypeLabel'] ?? '' );
	if ( $trip_type_label !== '' ) {
		$meta = '<p class="meta">' . esc_html( $trip_type_label ) . '</p>';
	}

	$prices = '';
	$price_section = $input['priceSection'] ?? null;
	if ( is_array( $price_section ) ) {
		$prices = MRT_trip_summary_price_section_html( $price_section );
	}

	return '<h1>' . esc_html( (string) ( $input['title'] ?? '' ) ) . '</h1>'
		. $meta
		. MRT_trip_summary_legs_layout_html( $legs )
		. $prices;
}

/**
 * @param string $body_html Summary body markup.
 */
function MRT_trip_summary_build_pdf_document( string $body_html ): string {
	return '<!DOCTYPE html><html lang="sv"><head><meta charset="utf-8"><style>'
		. MRT_trip_summary_pdf_styles()
		. '</style></head><body><div data-mrt-pdf-root>'
		. $body_html
		. '</div></body></html>';
}
