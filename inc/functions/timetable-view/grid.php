<?php
/**
 * Timetable grid rendering (header, body, group)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render a time cell for the timetable
 *
 * @param array|null $stop_time Stop time data
 * @param array $service_classes CSS classes for the service
 * @param array $service_info Service information
 * @param int $idx Service index
 * @return string HTML for the time cell
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
 * Render timetable grid header (2 rows: train types and train numbers)
 *
 * @param array $services_list List of services
 * @param array $service_classes CSS classes for each service
 * @param array $service_info Service information
 * @return string HTML for grid header
 */
function MRT_render_timetable_table_header($services_list, $service_classes, $service_info) {
    ob_start();
    ?>
    <div class="mrt-grid-header">
        <!-- Station column header (spans 2 rows) -->
        <div class="mrt-grid-cell mrt-station-col-header">
            <?php esc_html_e('Station', 'museum-railway-timetable'); ?>
        </div>
        <!-- Row 1: Train Types -->
        <?php foreach ($services_list as $idx => $service_data):
            $info = $service_info[$idx];
        ?>
            <div class="mrt-grid-cell mrt-header-train-type <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>">
                <?php if ($info['train_type']): ?>
                    <span class="mrt-train-type-icon"><?php echo MRT_get_train_type_icon($info['train_type']); ?></span>
                    <span class="mrt-train-type"><?php echo esc_html($info['train_type']->name); ?></span>
                <?php else: ?>
                    <span class="mrt-train-type">—</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <!-- Row 2: Train Numbers -->
        <div class="mrt-grid-cell mrt-station-col-header-empty"></div>
        <?php foreach ($services_list as $idx => $service_data):
            $info = $service_info[$idx];
        ?>
            <div class="mrt-grid-cell mrt-header-train-number <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>">
                <span class="mrt-service-number"><?php echo esc_html($info['service_number']); ?></span>
                <?php if ($info['is_special'] && !empty($info['special_name'])): ?>
                    <span class="mrt-special-label"><?php echo esc_html($info['special_name']); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Get time display for "Från" row (departure time)
 *
 * @param array|null $stop_time Stop time data
 * @return array|null Display stop time for formatting
 */
function MRT_get_from_row_display_stop_time($stop_time) {
    if (!$stop_time) {
        return null;
    }
    $time_to_show = !empty($stop_time['departure_time']) ? $stop_time['departure_time'] : ($stop_time['arrival_time'] ?? '');
    if (!$time_to_show) {
        return $stop_time;
    }
    return [
        'arrival_time' => '',
        'departure_time' => MRT_format_time_display($time_to_show),
        'pickup_allowed' => true,
        'dropoff_allowed' => true,
    ];
}

/**
 * Get time display for "Till" row (arrival time)
 *
 * @param array|null $stop_time Stop time data
 * @return array|null Display stop time for formatting
 */
function MRT_get_to_row_display_stop_time($stop_time) {
    if (!$stop_time) {
        return null;
    }
    $time_to_show = !empty($stop_time['arrival_time']) ? $stop_time['arrival_time'] : ($stop_time['departure_time'] ?? '');
    if (!$time_to_show) {
        return $stop_time;
    }
    return [
        'arrival_time' => MRT_format_time_display($time_to_show),
        'departure_time' => '',
        'pickup_allowed' => true,
        'dropoff_allowed' => true,
    ];
}

/**
 * Get label parts for a service (train type + service number)
 *
 * @param array $info Service info for index
 * @return array Label parts
 */
function MRT_get_service_label_parts($info) {
    $parts = [];
    if (!empty($info['train_type'])) {
        $parts[] = $info['train_type']->name;
    }
    $parts[] = $info['service_number'];
    return $parts;
}

/**
 * Render "Från [station]" row
 */
function MRT_render_grid_from_row($first_station, $services_list, $service_classes, $service_info) {
    $first_station_id = $first_station->ID;
    ob_start();
    ?>
    <div class="mrt-grid-row mrt-from-row">
        <div class="mrt-grid-cell mrt-station-col">
            <?php printf(esc_html__('Från %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($first_station))); ?>
        </div>
        <?php foreach ($services_list as $idx => $service_data):
            $stop_time = $service_data['stop_times'][$first_station_id] ?? null;
            $display = MRT_get_from_row_display_stop_time($stop_time);
            $time_display = MRT_format_stop_time_display($display ?? $stop_time);
            $label_parts = MRT_get_service_label_parts($service_info[$idx]);
            $special_name = $service_info[$idx]['special_name'] ?? '';
        ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-from-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"
                 data-service-number="<?php echo esc_attr($service_info[$idx]['service_number']); ?>"
                 data-service-label="<?php echo esc_attr(implode(' ', $label_parts)); ?>">
                <?php echo esc_html($time_display); ?>
                <?php if (!empty($special_name)): ?>
                    <span class="mrt-special-label-inline"><?php echo esc_html($special_name); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render regular station rows (middle stations)
 */
function MRT_render_grid_regular_station_rows($regular_stations, $services_list, $service_classes, $service_info) {
    $html = '';
    foreach ($regular_stations as $station) {
        $station_id = $station->ID;
        ob_start();
        ?>
        <div class="mrt-grid-row">
            <div class="mrt-grid-cell mrt-station-col">
                <?php echo esc_html($station->post_title); ?>
            </div>
            <?php foreach ($services_list as $idx => $service_data):
                $stop_time = $service_data['stop_times'][$station_id] ?? null;
                $time_display = MRT_format_stop_time_display($stop_time);
                $label_parts = MRT_get_service_label_parts($service_info[$idx]);
            ?>
                <div class="mrt-grid-cell mrt-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"
                     data-service-number="<?php echo esc_attr($service_info[$idx]['service_number']); ?>"
                     data-service-label="<?php echo esc_attr(implode(' ', $label_parts)); ?>">
                    <?php echo esc_html($time_display); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        $html .= ob_get_clean();
    }
    return $html;
}

/**
 * Render "Till [station]" row
 */
function MRT_render_grid_to_row($last_station, $services_list, $service_classes, $service_info) {
    $last_station_id = $last_station->ID;
    ob_start();
    ?>
    <div class="mrt-grid-row mrt-to-row">
        <div class="mrt-grid-cell mrt-station-col">
            <?php printf(esc_html__('Till %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($last_station))); ?>
        </div>
        <?php foreach ($services_list as $idx => $service_data):
            $stop_time = $service_data['stop_times'][$last_station_id] ?? null;
            $display = MRT_get_to_row_display_stop_time($stop_time);
            $time_display = MRT_format_stop_time_display($display ?? $stop_time);
            $label_parts = MRT_get_service_label_parts($service_info[$idx]);
        ?>
            <div class="mrt-grid-cell mrt-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"
                 data-service-number="<?php echo esc_attr($service_info[$idx]['service_number']); ?>"
                 data-service-label="<?php echo esc_attr(implode(' ', $label_parts)); ?>">
                <?php echo esc_html($time_display); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render "Tågbyte:" transfer rows (2 rows: train types + train numbers)
 */
function MRT_render_grid_transfer_rows($services_list, $service_classes, $all_connections) {
    ob_start();
    ?>
    <div class="mrt-grid-row mrt-transfer-row">
        <div class="mrt-grid-cell mrt-station-col mrt-transfer-station-col">
            <?php esc_html_e('Tågbyte:', 'museum-railway-timetable'); ?>
        </div>
        <?php foreach ($services_list as $idx => $service_data):
            if (isset($all_connections[$idx]) && !empty($all_connections[$idx])):
                $first_conn = $all_connections[$idx][0];
                $train_type_name = !empty($first_conn['train_type']) ? $first_conn['train_type'] : '';
        ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-transfer-train-type <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>">
                <?php echo esc_html($train_type_name); ?>
            </div>
        <?php else: ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-transfer-train-type <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"></div>
        <?php endif;
        endforeach; ?>
    </div>
    <div class="mrt-grid-row mrt-transfer-row">
        <div class="mrt-grid-cell mrt-station-col-empty"></div>
        <?php foreach ($services_list as $idx => $service_data):
            if (isset($all_connections[$idx]) && !empty($all_connections[$idx])):
                $conn_text = [];
                foreach ($all_connections[$idx] as $conn) {
                    $conn_str = esc_html($conn['service_number']);
                    if (!empty($conn['to_departure'])) {
                        $conn_str .= ' ' . esc_html(MRT_format_time_display($conn['to_departure']));
                    } elseif (!empty($conn['departure_time'])) {
                        $conn_str .= ' ' . esc_html(MRT_format_time_display($conn['departure_time']));
                    }
                    $conn_text[] = $conn_str;
                }
        ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-transfer-service-number <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>">
                <?php echo implode(', ', $conn_text); ?>
            </div>
        <?php else: ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-transfer-service-number <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"></div>
        <?php endif;
        endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render timetable grid body using CSS Grid
 *
 * @param array $station_posts Array of station post objects
 * @param array $services_list List of services with stop times
 * @param array $service_classes CSS classes for each service
 * @param array $service_info Service information
 * @param array $all_connections Connections for transfer row
 * @return string HTML for grid body
 */
function MRT_render_timetable_table_body($station_posts, $services_list, $service_classes, $service_info, $all_connections) {
    $regular_stations = !empty($station_posts) ? array_slice($station_posts, 1, -1) : [];

    $html = '<div class="mrt-grid-body">';
    if (!empty($station_posts)) {
        $html .= MRT_render_grid_from_row($station_posts[0], $services_list, $service_classes, $service_info);
    }
    $html .= MRT_render_grid_regular_station_rows($regular_stations, $services_list, $service_classes, $service_info);
    if (!empty($station_posts)) {
        $html .= MRT_render_grid_to_row(end($station_posts), $services_list, $service_classes, $service_info);
    }
    if (!empty($all_connections)) {
        $html .= MRT_render_grid_transfer_rows($services_list, $service_classes, $all_connections);
    }
    $html .= '</div>';

    return $html;
}

/**
 * Render a single timetable group (route)
 *
 * @param array $group Group data from MRT_group_services_by_route
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @return string HTML for the timetable group
 */
function MRT_render_timetable_group($group, $dateYmd) {
    $route = $group['route'];
    $direction = $group['direction'];
    $stations = $group['stations'];
    $services_list = $group['services'];

    // Get station posts
    $station_posts = [];
    if (!empty($stations)) {
        $station_posts = get_posts([
            'post_type' => 'mrt_station',
            'post__in' => $stations,
            'posts_per_page' => -1,
            'orderby' => 'post__in',
            'fields' => 'all',
        ]);
    }

    // Determine route label using helper function
    $route_label = MRT_get_route_label($route, $direction, $services_list, $station_posts);

    // Extract "Från" and "Till" stations for better display
    $from_station = !empty($station_posts) ? $station_posts[0] : null;
    $to_station = !empty($station_posts) ? end($station_posts) : null;

    // Prepare service information
    $prepared = MRT_prepare_service_info($services_list, $dateYmd);
    $service_classes = $prepared['service_classes'];
    $service_info = $prepared['service_info'];
    $all_connections = $prepared['all_connections'];

    $service_count = count($services_list);
    ob_start();
    ?>
    <div class="mrt-timetable-group">
        <div class="mrt-route-header">
            <div class="mrt-route-header-main"><?php echo esc_html($route_label); ?></div>
            <?php if ($from_station && $to_station): ?>
                <div class="mrt-route-header-details">
                    <span class="mrt-route-from"><?php printf(esc_html__('Från %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($from_station))); ?></span>
                    <span class="mrt-route-separator">→</span>
                    <span class="mrt-route-to"><?php printf(esc_html__('Till %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($to_station))); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="mrt-overview-grid" style="--service-count: <?php echo (int) $service_count; ?>;">
            <?php echo MRT_render_timetable_table_header($services_list, $service_classes, $service_info); ?>
            <?php echo MRT_render_timetable_table_body($station_posts, $services_list, $service_classes, $service_info, $all_connections); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
