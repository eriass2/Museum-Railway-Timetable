<?php
/**
 * Plugin Name: Museum Railway Timetable
 * Description: A calendar displaying train timetables for a museum railway.
 * Version: 0.3.1
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Erik
 * Text Domain: museum-railway-timetable
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

define( 'MRT_VERSION', '0.3.1' );
define( 'MRT_PATH', plugin_dir_path( __FILE__ ) );
define( 'MRT_URL', plugin_dir_url( __FILE__ ) );

require_once MRT_PATH . 'inc/constants.php';

// Load translations
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( MRT_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

// Activation & deactivation hooks
register_activation_hook( __FILE__, 'MRT_activate' );
register_deactivation_hook( __FILE__, 'MRT_deactivate' );

/**
 * Plugin activation hook
 * Creates custom database tables and sets default options
 */
function MRT_activate() {
	require_once MRT_PATH . 'inc/infrastructure/wordpress/db-schema.php';
	MRT_install_stoptimes_table();

	require_once MRT_PATH . 'inc/infrastructure/wordpress/plugin-settings.php';
	if ( get_option( 'mrt_settings' ) === false ) {
		add_option( 'mrt_settings', MRT_default_plugin_settings() );
	}
}

/**
 * Plugin deactivation hook
 * Keeps data on deactivation; uninstall.php will remove options
 */
function MRT_deactivate() {
	// No-op: keep data on deactivation; uninstall.php will remove options
}

require_once MRT_PATH . 'inc/bootstrap.php';
MRT_bootstrap_load();
