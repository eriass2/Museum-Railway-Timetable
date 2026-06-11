<?php
/**
 * Line registry (CSV pilot — LINES_REFACTOR).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return list<string>
 */
function MRT_csv_main_corridor_route_codes(): array {
	return array( 'faringe-uppsala-ostra', 'uppsala-faringe' );
}

/**
 * Shuttle routes mapped to transfer-branch line_code (Fas 2).
 *
 * @return array<string, string>
 */
function MRT_csv_branch_route_line_map(): array {
	return array(
		'selkna-fjallnora'              => 'fjallnora',
		'fjallnora-selkna'              => 'fjallnora',
		'marielund-linnes-hammarby'     => 'linnes-marielund',
		'linnes-hammarby-marielund'     => 'linnes-marielund',
	);
}

function MRT_line_registry_option_key(): string {
	return 'mrt_line_registry';
}

function MRT_service_line_code_meta_key(): string {
	return 'mrt_service_line_code';
}

/**
 * @return array<string, array{title: string, kind: string, station_codes: array<int, string>, junction_station_code?: string, requires_transfer?: bool}>
 */
function MRT_get_line_registry(): array {
	$stored = get_option( MRT_line_registry_option_key(), array() );
	return is_array( $stored ) ? $stored : array();
}

/**
 * @param array<string, array{title: string, kind: string, station_codes: array<int, string>, junction_station_code?: string, requires_transfer?: bool}> $registry
 */
function MRT_set_line_registry( array $registry ): void {
	update_option( MRT_line_registry_option_key(), $registry, false );
}

function MRT_get_service_line_code( int $service_id ): string {
	if ( $service_id <= 0 ) {
		return '';
	}
	return trim( (string) get_post_meta( $service_id, MRT_service_line_code_meta_key(), true ) );
}

/**
 * @param array<string, string> $routes_by_code route_code => branch_code
 */
function MRT_csv_resolve_service_line_code( array $row, array $routes_by_code ): string {
	$explicit = trim( (string) ( $row['line_code'] ?? '' ) );
	if ( $explicit !== '' ) {
		return $explicit;
	}
	$route_code = trim( (string) ( $row['route_code'] ?? '' ) );
	if ( $route_code === '' ) {
		return '';
	}
	$branch_line = MRT_csv_branch_route_line_map()[ $route_code ] ?? '';
	if ( $branch_line !== '' ) {
		return $branch_line;
	}
	if ( $route_code === 'linnes-uppsala' ) {
		return 'linnes-uppsala';
	}
	if ( in_array( $route_code, MRT_csv_main_corridor_route_codes(), true ) ) {
		return 'main';
	}
	$branch = trim( (string) ( $routes_by_code[ $route_code ] ?? '' ) );
	return $branch === 'main' ? 'main' : '';
}

function MRT_csv_line_kind_is_valid( string $kind ): bool {
	return in_array( $kind, array( 'main', 'branch', 'pattern' ), true );
}

function MRT_line_junction_station_code( string $line_code ): string {
	if ( $line_code === '' ) {
		return '';
	}
	$registry = MRT_get_line_registry();
	return trim( (string) ( $registry[ $line_code ]['junction_station_code'] ?? '' ) );
}

/**
 * @param array<string, mixed> $group Timetable service group.
 */
function MRT_timetable_group_line_code( array $group ): string {
	foreach ( (array) ( $group['services'] ?? array() ) as $service_data ) {
		$service = $service_data['service'] ?? null;
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$line_code = MRT_get_service_line_code( (int) $service->ID );
		if ( $line_code !== '' ) {
			return $line_code;
		}
	}
	return '';
}

/**
 * @param array<string, mixed> $group Branch shuttle group.
 */
function MRT_line_junction_station_id_for_group( array $group, string $line_code ): int {
	$junction_code = MRT_line_junction_station_code( $line_code );
	if ( $junction_code === '' ) {
		return 0;
	}
	foreach ( array_map( 'intval', (array) ( $group['stations'] ?? array() ) ) as $station_id ) {
		if ( $station_id <= 0 ) {
			continue;
		}
		$code = trim( (string) get_post_meta( $station_id, 'mrt_station_code', true ) );
		if ( $code === $junction_code ) {
			return $station_id;
		}
	}
	return 0;
}

function MRT_line_registry_entry( string $line_code ): array {
	if ( $line_code === '' ) {
		return array();
	}
	$registry = MRT_get_line_registry();
	$entry    = $registry[ $line_code ] ?? array();
	return is_array( $entry ) ? $entry : array();
}

function MRT_line_is_direct_pattern( string $line_code ): bool {
	return ( MRT_line_registry_entry( $line_code )['kind'] ?? '' ) === 'pattern';
}

function MRT_line_overview_corridor_after_station_code( string $line_code ): string {
	return trim( (string) ( MRT_line_registry_entry( $line_code )['overview_corridor_after_station_code'] ?? '' ) );
}

/**
 * @param array<int, WP_Post> $station_posts
 */
function MRT_station_post_id_from_code( string $station_code, array $station_posts ): int {
	if ( $station_code === '' ) {
		return 0;
	}
	foreach ( $station_posts as $station ) {
		if ( ! $station instanceof WP_Post ) {
			continue;
		}
		if ( trim( (string) get_post_meta( (int) $station->ID, 'mrt_station_code', true ) ) === $station_code ) {
			return (int) $station->ID;
		}
	}
	return 0;
}
