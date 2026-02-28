<?php
/**
 * Utility helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

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
 * Get icon for train type
 * Returns emoji or HTML based on train type name/slug
 *
 * @param WP_Term|null $train_type Train type term object
 * @return string Icon (emoji or empty string)
 */
function MRT_get_train_type_icon($train_type) {
    if (!$train_type) {
        return '';
    }
    
    $name_lower = strtolower($train_type->name);
    $slug_lower = strtolower($train_type->slug);
    
    // Match common train types
    if (strpos($name_lower, 'ång') !== false || strpos($slug_lower, 'steam') !== false || strpos($slug_lower, 'ang') !== false) {
        return '🚂'; // Steam train
    } elseif (strpos($name_lower, 'diesel') !== false || strpos($slug_lower, 'diesel') !== false) {
        return '🚃'; // Diesel train
    } elseif (strpos($name_lower, 'elektrisk') !== false || strpos($name_lower, 'electric') !== false || strpos($slug_lower, 'electric') !== false) {
        return '🚄'; // Electric train
    } elseif (strpos($name_lower, 'rälsbuss') !== false || strpos($name_lower, 'railbus') !== false || strpos($slug_lower, 'bus') !== false || strpos($slug_lower, 'buss') !== false) {
        return '🚌'; // Rail bus
    }
    
    return '🚆'; // Default train icon
}

/**
 * Convert time format from HH:MM to HH.MM
 *
 * @param string|null $time Time in HH:MM format or null
 * @return string Time in HH.MM format or empty string
 */
function MRT_format_time_display($time) {
    if (empty($time)) {
        return '';
    }
    return str_replace(':', '.', $time);
}

/**
 * Format time display for a stop time
 * Determines the appropriate symbol (P, A, X, |) and formats the time
 *
 * @param array|null $stop_time Stop time data array with keys: arrival_time, departure_time, pickup_allowed, dropoff_allowed
 * @return string Formatted time display (e.g., "10.13", "P 10.13", "X", "|", "—")
 */
function MRT_format_stop_time_display($stop_time) {
    if (!$stop_time) {
        return '—';
    }
    
    $arrival = $stop_time['arrival_time'] ?? '';
    $departure = $stop_time['departure_time'] ?? '';
    $pickup_allowed = !empty($stop_time['pickup_allowed']);
    $dropoff_allowed = !empty($stop_time['dropoff_allowed']);
    
    // Determine if train stops here
    $stops_here = $pickup_allowed || $dropoff_allowed;
    
    // If train doesn't stop (no pickup, no dropoff), show vertical bar
    if (!$stops_here) {
        return '|';
    }
    
    // Determine symbol prefix based on stop behavior
    $symbol_prefix = '';
    
    // Determine symbol based on pickup/dropoff behavior
    if ($pickup_allowed && !$dropoff_allowed) {
        // Only pickup allowed = P (påstigning)
        $symbol_prefix = 'P ';
    } elseif (!$pickup_allowed && $dropoff_allowed) {
        // Only dropoff allowed = A (avstigning)
        $symbol_prefix = 'A ';
    }
    // If both allowed, no prefix (normal stop)
    
    // Get time string (prefer departure, fallback to arrival)
    if ($departure) {
        $time_str = $departure;
    } elseif ($arrival) {
        $time_str = $arrival;
    } else {
        // No time specified - show X if both pickup/dropoff, otherwise just symbol
        if ($pickup_allowed && $dropoff_allowed) {
            $time_str = 'X';
            $symbol_prefix = ''; // X replaces prefix
        } else {
            $time_str = '';
        }
    }
    
    // Convert HH:MM to HH.MM format using helper
    if ($time_str && $time_str !== 'X') {
        $time_str = MRT_format_time_display($time_str);
    }
    
    return $symbol_prefix . $time_str;
}
