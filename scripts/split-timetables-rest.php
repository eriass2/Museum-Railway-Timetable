<?php
$root = dirname( __DIR__ );
$src  = file_get_contents( $root . '/inc/infrastructure/rest/timetables.php' );
$dir  = $root . '/inc/infrastructure/rest/';

require __DIR__ . '/lib/extract-php-functions.php';

$register = mrt_extract_php_function( $src, 'MRT_rest_register_timetable_routes' );
$handlers = array(
	'MRT_rest_list_timetables_handler',
	'MRT_rest_create_timetable_handler',
	'MRT_rest_get_timetable_handler',
	'MRT_rest_update_timetable_handler',
	'MRT_rest_timetable_overview_handler',
	'MRT_rest_add_timetable_service_handler',
	'MRT_rest_update_timetable_service_handler',
	'MRT_rest_remove_timetable_service_handler',
	'MRT_rest_delete_timetable_handler',
	'MRT_rest_get_deviations_handler',
	'MRT_rest_save_deviations_handler',
	'MRT_rest_route_destinations_handler',
);

$header = <<<'PHP'
<?php
/**
 * REST timetables: %s
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;

$handler_parts = array();
foreach ( $handlers as $name ) {
	$handler_parts[] = mrt_extract_php_function( $src, $name );
}

file_put_contents( $dir . 'timetables-register.php', sprintf( $header, 'route registration' ) . $register . "\n" );
file_put_contents( $dir . 'timetables-handlers.php', sprintf( $header, 'request handlers' ) . implode( "\n\n", $handler_parts ) . "\n" );

$loader = <<<'PHP'
<?php
/**
 * REST: timetables.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/infrastructure/rest/timetables-data.php';
require_once MRT_PATH . 'inc/domain/admin/timetable-deviations.php';
require_once MRT_PATH . 'inc/domain/admin/delete-entities.php';
require_once __DIR__ . '/timetables-register.php';
require_once __DIR__ . '/timetables-handlers.php';

PHP;

file_put_contents( $dir . 'timetables.php', $loader );
echo "wrote timetables REST split\n";
