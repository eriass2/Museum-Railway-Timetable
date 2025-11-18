<?php
/**
 * Service-related functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Resolve which services run on a given date (interval + weekday + include/exclude overrides)
 *
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @param string $train_type_slug Optional train type taxonomy slug
 * @param string $service_title_exact Optional exact service title
 * @return array Array of service post IDs
 */
function MRT_services_running_on_date($dateYmd, $train_type_slug = '', $service_title_exact = '') {
    global $wpdb;
    $calendar = $wpdb->prefix . 'mrt_calendar';

    $weekday = strtolower(date('D', strtotime($dateYmd))); // mon..sun
    $map = ['mon'=>'mon','tue'=>'tue','wed'=>'wed','thu'=>'thu','fri'=>'fri','sat'=>'sat','sun'=>'sun'];
    $col = $map[$weekday] ?? '';
    
    // Whitelist column names to prevent SQL injection
    $allowed_cols = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    $col = in_array($col, $allowed_cols) ? $col : 'mon';

    $sql = $wpdb->prepare("SELECT service_post_id, include_dates, exclude_dates, `$col` AS dow
        FROM $calendar
        WHERE %s BETWEEN start_date AND end_date", $dateYmd);
    $rows = $wpdb->get_results($sql, ARRAY_A);

    $ids = [];
    foreach ($rows as $r) {
        $include = array_filter(array_map('trim', explode(',', (string)$r['include_dates'])));
        $exclude = array_filter(array_map('trim', explode(',', (string)$r['exclude_dates'])));
        $run = false;

        if (in_array($dateYmd, $exclude, true)) {
            $run = false;
        } elseif (in_array($dateYmd, $include, true)) {
            $run = true;
        } elseif (intval($r['dow']) === 1) {
            $run = true;
        }

        if ($run) $ids[] = intval($r['service_post_id']);
    }

    $ids = array_values(array_unique($ids));
    if (!$ids) return [];

    // Filter by specific service title if provided
    if ($service_title_exact !== '') {
        $post = get_page_by_title($service_title_exact, OBJECT, 'mrt_service');
        if (!$post) return [];
        $ids = array_values(array_intersect($ids, [intval($post->ID)]));
        if (!$ids) return [];
    }

    // Filter by train type taxonomy
    if ($train_type_slug) {
        $q = new WP_Query([
            'post_type' => 'mrt_service',
            'post__in'  => $ids,
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
    return $ids;
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
    if (!$rows) return [];

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

