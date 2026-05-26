<?php
/**
 * Plugin options (mrt_settings) with defaults.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values for mrt_settings (stored in wp_options).
 *
 * @return array<string, mixed>
 */
function MRT_default_plugin_settings(): array {
	return array(
		'enabled'                => true,
		'note'                   => '',
		'min_transfer_minutes'   => 5,
		'max_transfer_minutes'   => 120,
	);
}

/**
 * Plugin settings merged with defaults.
 *
 * @return array<string, mixed>
 */
function MRT_get_plugin_settings(): array {
	$stored = get_option( 'mrt_settings', array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	return array_merge( MRT_default_plugin_settings(), $stored );
}

/**
 * Sanitize mrt_settings from Settings API form.
 *
 * @param array<string, mixed> $input Raw POST values
 * @return array<string, mixed>
 */
function MRT_sanitize_plugin_settings( $input ): array {
	if ( ! is_array( $input ) ) {
		$input = array();
	}
	$current = MRT_get_plugin_settings();
	$min     = isset( $input['min_transfer_minutes'] )
		? (int) $input['min_transfer_minutes']
		: (int) $current['min_transfer_minutes'];
	$max     = isset( $input['max_transfer_minutes'] )
		? (int) $input['max_transfer_minutes']
		: (int) $current['max_transfer_minutes'];
	$min     = max( 0, min( 60, $min ) );
	$max     = max( $min, min( 480, $max ) );

	return array(
		'enabled'              => ! empty( $input['enabled'] ),
		'note'                 => isset( $input['note'] ) ? sanitize_text_field( wp_unslash( $input['note'] ) ) : '',
		'min_transfer_minutes' => $min,
		'max_transfer_minutes' => $max,
	);
}
