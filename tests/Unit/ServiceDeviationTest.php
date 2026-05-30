<?php
/**
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ServiceDeviationTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_terms'], $GLOBALS['mrt_test_post_terms'] );
		parent::tearDown();
	}

	public function test_notice_for_date_overrides_global(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|mrt_service_notice'         => 'Global notice',
			'5|mrt_service_notices_by_date' => array(
				'2026-06-15' => 'Diesel pga väder',
			),
		);
		self::assertSame( 'Diesel pga väder', MRT_get_service_notice( 5, '2026-06-15' ) );
		self::assertSame( 'Global notice', MRT_get_service_notice( 5, '2026-06-16' ) );
	}

	public function test_notice_for_date_without_global(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|mrt_service_notices_by_date' => array(
				'2026-06-15' => 'Ersatt lok',
			),
		);
		self::assertSame( 'Ersatt lok', MRT_get_service_notice( 5, '2026-06-15' ) );
		self::assertSame( '', MRT_get_service_notice( 5, '2026-06-16' ) );
	}

	public function test_train_type_deviation_when_date_override_differs(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'7|mrt_service_train_types_by_date' => array(
				'2026-07-04' => 20,
			),
		);
		$GLOBALS['mrt_test_post_terms'] = array(
			7 => array( 10 ),
		);
		$GLOBALS['mrt_test_terms'] = array(
			10 => $this->make_term( 10, 'Ångtåg', 'ang' ),
			20 => $this->make_term( 20, 'Dieseltåg', 'diesel' ),
		);
		self::assertTrue( MRT_service_has_train_type_deviation( 7, '2026-07-04' ) );
		self::assertFalse( MRT_service_has_train_type_deviation( 7, '2026-07-05' ) );
	}

	public function test_deviation_print_key_row_includes_type_and_notice(): void {
		$service = new WP_Post( (object) array( 'ID' => 9, 'post_type' => 'mrt_service' ) );
		$GLOBALS['mrt_test_post_meta'] = array(
			'9|mrt_service_number'              => '71',
			'9|mrt_service_train_types_by_date' => array( '2026-08-01' => 20 ),
			'9|mrt_service_notices_by_date'     => array( '2026-08-01' => 'Pga underhåll' ),
		);
		$GLOBALS['mrt_test_post_terms'] = array(
			9 => array( 10 ),
		);
		$GLOBALS['mrt_test_terms'] = array(
			10 => $this->make_term( 10, 'Ångtåg', 'ang' ),
			20 => $this->make_term( 20, 'Dieseltåg', 'diesel' ),
		);
		self::assertTrue(
			MRT_service_has_train_type_deviation( 9, '2026-08-01' ),
			'Expected train type deviation for service 9'
		);
		$row = MRT_timetable_deviation_print_key_row( $service, '2026-08-01' );
		self::assertNotNull( $row );
		self::assertSame( '71†', $row['symbol'] );
		self::assertStringContainsString( 'Dieseltåg', $row['text'] );
		self::assertStringContainsString( 'Pga underhåll', $row['text'] );
	}

	private function make_term( int $id, string $name, string $slug ): WP_Term {
		$term           = new WP_Term();
		$term->term_id  = $id;
		$term->name     = $name;
		$term->slug     = $slug;
		return $term;
	}
}
