<?php
/**
 * Journey search engine (BFS, configurable max transfers).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/journey/engine/constraints.php';
require_once MRT_PATH . 'inc/domain/journey/engine/graph.php';
require_once __DIR__ . '/search-results.php';
require_once __DIR__ . '/search-bfs.php';
require_once __DIR__ . '/search-find.php';

/**
 * Build one leg payload from a graph edge.
 *
 * @param array{service_id: int, to_station_id: int, departure: string, arrival: string, connection: array<string, mixed>} $edge
 * @return array<string, mixed>
 */
function MRT_journey_engine_leg_from_edge(
	array $edge,
	int $from_station_id,
	string $dateYmd
): array {
	$leg = MRT_journey_build_leg_segment(
		(int) $edge['service_id'],
		$from_station_id,
		(int) $edge['to_station_id'],
		$dateYmd
	);
	if ( $leg !== null ) {
		return $leg;
	}
	return MRT_journey_leg_from_connection_row(
		$edge['connection'],
		$dateYmd,
		$from_station_id,
		(int) $edge['to_station_id']
	);
}
