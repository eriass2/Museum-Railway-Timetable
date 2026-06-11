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
	bool $use_to_display,
	string $row_label = ''
): string {
	if ( $row_kind === 'busDeparture' ) {
		return MRT_timetable_standalone_bus_boarding_time_text_or_dash( $service_data, $row_label );
	}
	if ( $row_kind === 'busArrival' ) {
		return '—';
	}

	$stop = $service_data['stop_times'][ $station_id ] ?? null;
	if ( is_array( $stop ) && MRT_stop_row_has_scheduled_time( $stop ) ) {
		return MRT_timetable_time_cell_text( $stop, $use_from_display, $use_to_display, $row_kind );
	}

	return MRT_timetable_standalone_bus_corridor_cell_text(
		$service_data,
		$info,
		$station_id,
		$row_kind,
		$station_posts,
		$use_to_display
	);
}

/**
 * @param array<string, mixed> $service_data
 */
function MRT_timetable_standalone_bus_boarding_time_text_or_dash(
	array $service_data,
	string $row_label
): string {
	$boarding_id = MRT_timetable_standalone_bus_boarding_station_id( $service_data );
	if ( $boarding_id <= 0 || $row_label === '' ) {
		return '—';
	}
	if ( ! MRT_timetable_standalone_bus_is_boarding_departure_row( $row_label, $boarding_id ) ) {
		return '—';
	}
	return MRT_timetable_standalone_bus_boarding_time_text( $service_data, $boarding_id );
}

function MRT_timetable_standalone_bus_is_boarding_departure_row( string $row_label, int $boarding_id ): bool {
	$station = get_post( $boarding_id );
	if ( ! $station instanceof WP_Post ) {
		return false;
	}
	$from_label = MRT_station_from_label( $station );
	if ( $from_label !== '' && $row_label === $from_label ) {
		return true;
	}
	if ( ! MRT_timetable_standalone_bus_row_label_matches_station( $row_label, $boarding_id ) ) {
		return false;
	}
	return str_contains( $row_label, 'Från ' ) || str_contains( $row_label, 'From ' );
}

