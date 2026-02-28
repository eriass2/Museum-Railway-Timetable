<?php
/**
 * Service stop times meta box
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render a single stop time row for the stoptimes table
 *
 * @param WP_Post $station Station post object
 * @param array|null $st Stop time data or null
 * @param int $index Row index
 * @param int $post_id Service post ID
 * @return string HTML for the row
 */
function MRT_render_stoptime_row($station, $st, $index, $post_id) {
    $stops_here = $st !== null;
    $sequence = $st ? $st['stop_sequence'] : ($index + 1);
    ob_start();
    ?>
    <tr class="mrt-row-hover mrt-route-station-row" data-station-id="<?php echo esc_attr($station->ID); ?>" data-service-id="<?php echo esc_attr($post_id); ?>" data-sequence="<?php echo esc_attr($sequence); ?>">
        <td><?php echo esc_html($index + 1); ?></td>
        <td><strong><?php echo esc_html($station->post_title); ?></strong></td>
        <td>
            <input type="checkbox" class="mrt-stops-here" <?php checked($stops_here); ?> data-station-id="<?php echo esc_attr($station->ID); ?>" />
        </td>
        <td class="mrt-time-field <?php echo $stops_here ? '' : 'mrt-field-disabled-opacity'; ?>">
            <input type="text" class="mrt-arrival-time mrt-time-input" value="<?php echo $st ? esc_attr($st['arrival_time']) : ''; ?>" placeholder="<?php esc_attr_e('HH:MM', 'museum-railway-timetable'); ?>" pattern="[0-2][0-9]:[0-5][0-9]" <?php echo $stops_here ? '' : 'disabled'; ?> />
            <p class="description mrt-description-small"><?php esc_html_e('Leave empty if train stops but time is not fixed', 'museum-railway-timetable'); ?></p>
        </td>
        <td class="mrt-time-field <?php echo $stops_here ? '' : 'mrt-field-disabled-opacity'; ?>">
            <input type="text" class="mrt-departure-time mrt-time-input" value="<?php echo $st ? esc_attr($st['departure_time']) : ''; ?>" placeholder="<?php esc_attr_e('HH:MM', 'museum-railway-timetable'); ?>" pattern="[0-2][0-9]:[0-5][0-9]" <?php echo $stops_here ? '' : 'disabled'; ?> />
            <p class="description mrt-description-small"><?php esc_html_e('Leave empty if train stops but time is not fixed', 'museum-railway-timetable'); ?></p>
        </td>
        <td class="mrt-option-field <?php echo $stops_here ? '' : 'mrt-field-disabled-opacity'; ?>">
            <label>
                <input type="checkbox" class="mrt-pickup" <?php checked($st ? $st['pickup_allowed'] : true, 1); ?> <?php echo $stops_here ? '' : 'disabled'; ?> />
                <?php esc_html_e('Pickup', 'museum-railway-timetable'); ?>
            </label>
        </td>
        <td class="mrt-option-field <?php echo $stops_here ? '' : 'mrt-field-disabled-opacity'; ?>">
            <label>
                <input type="checkbox" class="mrt-dropoff" <?php checked($st ? $st['dropoff_allowed'] : true, 1); ?> <?php echo $stops_here ? '' : 'disabled'; ?> />
                <?php esc_html_e('Dropoff', 'museum-railway-timetable'); ?>
            </label>
        </td>
    </tr>
    <?php
    return ob_get_clean();
}

/**
 * Render service stop times meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_service_stoptimes_box($post) {
    // Get all stop times for this service using helper function
    $stoptimes_by_station = MRT_get_service_stop_times($post->ID);
    
    // Get service route
    $route_id = get_post_meta($post->ID, 'mrt_service_route_id', true);
    $route_stations = [];
    if ($route_id) {
        $route_stations = get_post_meta($route_id, 'mrt_route_stations', true);
        if (!is_array($route_stations)) {
            $route_stations = [];
        }
    }
    
    // Get stations on route
    $stations = [];
    if (!empty($route_stations)) {
        $stations = get_posts([
            'post_type' => 'mrt_station',
            'post__in' => $route_stations,
            'posts_per_page' => -1,
            'orderby' => 'post__in',
            'fields' => 'all',
        ]);
    }
    
    wp_nonce_field('mrt_stoptimes_nonce', 'mrt_stoptimes_nonce');
    ?>
    <div id="mrt-stoptimes-container">
        <?php if ($route_id && !empty($route_stations)): ?>
            <div class="mrt-alert mrt-alert-info mrt-info-box">
                <p><strong><?php esc_html_e('💡 How to configure Stop Times:', 'museum-railway-timetable'); ?></strong></p>
                <ol>
                    <li><?php esc_html_e('Check "Stops here" for each station where the train stops', 'museum-railway-timetable'); ?></li>
                    <li><?php esc_html_e('Fill in Arrival and/or Departure times (HH:MM format, e.g., 09:15)', 'museum-railway-timetable'); ?></li>
                    <li><?php esc_html_e('Times can be left empty if the train stops but the time is not fixed', 'museum-railway-timetable'); ?></li>
                    <li><?php esc_html_e('Select Pickup/Dropoff if passengers can board/alight', 'museum-railway-timetable'); ?></li>
                    <li><?php esc_html_e('Click "Save Stop Times" at the bottom to save all changes', 'museum-railway-timetable'); ?></li>
                </ol>
            </div>
            <table class="widefat striped mrt-stoptimes-table">
                <thead>
                    <tr>
                        <th class="mrt-col-order"><?php esc_html_e('Order', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-stops-here"><?php esc_html_e('Stops here', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-time"><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-time"><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-pickup-dropoff"><?php esc_html_e('Pickup', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-pickup-dropoff"><?php esc_html_e('Dropoff', 'museum-railway-timetable'); ?></th>
                    </tr>
                </thead>
                <tbody id="mrt-stoptimes-tbody">
                    <?php if (!empty($stations)): ?>
                        <?php foreach ($stations as $index => $station):
                            $st = $stoptimes_by_station[$station->ID] ?? null;
                            echo MRT_render_stoptime_row($station, $st, $index, $post->ID);
                        endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="mrt-none">
                                <?php esc_html_e('No route selected. Select a route in Service Details above to configure stop times.', 'museum-railway-timetable'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (!empty($stations)): ?>
            <p class="mrt-mt-1">
                <button type="button" id="mrt-save-all-stoptimes" class="button button-primary" data-service-id="<?php echo esc_attr($post->ID); ?>">
                    <?php esc_html_e('Save Stop Times', 'museum-railway-timetable'); ?>
                </button>
                <span class="description mrt-ml-1">
                    <?php esc_html_e('Configure which stations the train stops at, then click "Save Stop Times" to save all changes.', 'museum-railway-timetable'); ?>
                </span>
            </p>
            <?php endif; ?>
        <?php else: ?>
            <p class="description mrt-description-error">
                <?php esc_html_e('Please select a Route in Service Details above first. Then you can configure which stations this service stops at.', 'museum-railway-timetable'); ?>
            </p>
        <?php endif; ?>
    </div>
    <?php
}
