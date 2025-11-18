<?php
/**
 * Sample CSV generators
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Generate sample stations CSV
 *
 * @return string CSV content
 */
function MRT_sample_csv_stations() {
    $rows = [
        'name,station_type,lat,lng,display_order',
        'Hultsfred Museum,station,57.486,15.842,1',
        'Skoghult Halt,halt,57.501,15.900,2',
        'Depån,depot,57.480,15.830,99',
    ];
    return implode("\n", $rows) . "\n";
}

/**
 * Generate sample stop times CSV
 *
 * @return string CSV content
 */
function MRT_sample_csv_stoptimes() {
    $rows = [
        'service,station,sequence,arrive,depart,pickup,dropoff',
        'Steam Train A,Hultsfred Museum,1,,10:00,1,1',
        'Steam Train A,Skoghult Halt,2,10:25,10:27,1,1',
        'Steam Train A,Depån,3,10:45,,0,1',
    ];
    return implode("\n", $rows) . "\n";
}

/**
 * Generate sample calendar CSV
 *
 * @return string CSV content
 */
function MRT_sample_csv_calendar() {
    $rows = [
        'service,start_date,end_date,mon,tue,wed,thu,fri,sat,sun,include_dates,exclude_dates',
        'Steam Train A,2025-06-01,2025-08-31,0,0,0,0,0,1,1,2025-06-06,',
        'Steam Train B,2025-07-01,2025-07-31,0,0,0,0,1,1,0,,2025-07-20',
    ];
    return implode("\n", $rows) . "\n";
}

