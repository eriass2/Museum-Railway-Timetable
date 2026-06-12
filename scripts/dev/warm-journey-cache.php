<?php
/**
 * Pre-warm journey wizard PHP transients for popular routes.
 *
 * Usage:
 *   docker compose exec -T wordpress php wp-content/plugins/museum-railway-timetable/scripts/warm-journey-cache.php
 *   docker compose exec -T wordpress php wp-content/plugins/museum-railway-timetable/scripts/warm-journey-cache.php 2026 7
 */

define( 'ABSPATH', '/var/www/html/' );
require ABSPATH . 'wp-load.php';

require_once MRT_PATH . 'inc/domain/journey/journey-cache-warm.php';

$year  = isset( $argv[1] ) ? (int) $argv[1] : null;
$month = isset( $argv[2] ) ? (int) $argv[2] : null;

$started = microtime( true );
$result  = MRT_journey_cache_warm_popular_routes( $year, $month );
$ms      = ( microtime( true ) - $started ) * 1000;

echo sprintf(
	"Warmed %d calendar months for %d route(s) around %04d-%02d in %.0f ms\n",
	(int) $result['warmed'],
	(int) $result['pairs'],
	(int) $result['year'],
	(int) $result['month'],
	$ms
);
echo 'Cache generation: ' . MRT_journey_cache_generation() . "\n";
