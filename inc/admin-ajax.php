<?php
/**
 * AJAX handlers for Stop Times and Calendar management
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Register AJAX actions
 */
add_action('wp_ajax_mrt_add_stoptime', 'MRT_ajax_add_stoptime');
add_action('wp_ajax_mrt_update_stoptime', 'MRT_ajax_update_stoptime');
add_action('wp_ajax_mrt_delete_stoptime', 'MRT_ajax_delete_stoptime');
add_action('wp_ajax_mrt_save_all_stoptimes', 'MRT_ajax_save_all_stoptimes');
add_action('wp_ajax_mrt_add_calendar', 'MRT_ajax_add_calendar');
add_action('wp_ajax_mrt_update_calendar', 'MRT_ajax_update_calendar');
add_action('wp_ajax_mrt_delete_calendar', 'MRT_ajax_delete_calendar');
add_action('wp_ajax_mrt_get_stoptime', 'MRT_ajax_get_stoptime');
add_action('wp_ajax_mrt_get_calendar', 'MRT_ajax_get_calendar');

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
    
    // Validation
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
    
    // Delete all existing stop times for this service
    $wpdb->delete($table, ['service_post_id' => $service_id], ['%d']);
    
    $inserted = 0;
    $sequence = 1;
    
    foreach ($stops as $stop) {
        $station_id = intval($stop['station_id'] ?? 0);
        $stops_here = isset($stop['stops_here']) && $stop['stops_here'] == '1';
        
        if (!$stops_here || $station_id <= 0) {
            continue; // Skip stations where train doesn't stop
        }
        
        $arrival = sanitize_text_field($stop['arrival'] ?? '');
        $departure = sanitize_text_field($stop['departure'] ?? '');
        
        // Validate times if provided
        if ($arrival && !MRT_validate_time_hhmm($arrival)) {
            continue;
        }
        if ($departure && !MRT_validate_time_hhmm($departure)) {
            continue;
        }
        
        $pickup = isset($stop['pickup']) && $stop['pickup'] == '1' ? 1 : 0;
        $dropoff = isset($stop['dropoff']) && $stop['dropoff'] == '1' ? 1 : 0;
        
        $result = $wpdb->insert($table, [
            'service_post_id' => $service_id,
            'station_post_id' => $station_id,
            'stop_sequence' => $sequence,
            'arrival_time' => $arrival ?: null,
            'departure_time' => $departure ?: null,
            'pickup_allowed' => $pickup,
            'dropoff_allowed' => $dropoff,
        ], ['%d', '%d', '%d', '%s', '%s', '%d', '%d']);
        
        if ($result !== false) {
            $inserted++;
            $sequence++;
        } else {
            MRT_check_db_error('MRT_ajax_save_all_stoptimes');
        }
    }
    
    wp_send_json_success([
        'message' => sprintf(__('%d stop times saved.', 'museum-railway-timetable'), $inserted),
        'count' => $inserted,
    ]);
}

/**
 * Add calendar entry via AJAX
 */
