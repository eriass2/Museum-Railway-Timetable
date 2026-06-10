<?php
/**
 * Service highlight meta (CSV-driven departure marking).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/service/highlight.php';

final class ServiceHighlightTest extends TestCase {

	public function test_sanitize_highlight_color_accepts_hex(): void {
		self::assertSame( '#fff9c4', MRT_sanitize_highlight_color( '#FFF9C4' ));
		self::assertSame( '', MRT_sanitize_highlight_color( 'yellow' ));
	}

	public function test_csv_update_clears_meta_when_label_empty(): void {
		$service_id = 501;
		update_post_meta( $service_id, 'mrt_service_highlight_label', 'Old' );
		MRT_csv_update_service_highlight_from_row(
			$service_id,
			array( 'highlight_label' => '' )
		);
		self::assertSame( '', get_post_meta( $service_id, 'mrt_service_highlight_label', true ) );
	}

	public function test_csv_update_stores_label_color_and_note(): void {
		$service_id = 502;
		MRT_csv_update_service_highlight_from_row(
			$service_id,
			array(
				'highlight_label' => "Thun's-expressen",
				'highlight_color' => '#fff9c4',
				'highlight_note'  => 'Till klädvaruhuset.',
			)
		);
		$highlight = MRT_get_service_highlight( $service_id );
		self::assertIsArray( $highlight );
		self::assertSame( "Thun's-expressen", $highlight['label'] );
		self::assertSame( '#fff9c4', $highlight['color'] );
		self::assertSame( 'Till klädvaruhuset.', $highlight['note'] );
	}
}
