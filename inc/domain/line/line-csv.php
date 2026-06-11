<?php
/**
 * Line registry (CSV pilot — Fas 1 LINES_REFACTOR).
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

function MRT_line_registry_option_key(): string {
	return 'mrt_line_registry';
}

function MRT_service_line_code_meta_key(): string {
	return 'mrt_service_line_code';
}

/**
 * @return array<string, array{title: string, kind: string, station_codes: array<int, string>}>
 */
function MRT_get_line_registry(): array {
	$stored = get_option( MRT_line_registry_option_key(), array() );
	return is_array( $stored ) ? $stored : array();
}

/**
 * @param array<string, array{title: string, kind: string, station_codes: array<int, string>}> $registry
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
	if ( in_array( $route_code, MRT_csv_main_corridor_route_codes(), true ) ) {
		return 'main';
	}
	$branch = trim( (string) ( $routes_by_code[ $route_code ] ?? '' ) );
	return $branch === 'main' ? 'main' : '';
}

function MRT_csv_line_kind_is_valid( string $kind ): bool {
	return in_array( $kind, array( 'main', 'branch', 'pattern' ), true );
}