function MRT_ajax_add_calendar() {
    check_ajax_referer('mrt_calendar_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $service_id = intval($_POST['service_id'] ?? 0);
    $start_date = sanitize_text_field($_POST['start_date'] ?? '');
    $end_date = sanitize_text_field($_POST['end_date'] ?? '');
    $mon = isset($_POST['mon']) ? 1 : 0;
    $tue = isset($_POST['tue']) ? 1 : 0;
    $wed = isset($_POST['wed']) ? 1 : 0;
    $thu = isset($_POST['thu']) ? 1 : 0;
    $fri = isset($_POST['fri']) ? 1 : 0;
    $sat = isset($_POST['sat']) ? 1 : 0;
    $sun = isset($_POST['sun']) ? 1 : 0;
    $include_dates = sanitize_text_field($_POST['include_dates'] ?? '');
    $exclude_dates = sanitize_text_field($_POST['exclude_dates'] ?? '');
    
    // Validation
    if ($service_id <= 0 || !MRT_validate_date($start_date) || !MRT_validate_date($end_date)) {
        wp_send_json_error(['message' => __('Invalid input.', 'museum-railway-timetable')]);
    }
    
    if (strtotime($start_date) > strtotime($end_date)) {
        wp_send_json_error(['message' => __('Start date must be before end date.', 'museum-railway-timetable')]);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_calendar';
    
    $result = $wpdb->insert($table, [
        'service_post_id' => $service_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'mon' => $mon,
        'tue' => $tue,
        'wed' => $wed,
        'thu' => $thu,
        'fri' => $fri,
        'sat' => $sat,
        'sun' => $sun,
        'include_dates' => $include_dates ?: null,
        'exclude_dates' => $exclude_dates ?: null,
    ], ['%d', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s']);
    
    if ($result === false) {
        MRT_check_db_error('MRT_ajax_add_calendar');
        wp_send_json_error(['message' => __('Failed to add calendar entry.', 'museum-railway-timetable')]);
    }
    
    $id = $wpdb->insert_id;
    
    $days = [];
    if ($mon) $days[] = __('Mon', 'museum-railway-timetable');
    if ($tue) $days[] = __('Tue', 'museum-railway-timetable');
    if ($wed) $days[] = __('Wed', 'museum-railway-timetable');
    if ($thu) $days[] = __('Thu', 'museum-railway-timetable');
    if ($fri) $days[] = __('Fri', 'museum-railway-timetable');
    if ($sat) $days[] = __('Sat', 'museum-railway-timetable');
    if ($sun) $days[] = __('Sun', 'museum-railway-timetable');
    $days_str = !empty($days) ? implode(', ', $days) : __('None', 'museum-railway-timetable');
    
    wp_send_json_success([
        'id' => $id,
        'days_str' => $days_str,
        'include_dates' => $include_dates ?: '—',
        'exclude_dates' => $exclude_dates ?: '—',
    ]);
}

/**
 * Update calendar entry via AJAX
 */
function MRT_ajax_update_calendar() {
    check_ajax_referer('mrt_calendar_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $id = intval($_POST['id'] ?? 0);
    $start_date = sanitize_text_field($_POST['start_date'] ?? '');
    $end_date = sanitize_text_field($_POST['end_date'] ?? '');
    $mon = isset($_POST['mon']) ? 1 : 0;
    $tue = isset($_POST['tue']) ? 1 : 0;
    $wed = isset($_POST['wed']) ? 1 : 0;
    $thu = isset($_POST['thu']) ? 1 : 0;
    $fri = isset($_POST['fri']) ? 1 : 0;
    $sat = isset($_POST['sat']) ? 1 : 0;
    $sun = isset($_POST['sun']) ? 1 : 0;
    $include_dates = sanitize_text_field($_POST['include_dates'] ?? '');
    $exclude_dates = sanitize_text_field($_POST['exclude_dates'] ?? '');
    
    if ($id <= 0 || !MRT_validate_date($start_date) || !MRT_validate_date($end_date)) {
        wp_send_json_error(['message' => __('Invalid input.', 'museum-railway-timetable')]);
    }
    
    if (strtotime($start_date) > strtotime($end_date)) {
        wp_send_json_error(['message' => __('Start date must be before end date.', 'museum-railway-timetable')]);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_calendar';
    
    $result = $wpdb->update($table, [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'mon' => $mon,
        'tue' => $tue,
        'wed' => $wed,
        'thu' => $thu,
        'fri' => $fri,
        'sat' => $sat,
        'sun' => $sun,
        'include_dates' => $include_dates ?: null,
        'exclude_dates' => $exclude_dates ?: null,
    ], ['id' => $id], ['%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s'], ['%d']);
    
    if ($result === false) {
        MRT_check_db_error('MRT_ajax_update_calendar');
        wp_send_json_error(['message' => __('Failed to update calendar entry.', 'museum-railway-timetable')]);
    }
    
    $days = [];
    if ($mon) $days[] = __('Mon', 'museum-railway-timetable');
    if ($tue) $days[] = __('Tue', 'museum-railway-timetable');
    if ($wed) $days[] = __('Wed', 'museum-railway-timetable');
    if ($thu) $days[] = __('Thu', 'museum-railway-timetable');
    if ($fri) $days[] = __('Fri', 'museum-railway-timetable');
    if ($sat) $days[] = __('Sat', 'museum-railway-timetable');
    if ($sun) $days[] = __('Sun', 'museum-railway-timetable');
    $days_str = !empty($days) ? implode(', ', $days) : __('None', 'museum-railway-timetable');
    
    wp_send_json_success([
        'days_str' => $days_str,
        'include_dates' => $include_dates ?: '—',
        'exclude_dates' => $exclude_dates ?: '—',
    ]);
}

/**
 * Delete calendar entry via AJAX
 */
function MRT_ajax_delete_calendar() {
    check_ajax_referer('mrt_calendar_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        wp_send_json_error(['message' => __('Invalid ID.', 'museum-railway-timetable')]);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_calendar';
    
    $result = $wpdb->delete($table, ['id' => $id], ['%d']);
    
    if ($result === false) {
        MRT_check_db_error('MRT_ajax_delete_calendar');
        wp_send_json_error(['message' => __('Failed to delete calendar entry.', 'museum-railway-timetable')]);
    }
    
    wp_send_json_success();
}

/**
 * Get calendar entry data via AJAX
 */
function MRT_ajax_get_calendar() {
    check_ajax_referer('mrt_calendar_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        wp_send_json_error(['message' => __('Invalid ID.', 'museum-railway-timetable')]);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_calendar';
    
    $calendar = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$calendar) {
        wp_send_json_error(['message' => __('Calendar entry not found.', 'museum-railway-timetable')]);
    }
    
    wp_send_json_success($calendar);
}

