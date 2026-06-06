<?php
/**
 * PHPUnit helpers: apply Lennakatten reference options (not production defaults).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

require_once ABSPATH . 'inc/import/lennakatten/reference-data.php';

trait MRT_Lennakatten_Test_Fixture {

	protected function mrt_apply_lennakatten_options(): void {
		if ( ! isset( $GLOBALS['mrt_test_options'] ) || ! is_array( $GLOBALS['mrt_test_options'] ) ) {
			$GLOBALS['mrt_test_options'] = array();
		}
		$GLOBALS['mrt_test_options']['mrt_settings']     = MRT_lennakatten_reference_plugin_settings();
		$GLOBALS['mrt_test_options']['mrt_price_schema'] = MRT_lennakatten_reference_price_schema();
		$GLOBALS['mrt_test_options']['mrt_price_matrix'] = MRT_lennakatten_reference_price_matrix();
	}

	protected function mrt_clear_test_options(): void {
		unset( $GLOBALS['mrt_test_options'] );
	}
}
