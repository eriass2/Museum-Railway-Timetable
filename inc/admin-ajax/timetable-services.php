<?php
/**
 * AJAX handlers for Timetable Services (add/remove trips)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Add service to timetable via AJAX
 */
function MRT_ajax_add_service_to_timetable() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('MRT: AJAX add_service_to_timetable called');
        error_log('MRT: POST data: ' . print_r($_POST, true));
    }
    
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mrt_timetable_services_nonce')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MRT: Nonce verification failed');
        }
        wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'museum-railway-timetable')]);
    }
    
    if (!current_user_can('edit_posts')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MRT: Permission denied for user: ' . get_current_user_id());
        }
        wp_send_json_error(['message' => __('Permission denied.', 'museum-railway-timetable')]);
    }
    
    $timetable_id = intval($_POST['timetable_id'] ?? 0);
    $route_id = intval($_POST['route_id'] ?? 0);
    $train_type_id = intval($_POST['train_type_id'] ?? 0);
    $end_station_id = intval($_POST['end_station_id'] ?? 0);
    $direction = sanitize_text_field($_POST['direction'] ?? '');
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('MRT: Parsed values - timetable_id: ' . $timetable_id . ', route_id: ' . $route_id . ', train_type_id: ' . $train_type_id . ', end_station_id: ' . $end_station_id . ', direction: ' . $direction);
    }
    
    if ($timetable_id <= 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MRT: Invalid timetable_id: ' . $timetable_id);
        }
        wp_send_json_error(['message' => __('Invalid timetable.', 'museum-railway-timetable')]);
    }
    
    if ($route_id <= 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MRT: Invalid route_id: ' . $route_id);
        }
        wp_send_json_error(['message' => __('Route is required.', 'museum-railway-timetable')]);
    }
    
    if ($end_station_id > 0) {
        $direction = MRT_calculate_direction_from_end_station($route_id, $end_station_id);
    } elseif ($direction !== '' && !in_array($direction, ['dit', 'från'], true)) {
        $direction = '';
    }
    
    $route = get_post($route_id);
    $route_name = $route ? $route->post_title : __('Route', 'museum-railway-timetable') . ' #' . $route_id;
    $destination_text = '';
    if ($end_station_id > 0) {
        $end_station = get_post($end_station_id);
        if ($end_station) {
            $destination_text = ' → ' . $end_station->post_title;
        }
    } elseif ($direction === 'dit') {
        $destination_text = ' - ' . __('Dit', 'museum-railway-timetable');
    } elseif ($direction === 'från') {
        $destination_text = ' - ' . __('Från', 'museum-railway-timetable');
    }
    $auto_title = $route_name . $destination_text;
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('MRT: Creating service with title: ' . $auto_title);
    }
    $service_id = wp_insert_post([
        'post_type' => 'mrt_service',
        'post_title' => $auto_title,
        'post_status' => 'publish',
    ]);
    
    if (is_wp_error($service_id)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MRT: Failed to create service: ' . $service_id->get_error_message());
        }
        wp_send_json_error(['message' => __('Failed to create trip: ', 'museum-railway-timetable') . $service_id->get_error_message()]);
    }
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('MRT: Service created with ID: ' . $service_id);
    }
    
    update_post_meta($service_id, 'mrt_service_timetable_id', $timetable_id);
    update_post_meta($service_id, 'mrt_service_route_id', $route_id);
    
    if ($end_station_id > 0) {
        update_post_meta($service_id, 'mrt_service_end_station_id', $end_station_id);
        if ($direction) {
            update_post_meta($service_id, 'mrt_direction', $direction);
        }
    } elseif ($direction !== '') {
        update_post_meta($service_id, 'mrt_direction', $direction);
    }
    
    if ($train_type_id > 0) {
        wp_set_object_terms($service_id, [$train_type_id], 'mrt_train_type');
    }
    
    $service = get_post($service_id);
    $route = get_post($route_id);
    $train_type = $train_type_id > 0 ? get_term($train_type_id, 'mrt_train_type') : null;
    
    $destination_name = '—';
    if ($end_station_id > 0) {
        $end_station = get_post($end_station_id);
        if ($end_station) {
            $destination_name = $end_station->post_title;
        }
    } elseif ($direction === 'dit') {
        $destination_name = __('Dit', 'museum-railway-timetable');
    } elseif ($direction === 'från') {
        $destination_name = __('Från', 'museum-railway-timetable');
    }
    
    $response_data = [
        'service_id' => $service_id,
        'service_title' => $service ? $service->post_title : '',
        'route_name' => $route ? $route->post_title : '—',
        'train_type_name' => $train_type ? $train_type->name : '—',
        'destination' => $destination_name,
        'direction' => $direction === 'dit' ? __('Dit', 'museum-railway-timetable') : ($direction === 'från' ? __('Från', 'museum-railway-timetable') : '—'),
        'edit_url' => get_edit_post_link($service_id, 'raw'),
    ];
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('MRT: Sending success response: ' . print_r($response_data, true));
    }
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
    
    delete_post_meta($service_id, 'mrt_service_timetable_id');
    
    wp_send_json_success(['message' => __('Trip removed from timetable.', 'museum-railway-timetable')]);
}
