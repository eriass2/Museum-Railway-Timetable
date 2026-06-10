<?php
/**
 * MRT_log and related helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LogTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_filters'] );
		parent::tearDown();
	}

	public function test_should_log_false_without_debug_constants(): void {
		self::assertFalse( MRT_should_log() );
	}

	public function test_should_log_honors_filter(): void {
		$GLOBALS['mrt_test_filters']['mrt_should_log'] = static fn (): bool => true;

		self::assertTrue( MRT_should_log() );
	}

	public function test_log_noops_when_logging_disabled(): void {
		MRT_log( 'silent message', array( 'key' => 'value' ) );
		self::assertTrue( true );
	}

	public function test_log_wp_error_noops_when_logging_disabled(): void {
		$error = new WP_Error( 'test_code', 'Test message', array( 'status' => 500 ) );
		MRT_log_wp_error( 'MRT_test', $error );
		self::assertTrue( true );
	}

	public function test_check_db_error_returns_false_when_clean(): void {
		global $wpdb;
		$wpdb->last_error = '';

		self::assertFalse( MRT_check_db_error( 'MRT_test_context' ) );
	}

	public function test_check_db_error_returns_true_when_error_set(): void {
		global $wpdb;
		$wpdb->last_error = 'Table missing';

		self::assertTrue( MRT_check_db_error( 'MRT_test_context' ) );
		$wpdb->last_error = '';
	}
}
