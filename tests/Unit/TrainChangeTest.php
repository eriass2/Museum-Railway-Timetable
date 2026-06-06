<?php
/**
 * Train-change map helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/journey/train-change.php';

final class TrainChangeTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_sanitize_station_train_change_map_skips_invalid_rows(): void {
		$map = MRT_sanitize_station_train_change_map(
			array(
				'71' => array(
					'typeName'      => 'Dieseltåg',
					'serviceNumber' => '61',
				),
				''   => array(
					'typeName'      => 'X',
					'serviceNumber' => '1',
				),
				'60' => 'not-an-array',
				'96' => array(
					'typeName'      => '',
					'serviceNumber' => '64',
				),
			)
		);

		self::assertSame(
			array(
				'71' => array(
					'typeName'      => 'Dieseltåg',
					'serviceNumber' => '61',
				),
			),
			$map
		);
	}

	public function test_train_change_map_for_station_title_returns_marielund_defaults(): void {
		$map = MRT_train_change_map_for_station_title( 'Marielund' );

		self::assertArrayHasKey( '71', $map );
		self::assertSame( 'Dieseltåg', $map['71']['typeName'] );
		self::assertSame( '61', $map['71']['serviceNumber'] );
	}

	public function test_get_station_train_change_map_uses_stored_meta_when_present(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'12|' . MRT_station_train_change_map_meta_key() => array(
				'50' => array(
					'typeName'      => 'Ångtåg',
					'serviceNumber' => '51',
				),
			),
		);

		$map = MRT_get_station_train_change_map( 12 );

		self::assertSame(
			array(
				'50' => array(
					'typeName'      => 'Ångtåg',
					'serviceNumber' => '51',
				),
			),
			$map
		);
	}
}
