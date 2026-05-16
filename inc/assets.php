<?php

declare(strict_types=1);

/**
 * Asset enqueuing loader for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base URL for plugin assets directory (trailing slash).
 */
function MRT_assets_base_url(): string {
	return MRT_URL . 'assets/';
}

/**
 * Enqueue shared train-type icon styles.
 *
 * @param array<int, string> $deps Style dependencies
 * @return string Style handle
 */
function MRT_enqueue_train_type_icon_styles( array $deps = array() ): string {
	wp_enqueue_style(
		'mrt-train-type-icons',
		MRT_assets_base_url() . 'train-type-icons.css',
		$deps,
		MRT_VERSION
	);
	return 'mrt-train-type-icons';
}

require_once MRT_PATH . 'inc/assets/admin.php';
require_once MRT_PATH . 'inc/assets/frontend.php';
