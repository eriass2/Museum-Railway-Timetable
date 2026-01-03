<?php
/**
 * Helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Get all stations ordered by display order
 *
 * @return array Array of station post IDs
 */
function MRT_get_all_stations() {
    $q = new WP_Query([
        'post_type' => 'mrt_station',
        'posts_per_page' => -1,
        'orderby' => [
            'meta_value_num' => 'ASC',
            'title' => 'ASC',
        ],
        'meta_key' => 'mrt_display_order',
        'order' => 'ASC',
        'fields' => 'ids',
        'nopaging' => true,
    ]);
    return $q->posts;
}

// ============================================================================
// ROUTE FUNCTIONS
// ============================================================================

/**
 * Get end stations (start and end) for a route
 *
 * @param int $route_id Route post ID
 * @return array Array with 'start' and 'end' station IDs, or empty array if not set
 */
function MRT_get_route_end_stations($route_id) {
    $start = get_post_meta($route_id, 'mrt_route_start_station', true);
    $end = get_post_meta($route_id, 'mrt_route_end_station', true);
    return [
        'start' => $start ? intval($start) : 0,
        'end' => $end ? intval($end) : 0,
    ];
}

/**
 * Get route stations array, normalized to array format
 *
 * @param int $route_id Route post ID
 * @return array Array of station post IDs
 */
function MRT_get_route_stations($route_id) {
    if (!$route_id || $route_id <= 0) {
        return [];
    }
    
    $route_stations = get_post_meta($route_id, 'mrt_route_stations', true);
    if (!is_array($route_stations)) {
        return [];
    }
    
    return $route_stations;
}

/**
 * Get train type for a service, with optional date-specific support
 *
 * @param int $service_id Service post ID
 * @param string|null $dateYmd Optional date in YYYY-MM-DD format for date-specific train types
 * @return WP_Term|null Train type term object or null if not found
 */
function MRT_get_service_train_type($service_id, $dateYmd = null) {
    if (!$service_id || $service_id <= 0) {
        return null;
    }
    
    // Use date-specific train type if date provided and function exists
    if ($dateYmd && function_exists('MRT_get_service_train_type_for_date')) {
        return MRT_get_service_train_type_for_date($service_id, $dateYmd);
    }
    
    // Fall back to default train type from taxonomy
    $train_types = wp_get_post_terms($service_id, 'mrt_train_type', ['fields' => 'all']);
    if (!empty($train_types) && !is_wp_error($train_types)) {
        return $train_types[0];
    }
    
    return null;
}

/**
 * Calculate direction based on route and end station
 * 
 * @param int $route_id Route post ID
 * @param int $end_station_id End station (destination) post ID
 * @return string 'dit' if going towards end station, 'från' if going from end station, or '' if cannot determine
 */
function MRT_calculate_direction_from_end_station($route_id, $end_station_id) {
    if (!$route_id || !$end_station_id) {
        return '';
    }
    
    $end_stations = MRT_get_route_end_stations($route_id);
    $route_stations = MRT_get_route_stations($route_id);
    
    if (empty($route_stations)) {
        return '';
    }
    
    // If end station is the route's end station, direction is 'dit' (towards)
    if ($end_stations['end'] == $end_station_id) {
        return 'dit';
    }
    
    // If end station is the route's start station, direction is 'från' (from)
    if ($end_stations['start'] == $end_station_id) {
        return 'från';
    }
    
    // Check position in route stations array
    $end_station_index = array_search($end_station_id, $route_stations);
    $start_station_index = array_search($end_stations['start'], $route_stations);
    $route_end_station_index = array_search($end_stations['end'], $route_stations);
    
    if ($end_station_index === false) {
        return '';
    }
    
    // If end station is closer to route's end station, direction is 'dit'
    if ($route_end_station_index !== false && $end_station_index > $route_end_station_index) {
        return 'dit';
    }
    
    // If end station is closer to route's start station, direction is 'från'
    if ($start_station_index !== false && $end_station_index < $start_station_index) {
        return 'från';
    }
    
    // Default: if end station is after middle point, assume 'dit', otherwise 'från'
    $middle = count($route_stations) / 2;
    return $end_station_index >= $middle ? 'dit' : 'från';
}

