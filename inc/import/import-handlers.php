<?php
/**
 * Import handlers for stations, stop times, and calendar
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Get station ID by name
 *
 * @param string $name Station name
 * @return int Station post ID or 0
 */
function MRT_get_station_id_by_name($name) {
    $post = MRT_get_post_by_title($name, 'mrt_station');
    return $post ? intval($post->ID) : 0;
}

/**
 * Get service ID by name, creating if it doesn't exist
 *
 * @param string $name Service name
 * @return int Service post ID or 0 on failure
 */
function MRT_get_service_id_by_name($name) {
    if (empty($name)) {
        return 0;
    }
    
    $post = MRT_get_post_by_title($name, 'mrt_service');
    if ($post) {
        return intval($post->ID);
    }
    
    // Auto-create service if not found
    $id = wp_insert_post([
        'post_type' => 'mrt_service',
        'post_title' => sanitize_text_field($name),
        'post_status' => 'publish'
    ]);
    
    if (is_wp_error($id)) {
        MRT_log_error('Failed to create service: ' . $id->get_error_message());
        return 0;
    }
    
    return intval($id);
}

/**
 * Import stations from CSV rows
 * Headers: name,station_type,lat,lng,display_order
 *
 * @param array $rows Parsed CSV rows
 * @return string Success message
 */
function MRT_import_stations($rows) {
    $created = 0; $updated = 0;
    foreach ($rows as $r) {
        $name = sanitize_text_field($r['name'] ?? '');
        if (!$name) continue;
        $type = sanitize_text_field($r['station_type'] ?? '');
        $lat  = isset($r['lat']) ? floatval($r['lat']) : null;
        $lng  = isset($r['lng']) ? floatval($r['lng']) : null;
        $order = isset($r['display_order']) ? intval($r['display_order']) : 0;

        // Find existing station by title
        $existing = MRT_get_post_by_title($name, 'mrt_station');
        if ($existing) {
            update_post_meta($existing->ID, 'mrt_station_type', $type);
            if ($lat !== null) update_post_meta($existing->ID, 'mrt_lat', $lat);
            if ($lng !== null) update_post_meta($existing->ID, 'mrt_lng', $lng);
            update_post_meta($existing->ID, 'mrt_display_order', $order);
            $updated++;
        } else {
            $id = wp_insert_post([
                'post_type' => 'mrt_station',
                'post_title' => $name,
                'post_status' => 'publish',
            ]);
            
            if (is_wp_error($id)) {
                MRT_log_error('Failed to create station "' . $name . '": ' . $id->get_error_message());
                continue;
            }
            
            if ($id) {
                $meta_updated = true;
                $meta_updated = update_post_meta($id, 'mrt_station_type', $type) && $meta_updated;
                if ($lat !== null) $meta_updated = update_post_meta($id, 'mrt_lat', $lat) && $meta_updated;
                if ($lng !== null) $meta_updated = update_post_meta($id, 'mrt_lng', $lng) && $meta_updated;
                $meta_updated = update_post_meta($id, 'mrt_display_order', $order) && $meta_updated;
                
                if ($meta_updated) {
                    $created++;
                } else {
                    MRT_log_error('Failed to update meta for station ID ' . $id);
                }
            }
        }
    }
    return sprintf(__('Stations: %d created, %d updated.', 'museum-railway-timetable'), $created, $updated);
}

/**
 * Import stop times from CSV rows
 * Headers: service,station,sequence,arrive,depart,pickup,dropoff
 *
 * @param array $rows Parsed CSV rows
 * @return string Success message
 */
