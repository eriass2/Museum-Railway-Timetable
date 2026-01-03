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
    $has_end_stations = false;
    $end_station_ids = [];
    
    if (!empty($services_list)) {
        foreach ($services_list as $service_data) {
            $service = is_array($service_data) && isset($service_data['service']) 
                ? $service_data['service'] 
                : (is_object($service_data) ? $service_data : null);
            
            if (!$service) continue;
            
            $end_station_id = get_post_meta($service->ID, 'mrt_service_end_station_id', true);
            if ($end_station_id) {
                $has_end_stations = true;
                $end_station_ids[] = $end_station_id;
            }
        }
    }
    
    if ($has_end_stations && !empty($end_station_ids)) {
        // Use the first end station found (they should all be the same for a group)
        $unique_end_stations = array_unique($end_station_ids);
        
        if (count($unique_end_stations) === 1) {
            $end_station_id = reset($unique_end_stations);
            $end_station_post = get_post($end_station_id);
            if ($end_station_post) {
                $end_stations = MRT_get_route_end_stations($route_id);
                $start_station_id = $end_stations['start'];
                $start_station = $start_station_id ? get_post($start_station_id) : null;
                
                if ($start_station) {
                    $route_label = sprintf(__('Från %s Till %s', 'museum-railway-timetable'), 
                        $start_station->post_title, 
                        $end_station_post->post_title);
                } else {
                    $route_label = sprintf(__('Route to %s', 'museum-railway-timetable'), $end_station_post->post_title);
                }
            }
        }
    } elseif ($direction === 'dit' || $direction === 'från') {
        // Fallback to direction-based label
        // Use provided station_posts if available, otherwise fetch them
        if (empty($station_posts)) {
            $route_stations = get_post_meta($route_id, 'mrt_route_stations', true);
            if (is_array($route_stations) && !empty($route_stations)) {
                $station_posts = get_posts([
                    'post_type' => 'mrt_station',
                    'post__in' => $route_stations,
                    'posts_per_page' => -1,
                    'orderby' => 'post__in',
                    'fields' => 'all',
                ]);
            }
        }
        
        if (!empty($station_posts)) {
            $first_station = $station_posts[0];
            $last_station = end($station_posts);
            
            if ($direction === 'dit') {
                $route_label = sprintf(__('Från %s Till %s', 'museum-railway-timetable'), 
                    $first_station->post_title, 
                    $last_station->post_title);
            } else {
                $route_label = sprintf(__('Från %s Till %s', 'museum-railway-timetable'), 
                    $last_station->post_title, 
                    $first_station->post_title);
            }
        }
    }
    
    return $route_label;
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

