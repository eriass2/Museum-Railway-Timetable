<?php

/**
 * PHPStan bootstrap – simulate WordPress environment
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
if (!defined('MRT_PATH')) {
    define('MRT_PATH', __DIR__ . '/');
}
if (!defined('MRT_URL')) {
    define('MRT_URL', '');
}
if (!defined('MRT_VERSION')) {
    define('MRT_VERSION', '0.3.0');
}

require_once __DIR__ . '/inc/constants.php';
