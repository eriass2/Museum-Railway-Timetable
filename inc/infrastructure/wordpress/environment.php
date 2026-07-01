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
 * @phpstan-impure
 */
function MRT_is_development_mode(): bool {
	$from_config = ( defined( 'WP_DEBUG' ) && WP_DEBUG )
		|| ( defined( 'MRT_DEVELOPMENT' ) && MRT_DEVELOPMENT );

	return (bool) apply_filters( 'mrt_is_development_mode', $from_config );
}

/**
 * Use post-name permalinks in development when still on WordPress "plain" URLs.
 *
 * Without this, paths like /wizard-smoke-test/ are not resolved and the static
 * front page (Tidtabeller) is shown instead.
 *
 * @return bool True when structure was updated and rewrite rules flushed.
 */
function MRT_ensure_pretty_permalinks(): bool {
	if ( ! MRT_is_development_mode() ) {
		return false;
	}
	$structure = (string) get_option( 'permalink_structure', '' );
	if ( $structure !== '' ) {
		return false;
	}
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules( true );
	return true;
}

require_once MRT_PATH . 'inc/infrastructure/wordpress/dev-localhost-url.php';
