<?php
/**
 * One-off benchmark: journey calendar month build (CLI via wp-load).
 *
 * Usage: docker compose exec -T wordpress php wp-content/plugins/museum-railway-timetable/scripts/bench-calendar.php
 */

define( 'ABSPATH', '/var/www/html/' );
require ABSPATH . 'wp-load.php';

$from   = (int) ( $argv[1] ?? 3339 );
$to     = (int) ( $argv[2] ?? 3353 );
$year   = (int) ( $argv[3] ?? 2026 );
$month  = (int) ( $argv[4] ?? 7 );
$trip   = (string) ( $argv[5] ?? 'return' );

if ( ! in_array( '--no-bump', $argv, true ) ) {
	MRT_bump_journey_calendar_cache_version();
}

$sw = microtime( true );
$r  = MRT_get_journey_calendar_month( $from, $to, $year, $month, $trip );
$ms = ( microtime( true ) - $sw ) * 1000;
echo sprintf( "Cold  %s %04d-%02d %s: %d days, %.0f ms\n", $trip, $year, $month, "{$from}→{$to}", count( $r ), $ms );

$sw2 = microtime( true );
$r2  = MRT_get_journey_calendar_month( $from, $to, $year, $month, $trip );
$ms2 = ( microtime( true ) - $sw2 ) * 1000;
echo sprintf( "Warm  %s %04d-%02d %s: %d days, %.0f ms\n", $trip, $year, $month, "{$from}→{$to}", count( $r2 ), $ms2 );

$key = MRT_journey_calendar_month_cache_key( $from, $to, $year, $month, $trip );
$cached = get_transient( $key );
echo 'Transient in DB: ' . ( is_array( $cached ) ? 'yes (' . count( $cached ) . ' days)' : 'NO' ) . "\n";
echo 'Cache version: ' . MRT_journey_calendar_cache_version() . "\n";
