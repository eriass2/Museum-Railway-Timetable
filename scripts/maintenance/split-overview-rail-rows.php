<?php
$root = dirname( __DIR__, 2 );
$src  = file_get_contents( $root . '/inc/domain/timetable/view/overview/overview-rail-rows.php' );
$dir  = $root . '/inc/domain/timetable/view/overview/';

require __DIR__ . '/../lib/extract-php-functions.php';

$columns = array( 'MRT_timetable_overview_columns_json' );
$cells   = array(
	'MRT_timetable_row_times_json',
	'MRT_timetable_time_cell_json',
	'MRT_timetable_time_cell_text',
	'MRT_timetable_train_change_rows_json',
	'MRT_timetable_train_change_cells_json',
	'MRT_timetable_train_change_cell_json',
	'MRT_timetable_vehicle_json',
);
$rows    = array(
	'MRT_timetable_rail_group_to_json',
	'MRT_timetable_overview_rail_rows_json',
	'MRT_timetable_overview_rail_endpoint_row_json',
	'MRT_timetable_overview_rail_rows_for_station',
);

$header = <<<'PHP'
<?php
/**
 * Timetable overview rail JSON: %s
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;

foreach (
	array(
		'overview-rail-columns.php' => array( 'columns', $columns ),
		'overview-rail-cells.php'   => array( 'time cells', $cells ),
	) as $file => $spec
) {
	$parts = array();
	foreach ( $spec[1] as $name ) {
		$parts[] = mrt_extract_php_function( $src, $name );
	}
	file_put_contents( $dir . $file, sprintf( $header, $spec[0] ) . implode( "\n\n", $parts ) . "\n" );
}

$row_parts = array();
foreach ( $rows as $name ) {
	$row_parts[] = mrt_extract_php_function( $src, $name );
}

$loader = sprintf( $header, 'row assembly' )
	. "require_once __DIR__ . '/overview-rail-columns.php';\n"
	. "require_once __DIR__ . '/overview-rail-cells.php';\n\n"
	. implode( "\n\n", $row_parts )
	. "\n";

file_put_contents( $dir . 'overview-rail-rows.php', $loader );
echo "wrote overview-rail-rows split\n";
