<?php
/**
 * Timetable view functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

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
    
    // Render HTML
    ob_start();
    ?>
    <div class="mrt-timetable-overview">
        <?php foreach ($grouped_services as $group): 
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
        ?>
            <div class="mrt-timetable-group">
                <h3 class="mrt-route-header"><?php echo esc_html($route_label); ?></h3>
                
                <table class="mrt-overview-table">
                    <thead>
                        <tr>
                            <th class="mrt-station-col"><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                            <?php 
                            // Pre-calculate service classes and info for reuse
                            $service_classes = [];
                            $service_info = [];
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
                                $service_info[$idx] = [
                                    'service' => $service,
                                    'train_type' => $train_type,
                                    'service_number' => $service_number,
                                    'is_special' => $is_special,
                                    'special_name' => $special_name,
                                ];
                            endforeach; 
                            
                            // Render header row
                            foreach ($services_list as $idx => $service_data): 
                                $info = $service_info[$idx];
                                // Get stop times for this service
                                $service_stop_times = $service_data['stop_times'] ?? [];
                            ?>
                                <th class="<?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>">
                                    <div class="mrt-service-header">
                                        <?php if ($info['train_type']): ?>
                                            <span class="mrt-train-type-icon"><?php echo MRT_get_train_type_icon($info['train_type']); ?></span>
                                            <span class="mrt-train-type"><?php echo esc_html($info['train_type']->name); ?></span>
                                        <?php endif; ?>
                                        <span class="mrt-service-number"><?php echo esc_html($info['service_number']); ?></span>
                                        <?php if ($info['is_special'] && !empty($info['special_name'])): ?>
                                            <span class="mrt-special-label"><?php echo esc_html($info['special_name']); ?></span>
                                        <?php endif; ?>
                                        <?php
                                        // Show destination/transfer info
                                        $destination_data = MRT_get_service_destination($info['service']->ID);
                                        if (!empty($destination_data['destination'])): 
                                            // Check if this is the end station for this service
                                            $end_station_id = $destination_data['end_station_id'];
                                            
                                            // Find if this service ends at any station in this route
                                            $is_end_station = false;
                                            $connections = [];
                                            if ($end_station_id && isset($service_stop_times[$end_station_id])) {
                                                $end_stop = $service_stop_times[$end_station_id];
                                                $end_arrival = $end_stop['arrival_time'] ?? '';
                                                if ($end_arrival && $dateYmd) {
                                                    // Find connecting services
                                                    $connections = MRT_find_connecting_services($end_station_id, $info['service']->ID, $end_arrival, $dateYmd, 2);
                                                    if (!empty($connections)) {
                                                        $is_end_station = true;
                                                    }
                                                }
                                            }
                                        ?>
                                            <div class="mrt-service-destination">
                                                <span class="mrt-destination-label"><?php echo esc_html($destination_data['destination']); ?></span>
                                                <?php if ($is_end_station && !empty($connections)): ?>
                                                    <div class="mrt-transfer-info">
                                                        <span class="mrt-transfer-label"><?php esc_html_e('Tågbyte', 'museum-railway-timetable'); ?>:</span>
                                                        <?php foreach ($connections as $conn): ?>
                                                            <span class="mrt-connecting-train"><?php 
                                                                echo esc_html($conn['service_number']);
                                                                if ($conn['departure_time']) {
                                                                    echo ' ' . esc_html(MRT_format_time_display($conn['departure_time']));
                                                                }
                                                            ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($station_posts as $station): 
                            $station_id = $station->ID;
                        ?>
                            <tr>
                                <td class="mrt-station-name">
                                    <?php 
                                    // Add direction arrow for first and last stations
                                    $is_first = ($station_id === $station_posts[0]->ID);
                                    $is_last = ($station_id === $station_posts[count($station_posts) - 1]->ID);
                                    if ($is_first || $is_last) {
                                        echo '<span class="mrt-direction-arrow">↓</span> ';
                                    }
                                    echo esc_html($station->post_title);
                                    ?>
                                </td>
                                <?php foreach ($services_list as $idx => $service_data): 
                                    $stop_time = isset($service_data['stop_times'][$station_id]) ? $service_data['stop_times'][$station_id] : null;
                                    
                                    if ($stop_time) {
                                        $arrival = $stop_time['arrival_time'];
                                        $departure = $stop_time['departure_time'];
                                        $pickup_allowed = !empty($stop_time['pickup_allowed']);
                                        $dropoff_allowed = !empty($stop_time['dropoff_allowed']);
                                        
                                        // Determine if train stops here
                                        $stops_here = $pickup_allowed || $dropoff_allowed;
                                        
                                        // If train doesn't stop (no pickup, no dropoff), show vertical bar
                                        if (!$stops_here) {
                                            $time_display = '|';
                                        } else {
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
                                            
                                            $time_display = $symbol_prefix . esc_html($time_str);
                                        }
                                    } else {
                                        // No stop time record - train doesn't stop here
                                        $time_display = '—';
                                    }
                                ?>
                                    <td class="mrt-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"><?php echo $time_display; ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render timetable for a specific date
 * Shows all services running on that date, grouped by route and direction
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
    
    // Render HTML (similar to timetable overview)
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
            <?php foreach ($grouped_services as $group): 
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
                ?>
                <div class="mrt-route-group">
                    <h4 class="mrt-route-header"><?php echo esc_html($route_label); ?></h4>
                    <table class="mrt-table mrt-overview-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Service', 'museum-railway-timetable'); ?></th>
                                <th><?php esc_html_e('Train Type', 'museum-railway-timetable'); ?></th>
                                <?php foreach ($station_posts as $station): ?>
                                    <th><?php echo esc_html($station->post_title); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services_list as $service_data): 
                                $service = $service_data['service'];
                                $train_type = $service_data['train_type'];
                                $stop_times = $service_data['stop_times'];
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($service->post_title); ?></strong></td>
                                    <td><?php echo $train_type ? esc_html($train_type->name) : '—'; ?></td>
                                    <?php foreach ($station_posts as $station): 
                                        $st = $stop_times[$station->ID] ?? null;
                                        $arrival = $st && !empty($st['arrival_time']) ? $st['arrival_time'] : '';
                                        $departure = $st && !empty($st['departure_time']) ? $st['departure_time'] : '';
                                        $time_display = '';
                                        if ($arrival && $departure) {
                                            $time_display = MRT_format_time_display($arrival) . ' / ' . MRT_format_time_display($departure);
                                        } elseif ($arrival) {
                                            $time_display = MRT_format_time_display($arrival);
                                        } elseif ($departure) {
                                            $time_display = MRT_format_time_display($departure);
                                        } else {
                                            $time_display = $st ? 'X' : '—';
                                        }
                                    ?>
                                        <td><?php echo esc_html($time_display); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

