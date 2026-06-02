<?php
/**
 * Import CSV rows into WordPress entities.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_import_stations( array $files, array &$maps ): int {
	$meta = MRT_csv_code_meta_keys()['stations'];
	$count = 0;
	foreach ( (array) ( $files['stations.csv'] ?? array() ) as $row ) {
		$code = MRT_csv_row_code( $row, 'station_code', 'name' );
		$id   = MRT_csv_find_post_by_code( $code, MRT_POST_TYPE_STATION, $meta );
		if ( $id <= 0 ) {
			$id = wp_insert_post(
				array(
					'post_type'   => MRT_POST_TYPE_STATION,
					'post_title'  => $row['name'],
					'post_status' => 'publish',
				)
			);
		} else {
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => $row['name'],
				)
			);
		}
		if ( ! $id || $id instanceof WP_Error ) {
			continue;
		}
		MRT_csv_save_post_code( (int) $id, $meta, $code );
		update_post_meta( $id, 'mrt_station_type', sanitize_text_field( $row['station_type'] ?? '' ) );
		update_post_meta( $id, 'mrt_display_order', (int) ( $row['display_order'] ?? 0 ) );
		update_post_meta( $id, 'mrt_station_bus_suffix', ( $row['bus_stop_marker'] ?? '0' ) === '1' ? '1' : '0' );
		if ( ( $row['lat'] ?? '' ) !== '' ) {
			update_post_meta( $id, 'mrt_lat', (float) $row['lat'] );
		}
		if ( ( $row['lng'] ?? '' ) !== '' ) {
			update_post_meta( $id, 'mrt_lng', (float) $row['lng'] );
		}
		if ( array_key_exists( 'price_zones', $row ) ) {
			MRT_update_station_price_zones_meta(
				(int) $id,
				MRT_parse_station_price_zones_csv( (string) ( $row['price_zones'] ?? '' ) )
			);
		}
		$maps['station'][ $code ] = (int) $id;
		++$count;
	}
	return $count;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 */
function MRT_csv_import_train_types( array $files ): void {
	foreach ( (array) ( $files['train_types.csv'] ?? array() ) as $row ) {
		$slug = sanitize_title( $row['slug'] ?? '' );
		$name = $row['name'] ?? '';
		if ( $slug === '' || $name === '' ) {
			continue;
		}
		$term = term_exists( $name, MRT_TAXONOMY_TRAIN_TYPE );
		if ( ! $term ) {
			$term = wp_insert_term( $name, MRT_TAXONOMY_TRAIN_TYPE, array( 'slug' => $slug ) );
		}
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_import_routes( array $files, array &$maps ): int {
	$meta  = MRT_csv_code_meta_keys()['routes'];
	$count = 0;
	$route_rows = (array) ( $files['routes.csv'] ?? array() );
	$station_rows = MRT_csv_group_route_stations( $files );
	foreach ( $route_rows as $row ) {
		$code = MRT_csv_row_code( $row, 'route_code', 'title' );
		$id   = MRT_csv_find_post_by_code( $code, MRT_POST_TYPE_ROUTE, $meta );
		$title = $row['title'];
		if ( $id <= 0 ) {
			$id = wp_insert_post(
				array(
					'post_type'   => MRT_POST_TYPE_ROUTE,
					'post_title'  => $title,
					'post_status' => 'publish',
				)
			);
		} else {
			wp_update_post( array( 'ID' => $id, 'post_title' => $title ) );
		}
		if ( ! $id || $id instanceof WP_Error ) {
			continue;
		}
		$station_ids = MRT_csv_resolve_route_station_ids( $station_rows[ $code ] ?? array(), $maps );
		update_post_meta( $id, 'mrt_route_stations', $station_ids );
		if ( $station_ids !== array() ) {
			update_post_meta( $id, 'mrt_route_start_station', $station_ids[0] );
			update_post_meta( $id, 'mrt_route_end_station', $station_ids[ count( $station_ids ) - 1 ] );
		}
		MRT_csv_save_post_code( (int) $id, $meta, $code );
		$maps['route'][ $code ] = (int) $id;
		++$count;
	}
	return $count;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @return array<string, array<int, array<string, string>>>
 */
function MRT_csv_group_route_stations( array $files ): array {
	$groups = array();
	foreach ( (array) ( $files['route_stations.csv'] ?? array() ) as $row ) {
		$code = $row['route_code'] ?? '';
		if ( $code === '' ) {
			continue;
		}
		$groups[ $code ][] = $row;
	}
	foreach ( $groups as $code => $rows ) {
		usort(
			$rows,
			static function ( array $a, array $b ): int {
				return (int) $a['sequence'] <=> (int) $b['sequence'];
			}
		);
		$groups[ $code ] = $rows;
	}
	return $groups;
}

/**
 * @param array<int, array<string, string>> $rows
 * @param array<string, array<string, int>> $maps
 * @return array<int, int>
 */
function MRT_csv_resolve_route_station_ids( array $rows, array $maps ): array {
	$ids = array();
	foreach ( $rows as $row ) {
		$code = $row['station_code'] ?? '';
		if ( isset( $maps['station'][ $code ] ) ) {
			$ids[] = (int) $maps['station'][ $code ];
		}
	}
	return $ids;
}

/**
 * Resolve stable code from row.
 */
function MRT_csv_row_code( array $row, string $field, string $fallback_field ): string {
	$code = trim( $row[ $field ] ?? '' );
	if ( $code !== '' ) {
		return $code;
	}
	return MRT_csv_slugify( (string) ( $row[ $fallback_field ] ?? '' ) );
}
