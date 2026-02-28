<?php
/**
 * Shortcode: Journey Planner [museum_journey_planner]
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render journey planner form
 *
 * @param array $stations Station IDs
 * @param int $from_station_id Selected from station
 * @param int $to_station_id Selected to station
 * @param string $selected_date Selected date
 */
function MRT_render_journey_form($stations, $from_station_id, $to_station_id, $selected_date) {
    ?>
    <form class="mrt-box mrt-journey-form" method="get" action="" data-ajax-enabled="true">
        <div class="mrt-journey-fields">
            <div class="mrt-journey-field">
                <label for="mrt_from"><?php esc_html_e('From', 'museum-railway-timetable'); ?></label>
                <select name="mrt_from" id="mrt_from" required>
                    <option value=""><?php esc_html_e('Select station', 'museum-railway-timetable'); ?></option>
                    <?php foreach ($stations as $station_id): ?>
                        <option value="<?php echo esc_attr($station_id); ?>" <?php selected($from_station_id, $station_id); ?>>
                            <?php echo esc_html(get_the_title($station_id)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mrt-journey-field">
                <label for="mrt_to"><?php esc_html_e('To', 'museum-railway-timetable'); ?></label>
                <select name="mrt_to" id="mrt_to" required>
                    <option value=""><?php esc_html_e('Select station', 'museum-railway-timetable'); ?></option>
                    <?php foreach ($stations as $station_id): ?>
                        <option value="<?php echo esc_attr($station_id); ?>" <?php selected($to_station_id, $station_id); ?>>
                            <?php echo esc_html(get_the_title($station_id)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mrt-journey-field">
                <label for="mrt_date"><?php esc_html_e('Date', 'museum-railway-timetable'); ?></label>
                <input type="date" name="mrt_date" id="mrt_date" value="<?php echo esc_attr($selected_date); ?>" required>
            </div>
            <div class="mrt-journey-field">
                <button type="submit" class="mrt-journey-search"><?php esc_html_e('Search', 'museum-railway-timetable'); ?></button>
            </div>
        </div>
    </form>
    <?php
}

/**
 * Render journey planner shortcode output
 *
 * @param array $atts Shortcode attributes
 * @return string HTML
 */
function MRT_render_shortcode_journey($atts) {
    $atts = shortcode_atts(['default_date' => ''], $atts, 'museum_journey_planner');
    $datetime = MRT_get_current_datetime();
    $default_date = !empty($atts['default_date']) && MRT_validate_date($atts['default_date'])
        ? $atts['default_date']
        : $datetime['date'];

    $from_station_id = isset($_GET['mrt_from']) ? intval($_GET['mrt_from']) : 0;
    $to_station_id = isset($_GET['mrt_to']) ? intval($_GET['mrt_to']) : 0;
    $selected_date = isset($_GET['mrt_date']) && MRT_validate_date($_GET['mrt_date'])
        ? sanitize_text_field($_GET['mrt_date'])
        : $default_date;
    $stations = MRT_get_all_stations();

    ob_start();
    ?>
    <div class="mrt-journey-planner">
        <?php MRT_render_journey_form($stations, $from_station_id, $to_station_id, $selected_date); ?>
        <?php MRT_render_journey_results($from_station_id, $to_station_id, $selected_date); ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render journey results title
 */
function MRT_render_journey_results_title($from_name, $to_name, $selected_date) {
    printf(
        esc_html__('Connections from %s to %s on %s', 'museum-railway-timetable'),
        esc_html($from_name),
        esc_html($to_name),
        esc_html(date_i18n(get_option('date_format'), strtotime($selected_date)))
    );
}

/**
 * Render journey results table (connections list)
 *
 * @param array $connections Connection data from MRT_find_connections
 */
function MRT_render_journey_connections_table($connections) {
    ?>
    <div class="mrt-journey-table-container">
        <table class="mrt-table mrt-journey-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Service', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Train Type', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Destination', 'museum-railway-timetable'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($connections as $conn): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($conn['service_name']); ?></strong>
                            <?php if (!empty($conn['route_name'])): ?>
                                <br><small class="mrt-route-name"><?php echo esc_html($conn['route_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($conn['train_type']); ?></td>
                        <td>
                            <strong><?php
                                $dep = $conn['from_departure'] ?: $conn['from_arrival'];
                                echo $dep ? esc_html(MRT_format_time_display($dep)) : '—';
                            ?></strong>
                        </td>
                        <td>
                            <strong><?php
                                $arr = $conn['to_arrival'] ?: $conn['to_departure'];
                                echo $arr ? esc_html(MRT_format_time_display($arr)) : '—';
                            ?></strong>
                        </td>
                        <td><?php echo esc_html(!empty($conn['destination']) ? $conn['destination'] : (!empty($conn['direction']) ? $conn['direction'] : '—')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Render journey search results section
 *
 * @param int $from_station_id From station ID
 * @param int $to_station_id To station ID
 * @param string $selected_date Date YYYY-MM-DD
 */
function MRT_render_journey_results($from_station_id, $to_station_id, $selected_date) {
    if ($from_station_id > 0 && $to_station_id > 0 && $from_station_id === $to_station_id) {
        echo '<div class="mrt-alert mrt-alert-error mrt-error">';
        esc_html_e('Please select different stations for departure and arrival.', 'museum-railway-timetable');
        echo '</div>';
        return;
    }
    if ($from_station_id <= 0 || $to_station_id <= 0) {
        return;
    }

    $connections = MRT_find_connections($from_station_id, $to_station_id, $selected_date);
    $from_name = get_the_title($from_station_id);
    $to_name = get_the_title($to_station_id);
    $services_on_date = MRT_services_running_on_date($selected_date);
    ?>
    <div class="mrt-journey-results">
        <h3 class="mrt-journey-results-title"><?php MRT_render_journey_results_title($from_name, $to_name, $selected_date); ?></h3>
        <?php if (empty($services_on_date)): ?>
            <div class="mrt-alert mrt-alert-error mrt-error">
                <p><strong><?php esc_html_e('No services running.', 'museum-railway-timetable'); ?></strong></p>
                <p><?php esc_html_e('There are no services running on the selected date. Please try a different date.', 'museum-railway-timetable'); ?></p>
            </div>
        <?php elseif (empty($connections)): ?>
            <div class="mrt-alert mrt-alert-info mrt-none">
                <p><strong><?php esc_html_e('No connections found.', 'museum-railway-timetable'); ?></strong></p>
                <p><?php esc_html_e('There are no direct connections between these stations on the selected date. Please try a different date or different stations.', 'museum-railway-timetable'); ?></p>
            </div>
        <?php else: ?>
            <?php MRT_render_journey_connections_table($connections); ?>
        <?php endif; ?>
    </div>
    <?php
}
