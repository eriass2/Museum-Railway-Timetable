<?php
/**
 * Development vs production environment helpers.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether development-only tools should be available.
 *
 * True when WP_DEBUG is on or MRT_DEVELOPMENT is defined true in wp-config.php.
 * Filter: mrt_is_development_mode
 *
 * @return bool
 */
function MRT_is_development_mode(): bool {
	$from_config = ( defined( 'WP_DEBUG' ) && WP_DEBUG )
		|| ( defined( 'MRT_DEVELOPMENT' ) && MRT_DEVELOPMENT );

	return (bool) apply_filters( 'mrt_is_development_mode', $from_config );
}

/**
 * Whether browser console debug is allowed (admin JS).
 *
 * @return bool
 */
function MRT_allow_script_debug(): bool {
	return MRT_is_development_mode();
}