// ============================================================================
// SERVICE FUNCTIONS
// ============================================================================

/**
 * Get destination station name for a service
 * Returns the end station name if set, otherwise falls back to direction (dit/från)
 *
 * @param int $service_id Service post ID
 * @return array Array with 'destination' (station name or direction), 'direction' (for backward compatibility), and 'end_station_id'
 */
function MRT_get_service_destination($service_id) {
    if (!$service_id || $service_id <= 0) {
        return [
            'destination' => '',
            'direction' => '',
            'end_station_id' => 0,
        ];
    }
    
    $destination = '';
    $direction = '';
    $end_station_id = get_post_meta($service_id, 'mrt_service_end_station_id', true);
    
    if ($end_station_id) {
        $end_station = get_post($end_station_id);
        if ($end_station) {
            $destination = $end_station->post_title;
        }
    }
    
    // Fallback to direction if no end station (backward compatibility)
    if (empty($destination)) {
        $direction = get_post_meta($service_id, 'mrt_direction', true);
        if ($direction === 'dit') {
            $destination = __('Dit', 'museum-railway-timetable');
        } elseif ($direction === 'från') {
            $destination = __('Från', 'museum-railway-timetable');
        }
    }
    
    return [
        'destination' => $destination,
        'direction' => $direction !== '' ? $direction : '',
        'end_station_id' => $end_station_id ? intval($end_station_id) : 0,
    ];
}

/**
 * Get route label from end stations
 * Helper function for MRT_get_route_label()
 *
 * @param int $route_id Route post ID
 * @param int $end_station_id End station post ID
 * @return string Route label or empty string if cannot determine
 */
function MRT_get_route_label_from_end_station($route_id, $end_station_id) {
    $end_station_post = get_post($end_station_id);
    if (!$end_station_post) {
        return '';
    }
    
    $end_stations = MRT_get_route_end_stations($route_id);
    $start_station_id = $end_stations['start'];
    $start_station = $start_station_id ? get_post($start_station_id) : null;
    
    if ($start_station) {
        return sprintf(__('Från %s Till %s', 'museum-railway-timetable'), 
            $start_station->post_title, 
            $end_station_post->post_title);
    }
    
    return sprintf(__('Route to %s', 'museum-railway-timetable'), $end_station_post->post_title);
}

/**
 * Get route label from direction
 * Helper function for MRT_get_route_label()
 *
 * @param int $route_id Route post ID
 * @param string $direction Direction ('dit' or 'från')
 * @param array $station_posts Optional array of station posts (will be fetched if not provided)
 * @return string Route label or empty string if cannot determine
 */
function MRT_get_route_label_from_direction($route_id, $direction, $station_posts = []) {
    if ($direction !== 'dit' && $direction !== 'från') {
        return '';
    }
    
    // Fetch station posts if not provided
    if (empty($station_posts)) {
        $route_stations = MRT_get_route_stations($route_id);
        if (!empty($route_stations)) {
            $station_posts = get_posts([
                'post_type' => 'mrt_station',
                'post__in' => $route_stations,
                'posts_per_page' => -1,
                'orderby' => 'post__in',
                'fields' => 'all',
            ]);
        }
    }
    
    if (empty($station_posts)) {
        return '';
    }
    
    $first_station = $station_posts[0];
    $last_station = end($station_posts);
    
    if ($direction === 'dit') {
        return sprintf(__('Från %s Till %s', 'museum-railway-timetable'), 
            $first_station->post_title, 
            $last_station->post_title);
    }
    
    return sprintf(__('Från %s Till %s', 'museum-railway-timetable'), 
        $last_station->post_title, 
        $first_station->post_title);
}

/**
 * Get route label based on end stations or direction
 * Creates a human-readable label like "Från X Till Y" or "Route to Y"
 *
 * @param WP_Post $route Route post object
 * @param string $direction Direction ('dit' or 'från')
 * @param array $services_list Optional array of service data to check for end stations
 * @param array $station_posts Optional array of station posts (for direction fallback)
 * @return string Route label
 */
