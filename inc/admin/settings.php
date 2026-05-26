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
			'default'           => MRT_default_plugin_settings(),
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
 * Journey search: transfer wait limits (stored in mrt_settings).
 */
function MRT_register_settings_journey_transfers(): void {
	add_settings_section(
		'mrt_journey_transfers',
		__( 'Journey search — transfers', 'museum-railway-timetable' ),
		static function (): void {
			echo '<p>' . esc_html__(
				'Minimum and maximum waiting time at a transfer station (planner and wizard). Bus hub stations (Bus stop marker) are preferred over other stops on the same route.',
				'museum-railway-timetable'
			) . '</p>';
		},
		'mrt_settings'
	);

	add_settings_field(
		'mrt_min_transfer_minutes',
		__( 'Min transfer wait (minutes)', 'museum-railway-timetable' ),
		'MRT_render_min_transfer_minutes_field',
		'mrt_settings',
		'mrt_journey_transfers'
	);

	add_settings_field(
		'mrt_max_transfer_minutes',
		__( 'Max transfer wait (minutes)', 'museum-railway-timetable' ),
		'MRT_render_max_transfer_minutes_field',
		'mrt_settings',
		'mrt_journey_transfers'
	);
}

/**
 * Render min transfer minutes field.
 */
function MRT_render_min_transfer_minutes_field(): void {
	$opts = MRT_get_plugin_settings();
	echo '<input type="number" name="mrt_settings[min_transfer_minutes]" id="mrt_min_transfer_minutes" ';
	echo 'value="' . esc_attr( (string) (int) $opts['min_transfer_minutes'] ) . '" min="0" max="60" step="1" class="small-text" />';
	echo '<p class="description">' . esc_html__( 'Earliest departure on leg 2 after arrival at transfer (default 5).', 'museum-railway-timetable' ) . '</p>';
}

/**
 * Render max transfer minutes field.
 */
function MRT_render_max_transfer_minutes_field(): void {
	$opts = MRT_get_plugin_settings();
	echo '<input type="number" name="mrt_settings[max_transfer_minutes]" id="mrt_max_transfer_minutes" ';
	echo 'value="' . esc_attr( (string) (int) $opts['max_transfer_minutes'] ) . '" min="5" max="480" step="1" class="small-text" />';
	echo '<p class="description">' . esc_html__( 'Longer waits are hidden from search results (default 120). Must be ≥ min.', 'museum-railway-timetable' ) . '</p>';
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
	MRT_register_settings_journey_transfers();
	MRT_register_settings_prices();
}

add_action( 'admin_init', 'MRT_register_plugin_settings' );
