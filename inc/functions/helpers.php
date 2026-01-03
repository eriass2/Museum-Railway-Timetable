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
    $route_stations = get_post_meta($route_id, 'mrt_route_stations', true);
    
    if (!is_array($route_stations) || empty($route_stations)) {
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

