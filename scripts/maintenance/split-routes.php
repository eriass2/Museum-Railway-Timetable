<?php
$root = dirname( __DIR__, 2 );
$src  = file_get_contents( $root . '/inc/domain/route/routes.php' );
$dir  = $root . '/inc/domain/route/';

$groups = array(
	'route-meta.php' => array(
		'MRT_get_route_end_stations',
		'MRT_get_route_stations',
		'MRT_update_route_terminus_station_meta',
	),
	'route-direction.php' => array(
		'MRT_calculate_direction_from_end_station',
		'MRT_route_direction_from_configured_endpoints',
		'MRT_route_direction_from_station_order',
		'MRT_route_station_index',
		'MRT_route_leg_travels_towards_station',
		'MRT_journey_transfer_overshoots_destination',
	),
	'route-labels.php' => array(
		'MRT_get_route_label_from_end_station',
		'MRT_get_route_label_from_direction',
		'MRT_get_route_label',
		'MRT_get_route_label_from_services_end_station',
		'MRT_route_label_service_object',
		'MRT_get_route_label_from_unique_end_station',
	),
);

require __DIR__ . '/../lib/extract-php-functions.php';

$header = <<<'PHP'
<?php
/**
 * Route domain: %s
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
	$label = str_replace( array( 'route-', '.php' ), '', $file );
	file_put_contents( $dir . $file, sprintf( $header, $label ) . implode( "\n\n", $parts ) . "\n" );
	echo "wrote $file\n";
}

$loader = <<<'PHP'
<?php
/**
 * Route helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/route-meta.php';
require_once __DIR__ . '/route-direction.php';
require_once __DIR__ . '/route-labels.php';

PHP;

file_put_contents( $dir . 'routes.php', $loader );
echo "wrote routes.php loader\n";
