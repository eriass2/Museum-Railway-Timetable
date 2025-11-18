<?php
/**
 * CSV parsing and validation functions
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Minimal CSV parser for pasted text areas; expects header row and comma delimiter
 *
 * @param string $csv CSV content
 * @return array Array of parsed rows, empty array on error
 */
function MRT_parse_csv($csv) {
    if (empty($csv) || !is_string($csv)) {
        return [];
    }
    
    $lines = preg_split('/\R/u', trim($csv));
    if (!$lines || count($lines) < 2) {
        return [];
    }
    
    $headers = str_getcsv(array_shift($lines));
    if (empty($headers)) {
        return [];
    }
    
    $rows = [];
    foreach ($lines as $line_num => $line) {
        if ('' === trim($line)) {
            continue;
        }
        
        $vals = str_getcsv($line);
        if (false === $vals) {
            MRT_log_error('Failed to parse CSV line ' . ($line_num + 2) . ': ' . $line);
            continue;
        }
        
        $row = [];
        foreach ($headers as $i => $h) {
            $key = sanitize_key($h);
            $row[$key] = $vals[$i] ?? '';
        }
        $rows[] = $row;
    }
    
    return $rows;
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

