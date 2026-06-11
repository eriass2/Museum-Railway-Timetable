<?php
/**
 * Ticket copy footnotes and station purchase info.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/infrastructure/wordpress/plugin-settings.php';
require_once ABSPATH . 'inc/domain/pricing/ticket-copy.php';

final class TicketCopyTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_options'], $GLOBALS['mrt_test_post_meta'] );
	}

	public function test_sanitize_ticket_copy_notes_keeps_enabled_entries(): void {
		$notes = MRT_sanitize_ticket_copy_notes(
			array(
				array(
					'id'        => 'student',
					'condition' => 'always',
					'text'      => 'Student-ID krävs.',
					'enabled'   => true,
				),
				array(
					'id'        => 'empty',
					'condition' => 'always',
					'text'      => '   ',
					'enabled'   => true,
				),
			)
		);

		self::assertCount( 1, $notes );
		self::assertSame( 'student', $notes[0]['id'] );
		self::assertTrue( $notes[0]['enabled'] );
	}

	public function test_resolve_footnotes_filters_by_context(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'ticket_copy_notes' => array(
					array(
						'id'        => 'always',
						'condition' => 'always',
						'text'      => 'Alltid synlig.',
						'enabled'   => true,
					),
					array(
						'id'        => 'afternoon',
						'condition' => 'afternoon',
						'text'      => 'Efter kl %1$s.',
						'enabled'   => true,
					),
					array(
						'id'        => 'day',
						'condition' => 'day_ticket',
						'text'      => 'Heldagsinfo.',
						'enabled'   => true,
					),
					array(
						'id'        => 'off',
						'condition' => 'always',
						'text'      => 'Avstängd.',
						'enabled'   => false,
					),
				),
			),
		);

		$afternoon = MRT_resolve_ticket_copy_footnotes(
			array(
				'is_afternoon'    => true,
				'has_day_ticket'  => false,
				'afternoon_clock' => '15:00',
			)
		);
		self::assertSame( array( 'Alltid synlig.', 'Efter kl 15:00.' ), $afternoon );

		$day = MRT_resolve_ticket_copy_footnotes(
			array(
				'is_afternoon'   => false,
				'has_day_ticket' => true,
			)
		);
		self::assertSame( array( 'Alltid synlig.', 'Heldagsinfo.' ), $day );
	}

	public function test_station_purchase_uses_custom_meta_over_default(): void {
		$station_id = 42;
		$GLOBALS['mrt_test_post_meta'] = array(
			$station_id . '|mrt_station_code'         => 'uppsala-ostra',
			$station_id . '|mrt_ticket_purchase_info' => 'Egen köptext.',
		);

		self::assertSame( 'Egen köptext.', MRT_get_station_ticket_purchase_info( $station_id ) );
	}

	public function test_station_purchase_falls_back_to_code_default(): void {
		$station_id = 43;
		$GLOBALS['mrt_test_post_meta'] = array(
			$station_id . '|mrt_station_code' => 'marielund',
		);

		$text = MRT_get_station_ticket_purchase_info( $station_id );
		self::assertStringContainsString( 'Marielund', $text );
	}
}
