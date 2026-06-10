<?php
/**
 * Timetable colour types (GRÖN / GUL / RÖD / ORANGE) for calendar UI.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Known timetable type slugs (matches admin mrt_timetable_type / CSV colour_type).
 *
 * @return list<string>
 */
function MRT_timetable_type_slugs(): array {
	return array( 'green', 'yellow', 'red', 'orange', 'blue' );
}

/**
 * @param string $type Raw meta or CSV value.
 */
function MRT_normalize_timetable_type( string $type ): string {
	$key = strtolower( trim( $type ) );
	return in_array( $key, MRT_timetable_type_slugs(), true ) ? $key : '';
}

/**
 * Unique normalized types for timetables active on a date.
 *
 * @return list<string>
 */
function MRT_timetable_types_for_date( string $dateYmd ): array {
	if ( ! MRT_validate_date( $dateYmd ) ) {
		return array();
	}

	$seen = array();
	foreach ( MRT_get_timetables_for_date( $dateYmd ) as $timetable_id ) {
		$type = MRT_normalize_timetable_type(
			(string) get_post_meta( (int) $timetable_id, 'mrt_timetable_type', true )
		);
		if ( $type !== '' ) {
			$seen[ $type ] = true;
		}
	}

	return array_keys( $seen );
}

/**
 * Sort type slugs in calendar display order (green → yellow → red → orange → blue).
 *
 * @param list<string> $types
 * @return list<string>
 */
function MRT_sort_timetable_types_for_calendar( array $types ): array {
	$seen = array();
	foreach ( $types as $type ) {
		$slug = MRT_normalize_timetable_type( (string) $type );
		if ( $slug !== '' ) {
			$seen[ $slug ] = true;
		}
	}

	$sorted = array();
	foreach ( MRT_timetable_type_slugs() as $slug ) {
		if ( ! empty( $seen[ $slug ] ) ) {
			$sorted[] = $slug;
		}
	}

	return $sorted;
}

/**
 * Pick one type when several timetables share a date (e.g. rail + bus both green).
 */
function MRT_dominant_timetable_type_for_date( string $dateYmd ): string {
	$types = MRT_timetable_types_for_date( $dateYmd );
	if ( $types === array() ) {
		return '';
	}

	$sorted = MRT_sort_timetable_types_for_calendar( $types );
	return $sorted[0] ?? '';
}

/**
 * Localized legend label for a timetable type slug.
 */
function MRT_timetable_type_calendar_label( string $type ): string {
	$labels = array(
		'green'  => __( 'GRÖN tidtabell', 'museum-railway-timetable' ),
		'yellow' => __( 'GUL tidtabell', 'museum-railway-timetable' ),
		'red'    => __( 'RÖD tidtabell', 'museum-railway-timetable' ),
		'orange' => __( 'ORANGE tidtabell', 'museum-railway-timetable' ),
		'blue'   => __( 'BLÅ tidtabell', 'museum-railway-timetable' ),
	);
	$key = strtolower( $type );
	return $labels[ $key ] ?? strtoupper( $type );
}

/**
 * Legend rows for types present in month day meta (sorted by profile order).
 *
 * @param array<int, array<string, mixed>> $dates Day index => meta from month shortcode.
 * @return list<array{type: string, label: string}>
 */
function MRT_month_calendar_legend_types( array $dates ): array {
	$seen = array();
	foreach ( $dates as $day ) {
		if ( ! is_array( $day ) ) {
			continue;
		}
		$day_types = array();
		if ( ! empty( $day['types'] ) && is_array( $day['types'] ) ) {
			$day_types = $day['types'];
		} elseif ( ! empty( $day['type'] ) ) {
			$day_types = array( (string) $day['type'] );
		}
		foreach ( $day_types as $raw_type ) {
			$type = MRT_normalize_timetable_type( (string) $raw_type );
			if ( $type !== '' ) {
				$seen[ $type ] = true;
			}
		}
	}

	$items = array();
	foreach ( MRT_timetable_type_slugs() as $slug ) {
		if ( empty( $seen[ $slug ] ) ) {
			continue;
		}
		$items[] = array(
			'type'  => $slug,
			'label' => MRT_timetable_type_calendar_label( $slug ),
		);
	}

	return $items;
}
