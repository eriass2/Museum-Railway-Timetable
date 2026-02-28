<?php
/**
 * Timetable view functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Prepare service information and CSS classes for timetable rendering
 *
 * @param array $services_list Array of service data from MRT_group_services_by_route
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @return array Array with 'service_classes', 'service_info', and 'all_connections'
 */
function MRT_prepare_service_info($services_list, $dateYmd) {
    $service_classes = [];
    $service_info = [];
    $all_connections = [];
    
    foreach ($services_list as $idx => $service_data): 
        $service = $service_data['service'];
        $train_type = $service_data['train_type'];
        
        // Get train number from meta, fallback to service ID
        $service_number = get_post_meta($service->ID, 'mrt_service_number', true);
        if (empty($service_number)) {
            $service_number = $service->ID;
        }
        
        // Determine CSS classes
        $classes = ['mrt-service-col'];
        $is_special = false;
        $special_name = '';
        if ($train_type) {
            $train_type_slug = $train_type->slug;
            $train_type_name_lower = strtolower($train_type->name);
            if (strpos($train_type_name_lower, 'buss') !== false || strpos($train_type_slug, 'bus') !== false) {
                $classes[] = 'mrt-service-bus';
            } elseif (strpos($train_type_name_lower, 'express') !== false || strpos($service->post_title, 'express') !== false) {
                $classes[] = 'mrt-service-special';
                $is_special = true;
                if (strpos(strtolower($service->post_title), 'express') !== false) {
                    $special_name = 'Express';
                } elseif (strpos(strtolower($service->post_title), 'thun') !== false) {
                    $special_name = "Thun's-expressen";
                }
            }
        }
        $service_classes[$idx] = $classes;
        
        // Get stop times for this service
        $service_stop_times = $service_data['stop_times'] ?? [];
        
        // Check for connections at end station
        $connections = [];
        $destination_data = MRT_get_service_destination($service->ID);
        if (!empty($destination_data['end_station_id'])) {
            $end_station_id = $destination_data['end_station_id'];
            if (isset($service_stop_times[$end_station_id])) {
                $end_stop = $service_stop_times[$end_station_id];
                $end_arrival = $end_stop['arrival_time'] ?? '';
                if ($end_arrival && $dateYmd) {
                    $connections = MRT_find_connecting_services($end_station_id, $service->ID, $end_arrival, $dateYmd, 2);
                }
            }
        }
        if (!empty($connections)) {
            $all_connections[$idx] = $connections;
        }
        
        $service_info[$idx] = [
            'service' => $service,
            'train_type' => $train_type,
            'service_number' => $service_number,
            'is_special' => $is_special,
            'special_name' => $special_name,
            'destination' => $destination_data['destination'] ?? '',
        ];
    endforeach;
    
    return [
        'service_classes' => $service_classes,
        'service_info' => $service_info,
        'all_connections' => $all_connections,
    ];
}

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
    $service_count = count($services_list);
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
    $service_count = count($services_list);
    ob_start();
    ?>
    <div class="mrt-grid-body">
        <?php 
        // "Från [station]" row - show departure times from first station
        if (!empty($station_posts)):
            $first_station = $station_posts[0];
            $first_station_id = $first_station->ID;
        ?>
            <div class="mrt-grid-row mrt-from-row">
                <div class="mrt-grid-cell mrt-station-col">
                    <?php printf(esc_html__('Från %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($first_station))); ?>
                </div>
                <?php foreach ($services_list as $idx => $service_data): 
                    $stop_time = isset($service_data['stop_times'][$first_station_id]) ? $service_data['stop_times'][$first_station_id] : null;
                    // For "Från" row, show departure time (or arrival if no departure)
                    if ($stop_time) {
                        $time_to_show = !empty($stop_time['departure_time']) ? $stop_time['departure_time'] : ($stop_time['arrival_time'] ?? '');
                        if ($time_to_show) {
                            $time_to_show = MRT_format_time_display($time_to_show);
                            $display_stop_time = [
                                'arrival_time' => '',
                                'departure_time' => $time_to_show,
                                'pickup_allowed' => true,
                                'dropoff_allowed' => true,
                            ];
                        } else {
                            $display_stop_time = $stop_time;
                        }
                    } else {
                        $display_stop_time = null;
                    }
                    $time_display = MRT_format_stop_time_display($display_stop_time ?? $stop_time);
                    $label_parts = [];
                    if ($service_info[$idx]['train_type']) {
                        $label_parts[] = $service_info[$idx]['train_type']->name;
                    }
                    $label_parts[] = $service_info[$idx]['service_number'];
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
        <?php endif; ?>
        
        <!-- Regular station rows (skip first and last station as they're shown in "Från"/"Till" rows) -->
        <?php 
        $regular_stations = [];
        if (!empty($station_posts)) {
            // Skip first and last station
            $regular_stations = array_slice($station_posts, 1, -1);
        }
        foreach ($regular_stations as $station): 
            $station_id = $station->ID;
        ?>
            <div class="mrt-grid-row">
                <div class="mrt-grid-cell mrt-station-col">
                    <?php echo esc_html($station->post_title); ?>
                </div>
                <?php foreach ($services_list as $idx => $service_data): 
                    $stop_time = isset($service_data['stop_times'][$station_id]) ? $service_data['stop_times'][$station_id] : null;
                    $time_display = MRT_format_stop_time_display($stop_time);
                    $label_parts = [];
                    if ($service_info[$idx]['train_type']) {
                        $label_parts[] = $service_info[$idx]['train_type']->name;
                    }
                    $label_parts[] = $service_info[$idx]['service_number'];
                ?>
                    <div class="mrt-grid-cell mrt-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>" 
                         data-service-number="<?php echo esc_attr($service_info[$idx]['service_number']); ?>"
                         data-service-label="<?php echo esc_attr(implode(' ', $label_parts)); ?>">
                        <?php echo esc_html($time_display); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <!-- "Till [station]" row - show arrival times at last station -->
        <?php if (!empty($station_posts)):
            $last_station = end($station_posts);
            $last_station_id = $last_station->ID;
        ?>
            <div class="mrt-grid-row mrt-to-row">
                <div class="mrt-grid-cell mrt-station-col">
                    <?php printf(esc_html__('Till %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($last_station))); ?>
                </div>
                <?php foreach ($services_list as $idx => $service_data): 
                    $stop_time = isset($service_data['stop_times'][$last_station_id]) ? $service_data['stop_times'][$last_station_id] : null;
                    // For "Till" row, show arrival time (or departure if no arrival)
                    if ($stop_time) {
                        $time_to_show = !empty($stop_time['arrival_time']) ? $stop_time['arrival_time'] : ($stop_time['departure_time'] ?? '');
                        if ($time_to_show) {
                            $time_to_show = MRT_format_time_display($time_to_show);
                            $display_stop_time = [
                                'arrival_time' => $time_to_show,
                                'departure_time' => '',
                                'pickup_allowed' => true,
                                'dropoff_allowed' => true,
                            ];
                        } else {
                            $display_stop_time = $stop_time;
                        }
                    } else {
                        $display_stop_time = null;
                    }
                    $time_display = MRT_format_stop_time_display($display_stop_time ?? $stop_time);
                    $label_parts = [];
                    if ($service_info[$idx]['train_type']) {
                        $label_parts[] = $service_info[$idx]['train_type']->name;
                    }
                    $label_parts[] = $service_info[$idx]['service_number'];
                ?>
                    <div class="mrt-grid-cell mrt-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>" 
                         data-service-number="<?php echo esc_attr($service_info[$idx]['service_number']); ?>"
                         data-service-label="<?php echo esc_attr(implode(' ', $label_parts)); ?>">
                        <?php echo esc_html($time_display); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- "Tågbyte:" row if there are connections - matching PDF structure with 2 rows -->
        <?php if (!empty($all_connections)): ?>
            <!-- Row 1: Train Types for connections -->
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
            <!-- Row 2: Train Numbers for connections -->
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
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
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
        
        <div class="mrt-overview-grid" style="--service-count: <?php echo count($services_list); ?>;">
            <?php echo MRT_render_timetable_table_header($services_list, $service_classes, $service_info); ?>
            <?php echo MRT_render_timetable_table_body($station_posts, $services_list, $service_classes, $service_info, $all_connections); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render overview timetable view (like the green timetable image)
 * Groups services by route and direction, shows train types
 *
 * @param int $timetable_id Timetable post ID
 * @param string|null $dateYmd Optional date in YYYY-MM-DD format to show date-specific train types
 * @return string HTML output
 */
function MRT_render_timetable_overview($timetable_id, $dateYmd = null) {
    global $wpdb;
    
    if (!$timetable_id || $timetable_id <= 0) {
        return '<div class="mrt-error">' . esc_html__('Invalid timetable.', 'museum-railway-timetable') . '</div>';
    }
    
    // Use current date if not provided
    if ($dateYmd === null) {
        $datetime = MRT_get_current_datetime();
        $dateYmd = $datetime['date'];
    }
    
    // Get all services for this timetable
    $services = get_posts([
        'post_type' => 'mrt_service',
        'posts_per_page' => -1,
        'meta_query' => [[
            'key' => 'mrt_service_timetable_id',
            'value' => $timetable_id,
            'compare' => '=',
        ]],
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'all',
    ]);
    
    if (empty($services)) {
        return '<div class="mrt-none">' . esc_html__('No trips in this timetable.', 'museum-railway-timetable') . '</div>';
    }
    
    // Group services by route and direction using helper function
    $grouped_services = MRT_group_services_by_route($services, $dateYmd);
    
    if (empty($grouped_services)) {
        return '<div class="mrt-none">' . esc_html__('No valid trips in this timetable.', 'museum-railway-timetable') . '</div>';
    }
    
    // Get timetable type label (GRÖN, RÖD, etc.)
    $timetable_type = get_post_meta($timetable_id, 'mrt_timetable_type', true);
    $type_labels = [
        'green' => 'GRÖN TIDTABELL',
        'red' => 'RÖD TIDTABELL',
        'yellow' => 'GUL TIDTABELL',
        'orange' => 'ORANGE TIDTABELL',
    ];
    $timetable_type_label = isset($type_labels[$timetable_type]) ? $type_labels[$timetable_type] : '';

    // Render HTML
    ob_start();
    ?>
    <div class="mrt-timetable-overview">
        <?php if (!empty($timetable_type_label)): ?>
            <div class="mrt-timetable-type-header"><?php echo esc_html($timetable_type_label); ?></div>
        <?php endif; ?>
        <?php 
        $group_count = count($grouped_services);
        $group_index = 0;
        foreach ($grouped_services as $group):
            $group_index++;
            echo MRT_render_timetable_group($group, $dateYmd);
            if ($group_index < $group_count):
        ?>
            <div class="mrt-timetable-separator"></div>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render timetable for a specific date
 * Shows all services running on that date, grouped by route and direction
 * Uses the same component as timetable overview for consistency
 *
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @param string $train_type_slug Optional train type filter
 * @return string HTML output
 */
function MRT_render_timetable_for_date($dateYmd, $train_type_slug = '') {
    global $wpdb;
    
    if (!MRT_validate_date($dateYmd)) {
        return '<div class="mrt-error">' . esc_html__('Invalid date.', 'museum-railway-timetable') . '</div>';
    }
    
    // Get all services running on this date
    $service_ids = MRT_services_running_on_date($dateYmd, $train_type_slug);
    
    if (empty($service_ids)) {
        return '<div class="mrt-none">' . esc_html__('No services running on this date.', 'museum-railway-timetable') . '</div>';
    }
    
    // Get service posts
    $services = get_posts([
        'post_type' => 'mrt_service',
        'post__in' => $service_ids,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'all',
    ]);
    
    if (empty($services)) {
        return '<div class="mrt-none">' . esc_html__('No services found.', 'museum-railway-timetable') . '</div>';
    }
    
    // Group services by route and direction using helper function
    $grouped_services = MRT_group_services_by_route($services, $dateYmd);
    
    if (empty($grouped_services)) {
        return '<div class="mrt-none">' . esc_html__('No valid services found for this date.', 'museum-railway-timetable') . '</div>';
    }
    
    // Render HTML using the same component as timetable overview
    ob_start();
    ?>
    <div class="mrt-day-timetable">
        <h3 class="mrt-day-timetable-title">
            <?php 
            printf(
                esc_html__('Timetable for %s', 'museum-railway-timetable'),
                esc_html(date_i18n(get_option('date_format'), strtotime($dateYmd)))
            );
            ?>
        </h3>
        <div class="mrt-timetable-overview">
            <?php 
            $group_count = count($grouped_services);
            $group_index = 0;
            foreach ($grouped_services as $group):
                $group_index++;
                echo MRT_render_timetable_group($group, $dateYmd);
                if ($group_index < $group_count):
            ?>
                <div class="mrt-timetable-separator"></div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

