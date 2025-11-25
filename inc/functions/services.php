<?php
/**
 * Service-related functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Resolve which services run on a given date (using Timetables)
 *
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @param string $train_type_slug Optional train type taxonomy slug
 * @param string $service_title_exact Optional exact service title
 * @return array Array of service post IDs
 */
function MRT_services_running_on_date($dateYmd, $train_type_slug = '', $service_title_exact = '') {
    // Validate date format
    if (!MRT_validate_date($dateYmd)) {
        return [];
    }
    
    // Find all timetables that include this date
    $timetables = get_posts([
        'post_type' => 'mrt_timetable',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => 'mrt_timetable_dates',
            'value' => $dateYmd,
            'compare' => 'LIKE', // WordPress stores arrays as serialized strings
        ]],
    ]);
    
    if (empty($timetables)) {
        return [];
    }
    
    // Find all services that belong to these timetables
    $service_ids = get_posts([
        'post_type' => 'mrt_service',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => 'mrt_service_timetable_id',
            'value' => $timetables,
            'compare' => 'IN',
        ]],
    ]);
    
    // Additional check: verify that the date is actually in the timetable's dates array
    // (WordPress LIKE comparison might match partial strings)
    $valid_service_ids = [];
    foreach ($service_ids as $service_id) {
        $timetable_id = get_post_meta($service_id, 'mrt_service_timetable_id', true);
        if (!$timetable_id || !in_array($timetable_id, $timetables)) {
            continue;
        }
        
        $timetable_dates = get_post_meta($timetable_id, 'mrt_timetable_dates', true);
        if (!is_array($timetable_dates)) {
            // Try to migrate from old single date field
            $old_date = get_post_meta($timetable_id, 'mrt_timetable_date', true);
            $timetable_dates = !empty($old_date) ? [$old_date] : [];
        }
        
        if (in_array($dateYmd, $timetable_dates, true)) {
            $valid_service_ids[] = $service_id;
        }
    }
    
    if (empty($valid_service_ids)) {
        return [];
    }
    
    // Filter by specific service title if provided
    if ($service_title_exact !== '') {
        $post = MRT_get_post_by_title($service_title_exact, 'mrt_service');
        if (!$post) return [];
        $valid_service_ids = array_values(array_intersect($valid_service_ids, [intval($post->ID)]));
        if (empty($valid_service_ids)) return [];
    }

    // Filter by train type taxonomy
    if ($train_type_slug) {
        $q = new WP_Query([
            'post_type' => 'mrt_service',
            'post__in'  => $valid_service_ids,
            'fields'    => 'ids',
            'nopaging'  => true,
            'tax_query' => [[
                'taxonomy' => 'mrt_train_type',
                'field' => 'slug',
                'terms' => sanitize_title($train_type_slug),
            ]]
        ]);
        return $q->posts;
    }
    
    return array_values(array_unique($valid_service_ids));
}

/**
 * Get next departures from a station after a given time
 *
 * @param int    $station_id Station post ID
 * @param array  $service_ids Array of service post IDs
 * @param string $timeHHMM Time in HH:MM format
 * @param int    $limit Maximum number of departures to return
 * @param bool   $with_arrival Whether to include arrival times
 * @return array Array of departure data
 */
function MRT_next_departures_for_station($station_id, $service_ids, $timeHHMM, $limit = 5, $with_arrival = false) {
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    if (!$service_ids) return [];

    $in = implode(',', array_map('intval', $service_ids));
    $col_time = $with_arrival ? "COALESCE(departure_time, arrival_time)" : "departure_time";

    // Validate inputs
    if ($station_id <= 0 || $limit <= 0) {
        return [];
    }
    
    // Validate time format (HH:MM) - empty not allowed here
    if (empty($timeHHMM) || !MRT_validate_time_hhmm($timeHHMM)) {
        return [];
    }
    
    $sql = $wpdb->prepare("
        SELECT s.service_post_id, s.arrival_time, s.departure_time, s.stop_sequence
        FROM $table s
        WHERE s.station_post_id = %d
          AND s.service_post_id IN ($in)
          AND (
              (s.departure_time IS NOT NULL AND s.departure_time >= %s)
              OR (s.departure_time IS NULL AND s.arrival_time IS NOT NULL AND s.arrival_time >= %s)
          )
        ORDER BY $col_time ASC
        LIMIT %d
    ", $station_id, $timeHHMM, $timeHHMM, $limit);

    $rows = $wpdb->get_results($sql, ARRAY_A);
    
    // Check for database errors
    if (MRT_check_db_error('MRT_next_departures_for_station')) {
        return [];
    }
    
    if (!$rows) {
        return [];
    }

    $out = [];
    foreach ($rows as $r) {
        $service_name = get_the_title($r['service_post_id']);
        $direction = get_post_meta($r['service_post_id'], 'mrt_direction', true);
        $out[] = [
            'service_id' => intval($r['service_post_id']),
            'service_name' => $service_name ?: ('#'.$r['service_post_id']),
            'arrival_time' => $r['arrival_time'],
            'departure_time' => $r['departure_time'],
            'direction' => $direction !== '' ? $direction : '',
        ];
    }
    return $out;
}

