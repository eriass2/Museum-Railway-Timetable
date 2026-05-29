<?php
/**
 * Per-service public notice (post meta mrt_service_notice, mrt_service_notices_by_date)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Date-specific notices keyed by YYYY-MM-DD.
 *
 * @param int $service_id Service post ID
 * @return array<string, string>
 */
function MRT_get_service_notices_by_date( $service_id ): array {
	if ( $service_id <= 0 ) {
		return array();
	}
	$raw = get_post_meta( $service_id, 'mrt_service_notices_by_date', true );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $date => $text ) {
		if ( ! is_string( $date ) || ! MRT_validate_date( $date ) || ! is_string( $text ) ) {
			continue;
		}
		$text = trim( $text );
		if ( $text !== '' ) {
			$out[ $date ] = $text;
		}
	}
	return $out;
}

/**
 * Notice text for one calendar day (date override only, not global fallback).
 *
 * @param int    $service_id Service post ID
 * @param string $dateYmd    Date YYYY-MM-DD
 */
function MRT_get_service_notice_for_date( $service_id, $dateYmd ): string {
	if ( $service_id <= 0 || ! MRT_validate_date( $dateYmd ) ) {
		return '';
	}
	$by_date = MRT_get_service_notices_by_date( $service_id );
	return $by_date[ $dateYmd ] ?? '';
}

/**
 * Notice text for a service (date-specific first, then global default).
 *
 * @param int         $service_id Service post ID
 * @param string|null $dateYmd    Date YYYY-MM-DD
 * @return string Plain text, may be empty
 */
function MRT_get_service_notice( $service_id, $dateYmd = null ) {
	if ( $service_id <= 0 ) {
		return '';
	}
	if ( $dateYmd !== null && MRT_validate_date( $dateYmd ) ) {
		$dated = MRT_get_service_notice_for_date( $service_id, $dateYmd );
		if ( $dated !== '' ) {
			return $dated;
		}
	}
	$raw = get_post_meta( $service_id, 'mrt_service_notice', true );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return '';
	}
	return trim( $raw );
}