function MRT_import_stoptimes($rows) {
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    $inserted = 0; $skipped = 0;

    // Group by service; for simplicity we replace existing stop times per service
    $by_service = [];
    foreach ($rows as $r) {
        $by_service[ $r['service'] ?? '' ][] = $r;
    }

        foreach ($by_service as $serviceName => $items) {
        if (empty($serviceName)) {
            $skipped += count($items);
            continue;
        }
        
        $service_id = MRT_get_service_id_by_name($serviceName);
        if (!$service_id) {
            $skipped += count($items);
            continue;
        }
        
        // Remove existing rows for this service
        $deleted = $wpdb->delete($table, ['service_post_id' => $service_id], ['%d']);
        if (false === $deleted) {
            MRT_check_db_error('MRT_import_stoptimes (delete)');
        }

        foreach ($items as $r) {
            $station_id = MRT_get_station_id_by_name($r['station'] ?? '');
            if (!$station_id) {
                $skipped++;
                continue;
            }
            
            $seq = intval($r['sequence'] ?? 0);
            if ($seq <= 0) {
                $skipped++;
                continue;
            }
            
            $arr = trim($r['arrive'] ?? '') ?: null;
            $dep = trim($r['depart'] ?? '') ?: null;
            if (!MRT_validate_time_hhmm($arr) || !MRT_validate_time_hhmm($dep)) {
                $skipped++;
                continue;
            }
            
            $pick = isset($r['pickup']) ? intval($r['pickup']) : 1;
            $drop = isset($r['dropoff']) ? intval($r['dropoff']) : 1;

            $result = $wpdb->insert($table, [
                'service_post_id' => $service_id,
                'station_post_id' => $station_id,
                'stop_sequence'   => $seq,
                'arrival_time'    => $arr,
                'departure_time'  => $dep,
                'pickup_allowed'  => $pick,
                'dropoff_allowed' => $drop,
            ], ['%d', '%d', '%d', '%s', '%s', '%d', '%d']);
            
            if (false === $result) {
                MRT_check_db_error('MRT_import_stoptimes (insert)');
                $skipped++;
            } else {
                $inserted++;
            }
        }
    }
    return sprintf(__('Stop times: %d inserted, %d skipped.', 'museum-railway-timetable'), $inserted, $skipped);
}

/**
 * Import calendar from CSV rows
 * Headers: service,start_date,end_date,mon,tue,wed,thu,fri,sat,sun,include_dates,exclude_dates
 *
 * @param array $rows Parsed CSV rows
 * @return string Success message
 */
function MRT_import_calendar($rows) {
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_calendar';
    $inserted = 0; $skipped = 0;

    foreach ($rows as $r) {
        $service_id = MRT_get_service_id_by_name($r['service'] ?? '');
        if (!$service_id) {
            $skipped++;
            continue;
        }
        
        $start = sanitize_text_field($r['start_date'] ?? '');
        $end   = sanitize_text_field($r['end_date'] ?? '');
        if (!MRT_validate_date($start) || !MRT_validate_date($end)) {
            $skipped++;
            continue;
        }
        
        // Validate date range
        if (strtotime($start) > strtotime($end)) {
            $skipped++;
            continue;
        }
        
        $days = ['mon','tue','wed','thu','fri','sat','sun'];
        $vals = [];
        foreach ($days as $d) {
            $vals[$d] = intval($r[$d] ?? 0);
        }

        $inc = sanitize_text_field($r['include_dates'] ?? '');
        $exc = sanitize_text_field($r['exclude_dates'] ?? '');

        // Prepare format array for wpdb->insert
        $formats = ['%d', '%s', '%s', '%s', '%s'];
        foreach ($days as $d) {
            $formats[] = '%d';
        }

        $result = $wpdb->insert($table, array_merge([
            'service_post_id' => $service_id,
            'start_date' => $start,
            'end_date'   => $end,
            'include_dates' => $inc ?: null,
            'exclude_dates' => $exc ?: null,
        ], $vals), $formats);

        if (false === $result) {
            MRT_check_db_error('MRT_import_calendar');
            $skipped++;
        } else {
            $inserted++;
        }
    }

    return sprintf(__('Calendar: %d inserted, %d skipped.', 'museum-railway-timetable'), $inserted, $skipped);
}

/**
 * Handle CSV import dispatch
 *
 * @param string $tab Import type (stations, stoptimes, calendar)
 * @param string $csv CSV content
 * @return string Result message
 */
function MRT_handle_csv_import($tab, $csv) {
    // Dispatch import by selected tab type
    $rows = MRT_parse_csv($csv);
    if (!$rows) return __('No rows parsed. Check CSV headers.', 'museum-railway-timetable');

    switch ($tab) {
        case 'stations':
            return MRT_import_stations($rows);
        case 'stoptimes':
            return MRT_import_stoptimes($rows);
        case 'calendar':
            return MRT_import_calendar($rows);
        default:
            return __('Unknown tab.', 'museum-railway-timetable');
    }
}

