<?php
/**
 * Admin-post handler to serve sample CSV downloads
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

add_action('admin_post_mrt_download_csv', 'MRT_handle_download_csv');

/**
 * Handle CSV download requests
 */
function MRT_handle_download_csv() {
    if ( ! current_user_can('manage_options') ) {
        wp_die( esc_html__('You do not have permission to access this resource.', 'museum-railway-timetable'), 403 );
    }
    if ( empty($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'mrt_download_csv') ) {
        wp_die( esc_html__('Nonce verification failed.', 'museum-railway-timetable'), 400 );
    }

    $type = isset($_GET['type']) ? sanitize_key($_GET['type']) : '';
    switch ($type) {
        case 'stations':
            $filename = 'stations-sample.csv';
            $csv = MRT_sample_csv_stations();
            break;
        case 'stoptimes':
            $filename = 'stoptimes-sample.csv';
            $csv = MRT_sample_csv_stoptimes();
            break;
        case 'calendar':
            $filename = 'calendar-sample.csv';
            $csv = MRT_sample_csv_calendar();
            break;
        default:
            wp_die( esc_html__('Unknown CSV type.', 'museum-railway-timetable'), 400 );
    }

    // Send as download with sane headers
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    echo $csv;
    exit;
}

