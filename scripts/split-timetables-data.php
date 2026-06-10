<?php
$root = dirname( __DIR__ );
$src  = file_get_contents( $root . '/inc/infrastructure/rest/admin/timetables-data.php' );
$dir  = $root . '/inc/infrastructure/rest/admin/';

require __DIR__ . '/lib/extract-php-functions.php';

$list = array(
	'MRT_rest_list_timetables',
	'MRT_rest_get_timetable_detail',
	'MRT_rest_format_timetable_services',
	'MRT_rest_format_route_options',
	'MRT_rest_format_train_type_options',
);
$write = array(
	'MRT_rest_create_timetable',
	'MRT_rest_update_timetable',
	'MRT_rest_save_timetable_dates',
	'MRT_rest_add_timetable_service',
	'MRT_rest_update_timetable_service',
);

$header = <<<'PHP'
<?php
/**
 * REST timetable data: %s
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;

$list_parts = array();
foreach ( $list as $name ) {
	$list_parts[] = mrt_extract_php_function( $src, $name );
}
$write_parts = array();
foreach ( $write as $name ) {
	$write_parts[] = mrt_extract_php_function( $src, $name );
}

file_put_contents( $dir . 'timetables-data-list.php', sprintf( $header, 'read serializers' ) . implode( "\n\n", $list_parts ) . "\n" );
file_put_contents( $dir . 'timetables-data-write.php', sprintf( $header, 'mutations' ) . implode( "\n\n", $write_parts ) . "\n" );

$loader = <<<'PHP'
<?php
/**
 * REST timetable serializers and mutations.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/service/timetable-trip-update.php';
require_once MRT_PATH . 'inc/domain/route/destinations.php';
require_once __DIR__ . '/timetables-data-list.php';
require_once __DIR__ . '/timetables-data-write.php';

PHP;

file_put_contents( $dir . 'timetables-data.php', $loader );
echo "wrote timetables-data split\n";
