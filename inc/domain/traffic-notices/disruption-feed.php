<?php
/**
 * UL-like disruption feed (sources A+B, extended horizon).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/traffic-notices/aggregate.php';

const MRT_DISRUPTION_FEED_DEFAULT_HORIZON = 90;
const MRT_DISRUPTION_FEED_MAX_HORIZON     = 365;

/**
 * Build disruption feed for public/admin v2.
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_disruption_feed_build( string $reference_date, int $horizon_days = MRT_DISRUPTION_FEED_DEFAULT_HORIZON ) {
	if ( ! MRT_validate_date( $reference_date ) ) {
		return new WP_Error( 'mrt_disruption_date', __( 'Ogiltigt datum.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$horizon_days = MRT_disruption_feed_clamp_horizon( $horizon_days );
	$end_date     = MRT_disruption_feed_end_date( $reference_date, $horizon_days );
	$items        = array_merge(
		MRT_disruption_feed_items_from_notices( $reference_date, $end_date ),
		MRT_disruption_feed_items_from_deviations( $reference_date, $end_date )
	);
	$items        = MRT_disruption_feed_sort_items( $items );
	$sections     = MRT_disruption_feed_split_sections( $items );

	return array(
		'reference_date' => $reference_date,
		'horizon_days'   => $horizon_days,
		'end_date'       => $end_date,
		'ongoing'        => $sections['ongoing'],
		'upcoming'       => $sections['upcoming'],
		'items'          => $items,
		'is_empty'       => $items === array(),
	);
}

function MRT_disruption_feed_clamp_horizon( int $horizon_days ): int {
	return max( 1, min( MRT_DISRUPTION_FEED_MAX_HORIZON, $horizon_days ) );
}

function MRT_disruption_feed_end_date( string $reference_date, int $horizon_days ): string {
	$offset = MRT_disruption_feed_clamp_horizon( $horizon_days ) - 1;
	$ts     = strtotime( $reference_date . ' +' . $offset . ' days' );
	return $ts === false ? $reference_date : gmdate( 'Y-m-d', $ts );
}

/**
 * @return list<array<string, mixed>>
 */
function MRT_disruption_feed_items_from_notices( string $reference_date, string $end_date ): array {
	$items = array();
	foreach ( MRT_public_notices_get_all() as $notice ) {
		if ( empty( $notice['enabled'] ) ) {
			continue;
		}
		$window = MRT_disruption_feed_notice_window( $notice, $reference_date, $end_date );
		if ( $window === null ) {
			continue;
		}
		$items[] = MRT_disruption_feed_item_from_notice( $notice, $window, $reference_date );
	}
	return $items;
}

/**
 * @param array<string, mixed> $notice
 * @return array{from: string, to: string}|null
 */
function MRT_disruption_feed_notice_window( array $notice, string $reference_date, string $end_date ): ?array {
	$from = (string) ( $notice['active_from'] ?? '' );
	$to   = (string) ( $notice['active_to'] ?? '' );
	if ( $from === '' ) {
		$from = $reference_date;
	}
	if ( $to === '' ) {
		$to = $end_date;
	}
	if ( strcmp( $to, $reference_date ) < 0 || strcmp( $from, $end_date ) > 0 ) {
		return null;
	}
	if ( strcmp( $from, $reference_date ) < 0 ) {
		$from = $reference_date;
	}
	if ( strcmp( $to, $end_date ) > 0 ) {
		$to = $end_date;
	}
	return array(
		'from' => $from,
		'to'   => $to,
	);
}

/**
 * @param array<string, mixed> $notice
 * @param array{from: string, to: string} $window
 * @return array<string, mixed>
 */
function MRT_disruption_feed_item_from_notice( array $notice, array $window, string $reference_date ): array {
	$text = trim( (string) ( $notice['text'] ?? '' ) );
	return array(
		'id'             => 'notice-' . (string) ( $notice['id'] ?? '' ),
		'source'         => 'general',
		'kind'           => 'info',
		'phase'          => MRT_disruption_feed_phase_for_range( $window['from'], $window['to'], $reference_date ),
		'date_from'      => $window['from'],
		'date_to'        => $window['to'],
		'date_label'     => MRT_disruption_feed_range_label( $window['from'], $window['to'], $reference_date ),
		'headline'       => MRT_disruption_feed_notice_headline( $text ),
		'body'           => $text,
		'train_numbers'  => array(),
		'service_ids'    => array(),
	);
}