function MRT_get_route_label($route, $direction, $services_list = [], $station_posts = []) {
    if (!$route) {
        return '';
    }
    
    $route_id = $route->ID;
    $route_label = $route->post_title;
    
    // Check if services have end stations set
    $end_station_ids = [];
    if (!empty($services_list)) {
        foreach ($services_list as $service_data) {
            $service = is_array($service_data) && isset($service_data['service']) 
                ? $service_data['service'] 
                : (is_object($service_data) ? $service_data : null);
            
            if (!$service) continue;
            
            $end_station_id = get_post_meta($service->ID, 'mrt_service_end_station_id', true);
            if ($end_station_id) {
                $end_station_ids[] = $end_station_id;
            }
        }
    }
    
    // Try to get label from end stations first
    if (!empty($end_station_ids)) {
        $unique_end_stations = array_unique($end_station_ids);
        if (count($unique_end_stations) === 1) {
            $label = MRT_get_route_label_from_end_station($route_id, reset($unique_end_stations));
            if (!empty($label)) {
                return $label;
            }
        }
    }
    
    // Fallback to direction-based label
    $label = MRT_get_route_label_from_direction($route_id, $direction, $station_posts);
    if (!empty($label)) {
        return $label;
    }
    
    return $route_label;
}

/**
 * Get stop times for a service, indexed by station ID
 *
 * @param int $service_id Service post ID
 * @return array Array of stop times indexed by station_post_id
 */
function MRT_get_service_stop_times($service_id) {
    global $wpdb;
    
    if (!$service_id || $service_id <= 0) {
        return [];
    }
    
    $stoptimes_table = $wpdb->prefix . 'mrt_stoptimes';
    $stop_times = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $stoptimes_table WHERE service_post_id = %d ORDER BY stop_sequence ASC",
        $service_id
    ), ARRAY_A);
    
    if (MRT_check_db_error('MRT_get_service_stop_times')) {
        return [];
    }
    
    $stop_times_by_station = [];
    foreach ($stop_times as $st) {
        $stop_times_by_station[$st['station_post_id']] = $st;
    }
    
    return $stop_times_by_station;
}

/**
 * Get timetable dates, handling both array and legacy single date format
 *
 * @param int $timetable_id Timetable post ID
 * @return array Array of dates in YYYY-MM-DD format
 */
function MRT_get_timetable_dates($timetable_id) {
    if (!$timetable_id || $timetable_id <= 0) {
        return [];
    }
    
    $timetable_dates = get_post_meta($timetable_id, 'mrt_timetable_dates', true);
    
    // Handle array format (new)
    if (is_array($timetable_dates)) {
        return $timetable_dates;
    }
    
    // Handle legacy single date field (old)
    $old_date = get_post_meta($timetable_id, 'mrt_timetable_date', true);
    if (!empty($old_date)) {
        return [$old_date];
    }
    
    return [];
}

/**
 * Group services by route and direction
 * Prepares services for timetable rendering
 *
 * @param array $services Array of service post objects
 * @param string|null $dateYmd Optional date for date-specific train types
 * @return array Grouped services array
 */
