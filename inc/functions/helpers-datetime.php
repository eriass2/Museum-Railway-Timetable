<?php
/**
 * Datetime helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Get current datetime information
 *
 * @return array Array with 'timestamp', 'date' (Y-m-d), and 'time' (H:i)
 */
function MRT_get_current_datetime() {
    $timestamp = current_time('timestamp');
    return [
        'timestamp' => $timestamp,
        'date' => date('Y-m-d', $timestamp),
        'time' => date('H:i', $timestamp),
    ];
}

/**
 * Validate time format (HH:MM)
 *
 * @param string $s Time string
 * @return bool True if valid or empty
 */
function MRT_validate_time_hhmm($s) {
    // Accept empty for first/last stop cases
    if ($s === '' || $s === null) return true;
    return (bool) preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $s);
}

/**
 * Validate date format (YYYY-MM-DD)
 *
 * @param string $s Date string
 * @return bool True if valid
 */
function MRT_validate_date($s) {
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $s);
}
