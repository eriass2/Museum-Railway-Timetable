<?php
/**
 * Train type REST serializers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}

require_once ABSPATH . 'inc/domain/train-type/icons.php';
require_once ABSPATH . 'inc/domain/train-type/rest-format.php';

final class TrainTypeRestFormatTest extends TestCase
{
	protected function tearDown(): void
	{
		unset( $GLOBALS['mrt_test_terms'], $GLOBALS['mrt_test_term_meta'] );
		parent::tearDown();
	}

	public function test_format_train_type_includes_resolved_icon(): void
	{
		$term = new WP_Term();
		$term->term_id = 7;
		$term->name    = 'Ångtåg';
		$term->slug    = 'angtag';

		$row = MRT_rest_format_train_type( $term );

		self::assertSame( 7, $row['id'] );
		self::assertSame( 'Ångtåg', $row['name'] );
		self::assertSame( 'angtag', $row['slug'] );
		self::assertSame( 'steam', $row['icon_key'] );
	}

	public function test_format_train_type_uses_stored_icon_key(): void
	{
		$term = new WP_Term();
		$term->term_id = 8;
		$term->name    = 'Special';
		$term->slug    = 'special';
		$GLOBALS['mrt_test_term_meta'] = array(
			'8|mrt_icon_key' => 'railbus',
		);

		$row = MRT_rest_format_train_type( $term );

		self::assertSame( 'railbus', $row['icon_key'] );
	}

	public function test_create_train_type_requires_name(): void
	{
		$result = MRT_rest_create_train_type( array() );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid_name', $result->get_error_code() );
	}

	public function test_update_train_type_not_found(): void
	{
		$result = MRT_rest_update_train_type( 99, array( 'name' => 'Ny' ) );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'not_found', $result->get_error_code() );
	}

	public function test_apply_icon_key_stores_valid_key(): void
	{
		MRT_rest_apply_train_type_icon_key( 3, 'diesel' );

		self::assertSame( 'diesel', get_term_meta( 3, 'mrt_icon_key', true ) );
	}

	public function test_apply_icon_key_clears_invalid_key(): void
	{
		update_term_meta( 3, 'mrt_icon_key', 'diesel' );

		MRT_rest_apply_train_type_icon_key( 3, 'not-a-real-icon' );

		self::assertSame( '', get_term_meta( 3, 'mrt_icon_key', true ) );
	}
}
