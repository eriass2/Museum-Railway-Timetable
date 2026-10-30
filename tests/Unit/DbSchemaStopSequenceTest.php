<?php
/**
 * Unique stop-sequence index detection.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/inc/infrastructure/wordpress/db-schema.php';

final class DbSchemaStopSequenceTest extends TestCase {

	public function test_missing_index_is_not_unique(): void {
		self::assertFalse( MRT_stop_sequence_index_is_unique( array() ) );
	}

	public function test_non_unique_index_is_rejected(): void {
		self::assertFalse(
			MRT_stop_sequence_index_is_unique(
				array(
					(object) array( 'Non_unique' => '1' ),
				)
			)
		);
	}

	public function test_unique_index_is_accepted(): void {
		self::assertTrue(
			MRT_stop_sequence_index_is_unique(
				array(
					array( 'Non_unique' => '0' ),
					array( 'Non_unique' => 0 ),
				)
			)
		);
	}

	public function test_schema_version_requires_the_unique_stop_sequence(): void {
		self::assertSame( '4', MRT_db_schema_version() );
	}
}
