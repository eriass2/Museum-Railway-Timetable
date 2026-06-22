<?php
/**
 * Dompdf rendering for trip summary PDF downloads.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slug for PDF download filename (ASCII-safe).
 */
function MRT_trip_summary_pdf_filename( string $trip_name ): string {
	$slug = function_exists( 'mb_strtolower' )
		? mb_strtolower( trim( $trip_name ), 'UTF-8' )
		: strtolower( trim( $trip_name ) );
	$slug = strtr(
		$slug,
		array(
			'å' => 'a',
			'ä' => 'a',
			'ö' => 'o',
			'Å' => 'a',
			'Ä' => 'a',
			'Ö' => 'o',
		)
	);
	$slug = (string) preg_replace( '/[^a-z0-9]+/', '-', $slug );
	$slug = trim( $slug, '-' );

	return ( $slug !== '' ? $slug : 'resa' ) . '.pdf';
}

/**
 * @return string|WP_Error Raw PDF bytes.
 */
function MRT_trip_summary_render_pdf( string $html ) {
	if ( ! class_exists( Dompdf::class ) ) {
		return new WP_Error(
			'mrt_trip_summary_pdf_unavailable',
			__( 'PDF library is not installed.', 'museum-railway-timetable' ),
			array( 'status' => 500 )
		);
	}

	$options = new Options();
	$options->set( 'isRemoteEnabled', false );
	$options->set( 'defaultFont', 'DejaVu Sans' );

	$dompdf = new Dompdf( $options );
	$dompdf->loadHtml( $html, 'UTF-8' );
	$dompdf->setPaper( 'A4', 'portrait' );
	$dompdf->render();

	$output = $dompdf->output();
	if ( ! is_string( $output ) || $output === '' ) {
		return new WP_Error(
			'mrt_trip_summary_pdf_failed',
			__( 'Could not generate PDF.', 'museum-railway-timetable' ),
			array( 'status' => 500 )
		);
	}

	return $output;
}

/**
 * @param array<string, mixed> $input Parsed trip summary input.
 * @return array{filename: string, content_base64: string}|WP_Error
 */
function MRT_trip_summary_pdf_download_payload( array $input ) {
	$html = MRT_trip_summary_build_pdf_document( MRT_trip_summary_build_html( $input ) );
	$pdf  = MRT_trip_summary_render_pdf( $html );
	if ( is_wp_error( $pdf ) ) {
		return $pdf;
	}

	return array(
		'filename'       => MRT_trip_summary_pdf_filename( (string) ( $input['downloadName'] ?? 'resa' ) ),
		'content_base64' => base64_encode( $pdf ),
	);
}
