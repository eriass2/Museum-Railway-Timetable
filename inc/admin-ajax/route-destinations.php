<?php
/**
 * AJAX handler for route destinations
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Get available destinations for a route via AJAX
 */
function MRT_ajax_get_route_destinations() {
    $nonce = $_POST['nonce'] ?? '';
    $valid = false;
    
    if (wp_verify_nonce($nonce, 'mrt_timetable_services_nonce')) {
        $valid = true;
    } elseif (wp_verify_nonce($nonce, 'mrt_save_service_meta')) {
        $valid = true;
    }
    
    if (!$valid) {
        wp_send_json_error(['message' => __('Security check failed.', 'museum-railway-timetable')]);
        return;
    }
    MRT_verify_ajax_permission();

    $route_id = intval($_POST['route_id'] ?? 0);
    
    if ($route_id <= 0) {
        wp_send_json_error(['message' => __('Invalid route.', 'museum-railway-timetable')]);
    }
    
    $end_stations = MRT_get_route_end_stations($route_id);
    $route_stations = get_post_meta($route_id, 'mrt_route_stations', true);
    if (!is_array($route_stations)) {
        $route_stations = [];
    }
    
    $destinations = [];
    
    if ($end_stations['start'] > 0) {
        $start_station = get_post($end_stations['start']);
        if ($start_station) {
            $destinations[] = [
                'id' => $end_stations['start'],
                'name' => $start_station->post_title . ' (' . __('Start', 'museum-railway-timetable') . ')',
            ];
        }
    }
    if ($end_stations['end'] > 0) {
        $end_station = get_post($end_stations['end']);
        if ($end_station) {
            $destinations[] = [
                'id' => $end_stations['end'],
                'name' => $end_station->post_title . ' (' . __('End', 'museum-railway-timetable') . ')',
            ];
        }
    }
    
    foreach ($route_stations as $station_id) {
        if ($station_id == $end_stations['start'] || $station_id == $end_stations['end']) {
            continue;
        }
        $station = get_post($station_id);
        if ($station) {
            $destinations[] = [
                'id' => $station_id,
                'name' => $station->post_title,
            ];
        }
    }
    
    wp_send_json_success(['destinations' => $destinations]);
}
