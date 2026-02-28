<?php

declare(strict_types=1);

/**
 * Utility helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verify meta box save: nonce, autosave, permissions
 * Call at start of save_post_* handlers.
 *
 * @param int $post_id Post ID
 * @param string $nonce_name $_POST key for nonce
 * @param string $nonce_action wp_verify_nonce action
 * @return bool True if save should proceed, false to abort
 */
function MRT_verify_meta_box_save(int $post_id, string $nonce_name, string $nonce_action): bool {
    if (!isset($_POST[$nonce_name]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_name])), $nonce_action)) {
        return false;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return false;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return false;
    }
    return true;
}

/**
 * Verify AJAX permission for edit_posts
 * Sends JSON error and exits on failure.
 *
 * @return void
 */
function MRT_verify_ajax_permission(): void {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Permission denied.', MRT_TEXT_DOMAIN)]);
        exit;
    }
}

/**
 * Render alert HTML (error, info, warning)
 *
 * @param string $message Message text (will be escaped)
 * @param string $type 'error'|'info'|'warning'
 * @param string $extra_classes Optional extra CSS classes (e.g. 'mrt-empty')
 * @return string HTML
 */
function MRT_render_alert(string $message, string $type = 'error', string $extra_classes = ''): string {
    $allowed_types = ['error', 'info', 'warning'];
    $type = in_array($type, $allowed_types) ? $type : 'error';
    $classes = 'mrt-alert mrt-alert-' . $type;
    if (!empty($extra_classes)) {
        $classes .= ' ' . esc_attr($extra_classes);
    }
    return '<div class="' . $classes . '">' . esc_html($message) . '</div>';
}

/**
 * Render info box (title + content)
 *
 * @param string $title Box title (will be escaped)
 * @param string $content HTML content (use esc_html for plain text, or wp_kses_post for safe HTML)
 * @param string $extra_classes Optional extra CSS classes (e.g. 'mrt-mb-1')
 * @return void Outputs HTML
 */
function MRT_render_info_box(string $title, string $content, string $extra_classes = ''): void {
    $classes = 'mrt-alert mrt-alert-info mrt-info-box';
    if (!empty($extra_classes)) {
        $classes .= ' ' . esc_attr($extra_classes);
    }
    echo '<div class="' . $classes . '">';
    echo '<p><strong>' . esc_html($title) . '</strong></p>';
    echo $content;
    echo '</div>';
}

/**
 * Get post by title and post type
 *
 * @param string $title Post title
 * @param string $post_type Post type (e.g., 'mrt_station', 'mrt_service')
 * @return WP_Post|null Post object or null if not found
 */
function MRT_get_post_by_title(string $title, string $post_type): ?WP_Post {
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
function MRT_check_db_error(string $context = ''): bool {
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
function MRT_log_error(string $message): void {
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
function MRT_get_train_type_icon(?WP_Term $train_type): string {
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
function MRT_format_time_display(?string $time): string {
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
function MRT_format_stop_time_display(?array $stop_time): string {
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
