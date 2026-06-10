<?php
$root = dirname( __DIR__ );
$src  = file_get_contents( $root . '/inc/domain/pricing/price-rules.php' );
$dir  = $root . '/inc/domain/pricing/';

$groups = array(
	'price-rules-zones.php' => array(
		'MRT_pricing_zone_count',
		'MRT_parse_trip_clock_minutes',
		'MRT_qualifies_for_afternoon_return',
		'MRT_zones_for_station_pair',
		'MRT_zones_pair_span',
		'MRT_zones_distinct_on_path',
		'MRT_zones_min_range_on_path',
		'MRT_zones_for_station_path',
		'MRT_collect_journey_leg_station_ids',
		'MRT_zones_for_journey_legs',
		'MRT_zones_for_trip_price',
		'MRT_parse_trip_price_legs_param',
		'MRT_zones_for_station_pair_ids',
	),
	'price-rules-matrix.php' => array(
		'MRT_price_matrix_has_any_price',
		'MRT_afternoon_return_price_matrix_flat',
		'MRT_price_matrix_for_trip',
		'MRT_day_ticket_matrix',
		'MRT_trip_prices_response',
	),
);

require __DIR__ . '/lib/extract-php-functions.php';

$header = <<<'PHP'
<?php
/**
 * Trip price rules: %s
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;

foreach ( $groups as $file => $names ) {
	$parts = array();
	foreach ( $names as $name ) {
		$parts[] = mrt_extract_php_function( $src, $name );
	}
	$label = str_replace( array( 'price-rules-', '.php' ), '', $file );
	file_put_contents( $dir . $file, sprintf( $header, $label ) . implode( "\n\n", $parts ) . "\n" );
	echo "wrote $file\n";
}

$loader = <<<'PHP'
<?php
/**
 * Trip price selection rules (zone span, afternoon return, matrix lookup).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/price-rules-zones.php';
require_once __DIR__ . '/price-rules-matrix.php';

PHP;

file_put_contents( $dir . 'price-rules.php', $loader );
echo "wrote price-rules.php loader\n";
