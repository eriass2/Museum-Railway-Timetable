<?php
/**
 * Plugin Name: Museum Railway Timetable
 * Description: A calendar displaying train timetables for a museum railway.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Erik
 * Text Domain: museum-railway-timetable
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) { exit; }

define('MRT_VERSION', '0.1.0');
define('MRT_PATH', plugin_dir_path(__FILE__));
define('MRT_URL', plugin_dir_url(__FILE__));

// Load translations
add_action('plugins_loaded', function() {
    load_plugin_textdomain('museum-railway-timetable', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Activation & deactivation hooks
register_activation_hook(__FILE__, 'MRT_activate');
register_deactivation_hook(__FILE__, 'MRT_deactivate');

function MRT_activate() {
    // Create custom DB tables and default options if needed
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();

    $stoptimes = $wpdb->prefix . 'mrt_stoptimes';
    $sql1 = "CREATE TABLE $stoptimes (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        service_post_id BIGINT UNSIGNED NOT NULL,
        station_post_id BIGINT UNSIGNED NOT NULL,
        stop_sequence INT NOT NULL,
        arrival_time CHAR(5) NULL,
        departure_time CHAR(5) NULL,
        pickup_allowed TINYINT(1) DEFAULT 1,
        dropoff_allowed TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id),
        KEY service_seq (service_post_id, stop_sequence),
        KEY station (station_post_id)
    ) $charset;";

    $calendar = $wpdb->prefix . 'mrt_calendar';
    $sql2 = "CREATE TABLE $calendar (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        service_post_id BIGINT UNSIGNED NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        mon TINYINT(1) DEFAULT 0,
        tue TINYINT(1) DEFAULT 0,
        wed TINYINT(1) DEFAULT 0,
        thu TINYINT(1) DEFAULT 0,
        fri TINYINT(1) DEFAULT 0,
        sat TINYINT(1) DEFAULT 0,
        sun TINYINT(1) DEFAULT 0,
        include_dates TEXT NULL,
        exclude_dates TEXT NULL,
        PRIMARY KEY (id),
        KEY service (service_post_id),
        KEY range_idx (start_date, end_date)
    ) $charset;";

    dbDelta($sql1);
    dbDelta($sql2);

    if (get_option('mrt_settings') === false) {
        add_option('mrt_settings', ['enabled' => true]);
    }
}

function MRT_deactivate() {
    // No-op: keep data on deactivation; uninstall.php will remove options
}

// Admin and features
require_once MRT_PATH . 'inc/admin-page.php';
require_once MRT_PATH . 'inc/admin-list.php';
require_once MRT_PATH . 'inc/cpt.php';
require_once MRT_PATH . 'inc/import.php';
require_once MRT_PATH . 'inc/shortcode.php';

// Example init hook for future extensions
add_action('init', function () {
    // Register additional hooks or logic here if needed
});
