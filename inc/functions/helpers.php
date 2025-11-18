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
    echo '<th>'.esc_html__('Direction', 'museum-railway-timetable').'</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>'.esc_html($r['service_name']).'</td>';
        if ($show_arrival) echo '<td>'.esc_html($r['arrival_time'] ?? '').'</td>';
        echo '<td>'.esc_html($r['departure_time'] ?? '').'</td>';
        echo '<td>'.esc_html($r['direction']).'</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    return ob_get_clean();
}

