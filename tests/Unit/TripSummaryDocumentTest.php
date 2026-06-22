<?php
/**
 * Trip summary PDF HTML builder.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TripSummaryDocumentTest extends TestCase {
	private const SAMPLE_INPUT = array(
		'title'         => 'Din resa',
		'downloadName'  => 'Uppsala → Fjällnora',
		'tripTypeLabel' => 'Enkel resa',
		'legs'          => array(
			array(
				'heading'   => 'Utresa',
				'route'     => 'Uppsala → Fjällnora',
				'timeRange' => '11:10 – 11:57',
				'date'      => 'onsdag 1 juli 2026',
				'segments'  => array(),
			),
		),
	);

	public function test_build_html_includes_title_and_route(): void {
		$html = MRT_trip_summary_build_html( self::SAMPLE_INPUT );

		self::assertStringContainsString( '<h1>Din resa</h1>', $html );
		self::assertStringContainsString( 'Uppsala → Fjällnora', $html );
		self::assertStringContainsString( 'Enkel resa', $html );
	}

	public function test_build_pdf_document_wraps_body(): void {
		$body = MRT_trip_summary_build_html( self::SAMPLE_INPUT );
		$doc  = MRT_trip_summary_build_pdf_document( $body );

		self::assertStringContainsString( '<!DOCTYPE html>', $doc );
		self::assertStringContainsString( 'data-mrt-pdf-root', $doc );
		self::assertStringContainsString( '<h1>Din resa</h1>', $doc );
	}

	public function test_price_table_uses_label_and_value_cells(): void {
		$input                     = self::SAMPLE_INPUT;
		$input['priceSection']     = array(
			'heading'         => 'Priser',
			'ticketTypeLabel' => 'Enkelbiljett',
			'rows'            => array(
				array(
					'label' => 'Vuxen',
					'value' => '80 kr',
				),
			),
		);
		$html = MRT_trip_summary_build_html( $input );

		self::assertStringContainsString( '<table class="price-table">', $html );
		self::assertStringContainsString( '<td class="price-label">Vuxen</td>', $html );
		self::assertStringContainsString( '<td class="price-value">80 kr</td>', $html );
	}

	public function test_round_trip_legs_use_side_by_side_table(): void {
		$input                 = self::SAMPLE_INPUT;
		$input['tripTypeLabel'] = 'Tur och retur';
		$input['legs']          = array(
			array(
				'heading'   => 'Utresa',
				'route'     => 'Uppsala Östra → Gunsta',
				'timeRange' => '10.00 – 10.24',
				'date'      => '13 juni 2026',
				'segments'  => array(),
			),
			array(
				'heading'   => 'Återresa',
				'route'     => 'Gunsta → Uppsala Östra',
				'timeRange' => '11.50 – 12.17',
				'date'      => '13 juni 2026',
				'segments'  => array(),
			),
		);
		$html = MRT_trip_summary_build_html( $input );

		self::assertStringContainsString( '<table class="legs-table">', $html );
		self::assertStringContainsString( 'Utresa', $html );
		self::assertStringContainsString( 'Återresa', $html );
	}

	public function test_pdf_filename_slugifies_swedish_route(): void {
		self::assertSame(
			'uppsala-ostra-fjallnora.pdf',
			MRT_trip_summary_pdf_filename( 'Uppsala Östra → Fjällnora' )
		);
	}
}
