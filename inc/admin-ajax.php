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
add_action('wp_ajax_mrt_get_stoptime', 'MRT_ajax_get_stoptime');
add_action('wp_ajax_mrt_add_service_to_timetable', 'MRT_ajax_add_service_to_timetable');
add_action('wp_ajax_mrt_remove_service_from_timetable', 'MRT_ajax_remove_service_from_timetable');

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
 * Add service to timetable via AJAX
 */
function MRT_ajax_add_service_to_timetable() {
    error_log('MRT: AJAX add_service_to_timetable called');
    error_log('MRT: POST data: ' . print_r($_POST, true));
    
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mrt_timetable_services_nonce')) {
        error_log('MRT: Nonce verification failed');
        wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'museum-railway-timetable')]);
    }
    
    if (!current_user_can('edit_posts')) {
        error_log('MRT: Permission denied for user: ' . get_current_user_id());
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $timetable_id = intval($_POST['timetable_id'] ?? 0);
    $route_id = intval($_POST['route_id'] ?? 0);
    $train_type_id = intval($_POST['train_type_id'] ?? 0);
    $direction = sanitize_text_field($_POST['direction'] ?? '');
    
    error_log('MRT: Parsed values - timetable_id: ' . $timetable_id . ', route_id: ' . $route_id . ', train_type_id: ' . $train_type_id . ', direction: ' . $direction);
    
    // Validation
    if ($timetable_id <= 0) {
        error_log('MRT: Invalid timetable_id: ' . $timetable_id);
        wp_send_json_error(['message' => __('Invalid timetable.', 'museum-railway-timetable')]);
    }
    
    if ($route_id <= 0) {
        error_log('MRT: Invalid route_id: ' . $route_id);
        wp_send_json_error(['message' => __('Route is required.', 'museum-railway-timetable')]);
    }
    
    // Validate direction
    if ($direction !== '' && !in_array($direction, ['dit', 'från'], true)) {
        $direction = '';
    }
    
    // Generate automatic title based on route and direction
    $route = get_post($route_id);
    $route_name = $route ? $route->post_title : __('Route', 'museum-railway-timetable') . ' #' . $route_id;
    $direction_text = '';
    if ($direction === 'dit') {
        $direction_text = ' - ' . __('Dit', 'museum-railway-timetable');
    } elseif ($direction === 'från') {
        $direction_text = ' - ' . __('Från', 'museum-railway-timetable');
    }
    $auto_title = $route_name . $direction_text;
    
    // Create service
    error_log('MRT: Creating service with title: ' . $auto_title);
    $service_id = wp_insert_post([
        'post_type' => 'mrt_service',
        'post_title' => $auto_title,
        'post_status' => 'publish',
    ]);
    
    if (is_wp_error($service_id)) {
        error_log('MRT: Failed to create service: ' . $service_id->get_error_message());
        wp_send_json_error(['message' => __('Failed to create trip: ', 'museum-railway-timetable') . $service_id->get_error_message()]);
    }
    
    error_log('MRT: Service created with ID: ' . $service_id);
    
    // Link to timetable
    update_post_meta($service_id, 'mrt_service_timetable_id', $timetable_id);
    
    // Link to route
    update_post_meta($service_id, 'mrt_service_route_id', $route_id);
    
    // Set direction
    if ($direction !== '') {
        update_post_meta($service_id, 'mrt_direction', $direction);
    }
    
    // Set train type
    if ($train_type_id > 0) {
        wp_set_object_terms($service_id, [$train_type_id], 'mrt_train_type');
    }
    
    // Get service data for response
    $service = get_post($service_id);
    $route = get_post($route_id);
    $train_type = $train_type_id > 0 ? get_term($train_type_id, 'mrt_train_type') : null;
    
    $response_data = [
        'service_id' => $service_id,
        'service_title' => $service ? $service->post_title : '',
        'route_name' => $route ? $route->post_title : '—',
        'train_type_name' => $train_type ? $train_type->name : '—',
        'direction' => $direction === 'dit' ? __('Dit', 'museum-railway-timetable') : ($direction === 'från' ? __('Från', 'museum-railway-timetable') : '—'),
        'edit_url' => get_edit_post_link($service_id, 'raw'),
    ];
    
    error_log('MRT: Sending success response: ' . print_r($response_data, true));
    wp_send_json_success($response_data);
}

/**
 * Remove service from timetable via AJAX
 */
function MRT_ajax_remove_service_from_timetable() {
    check_ajax_referer('mrt_timetable_services_nonce', 'nonce');
    
    if (!current_user_can('delete_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $service_id = intval($_POST['service_id'] ?? 0);
    
    if ($service_id <= 0) {
        wp_send_json_error(['message' => __('Invalid service.', 'museum-railway-timetable')]);
    }
    
    // Remove timetable link (don't delete the service, just unlink it)
    delete_post_meta($service_id, 'mrt_service_timetable_id');
    
    wp_send_json_success(['message' => __('Trip removed from timetable.', 'museum-railway-timetable')]);
}

