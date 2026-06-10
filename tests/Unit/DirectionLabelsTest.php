<?php
/**
 * Service direction and timetable row labels.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/station/stations.php';
require_once ABSPATH . 'inc/domain/route/direction-labels.php';

final class DirectionLabelsTest extends TestCase {

	public function test_service_direction_label(): void {
		self::assertSame( 'Dit', MRT_service_direction_label( 'dit' ) );
		self::assertSame( 'Från', MRT_service_direction_label( 'från' ) );
		self::assertSame( '', MRT_service_direction_label( 'north' ) );
	}

	public function test_service_direction_title_suffix(): void {
		self::assertSame( ' - Dit', MRT_service_direction_title_suffix( 'dit' ) );
		self::assertSame( '', MRT_service_direction_title_suffix( '' ) );
	}

	public function test_station_from_and_to_labels(): void {
		$station = new WP_Post( (object) array( 'ID' => 1, 'post_title' => 'Skogen' ) );

		self::assertSame( 'Från Skogen', MRT_station_from_label( $station ) );
		self::assertSame( 'Till Skogen', MRT_station_to_label( $station ) );
	}

	public function test_route_from_to_label(): void {
		self::assertSame(
			'Från A Till B',
			MRT_route_from_to_label( 'A', 'B' )
		);
	}
}
