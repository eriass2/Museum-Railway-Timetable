<?php
/**
 * Trip summary PDF REST handler.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

require_once ABSPATH . 'inc/infrastructure/rest/shared/permissions.php';
require_once ABSPATH . 'inc/infrastructure/rest/public/journey-trip-summary-pdf.php';

final class RestTripSummaryPdfTest extends TestCase {
	public function test_handler_rejects_missing_title(): void {
		$request = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/journey/trip-summary/pdf' );
		$request->set_json_params(
			array(
				'legs' => array(
					array(
						'heading'   => 'Utresa',
						'route'     => 'A → B',
						'timeRange' => '10:00 – 11:00',
						'date'      => '1 juli 2026',
					),
				),
			)
		);

		$result = MRT_rest_journey_trip_summary_pdf_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_trip_summary_invalid', $result->get_error_code() );
	}

	public function test_handler_returns_pdf_payload(): void {
		if ( ! class_exists( \Dompdf\Dompdf::class ) ) {
			self::markTestSkipped( 'Dompdf not installed' );
		}

		$request = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/journey/trip-summary/pdf' );
		$request->set_json_params(
			array(
				'title'         => 'Din resa',
				'downloadName'  => 'Uppsala → Fjällnora',
				'tripTypeLabel' => 'Enkel resa',
				'legs'          => array(
					array(
						'heading'   => 'Utresa',
						'route'     => 'Uppsala → Fjällnora',
						'timeRange' => '11:10 – 11:57',
						'date'      => 'onsdag 1 juli 2026',
					),
				),
				'priceSection'  => array(
					'heading'         => 'Priser',
					'ticketTypeLabel' => 'Enkelbiljett',
					'rows'            => array(
						array(
							'label' => 'Vuxen',
							'value' => '80 kr',
						),
					),
				),
			)
		);

		$response = MRT_rest_journey_trip_summary_pdf_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;
		self::assertIsArray( $data );
		self::assertArrayHasKey( 'filename', $data );
		self::assertStringEndsWith( '.pdf', (string) $data['filename'] );
		self::assertNotEmpty( $data['content_base64'] ?? '' );

		$raw = base64_decode( (string) $data['content_base64'], true );
		self::assertIsString( $raw );
		self::assertStringStartsWith( '%PDF', $raw );
	}
}
