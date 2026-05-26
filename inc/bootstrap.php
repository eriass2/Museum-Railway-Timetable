<?php
/**
 * Plugin bootstrap – incremental module loading for rebuild.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Domain helpers and view logic (loaders may point at inc/domain/).
 */
function MRT_bootstrap_load_domain(): void {
	require_once MRT_PATH . 'inc/functions/helpers.php';
	require_once MRT_PATH . 'inc/functions/services.php';
	require_once MRT_PATH . 'inc/functions/journey-loader.php';
	require_once MRT_PATH . 'inc/functions/timetable-view.php';
}

/**
 * WordPress adapters: assets, admin, import, CPT, public shortcodes.
 */
function MRT_bootstrap_load_app(): void {
	require_once MRT_PATH . 'inc/assets.php';
	require_once MRT_PATH . 'inc/admin-page.php';
	require_once MRT_PATH . 'inc/admin-meta-boxes.php';
	require_once MRT_PATH . 'inc/admin-ajax.php';
	require_once MRT_PATH . 'inc/import-lennakatten/loader.php';
	require_once MRT_PATH . 'inc/cpt.php';
	require_once MRT_PATH . 'inc/shortcodes.php';
}

/**
 * Load all plugin modules (called from museum-railway-timetable.php).
 */
function MRT_bootstrap_load(): void {
	MRT_bootstrap_load_domain();
	MRT_bootstrap_load_app();
}
