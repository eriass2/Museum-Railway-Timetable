<?php
/**
 * Boot shortcode unit tests with a capture stub for MRT_render_vue_mount().
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

function MRT_test_install_vue_mount_stub(): void {
	if ( function_exists( 'MRT_render_vue_mount' ) ) {
		return;
	}

	function MRT_render_vue_mount( string $app, array $config ): string {
		$GLOBALS['mrt_test_vue_mount'] = array(
			'app'    => $app,
			'config' => $config,
		);

		return '<div class="mrt-vue-mount"></div>';
	}
}

function MRT_test_boot_journey_wizard_shortcode(): void {
	static $booted = false;
	if ( $booted ) {
		return;
	}

	MRT_test_install_vue_mount_stub();

	if ( ! function_exists( 'MRT_journey_wizard_debug_presets' ) ) {
		/**
		 * @return array<string, array<string, mixed>>
		 */
		function MRT_journey_wizard_debug_presets(): array {
			return array(
				'date'     => array(),
				'outbound' => array(),
			);
		}
	}

	require_once ABSPATH . 'inc/public/journey-wizard/timetable.php';
	require_once ABSPATH . 'inc/public/journey-wizard/shell.php';
	require_once ABSPATH . 'inc/public/vue-shortcode-config.php';

	$booted = true;
}

function MRT_test_boot_timetable_overview_shortcode(): void {
	static $booted = false;
	if ( $booted ) {
		return;
	}

	MRT_test_install_vue_mount_stub();

	require_once ABSPATH . 'inc/public/vue-shortcode-config.php';
	require_once ABSPATH . 'inc/public/timetable-overview/shortcode.php';

	$booted = true;
}
