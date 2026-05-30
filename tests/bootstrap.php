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
define('MRT_TEXT_DOMAIN', 'museum-railway-timetable');
define('MRT_POST_TYPE_SERVICE', 'mrt_service');
define('MRT_POST_TYPE_TIMETABLE', 'mrt_timetable');

require_once __DIR__ . '/wp-stubs.php';
require_once __DIR__ . '/JourneyTestFixtures.php';

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