function MRT_group_services_by_route($services, $dateYmd = null) {
    global $wpdb;
    
    if (empty($services)) {
        return [];
    }
    
    $grouped_services = [];
    $stoptimes_table = $wpdb->prefix . 'mrt_stoptimes';
    
    foreach ($services as $service) {
        $route_id = get_post_meta($service->ID, 'mrt_service_route_id', true);
        $direction = get_post_meta($service->ID, 'mrt_direction', true);
        
        if (!$route_id) {
            continue;
        }
        
        // Get route info
        $route = get_post($route_id);
        if (!$route) {
            continue;
        }
        
        // Get route stations using helper function
        $route_stations = MRT_get_route_stations($route_id);
        
        // Get train type using helper function
        $train_type = MRT_get_service_train_type($service->ID, $dateYmd);
        
        // Create group key: route_id + direction
        $group_key = $route_id . '_' . $direction;
        
        if (!isset($grouped_services[$group_key])) {
            $grouped_services[$group_key] = [
                'route' => $route,
                'route_id' => $route_id,
                'direction' => $direction,
                'stations' => $route_stations,
                'services' => [],
            ];
        }
        
        // Get stop times using helper function
        $stop_times_by_station = MRT_get_service_stop_times($service->ID);
        
        $grouped_services[$group_key]['services'][] = [
            'service' => $service,
            'train_type' => $train_type,
            'stop_times' => $stop_times_by_station,
        ];
    }
    
    return $grouped_services;
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Get post by title and post type
 *
 * @param string $title Post title
 * @param string $post_type Post type (e.g., 'mrt_station', 'mrt_service')
 * @return WP_Post|null Post object or null if not found
 */
function MRT_get_post_by_title($title, $post_type) {
    if (empty($title) || empty($post_type)) {
        return null;
    }
    $post = get_page_by_title(sanitize_text_field($title), OBJECT, $post_type);
    return $post ?: null;
}

/**
 * Get current datetime information
 *
 * @return array Array with 'timestamp', 'date' (Y-m-d), and 'time' (H:i)
 */
function MRT_get_current_datetime() {
    $timestamp = current_time('timestamp');
    return [
        'timestamp' => $timestamp,
        'date' => date('Y-m-d', $timestamp),
        'time' => date('H:i', $timestamp),
    ];
}

/**
 * Check for database errors and log if WP_DEBUG is enabled
 *
 * @param string $context Context string for error logging (e.g., function name)
 * @return bool True if error occurred, false otherwise
 */
function MRT_check_db_error($context = '') {
    global $wpdb;
    if ($wpdb->last_error) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $message = 'MRT: Database error';
            if ($context) {
                $message .= ' in ' . $context;
            }
            $message .= ': ' . $wpdb->last_error;
            error_log($message);
        }
        return true;
    }
    return false;
}

/**
 * Log error message if WP_DEBUG is enabled
 *
 * @param string $message Error message to log
 * @return void
 */
function MRT_log_error($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('MRT: ' . $message);
    }
}

// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validate time format (HH:MM)
 *
 * @param string $s Time string
 * @return bool True if valid or empty
 */
function MRT_validate_time_hhmm($s) {
    // Accept empty for first/last stop cases
    if ($s === '' || $s === null) return true;
    return (bool) preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $s);
}

/**
 * Validate date format (YYYY-MM-DD)
 *
 * @param string $s Date string
 * @return bool True if valid
 */
function MRT_validate_date($s) {
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $s);
}

// ============================================================================
// RENDERING FUNCTIONS
// ============================================================================

/**
 * Render a generic timetable table (reused by multiple shortcodes)
 *
 * @param array $rows Array of timetable row data
 * @param bool  $show_arrival Whether to show arrival column
 * @return string HTML table
 */
function MRT_render_timetable_table($rows, $show_arrival = false) {
    if (!$rows) return '<div class="mrt-none">'.esc_html__('No upcoming departures.', 'museum-railway-timetable').'</div>';
    ob_start();
    echo '<div class="mrt-timetable"><table class="mrt-table"><thead><tr>';
    echo '<th>'.esc_html__('Service', 'museum-railway-timetable').'</th>';
    if ($show_arrival) echo '<th>'.esc_html__('Arrives', 'museum-railway-timetable').'</th>';
    echo '<th>'.esc_html__('Departs', 'museum-railway-timetable').'</th>';
    echo '<th>'.esc_html__('Destination', 'museum-railway-timetable').'</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>'.esc_html($r['service_name']).'</td>';
        if ($show_arrival) echo '<td>'.esc_html($r['arrival_time'] ?? '').'</td>';
        echo '<td>'.esc_html($r['departure_time'] ?? '').'</td>';
        // Use destination if available, otherwise fallback to direction
        $destination = !empty($r['destination']) ? $r['destination'] : (!empty($r['direction']) ? $r['direction'] : '—');
        echo '<td>'.esc_html($destination).'</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    return ob_get_clean();
}

