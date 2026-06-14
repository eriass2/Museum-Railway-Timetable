<?php
/**
 * Minimal environment for unit tests (no full WordPress load).
 *
 * Loads production code from inc/; business rules stay in those files, not here.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('MRT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
if ( ! defined( 'MRT_URL' ) ) {
	define( 'MRT_URL', 'https://example.test/wp-content/plugins/museum-railway-timetable/' );
}
define('MRT_TEXT_DOMAIN', 'museum-railway-timetable');
define('MRT_POST_TYPE_SERVICE', 'mrt_service');
define('MRT_POST_TYPE_TIMETABLE', 'mrt_timetable');
define('MRT_POST_TYPE_STATION', 'mrt_station');
define('MRT_POST_TYPE_ROUTE', 'mrt_route');
define(
	'MRT_POST_TYPES',
	array(
		MRT_POST_TYPE_STATION,
		MRT_POST_TYPE_ROUTE,
		MRT_POST_TYPE_TIMETABLE,
		MRT_POST_TYPE_SERVICE,
	)
);

require_once __DIR__ . '/wp-stubs.php';
require_once __DIR__ . '/JourneyTestFixtures.php';
require_once __DIR__ . '/StopTimeTestFixtures.php';
require_once __DIR__ . '/LennakattenTestFixtures.php';

if (!isset($GLOBALS['wpdb'])) {
    $GLOBALS['wpdb'] = new class {
        /** @var string */
        public $prefix = 'wp_';

        /** @var string */
        public $last_error = '';

        public function prepare(string $query, ...$args): string {
            return $query;
        }

        /**
         * @param string|null $query
         * @param string      $output
         * @return array<int, mixed>
         */
        public function get_results($query = null, $output = OBJECT) {
            return [];
        }

        /**
         * @param string|null $query
         * @param int         $x
         * @param int         $y
         */
        public function get_var($query = null, $x = 0, $y = 0) {
            if (isset($GLOBALS['mrt_test_wpdb_get_var']) && is_callable($GLOBALS['mrt_test_wpdb_get_var'])) {
                return $GLOBALS['mrt_test_wpdb_get_var']($query, $x, $y);
            }
            return '0';
        }

        /**
         * @param string       $table
         * @param array<mixed> $data
         * @param array<mixed> $where
         * @param array<mixed> $format
         * @param array<mixed> $where_format
         */
        public function delete($table, $data, $where_format = null, $where = null) {
            unset($table, $data, $where_format, $where);
            return 1;
        }

        /**
         * @param string $query
         * @return bool|int
         */
        public function query($query) {
            unset($query);
            return true;
        }
    };
}

if (!function_exists('__')) {
    /**
     * @param string $text
     * @param string $domain
     */
    function __($text, $domain = 'default') {
        return $text;
    }
}

require_once ABSPATH . 'inc/infrastructure/wordpress/plugin-settings.php';
require_once ABSPATH . 'inc/bootstrap/domain.php';
MRT_load_domain_modules();
require_once ABSPATH . 'inc/domain/journey/request-params.php';
require_once ABSPATH . 'inc/domain/service/stoptimes-persist.php';
