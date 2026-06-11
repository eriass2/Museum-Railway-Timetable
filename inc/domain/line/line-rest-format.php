<?php
/**
 * Line registry REST formatting.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/line/line-csv.php';

function MRT_station_post_id_from_station_code( string $station_code ): int {
	if ( $station_code === '' ) {
		return 0;
	}
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_STATION,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => 'mrt_station_code',
			'meta_value'     => $station_code,
		)
	);
	return isset( $posts[0] ) ? (int) $posts[0] : 0;
}

/**
 * @return array<int, array<string, mixed>>
 */
function MRT_rest_format_lines_list(): array {
	$registry = MRT_get_line_registry();
	if ( $registry === array() ) {
		return array();
	}
	$rows = array();
	foreach ( $registry as $code => $entry ) {
		if ( ! is_array( $entry ) ) {
			continue;
		}
		$row = MRT_rest_format_line_entry( (string) $code, $entry );
		if ( $row !== null ) {
			$rows[] = $row;
		}
	}
	usort(
		$rows,
		static fn ( array $a, array $b ): int => strcmp( (string) ( $a['title'] ?? '' ), (string) ( $b['title'] ?? '' ) )
	);
	return $rows;
}

/**
 * @param array<string, mixed> $entry
 * @return array<string, mixed>|null
 */
function MRT_rest_format_line_entry( string $code, array $entry ): ?array {
	$station_codes = $entry['station_codes'] ?? array();
	if ( ! is_array( $station_codes ) || $station_codes === array() ) {
		return null;
	}
	$station_ids = array();
	foreach ( $station_codes as $station_code ) {
		$station_code = trim( (string) $station_code );
		if ( $station_code === '' ) {
			continue;
		}
		$station_id = MRT_station_post_id_from_station_code( $station_code );
		if ( $station_id > 0 ) {
			$station_ids[] = $station_id;
		}
	}
	if ( $station_ids === array() ) {
		return null;
	}
	$junction_code = trim( (string) ( $entry['junction_station_code'] ?? '' ) );
	$junction_id   = $junction_code !== '' ? MRT_station_post_id_from_station_code( $junction_code ) : 0;
	$kind          = (string) ( $entry['kind'] ?? '' );
	return array(
		'code'                  => $code,
		'title'                 => (string) ( $entry['title'] ?? $code ),
		'kind'                  => $kind,
		'station_ids'           => $station_ids,
		'start_station'         => (int) $station_ids[0],
		'end_station'           => (int) $station_ids[ count( $station_ids ) - 1 ],
		'junction_station_id'   => $junction_id,
		'junction_station_code' => $junction_code,
		'junction_station_name' => $junction_id > 0 ? (string) get_the_title( $junction_id ) : '',
		'requires_transfer'     => (bool) ( $entry['requires_transfer'] ?? false ),
		'bidirectional'         => in_array( $kind, array( 'main', 'branch' ), true ),
	);
}

/**
 * Slim options for Turvy trip editor (line + direction).
 *
 * @return array<int, array<string, mixed>>
 */
function MRT_rest_format_line_options(): array {
	$rows = array();
	foreach ( MRT_rest_format_lines_list() as $line ) {
		$termini = array();
		foreach ( array( $line['start_station'] ?? 0, $line['end_station'] ?? 0 ) as $station_id ) {
			$station_id = (int) $station_id;
			if ( $station_id <= 0 ) {
				continue;
			}
			$station_code = trim( (string) get_post_meta( $station_id, 'mrt_station_code', true ) );
			$termini[]    = array(
				'station_id'     => $station_id,
				'station_code'   => $station_code,
				'station_name'   => (string) get_the_title( $station_id ),
			);
		}
		$rows[] = array(
			'code'    => (string) ( $line['code'] ?? '' ),
			'title'   => (string) ( $line['title'] ?? '' ),
			'kind'    => (string) ( $line['kind'] ?? '' ),
			'termini' => $termini,
		);
	}
	return $rows;
}
