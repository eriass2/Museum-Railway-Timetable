<?php
$root = dirname( __DIR__ );
$src  = file_get_contents( $root . '/inc/domain/timetable/view/overview/overview-bus-rows.php' );
$dir  = $root . '/inc/domain/timetable/view/overview/';

require __DIR__ . '/lib/extract-php-functions.php';

$junction = array(
	'MRT_timetable_station_is_bus_junction',
	'MRT_connection_has_any_buses',
	'MRT_timetable_junction_bus_rows_json',
);
$stops = array(
	'MRT_timetable_bus_remote_station_label',
	'MRT_timetable_bus_remote_station_id',
	'MRT_find_bus_service_in_branch',
	'MRT_timetable_bus_time_row_json',
	'MRT_timetable_bus_time_cell_json',
	'MRT_timetable_bus_stop_for_role',
	'MRT_timetable_bus_stop_display_time',
);

$header = <<<'PHP'
<?php
/**
 * Timetable overview bus JSON: %s
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;

$junction_parts = array();
foreach ( $junction as $name ) {
	$junction_parts[] = mrt_extract_php_function( $src, $name );
}
$stop_parts = array();
foreach ( $stops as $name ) {
	$stop_parts[] = mrt_extract_php_function( $src, $name );
}

file_put_contents( $dir . 'overview-bus-junction.php', sprintf( $header, 'junction rows' ) . implode( "\n\n", $junction_parts ) . "\n" );
file_put_contents( $dir . 'overview-bus-stops.php', sprintf( $header, 'bus stop cells' ) . implode( "\n\n", $stop_parts ) . "\n" );

$loader = sprintf( $header, 'loader' )
	. "require_once __DIR__ . '/overview-bus-junction.php';\n"
	. "require_once __DIR__ . '/overview-bus-stops.php';\n";

file_put_contents( $dir . 'overview-bus-rows.php', $loader );
echo "wrote overview-bus-rows split\n";
