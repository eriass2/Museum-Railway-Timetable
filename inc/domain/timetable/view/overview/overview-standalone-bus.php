<?php
/**
 * Timetable overview: standalone bus columns in the rail grid.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/service/overview-column.php';

/**
 * @param array<string, mixed> $view
 * @return array<string, mixed>
 */
function MRT_timetable_view_merge_standalone_buses(
	array $view,
	array $rail_group,
	array $grouped_services,
	string $date_ymd
): array {
	$standalone = MRT_timetable_standalone_bus_entries_for_rail_group( $grouped_services, $rail_group );
	if ( $standalone === array() ) {
		$view['rail_service_count'] = count( $view['services_list'] );
		return $view;
	}

	$rail_count = count( $view['services_list'] );
	$view['services_list'] = array_merge( $view['services_list'], $standalone );
	$prepared              = MRT_prepare_service_info( $view['services_list'], $date_ymd );
	$view['service_info']  = $prepared['service_info'];
	$view['service_classes'] = $prepared['service_classes'];
	$view['all_connections'] = $prepared['all_connections'];
	$view['rail_service_count'] = $rail_count;
	$view['standalone_bus_count'] = count( $standalone );

	foreach ( $view['service_info'] as $idx => $row ) {
		if ( $idx < $rail_count ) {
			continue;
		}
		$service = $view['services_list'][ $idx ]['service'] ?? null;
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$view['service_info'][ $idx ]['standalone_overview_column'] = true;
		$view['service_info'][ $idx ]['overview_pass_from_station_id'] = MRT_service_overview_pass_from_station_id(
			(int) $service->ID
		);
	}

	return $view;
}

/**
 * @param array<string, mixed> $view
 * @return array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}>
 */
function MRT_timetable_append_standalone_bus_display_columns( array $view, array $columns ): array {
	$rail_count = (int) ( $view['rail_service_count'] ?? count( $view['services_list'] ) );
	$standalone = (int) ( $view['standalone_bus_count'] ?? 0 );
	if ( $standalone <= 0 ) {
		return $columns;
	}

	for ( $i = 0; $i < $standalone; ++$i ) {
		$columns[] = array(
			'primary_idx'      => $rail_count + $i,
			'continuation_idx' => null,
			'split_station_id' => 0,
		);
	}

	$station_posts = $view['station_posts'] ?? array();
	$first_id      = $station_posts !== array() ? (int) $station_posts[0]->ID : 0;
	if ( $first_id > 0 ) {
		$columns = MRT_timetable_sort_display_columns( $columns, $view['services_list'], $first_id );
	}

	return $columns;
}

/**
 * @param array<string, mixed> $service_data
 * @param array<string, mixed> $info
 * @param array<int, WP_Post>  $station_posts
 */
function MRT_timetable_standalone_bus_cell_text(
	array $service_data,
	array $info,
	int $station_id,
	string $row_kind,
	array $station_posts,
	bool $use_from_display,
	bool $use_to_display
): string {
	if ( $row_kind === 'busDeparture' ) {
		$remote_id = MRT_timetable_standalone_bus_boarding_station_id( $service_data );
		$stop      = $remote_id > 0 ? ( $service_data['stop_times'][ $remote_id ] ?? null ) : null;
		return MRT_timetable_time_cell_text( is_array( $stop ) ? $stop : null, true, false, 'from' );
	}
	if ( $row_kind === 'busArrival' ) {
		return '—';
	}

	$stop = $service_data['stop_times'][ $station_id ] ?? null;
	if ( is_array( $stop ) && MRT_stop_row_has_scheduled_time( $stop ) ) {
		return MRT_timetable_time_cell_text( $stop, $use_from_display, $use_to_display, $row_kind );
	}

	$pass_from = (int) ( $info['overview_pass_from_station_id'] ?? 0 );
	$order     = MRT_timetable_station_id_order( $station_posts );
	$pos       = array_search( $station_id, $order, true );
	$pass_pos  = $pass_from > 0 ? array_search( $pass_from, $order, true ) : false;
	$alight_id = MRT_timetable_standalone_bus_alight_station_id( $service_data, $station_posts );
	$alight_pos = $alight_id > 0 ? array_search( $alight_id, $order, true ) : false;

	if ( $pos === false ) {
		return '—';
	}
	if ( $pass_pos !== false && $pos < $pass_pos ) {
		return '—';
	}
	if ( $alight_pos !== false && $pos > $alight_pos ) {
		return '—';
	}
	if ( $alight_pos !== false && $pos === $alight_pos ) {
		$stop = $service_data['stop_times'][ $alight_id ] ?? null;
		return MRT_timetable_time_cell_text( is_array( $stop ) ? $stop : null, false, true, 'to' );
	}

	return '|';
}

