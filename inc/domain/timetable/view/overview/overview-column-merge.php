<?php
/**
 * Timetable overview: merge primary + continuation services into one column.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<int, WP_Post> $station_posts
 * @return array<string, array{primary: string, station_id: int}>
 */
function MRT_timetable_continuation_to_primary_map( array $station_posts ): array {
	$reverse = array();
	foreach ( $station_posts as $station ) {
		if ( ! $station instanceof WP_Post ) {
			continue;
		}
		$station_id = (int) $station->ID;
		$map        = MRT_get_station_train_change_map( $station_id );
		foreach ( $map as $primary => $transfer ) {
			$cont = (string) ( $transfer['serviceNumber'] ?? '' );
			if ( $cont === '' ) {
				continue;
			}
			$reverse[ $cont ] = array(
				'primary'    => (string) $primary,
				'station_id' => $station_id,
			);
		}
	}
	return $reverse;
}

/**
 * @param array<int, array<string, mixed>> $info
 * @return array<string, int>
 */
function MRT_timetable_service_number_index_map( array $info ): array {
	$map = array();
	foreach ( $info as $idx => $row ) {
		$num = (string) ( $row['service_number'] ?? '' );
		if ( $num !== '' ) {
			$map[ $num ] = (int) $idx;
		}
	}
	return $map;
}

/**
 * @param array<int, WP_Post> $station_posts
 * @return array<int, array<string, string>>
 */
function MRT_timetable_train_change_forward_by_station( array $station_posts ): array {
	$by_station = array();
	foreach ( $station_posts as $station ) {
		if ( ! $station instanceof WP_Post ) {
			continue;
		}
		$station_id = (int) $station->ID;
		$map        = MRT_get_station_train_change_map( $station_id );
		if ( $map === array() ) {
			continue;
		}
		$forward = array();
		foreach ( $map as $primary => $transfer ) {
			$forward[ (string) $primary ] = (string) ( $transfer['serviceNumber'] ?? '' );
		}
		$by_station[ $station_id ] = $forward;
	}
	return $by_station;
}

/**
 * @param array<int, array<string, mixed>> $services_list
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}> $columns
 */
function MRT_timetable_sort_display_columns( array $columns, array $services_list, int $first_station_id ): array {
	usort(
		$columns,
		static function ( array $a, array $b ) use ( $services_list, $first_station_id ): int {
			$a_time = MRT_timetable_display_column_sort_time( $a, $services_list, $first_station_id );
			$b_time = MRT_timetable_display_column_sort_time( $b, $services_list, $first_station_id );
			if ( $a_time === '' && $b_time === '' ) {
				return 0;
			}
			if ( $a_time === '' ) {
				return 1;
			}
			if ( $b_time === '' ) {
				return -1;
			}
			return strcmp( $a_time, $b_time );
		}
	);
	return $columns;
}

/**
 * @param array{primary_idx: int, continuation_idx: int|null, split_station_id: int} $column
 * @param array<int, array<string, mixed>> $services_list
 */
function MRT_timetable_display_column_sort_time( array $column, array $services_list, int $first_station_id ): string {
	$idx  = (int) $column['primary_idx'];
	$data = $services_list[ $idx ] ?? array();
	$stop = $data['stop_times'][ $first_station_id ] ?? array();
	return MRT_stop_effective_departure( is_array( $stop ) ? $stop : array() );
}

/**
 * @param array<string, mixed> $view From MRT_prepare_timetable_group_view().
 * @return array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}>
 */
function MRT_timetable_build_display_columns( array $view ): array {
	$info           = $view['service_info'];
	$station_posts  = $view['station_posts'];
	$services_list  = $view['services_list'];
	$reverse        = MRT_timetable_continuation_to_primary_map( $station_posts );
	$num_to_idx     = MRT_timetable_service_number_index_map( $info );
	$forward_lookup = MRT_timetable_train_change_forward_by_station( $station_posts );
	$columns        = array();

	foreach ( $info as $idx => $row ) {
		$num = (string) ( $row['service_number'] ?? '' );
		if ( $num !== '' && isset( $reverse[ $num ] ) ) {
			continue;
		}
		$col = array(
			'primary_idx'      => (int) $idx,
			'continuation_idx' => null,
			'split_station_id' => 0,
		);
		if ( $num !== '' ) {
			foreach ( $forward_lookup as $station_id => $forward ) {
				if ( ! isset( $forward[ $num ] ) ) {
					continue;
				}
				$cont_num                 = $forward[ $num ];
				$col['continuation_idx']  = $num_to_idx[ $cont_num ] ?? null;
				$col['split_station_id']  = (int) $station_id;
				break;
			}
		}
		$columns[] = $col;
	}

	if ( $station_posts !== array() ) {
		$first_id = (int) $station_posts[0]->ID;
		$columns  = MRT_timetable_sort_display_columns( $columns, $services_list, $first_id );
	}

	return $columns;
}

/**
 * @param array{primary_idx: int, continuation_idx: int|null, split_station_id: int} $column
 * @param array<int, WP_Post> $station_posts
 */
function MRT_timetable_display_column_service_idx(
	array $column,
	int $station_id,
	string $row_kind,
	array $station_posts
): int {
	$primary      = (int) $column['primary_idx'];
	$continuation = $column['continuation_idx'];
	$split_id     = (int) ( $column['split_station_id'] ?? 0 );
	if ( $continuation === null || $split_id <= 0 ) {
		return $primary;
	}
	if ( $station_id === $split_id ) {
		return $row_kind === 'departure' ? (int) $continuation : $primary;
	}
	$order = MRT_timetable_station_id_order( $station_posts );
	$split = array_search( $split_id, $order, true );
	$pos   = array_search( $station_id, $order, true );
	if ( $split === false || $pos === false ) {
		return $primary;
	}
	return $pos <= $split ? $primary : (int) $continuation;
}

/**
 * @param array<int, WP_Post> $station_posts
 * @return array<int, int>
 */
function MRT_timetable_station_id_order( array $station_posts ): array {
	$order = array();
	foreach ( $station_posts as $station ) {
		if ( $station instanceof WP_Post ) {
			$order[] = (int) $station->ID;
		}
	}
	return $order;
}

/**
 * @param array<string, mixed> $service_data
 */
function MRT_timetable_service_id_from_data( array $service_data ): int {
	$service = $service_data['service'] ?? null;
	return $service instanceof WP_Post ? (int) $service->ID : 0;
}
