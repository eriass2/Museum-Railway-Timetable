<?php
/**
 * Parse and sanitize trip summary PDF REST input.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed> $raw Request body.
 * @return array<string, mixed>|WP_Error
 */
function MRT_trip_summary_parse_rest_input( array $raw ) {
	$title = sanitize_text_field( (string) ( $raw['title'] ?? '' ) );
	if ( $title === '' ) {
		return new WP_Error(
			'mrt_trip_summary_invalid',
			__( 'Trip summary title is required.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}

	$legs = MRT_trip_summary_parse_legs( $raw['legs'] ?? null );
	if ( is_wp_error( $legs ) ) {
		return $legs;
	}
	if ( $legs === array() ) {
		return new WP_Error(
			'mrt_trip_summary_invalid',
			__( 'At least one trip leg is required.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}

	$price_section = MRT_trip_summary_parse_price_section( $raw['priceSection'] ?? null );

	return array(
		'title'          => $title,
		'downloadName'   => sanitize_text_field( (string) ( $raw['downloadName'] ?? $title ) ),
		'tripTypeLabel'  => sanitize_text_field( (string) ( $raw['tripTypeLabel'] ?? '' ) ),
		'legs'           => $legs,
		'priceSection'   => $price_section,
	);
}

/**
 * @param mixed $raw Legs payload.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function MRT_trip_summary_parse_legs( $raw ) {
	if ( ! is_array( $raw ) ) {
		return new WP_Error(
			'mrt_trip_summary_invalid',
			__( 'Trip legs must be an array.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}

	$legs = array();
	foreach ( array_slice( $raw, 0, 4 ) as $leg_raw ) {
		if ( ! is_array( $leg_raw ) ) {
			continue;
		}
		$leg = MRT_trip_summary_parse_leg( $leg_raw );
		if ( $leg !== null ) {
			$legs[] = $leg;
		}
	}

	return $legs;
}

/**
 * @param array<string, mixed> $leg_raw Single leg.
 * @return array<string, mixed>|null
 */
function MRT_trip_summary_parse_leg( array $leg_raw ): ?array {
	$heading    = sanitize_text_field( (string) ( $leg_raw['heading'] ?? '' ) );
	$route      = sanitize_text_field( (string) ( $leg_raw['route'] ?? '' ) );
	$time_range = sanitize_text_field( (string) ( $leg_raw['timeRange'] ?? '' ) );
	$date       = sanitize_text_field( (string) ( $leg_raw['date'] ?? '' ) );
	if ( $heading === '' || $route === '' || $time_range === '' ) {
		return null;
	}

	$leg = array(
		'heading'    => $heading,
		'route'      => $route,
		'timeRange'  => $time_range,
		'date'       => $date,
		'segments'   => MRT_trip_summary_parse_segments( $leg_raw['segments'] ?? null ),
	);

	return $leg;
}

/**
 * @param mixed $raw Segments payload.
 * @return array<int, array<string, mixed>>
 */
function MRT_trip_summary_parse_segments( $raw ): array {
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$segments = array();
	foreach ( array_slice( $raw, 0, 12 ) as $segment_raw ) {
		if ( ! is_array( $segment_raw ) ) {
			continue;
		}
		$type = sanitize_key( (string) ( $segment_raw['type'] ?? '' ) );
		if ( $type === 'transfer' ) {
			$label = sanitize_text_field( (string) ( $segment_raw['label'] ?? '' ) );
			if ( $label !== '' ) {
				$segments[] = array(
					'type'  => 'transfer',
					'label' => $label,
				);
			}
			continue;
		}
		if ( $type !== 'leg' || ! isset( $segment_raw['leg'] ) || ! is_array( $segment_raw['leg'] ) ) {
			continue;
		}
		$detail = $segment_raw['leg'];
		$segments[] = array(
			'type' => 'leg',
			'leg'  => array(
				'vehicleLabel' => sanitize_text_field( (string) ( $detail['vehicleLabel'] ?? '' ) ),
				'timeRange'    => sanitize_text_field( (string) ( $detail['timeRange'] ?? '' ) ),
				'route'        => sanitize_text_field( (string) ( $detail['route'] ?? '' ) ),
			),
		);
	}

	return $segments;
}

/**
 * @param mixed $raw Price section payload.
 * @return array<string, mixed>|null
 */
function MRT_trip_summary_parse_price_section( $raw ): ?array {
	if ( ! is_array( $raw ) ) {
		return null;
	}

	$rows = MRT_trip_summary_parse_price_rows( $raw['rows'] ?? null );
	if ( $rows === array() ) {
		return null;
	}

	$section = array(
		'heading'         => sanitize_text_field( (string) ( $raw['heading'] ?? __( 'Priser', 'museum-railway-timetable' ) ) ),
		'ticketTypeLabel' => sanitize_text_field( (string) ( $raw['ticketTypeLabel'] ?? '' ) ),
		'rows'            => $rows,
		'notes'           => MRT_trip_summary_parse_notes( $raw['notes'] ?? null ),
	);

	$day_rows = MRT_trip_summary_parse_price_rows( $raw['dayTicketRows'] ?? null );
	if ( $day_rows !== array() ) {
		$section['dayTicketHeading'] = sanitize_text_field(
			(string) ( $raw['dayTicketHeading'] ?? __( 'Heldagsbiljett', 'museum-railway-timetable' ) )
		);
		$section['dayTicketRows']    = $day_rows;
	}

	return $section;
}

/**
 * @param mixed $raw Price rows.
 * @return array<int, array{label: string, value: string}>
 */
function MRT_trip_summary_parse_price_rows( $raw ): array {
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$rows = array();
	foreach ( array_slice( $raw, 0, 12 ) as $row_raw ) {
		if ( ! is_array( $row_raw ) ) {
			continue;
		}
		$label = sanitize_text_field( (string) ( $row_raw['label'] ?? '' ) );
		$value = sanitize_text_field( (string) ( $row_raw['value'] ?? '' ) );
		if ( $label === '' || $value === '' ) {
			continue;
		}
		$rows[] = array(
			'label' => $label,
			'value' => $value,
		);
	}

	return $rows;
}

/**
 * @param mixed $raw Footnotes.
 * @return array<int, string>
 */
function MRT_trip_summary_parse_notes( $raw ): array {
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$notes = array();
	foreach ( array_slice( $raw, 0, 8 ) as $note_raw ) {
		$note = sanitize_text_field( (string) $note_raw );
		if ( $note !== '' ) {
			$notes[] = $note;
		}
	}

	return $notes;
}
