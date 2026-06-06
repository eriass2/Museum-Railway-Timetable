<?php
/**
 * Configurable price matrix schema.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/pricing/price-schema.php';

final class PriceSchemaTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_options'] );
	}

	public function test_default_schema_matches_builtin_keys(): void {
		$schema = MRT_get_default_price_schema();

		self::assertSame( array( 'single', 'return', 'day' ), array_column( $schema['ticket_types'], 'key' ) );
		self::assertSame( array( 1, 2, 3, 4 ), $schema['zones'] );
	}

	public function test_sanitize_price_schema_from_admin_maps(): void {
		$schema = MRT_sanitize_price_schema_from_admin_maps(
			array(
				'weekend' => 'Helgbiljett',
			),
			array(
				'adult' => 'Vuxen',
			),
			array( 1, 2 )
		);

		self::assertSame( 'weekend', $schema['ticket_types'][0]['key'] );
		self::assertSame( array( 1, 2 ), $schema['zones'] );
	}

	public function test_stored_schema_overrides_defaults(): void {
		$GLOBALS['mrt_test_options']['mrt_price_schema'] = array(
			'ticket_types' => array(
				array(
					'key'   => 'family',
					'label' => 'Familj',
				),
			),
			'categories'   => array(
				array(
					'key'   => 'adult',
					'label' => 'Vuxen',
				),
			),
			'zones'        => array( 1, 3 ),
		);

		self::assertSame( array( 'family' ), MRT_price_schema_ticket_keys() );
		self::assertSame( array( 1, 3 ), MRT_price_schema_zone_keys() );
	}
}