function MRT_timetable_standalone_bus_row_label_matches_station( string $row_label, int $station_id ): bool {
	$station = get_post( $station_id );
	if ( ! $station instanceof WP_Post ) {
		return false;
	}
	$title = trim( (string) $station->post_title );
	$name  = MRT_get_station_display_name( $station );
	foreach ( array_filter( array( $name, $title, rtrim( $name, '*' ) ) ) as $candidate ) {
		if ( str_contains( $row_label, $candidate ) ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array<string, mixed> $service_data
 */
function MRT_timetable_standalone_bus_boarding_time_text( array $service_data, int $boarding_id ): string {
	$stop = $service_data['stop_times'][ $boarding_id ] ?? null;
	return MRT_timetable_time_cell_text( is_array( $stop ) ? $stop : null, true, false, 'from' );
}

/**
 * @param array<string, mixed> $service_data
 * @param array<string, mixed> $info
 * @param array<int, WP_Post>  $station_posts
 */
function MRT_timetable_standalone_bus_corridor_cell_text(
	array $service_data,
	array $info,
	int $station_id,
	string $row_kind,
	array $station_posts,
	bool $use_to_display
): string {
	$pass_from = (int) ( $info['overview_pass_from_station_id'] ?? 0 );
	$order     = MRT_timetable_station_id_order( $station_posts );
	$pos       = array_search( $station_id, $order, true );
	$pass_pos  = $pass_from > 0 ? array_search( $pass_from, $order, true ) : false;
	$rail_ids  = array_map( 'intval', $order );
	$alight_id = MRT_timetable_standalone_bus_alight_station_on_route( $service_data, $rail_ids );
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
	if ( $alight_pos !== false && $pos === $alight_pos && $row_kind === 'to' && $use_to_display ) {
		$stop = $service_data['stop_times'][ $alight_id ] ?? null;
		return MRT_timetable_time_cell_text( is_array( $stop ) ? $stop : null, false, true, 'to' );
	}
	if ( $pass_pos !== false && $pos === $pass_pos ) {
		return MRT_timetable_standalone_bus_pass_station_cell_text( $row_kind );
	}
	if ( $pass_pos !== false && $pos > $pass_pos && $alight_pos !== false && $pos < $alight_pos ) {
		return '|';
	}

	return '—';
}

function MRT_timetable_standalone_bus_pass_station_cell_text( string $row_kind ): string {
	if ( $row_kind === 'departure' ) {
		return '|';
	}
	return '—';
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
	$station_posts = (array) ( $view['station_posts'] ?? array() );

	foreach ( $rows as $row_idx => $row ) {
		$kind = (string) ( $row['kind'] ?? '' );
		if ( $kind !== 'busDeparture' && $kind !== 'busArrival' ) {
			continue;
		}
		$cells = (array) ( $row['cells'] ?? array() );
		$label = (string) ( $row['label'] ?? '' );
		foreach ( $display_columns as $col_idx => $column ) {
			$idx      = (int) $column['primary_idx'];
			$row_info = $view['service_info'][ $idx ] ?? array();
			if ( empty( $row_info['standalone_overview_column'] ) ) {
				continue;
			}
			$service_data = $view['services_list'][ $idx ] ?? array();
			$text         = MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$row_info,
				0,
				$kind,
				$station_posts,
				false,
				false,
				$label
			);
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

	return MRT_timetable_ensure_standalone_bus_boarding_time( $rows, $display_columns, $view, $station_posts );
}

/**
 * @param array<int, array<string, mixed>> $rows
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}> $display_columns
 * @param array<string, mixed> $view
 * @param array<int, WP_Post>  $station_posts
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_ensure_standalone_bus_boarding_time(
	array $rows,
	array $display_columns,
	array $view,
	array $station_posts
): array {
	foreach ( $display_columns as $col_idx => $column ) {
		$idx = (int) $column['primary_idx'];
		$row_info = $view['service_info'][ $idx ] ?? array();
		if ( empty( $row_info['standalone_overview_column'] ) ) {
			continue;
		}
		$service_data = $view['services_list'][ $idx ] ?? array();
		if ( MRT_timetable_standalone_bus_rows_already_show_boarding( $rows, (int) $col_idx ) ) {
			continue;
		}
		$rows = MRT_timetable_apply_standalone_bus_boarding_fallback(
			$rows,
			(int) $col_idx,
			$service_data,
			$row_info,
			$station_posts
		);
	}
	return $rows;
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function MRT_timetable_standalone_bus_rows_already_show_boarding( array $rows, int $col_idx ): bool {
	foreach ( $rows as $row ) {
		$kind = (string) ( $row['kind'] ?? '' );
		if ( $kind !== 'busDeparture' && $kind !== 'departure' ) {
			continue;
		}
		$text = (string) ( $row['cells'][ $col_idx ]['text'] ?? '' );
		if ( $text !== '' && $text !== '—' && $text !== '|' ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array<int, array<string, mixed>> $rows
 * @param array<string, mixed> $info
 * @param array<int, WP_Post>  $station_posts
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_apply_standalone_bus_boarding_fallback(
	array $rows,
	int $col_idx,
	array $service_data,
	array $info,
	array $station_posts
): array {
	$boarding_id = MRT_timetable_standalone_bus_boarding_station_id( $service_data );
	if ( $boarding_id <= 0 ) {
		return $rows;
	}
	$text = MRT_timetable_standalone_bus_boarding_time_text( $service_data, $boarding_id );
	foreach ( $rows as $row_idx => $row ) {
		$kind  = (string) ( $row['kind'] ?? '' );
		$label = (string) ( $row['label'] ?? '' );
		if ( $kind !== 'busDeparture' ) {
			continue;
		}
		if ( ! MRT_timetable_standalone_bus_is_boarding_departure_row( $label, $boarding_id ) ) {
			continue;
		}
		$rows[ $row_idx ]['cells'][ $col_idx ] = array( 'text' => $text, 'approximateTime' => false );
		return $rows;
	}
	return $rows;
}
