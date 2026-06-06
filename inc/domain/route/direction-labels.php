<?php
/**
 * Service direction (dit/från) and timetable row labels.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return 'dit'|'från'|''
 */
function MRT_normalize_service_direction( string $direction ): string {
	if ( $direction === 'dit' || $direction === 'från' ) {
		return $direction;
	}
	return '';
}

/**
 * Translated short direction label (Dit / Från), or empty when unknown.
 */
function MRT_service_direction_label( string $direction ): string {
	if ( $direction === 'dit' ) {
		return __( 'Dit', 'museum-railway-timetable' );
	}
	if ( $direction === 'från' ) {
		return __( 'Från', 'museum-railway-timetable' );
	}
	return '';
}

/**
 * Suffix for auto-generated service titles when no end station is set.
 */
function MRT_service_direction_title_suffix( string $direction ): string {
	$label = MRT_service_direction_label( $direction );
	return $label !== '' ? ' - ' . $label : '';
}

/**
 * Translated direction label with em dash fallback.
 */
function MRT_service_direction_label_or_dash( string $direction ): string {
	$label = MRT_service_direction_label( $direction );
	return $label !== '' ? $label : '—';
}

/**
 * "Från {place}" row label.
 */
function MRT_from_place_label( string $place_name ): string {
	return sprintf( __( 'Från %s', 'museum-railway-timetable' ), $place_name );
}

/**
 * "Till {place}" row label.
 */
function MRT_to_place_label( string $place_name ): string {
	return sprintf( __( 'Till %s', 'museum-railway-timetable' ), $place_name );
}

/**
 * "Från {station}" using display name (suffix-aware).
 *
 * @param WP_Post|int|string $station Station post, ID, or name.
 */
function MRT_station_from_label( $station ): string {
	return MRT_from_place_label( MRT_get_station_display_name( $station ) );
}

/**
 * "Till {station}" using display name (suffix-aware).
 *
 * @param WP_Post|int|string $station Station post, ID, or name.
 */
function MRT_station_to_label( $station ): string {
	return MRT_to_place_label( MRT_get_station_display_name( $station ) );
}

/**
 * "Från X Till Y" route heading.
 */
function MRT_route_from_to_label( string $from_name, string $to_name ): string {
	return sprintf(
		__( 'Från %1$s Till %2$s', 'museum-railway-timetable' ),
		$from_name,
		$to_name
	);
}