function MRT_disruption_feed_notice_headline( string $text ): string {
	if ( $text === '' ) {
		return __( 'Trafikmeddelande', 'museum-railway-timetable' );
	}
	$first = preg_split( '/\R/u', $text )[0] ?? $text;
	$first = trim( (string) $first );
	if ( mb_strlen( $first ) > 120 ) {
		return mb_substr( $first, 0, 117 ) . '…';
	}
	return $first;
}

/**
 * @return list<array<string, mixed>>
 */
function MRT_disruption_feed_items_from_deviations( string $reference_date, string $end_date ): array {
	$groups = array();
	foreach ( MRT_disruption_feed_raw_deviations( $reference_date, $end_date ) as $row ) {
		$key = MRT_disruption_feed_deviation_group_key( $row );
		if ( ! isset( $groups[ $key ] ) ) {
			$groups[ $key ] = array();
		}
		$groups[ $key ][] = $row;
	}
	$items = array();
	foreach ( $groups as $group ) {
		$items[] = MRT_disruption_feed_item_from_deviation_group( $group, $reference_date );
	}
	return $items;
}

/**
 * @return list<array<string, mixed>>
 */
function MRT_disruption_feed_raw_deviations( string $start_date, string $end_date ): array {
	$rows = array();
	foreach ( MRT_get_published_timetables() as $timetable ) {
		if ( ! $timetable instanceof WP_Post ) {
			continue;
		}
		$services = MRT_get_services_for_timetable( (int) $timetable->ID );
		foreach ( MRT_collect_timetable_deviation_rows( $services ) as $row ) {
			$date = (string) ( $row['date'] ?? '' );
			if ( $date === '' || strcmp( $date, $start_date ) < 0 || strcmp( $date, $end_date ) > 0 ) {
				continue;
			}
			$service = get_post( (int) $row['service_id'] );
			if ( ! $service instanceof WP_Post ) {
				continue;
			}
			$formatted = MRT_traffic_notice_format_deviation( $service, $row );
			if ( $formatted === null ) {
				continue;
			}
			$formatted['date'] = $date;
			$rows[]            = $formatted;
		}
	}
	return $rows;
}

/**
 * @param array<string, mixed> $row
 */
function MRT_disruption_feed_deviation_group_key( array $row ): string {
	$notice = mb_strtolower( trim( (string) ( $row['notice'] ?? '' ) ) );
	return (string) ( $row['date'] ?? '' ) . "\0" . $notice . "\0" . (int) ( $row['train_type_id'] ?? 0 );
}

/**
 * @param list<array<string, mixed>> $group
 * @return array<string, mixed>
 */
function MRT_disruption_feed_item_from_deviation_group( array $group, string $reference_date ): array {
	$date = (string) ( $group[0]['date'] ?? $reference_date );
	$numbers = MRT_disruption_feed_unique_train_numbers( $group );
	$notice  = trim( (string) ( $group[0]['notice'] ?? '' ) );
	$cancel  = ! empty( $group[0]['is_cancelled'] ) || MRT_notice_indicates_cancelled( $notice );
	return array(
		'id'            => 'deviation-' . md5( MRT_disruption_feed_deviation_group_key( $group[0] ) ),
		'source'        => 'deviation',
		'kind'          => $cancel ? 'cancelled' : 'deviation',
		'phase'         => MRT_disruption_feed_phase_for_range( $date, $date, $reference_date ),
		'date_from'     => $date,
		'date_to'       => $date,
		'date_label'    => MRT_disruption_feed_range_label( $date, $date, $reference_date ),
		'headline'      => MRT_disruption_feed_deviation_headline( $notice, $numbers, $cancel ),
		'body'          => $notice,
		'train_numbers' => $numbers,
		'service_ids'   => array_values(
			array_unique(
				array_map(
					static fn( array $row ): int => (int) ( $row['service_id'] ?? 0 ),
					$group
				)
			)
		),
	);
}

/**
 * @param list<array<string, mixed>> $group
 * @return list<string>
 */
