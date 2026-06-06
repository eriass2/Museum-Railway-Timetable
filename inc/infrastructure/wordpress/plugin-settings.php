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
		'enabled'                            => true,
		'note'                               => '',
		'operator_name'                      => '',
		'ticket_url'                         => '',
		'min_transfer_minutes'               => 0,
		'max_transfer_minutes'               => 120,
		'max_transfers'                      => 2,
		'afternoon_return_threshold_minutes' => 900,
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
 * Minutes from midnight when afternoon-return pricing applies (default 15:00).
 */
function MRT_afternoon_return_threshold_minutes(): int {
	$opts    = MRT_get_plugin_settings();
	$minutes = (int) ( $opts['afternoon_return_threshold_minutes'] ?? 900 );
	return max( 0, min( 1439, $minutes ) );
}

/**
 * Maximum transfers allowed in journey search (default 2 = three legs).
 */
function MRT_plugin_max_transfers(): int {
	$opts = MRT_get_plugin_settings();
	$max  = (int) ( $opts['max_transfers'] ?? 2 );
	return max( 0, min( 5, $max ) );
}

/**
 * Global ticket URL from settings (empty when unset).
 */
function MRT_plugin_ticket_url(): string {
	$url = (string) ( MRT_get_plugin_settings()['ticket_url'] ?? '' );
	return $url !== '' ? esc_url( $url ) : '';
}

/**
 * Operator display name from settings (empty when unset).
 */
function MRT_plugin_operator_name(): string {
	return trim( (string) ( MRT_get_plugin_settings()['operator_name'] ?? '' ) );
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
	$min     = MRT_sanitize_plugin_settings_transfer_min(
		$input['min_transfer_minutes'] ?? $current['min_transfer_minutes']
	);
	$max     = MRT_sanitize_plugin_settings_transfer_max(
		$input['max_transfer_minutes'] ?? $current['max_transfer_minutes'],
		$min
	);

	return array(
		'enabled'                            => ! empty( $input['enabled'] ),
		'note'                               => isset( $input['note'] ) ? sanitize_text_field( wp_unslash( $input['note'] ) ) : (string) $current['note'],
		'operator_name'                      => isset( $input['operator_name'] ) ? sanitize_text_field( wp_unslash( $input['operator_name'] ) ) : (string) $current['operator_name'],
		'ticket_url'                         => isset( $input['ticket_url'] ) ? esc_url_raw( wp_unslash( (string) $input['ticket_url'] ) ) : (string) $current['ticket_url'],
		'min_transfer_minutes'               => $min,
		'max_transfer_minutes'               => $max,
		'max_transfers'                      => MRT_sanitize_plugin_settings_max_transfers(
			$input['max_transfers'] ?? $current['max_transfers']
		),
		'afternoon_return_threshold_minutes' => MRT_sanitize_plugin_settings_afternoon_threshold(
			$input['afternoon_return_threshold_minutes'] ?? $current['afternoon_return_threshold_minutes']
		),
	);
}

/**
 * @param mixed $value Raw minutes.
 */
function MRT_sanitize_plugin_settings_transfer_min( $value ): int {
	return max( 0, min( 60, (int) $value ) );
}

/**
 * @param mixed $value Raw minutes.
 */
function MRT_sanitize_plugin_settings_transfer_max( $value, int $min ): int {
	$max = max( 0, min( 480, (int) $value ) );
	return max( $min, $max );
}

/**
 * @param mixed $value Raw transfer count.
 */
function MRT_sanitize_plugin_settings_max_transfers( $value ): int {
	return max( 0, min( 5, (int) $value ) );
}

/**
 * @param mixed $value Minutes from midnight.
 */
function MRT_sanitize_plugin_settings_afternoon_threshold( $value ): int {
	return max( 0, min( 1439, (int) $value ) );
}
