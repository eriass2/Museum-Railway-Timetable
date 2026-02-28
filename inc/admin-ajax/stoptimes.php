<?php
/**
 * AJAX handlers for Stop Times
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Add stop time via AJAX
 */
function MRT_ajax_add_stoptime() {
    check_ajax_referer('mrt_stoptimes_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $service_id = intval($_POST['service_id'] ?? 0);
    $station_id = intval($_POST['station_id'] ?? 0);
    $sequence = intval($_POST['sequence'] ?? 0);
    $arrival = sanitize_text_field($_POST['arrival'] ?? '');
    $departure = sanitize_text_field($_POST['departure'] ?? '');
    $pickup = isset($_POST['pickup']) ? 1 : 0;
    $dropoff = isset($_POST['dropoff']) ? 1 : 0;
    
    if ($service_id <= 0 || $station_id <= 0 || $sequence <= 0) {
        wp_send_json_error(['message' => __('Invalid input.', 'museum-railway-timetable')]);
    }
    
    if ($arrival && !MRT_validate_time_hhmm($arrival)) {
        wp_send_json_error(['message' => __('Invalid arrival time format. Use HH:MM.', 'museum-railway-timetable')]);
    }
    
    if ($departure && !MRT_validate_time_hhmm($departure)) {
        wp_send_json_error(['message' => __('Invalid departure time format. Use HH:MM.', 'museum-railway-timetable')]);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    
    $result = $wpdb->insert($table, [
        'service_post_id' => $service_id,
        'station_post_id' => $station_id,
        'stop_sequence' => $sequence,
        'arrival_time' => $arrival ?: null,
        'departure_time' => $departure ?: null,
        'pickup_allowed' => $pickup,
        'dropoff_allowed' => $dropoff,
    ], ['%d', '%d', '%d', '%s', '%s', '%d', '%d']);
    
    if ($result === false) {
        MRT_check_db_error('MRT_ajax_add_stoptime');
        wp_send_json_error(['message' => __('Failed to add stop time.', 'museum-railway-timetable')]);
    }
    
    $id = $wpdb->insert_id;
    $station = get_post($station_id);
    $station_name = $station ? $station->post_title : '#' . $station_id;
    
    wp_send_json_success([
        'id' => $id,
        'station_name' => $station_name,
        'arrival' => $arrival ?: '—',
        'departure' => $departure ?: '—',
        'pickup' => $pickup,
        'dropoff' => $dropoff,
    ]);
}

/**
 * Update stop time via AJAX
 */
function MRT_ajax_update_stoptime() {
    check_ajax_referer('mrt_stoptimes_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $id = intval($_POST['id'] ?? 0);
    $station_id = intval($_POST['station_id'] ?? 0);
    $sequence = intval($_POST['sequence'] ?? 0);
    $arrival = sanitize_text_field($_POST['arrival'] ?? '');
    $departure = sanitize_text_field($_POST['departure'] ?? '');
    $pickup = isset($_POST['pickup']) ? 1 : 0;
    $dropoff = isset($_POST['dropoff']) ? 1 : 0;
    
    if ($id <= 0 || $station_id <= 0 || $sequence <= 0) {
        wp_send_json_error(['message' => __('Invalid input.', 'museum-railway-timetable')]);
    }
    
    if ($arrival && !MRT_validate_time_hhmm($arrival)) {
        wp_send_json_error(['message' => __('Invalid arrival time format.', 'museum-railway-timetable')]);
    }
    
    if ($departure && !MRT_validate_time_hhmm($departure)) {
        wp_send_json_error(['message' => __('Invalid departure time format.', 'museum-railway-timetable')]);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    
    $result = $wpdb->update($table, [
        'station_post_id' => $station_id,
        'stop_sequence' => $sequence,
        'arrival_time' => $arrival ?: null,
        'departure_time' => $departure ?: null,
        'pickup_allowed' => $pickup,
        'dropoff_allowed' => $dropoff,
    ], ['id' => $id], ['%d', '%d', '%s', '%s', '%d', '%d'], ['%d']);
    
    if ($result === false) {
        MRT_check_db_error('MRT_ajax_update_stoptime');
        wp_send_json_error(['message' => __('Failed to update stop time.', 'museum-railway-timetable')]);
    }
    
    $station = get_post($station_id);
    $station_name = $station ? $station->post_title : '#' . $station_id;
    
    wp_send_json_success([
        'station_name' => $station_name,
        'arrival' => $arrival ?: '—',
        'departure' => $departure ?: '—',
        'pickup' => $pickup,
        'dropoff' => $dropoff,
    ]);
}

/**
 * Delete stop time via AJAX
 */
function MRT_ajax_delete_stoptime() {
    check_ajax_referer('mrt_stoptimes_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        wp_send_json_error(['message' => __('Invalid ID.', 'museum-railway-timetable')]);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    
    $result = $wpdb->delete($table, ['id' => $id], ['%d']);
    
    if ($result === false) {
        MRT_check_db_error('MRT_ajax_delete_stoptime');
        wp_send_json_error(['message' => __('Failed to delete stop time.', 'museum-railway-timetable')]);
    }
    
    wp_send_json_success();
}

/**
 * Get stop time data via AJAX
 */
function MRT_ajax_get_stoptime() {
    check_ajax_referer('mrt_stoptimes_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        wp_send_json_error(['message' => __('Invalid ID.', 'museum-railway-timetable')]);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    
    $stoptime = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$stoptime) {
        wp_send_json_error(['message' => __('Stop time not found.', 'museum-railway-timetable')]);
    }
    
    wp_send_json_success($stoptime);
}

/**
 * Insert a single stop time for save_all
 *
 * @param wpdb $wpdb WordPress DB object
 * @param array $stop Stop data
 * @param int $service_id Service ID
 * @param int $sequence Stop sequence
 * @return int|false New sequence if inserted, false otherwise
 */
function MRT_insert_stoptime_for_save_all($wpdb, $stop, $service_id, $sequence) {
    $station_id = intval($stop['station_id'] ?? 0);
    $stops_here = isset($stop['stops_here']) && $stop['stops_here'] == '1';
    if (!$stops_here || $station_id <= 0) {
        return false;
    }
    $arrival = sanitize_text_field($stop['arrival'] ?? '');
    $departure = sanitize_text_field($stop['departure'] ?? '');
    if (($arrival && !MRT_validate_time_hhmm($arrival)) || ($departure && !MRT_validate_time_hhmm($departure))) {
        return false;
    }
    $pickup = isset($stop['pickup']) && $stop['pickup'] == '1' ? 1 : 0;
    $dropoff = isset($stop['dropoff']) && $stop['dropoff'] == '1' ? 1 : 0;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    $result = $wpdb->insert($table, [
        'service_post_id' => $service_id,
        'station_post_id' => $station_id,
        'stop_sequence' => $sequence,
        'arrival_time' => $arrival ?: null,
        'departure_time' => $departure ?: null,
        'pickup_allowed' => $pickup,
        'dropoff_allowed' => $dropoff,
    ], ['%d', '%d', '%d', '%s', '%s', '%d', '%d']);
    if ($result === false) {
        MRT_check_db_error('MRT_ajax_save_all_stoptimes');
        return false;
    }
    return $sequence + 1;
}

/**
 * Save all stop times for a service (from route-based form)
 */
function MRT_ajax_save_all_stoptimes() {
    check_ajax_referer('mrt_stoptimes_nonce', 'nonce');
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    $service_id = intval($_POST['service_id'] ?? 0);
    if ($service_id <= 0) {
        wp_send_json_error(['message' => __('Invalid service ID.', 'museum-railway-timetable')]);
    }
    $stops = isset($_POST['stops']) ? $_POST['stops'] : [];
    if (!is_array($stops)) {
        wp_send_json_error(['message' => __('Invalid stops data.', 'museum-railway-timetable')]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    $wpdb->delete($table, ['service_post_id' => $service_id], ['%d']);

    $inserted = 0;
    $sequence = 1;
    foreach ($stops as $stop) {
        $next = MRT_insert_stoptime_for_save_all($wpdb, $stop, $service_id, $sequence);
        if ($next !== false) {
            $inserted++;
            $sequence = $next;
        }
    }

    wp_send_json_success([
        'message' => sprintf(__('%d stop times saved.', 'museum-railway-timetable'), $inserted),
        'count' => $inserted,
    ]);
}