function MRT_disruption_feed_unique_train_numbers( array $group ): array {
	$numbers = array();
	foreach ( $group as $row ) {
		$number = trim( (string) ( $row['service_number'] ?? '' ) );
		if ( $number !== '' ) {
			$numbers[] = $number;
		}
	}
	$numbers = array_values( array_unique( $numbers ) );
	sort( $numbers, SORT_NATURAL );
	return $numbers;
}

/**
 * @param list<string> $train_numbers
 */
function MRT_disruption_feed_deviation_headline( string $notice, array $train_numbers, bool $cancelled ): string {
	$trains = implode( ', ', $train_numbers );
	if ( $cancelled ) {
		return $trains === ''
			? __( 'Inställd trafik', 'museum-railway-timetable' )
			: sprintf(
				/* translators: %s: comma-separated train numbers */
				__( 'Inställd trafik — Tåg %s', 'museum-railway-timetable' ),
				$trains
			);
	}
	if ( $notice !== '' && $trains !== '' ) {
		return sprintf(
			/* translators: 1: notice text, 2: comma-separated train numbers */
			__( '%1$s — Tåg %2$s', 'museum-railway-timetable' ),
			$notice,
			$trains
		);
	}
	if ( $notice !== '' ) {
		return $notice;
	}
	return $trains === ''
		? __( 'Tur-avvikelse', 'museum-railway-timetable' )
		: sprintf(
			/* translators: %s: comma-separated train numbers */
			__( 'Tur-avvikelse — Tåg %s', 'museum-railway-timetable' ),
			$trains
		);
}

function MRT_disruption_feed_phase_for_range( string $from, string $to, string $reference_date ): string {
	if ( strcmp( $from, $reference_date ) > 0 ) {
		return 'upcoming';
	}
	if ( strcmp( $to, $reference_date ) < 0 ) {
		return 'past';
	}
	return 'ongoing';
}

function MRT_disruption_feed_range_label( string $from, string $to, string $reference_date ): string {
	if ( $from === $to ) {
		return MRT_traffic_notices_date_label( $from, $reference_date );
	}
	$from_label = MRT_disruption_feed_single_date_label( $from );
	$to_label   = MRT_disruption_feed_single_date_label( $to );
	return $from_label . ' – ' . $to_label;
}

function MRT_disruption_feed_single_date_label( string $date_ymd ): string {
	$ts = strtotime( $date_ymd );
	if ( $ts === false ) {
		return $date_ymd;
	}
	$label = wp_date( 'j M Y', $ts );
	return is_string( $label ) ? $label : $date_ymd;
}

/**
 * @param list<array<string, mixed>> $items
 * @return list<array<string, mixed>>
 */
function MRT_disruption_feed_sort_items( array $items ): array {
	usort(
		$items,
		static function ( array $a, array $b ): int {
			$cmp = strcmp( (string) ( $a['date_from'] ?? '' ), (string) ( $b['date_from'] ?? '' ) );
			if ( $cmp !== 0 ) {
				return $cmp;
			}
			$phase_order = array( 'ongoing' => 0, 'upcoming' => 1, 'past' => 2 );
			$a_phase     = $phase_order[ (string) ( $a['phase'] ?? '' ) ] ?? 9;
			$b_phase     = $phase_order[ (string) ( $b['phase'] ?? '' ) ] ?? 9;
			if ( $a_phase !== $b_phase ) {
				return $a_phase <=> $b_phase;
			}
			return strcmp( (string) ( $a['headline'] ?? '' ), (string) ( $b['headline'] ?? '' ) );
		}
	);
	return $items;
}

/**
 * @param list<array<string, mixed>> $items
 * @return array{ongoing: list<array<string, mixed>>, upcoming: list<array<string, mixed>>}
 */
function MRT_disruption_feed_split_sections( array $items ): array {
	$ongoing  = array();
	$upcoming = array();
	foreach ( $items as $item ) {
		if ( (string) ( $item['phase'] ?? '' ) === 'upcoming' ) {
			$upcoming[] = $item;
			continue;
		}
		if ( (string) ( $item['phase'] ?? '' ) === 'ongoing' ) {
			$ongoing[] = $item;
		}
	}
	return array(
		'ongoing'  => $ongoing,
		'upcoming' => $upcoming,
	);
}