/**
 * @param array<string, mixed> $stop
 */
function MRT_stop_row_has_scheduled_time( array $stop ): bool {
	return MRT_stop_effective_departure( $stop ) !== '' || MRT_stop_effective_arrival( $stop ) !== '';
}

/**
 * @param array<string, mixed> $service_data
 */
function MRT_timetable_standalone_bus_boarding_station_id( array $service_data ): int {
	$stops = $service_data['stop_times'] ?? array();
	foreach ( $stops as $station_id => $stop ) {
		if ( ! is_array( $stop ) ) {
			continue;
		}
		if ( MRT_stop_effective_departure( $stop ) !== '' ) {
			return (int) $station_id;
		}
	}
	return 0;
}

/**
 * @param array<string, mixed> $service_data
 * @param array<int, WP_Post>  $station_posts
 */
function MRT_timetable_standalone_bus_alight_station_id( array $service_data, array $station_posts ): int {
	$on_route = array();
	foreach ( $station_posts as $station ) {
		if ( $station instanceof WP_Post ) {
			$on_route[ (int) $station->ID ] = true;
		}
	}
	$last = 0;
	foreach ( $service_data['stop_times'] ?? array() as $station_id => $stop ) {
		if ( ! is_array( $stop ) || ! isset( $on_route[ (int) $station_id ] ) ) {
			continue;
		}
		if ( MRT_stop_effective_arrival( $stop ) !== '' || MRT_stop_effective_departure( $stop ) !== '' ) {
			$last = (int) $station_id;
		}
	}
	return $last;
}

/**
 * @param array<int, array<string, mixed>> $rows
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}> $display_columns
 * @param array<string, mixed> $view
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_patch_standalone_bus_junction_rows(
	array $rows,
	array $display_columns,
	array $view
): array {
	foreach ( $rows as $row_idx => $row ) {
		$kind = (string) ( $row['kind'] ?? '' );
		if ( $kind !== 'busDeparture' && $kind !== 'busArrival' ) {
			continue;
		}
		$cells = (array) ( $row['cells'] ?? array() );
		foreach ( $display_columns as $col_idx => $column ) {
			$idx      = (int) $column['primary_idx'];
			$row_info = $view['service_info'][ $idx ] ?? array();
			if ( empty( $row_info['standalone_overview_column'] ) ) {
				continue;
			}
			$service_data = $view['services_list'][ $idx ] ?? array();
			$label = (string) ( $row['label'] ?? '' );
			if ( $kind === 'busDeparture' && str_contains( $label, 'Linnés' ) ) {
				$remote_id = MRT_timetable_standalone_bus_boarding_station_id( $service_data );
				$stop      = $remote_id > 0 ? ( $service_data['stop_times'][ $remote_id ] ?? null ) : null;
				$text      = MRT_timetable_time_cell_text( is_array( $stop ) ? $stop : null, true, false, 'from' );
			} else {
				$text = '—';
			}
			$cells[ $col_idx ] = array(
				'text'            => $text,
				'approximateTime' => false,
			);
			$service_id = MRT_timetable_service_id_from_data( $service_data );
			if ( $service_id > 0 ) {
				$cells[ $col_idx ]['serviceId'] = $service_id;
			}
		}
		$rows[ $row_idx ]['cells'] = $cells;
	}
	return $rows;
}
