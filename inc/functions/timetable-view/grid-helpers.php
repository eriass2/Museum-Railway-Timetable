<?php
/**
 * Timetable grid – helper functions
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render a time cell for the timetable
 */
function MRT_render_time_cell($stop_time, $service_classes, $service_info, $idx) {
    $time_display = MRT_format_stop_time_display($stop_time);
    $label_parts = [];
    if ($service_info[$idx]['train_type']) {
        $label_parts[] = $service_info[$idx]['train_type']->name;
    }
    $label_parts[] = $service_info[$idx]['service_number'];

    $html = '<td class="mrt-time-cell ' . esc_attr(implode(' ', $service_classes[$idx])) . '" ';
    $html .= 'data-service-number="' . esc_attr($service_info[$idx]['service_number']) . '" ';
    $html .= 'data-service-label="' . esc_attr(implode(' ', $label_parts)) . '">';
    $html .= esc_html($time_display);
    $html .= '</td>';

    return $html;
}

/**
 * Get time display for "Från" row (departure time)
 */
function MRT_get_from_row_display_stop_time($stop_time) {
    if (!$stop_time) return null;
    $time_to_show = !empty($stop_time['departure_time']) ? $stop_time['departure_time'] : ($stop_time['arrival_time'] ?? '');
    if (!$time_to_show) return $stop_time;
    return [
        'arrival_time' => '',
        'departure_time' => MRT_format_time_display($time_to_show),
        'pickup_allowed' => true,
        'dropoff_allowed' => true,
    ];
}

/**
 * Get time display for "Till" row (arrival time)
 */
function MRT_get_to_row_display_stop_time($stop_time) {
    if (!$stop_time) return null;
    $time_to_show = !empty($stop_time['arrival_time']) ? $stop_time['arrival_time'] : ($stop_time['departure_time'] ?? '');
    if (!$time_to_show) return $stop_time;
    return [
        'arrival_time' => MRT_format_time_display($time_to_show),
        'departure_time' => '',
        'pickup_allowed' => true,
        'dropoff_allowed' => true,
    ];
}

/**
 * Get label parts for a service (train type + service number)
 */
function MRT_get_service_label_parts($info) {
    $parts = [];
    if (!empty($info['train_type'])) {
        $parts[] = $info['train_type']->name;
    }
    $parts[] = $info['service_number'];
    return $parts;
}
