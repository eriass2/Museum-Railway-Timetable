<?php
/**
 * Trip price rule helpers (production: inc/domain/pricing/price-rules.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PriceRulesTest extends TestCase {

	/**
	 * @return array<string, array<string, array<int, int|null>>>
	 */
	private function sample_zone_matrix(): array {
		return array(
			'single' => array(
				'adult'          => array( 2 => 110 ),
				'child_4_15'     => array( 2 => 30 ),
				'child_0_3'      => array( 2 => 0 ),
				'student_senior' => array( 2 => 100 ),
			),
			'return' => array(
				'adult'          => array( 2 => 220 ),
				'child_4_15'     => array( 2 => 60 ),
				'child_0_3'      => array( 2 => 0 ),
				'student_senior' => array( 2 => 200 ),
			),
			'day'    => array(
				'adult'          => array( 2 => 280 ),
				'child_4_15'     => array( 2 => 80 ),
				'child_0_3'      => array( 2 => 0 ),
				'student_senior' => array( 2 => 260 ),
			),
		);
	}

	public function test_zones_for_station_pair_returns_cap_when_unknown(): void {
		self::assertSame( 3, MRT_zones_for_station_pair( 1, 2, array() ) );
	}

	public function test_zones_for_station_pair_computes_span(): void {
		$map = array(
			1 => array( 1 ),
			2 => array( 3 ),
		);
		self::assertSame( 3, MRT_zones_for_station_pair( 1, 2, $map ) );
	}

	public function test_zones_for_station_pair_caps_at_three(): void {
		$map = array(
			1 => array( 1 ),
			2 => array( 4 ),
		);
		self::assertSame( 3, MRT_zones_for_station_pair( 1, 2, $map ) );
	}

	public function test_zones_for_station_path_counts_distinct_zones(): void {
		$map = array(
			1 => array( 1 ),
			2 => array( 1, 2 ),
			3 => array( 2 ),
			4 => array( 4 ),
		);
		self::assertSame( 2, MRT_zones_for_station_path( array( 1, 3 ), $map ) );
		self::assertSame( 2, MRT_zones_for_station_path( array( 1, 4 ), $map ) );
		self::assertSame( 2, MRT_zones_for_station_path( array( 1, 2, 3 ), $map ) );
	}

	public function test_parse_trip_price_legs_param(): void {
		$json = wp_json_encode(
			array(
				array(
					'service_id'      => 5,
					'from_station_id' => 1,
					'to_station_id'   => 2,
				),
			)
		);
		$legs = MRT_parse_trip_price_legs_param( (string) $json );
		self::assertNotNull( $legs );
		self::assertSame( 5, $legs[0]['service_id'] );
		self::assertNull( MRT_parse_trip_price_legs_param( '' ) );
		self::assertNull( MRT_parse_trip_price_legs_param( 'not-json' ) );
	}

	public function test_pricing_zone_count_caps_at_three(): void {
		self::assertSame( 3, MRT_pricing_zone_count( 4 ) );
		self::assertSame( 2, MRT_pricing_zone_count( 2 ) );
	}

	public function test_parse_trip_clock_minutes(): void {
		self::assertSame( 900, MRT_parse_trip_clock_minutes( '15:00' ) );
		self::assertSame( 899, MRT_parse_trip_clock_minutes( '14:59' ) );
		self::assertNull( MRT_parse_trip_clock_minutes( 'bad' ) );
	}

	public function test_qualifies_for_afternoon_return(): void {
		self::assertTrue( MRT_qualifies_for_afternoon_return( 'return', '15:00', '16:00' ) );
		self::assertFalse( MRT_qualifies_for_afternoon_return( 'return', '14:59', '16:00' ) );
		self::assertFalse( MRT_qualifies_for_afternoon_return( 'single', '15:00', '16:00' ) );
	}

	public function test_price_matrix_has_any_price(): void {
		self::assertFalse( MRT_price_matrix_has_any_price( array() ) );
		self::assertTrue(
			MRT_price_matrix_has_any_price(
				array(
					'single' => array( 'adult' => 120 ),
				)
			)
		);
	}

	public function test_price_matrix_for_trip_returns_null_when_empty(): void {
		self::assertNull( MRT_price_matrix_for_trip( 'single', 4, '', '', array() ) );
	}

	public function test_price_matrix_for_trip_selects_return_type(): void {
		$result = MRT_price_matrix_for_trip( 'return', 2, '', '', $this->sample_zone_matrix() );
		self::assertNotNull( $result );
		self::assertSame( 'return', $result['activeType'] );
		self::assertSame( 220, $result['matrix']['return']['adult'] );
		self::assertSame( 60, $result['matrix']['return']['child_4_15'] );
	}

	public function test_price_matrix_for_trip_uses_afternoon_return_prices(): void {
		$result = MRT_price_matrix_for_trip( 'return', 2, '15:10', '16:00', $this->sample_zone_matrix() );
		self::assertNotNull( $result );
		self::assertTrue( $result['isAfternoonReturn'] );
		self::assertSame( 160, $result['matrix']['return']['adult'] );
		self::assertSame( 140, $result['matrix']['return']['student_senior'] );
	}

	public function test_price_matrix_for_trip_selects_single_type(): void {
		$result = MRT_price_matrix_for_trip( 'single', 2, '', '', $this->sample_zone_matrix() );
		self::assertNotNull( $result );
		self::assertSame( 'single', $result['activeType'] );
		self::assertFalse( $result['isAfternoonReturn'] );
		self::assertSame( 110, $result['matrix']['single']['adult'] );
	}

	public function test_afternoon_return_fails_when_inbound_before_threshold(): void {
		self::assertFalse( MRT_qualifies_for_afternoon_return( 'return', '15:00', '14:59' ) );
		$result = MRT_price_matrix_for_trip( 'return', 2, '15:00', '14:59', $this->sample_zone_matrix() );
		self::assertNotNull( $result );
		self::assertFalse( $result['isAfternoonReturn'] );
		self::assertSame( 220, $result['matrix']['return']['adult'] );
	}

	public function test_trip_prices_response_returns_single_trip(): void {
		$result = MRT_trip_prices_response( 1, 2, 'single' );
		self::assertSame( 3, $result['zones'] );
		self::assertNotNull( $result['trip'] );
		self::assertSame( 'single', $result['trip']['activeType'] );
		self::assertNull( $result['day'] );
	}

	public function test_trip_prices_response_includes_day_ticket(): void {
		$result = MRT_trip_prices_response( 1, 2, 'return', '15:00', '16:00', true );
		self::assertNotNull( $result['trip'] );
		self::assertTrue( $result['trip']['isAfternoonReturn'] );
		self::assertNotNull( $result['day'] );
		self::assertArrayHasKey( 'day', $result['day'] );
	}

	public function test_day_ticket_matrix_returns_day_row(): void {
		$day = MRT_day_ticket_matrix( 2, $this->sample_zone_matrix() );
		self::assertNotNull( $day );
		self::assertSame( 280, $day['day']['adult'] );
	}
}
