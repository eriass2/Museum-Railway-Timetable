<?php

declare(strict_types=1);

/**
 * Layout helpers for Vue shortcode mount nodes.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extra CSS classes on the Vue mount node (block-theme layout).
 *
 * @param string               $app    Vue app id.
 * @param array<string, mixed> $config Mount config (may include embedded).
 */
function MRT_vue_mount_extra_classes( string $app, array $config ): string {
	if ( 'wizard' === $app ) {
		return ' alignfull';
	}

	if ( in_array( $app, array( 'overview', 'month', 'index', 'traffic_notices' ), true ) ) {
		return ' alignwide';
	}

	return '';
}
