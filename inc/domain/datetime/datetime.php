<?php
/**
 * Datetime helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Get current datetime information
 *
 * @return array Array with 'timestamp', 'date' (Y-m-d), and 'time' (H:i)
 */
function MRT_get_current_datetime() {
	$timestamp = current_time( 'timestamp' );
	return array(
		'timestamp' => $timestamp,
		'date'      => date( 'Y-m-d', $timestamp ),
		'time'      => date( 'H:i', $timestamp ),
	);
}

/**
 * Validate a service-day time (HH:MM). Hours may pass 24, so 25:10 stays 25:10.
 * The value must fit CHAR(5): two hour digits and minutes 00–59.
 *
 * @param string $s Time string
 * @return bool True if valid or empty
 */
function MRT_validate_time_hhmm( $s ) {
	// Accept empty for first/last stop cases
	if ( $s === '' || $s === null ) {
		return true;
	}
	return (bool) preg_match( '/^\d{2}:[0-5]\d$/', $s );
}

/**
 * Validate date format (YYYY-MM-DD)
 *
 * @param string $s Date string
 * @return bool True if valid
 */
function MRT_validate_date( $s ) {
	return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $s );
}

/**
 * Parse HH:MM to minutes from the service-day start. 25:10 is 1510.
 *
 * @param string $hhmm Time string
 * @return int|null Minutes or null if invalid
 */
function MRT_time_hhmm_to_minutes( $hhmm ) {
	if ( $hhmm === '' || $hhmm === null || ! MRT_validate_time_hhmm( $hhmm ) ) {
		return null;
	}
	[$h, $m] = array_map( 'intval', explode( ':', $hhmm, 2 ) );
	return $h * 60 + $m;
}

/**
 * Compare two service-day times. 25:10 is later than 23:50.
 *
 * @param string $a First time
 * @param string $b Second time
 * @return int -1 if a<b, 0 if equal, 1 if a>b; 0 if either invalid
 */
function MRT_compare_hhmm( $a, $b ) {
	$ma = MRT_time_hhmm_to_minutes( $a );
	$mb = MRT_time_hhmm_to_minutes( $b );
	if ( $ma === null || $mb === null ) {
		return 0;
	}
	return $ma <=> $mb;
}

/**
 * Add minutes to a service-day time. Result stays within 00:00–99:59.
 *
 * @param string $hhmm Base time
 * @param int    $add  Minutes to add
 * @return string|null Result HH:MM or null
 */
function MRT_add_minutes_to_hhmm( $hhmm, $add ) {
	$base = MRT_time_hhmm_to_minutes( $hhmm );
	if ( $base === null ) {
		return null;
	}
	$total = min( 99 * 60 + 59, max( 0, $base + (int) $add ) );
	$h     = intdiv( $total, 60 );
	$m     = $total % 60;
	return sprintf( '%02d:%02d', $h, $m );
}

/**
 * Duration in whole minutes between two service-day times. Null when arrival is earlier.
 *
 * @param string $dep Departure HH:MM
 * @param string $arr Arrival HH:MM
 * @return int|null Null if not computable
 */
function MRT_format_duration_minutes( $dep, $arr ) {
	$d = MRT_time_hhmm_to_minutes( $dep );
	$a = MRT_time_hhmm_to_minutes( $arr );
	if ( $d === null || $a === null ) {
		return null;
	}
	$diff = $a - $d;
	return $diff >= 0 ? $diff : null;
}

/**
 * Convert time format from HH:MM to HH.MM for public display.
 *
 * @param string|null $time Time in HH:MM format or null.
 * @return string Time in HH.MM format or empty string.
 */
function MRT_format_time_display( ?string $time ): string {
	if ( empty( $time ) ) {
		return '';
	}
	return str_replace( ':', '.', $time );
}
