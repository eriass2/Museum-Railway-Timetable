<?php
/**
 * Plugin settings (Settings API).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General plugin settings fields.
 */
function MRT_register_settings_general(): void {
	register_setting(
		'mrt_group',
		'mrt_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'MRT_sanitize_settings',
			'default'           => array(
				'enabled' => true,
				'note'    => '',
			),
		)
	);

	add_settings_section(
		'mrt_main',
		__( 'General Settings', 'museum-railway-timetable' ),
		static function (): void {
			echo '<p>' . esc_html__( 'Configure timetable display.', 'museum-railway-timetable' ) . '</p>';
		},
		'mrt_settings'
	);

	add_settings_field(
		'mrt_enabled',
		__( 'Enable Plugin', 'museum-railway-timetable' ),
		'MRT_render_enabled_field',
		'mrt_settings',
		'mrt_main'
	);

	add_settings_field(
		'mrt_note',
		__( 'Note', 'museum-railway-timetable' ),
		'MRT_render_note_field',
		'mrt_settings',
		'mrt_main'
	);
}

/**
 * Price matrix settings (public journey).
 */
function MRT_register_settings_prices(): void {
	register_setting(
		'mrt_group',
		'mrt_price_matrix',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'MRT_sanitize_price_matrix',
			'default'           => array(),
		)
	);

	add_settings_section(
		'mrt_prices',
		__( 'Public journey — price matrix', 'museum-railway-timetable' ),
		static function (): void {
			echo '<p>' . esc_html__( 'Optional prices for passenger categories (display/API).', 'museum-railway-timetable' ) . '</p>';
		},
		'mrt_settings'
	);

	add_settings_field(
		'mrt_price_matrix',
		__( 'Prices (SEK)', 'museum-railway-timetable' ),
		'MRT_render_price_matrix_field',
		'mrt_settings',
		'mrt_prices'
	);
}

/**
 * Register settings API entries.
 */
function MRT_register_plugin_settings(): void {
	MRT_register_settings_general();
	MRT_register_settings_prices();
}

add_action( 'admin_init', 'MRT_register_plugin_settings' );
