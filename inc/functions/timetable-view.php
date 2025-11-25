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
 * @return string HTML output
 */
function MRT_render_timetable_overview($timetable_id) {
    global $wpdb;
    
    if (!$timetable_id || $timetable_id <= 0) {
        return '<div class="mrt-error">' . esc_html__('Invalid timetable.', 'museum-railway-timetable') . '</div>';
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
    
    // Group services by route and direction
    $grouped_services = [];
    $stoptimes_table = $wpdb->prefix . 'mrt_stoptimes';
    
    foreach ($services as $service) {
        $route_id = get_post_meta($service->ID, 'mrt_service_route_id', true);
        $direction = get_post_meta($service->ID, 'mrt_direction', true);
        
        if (!$route_id) {
            continue;
        }
        
        // Get route info
        $route = get_post($route_id);
        if (!$route) {
            continue;
        }
        
        // Get route stations
        $route_stations = get_post_meta($route_id, 'mrt_route_stations', true);
        if (!is_array($route_stations)) {
            $route_stations = [];
        }
        
        // Get train types
        $train_types = wp_get_post_terms($service->ID, 'mrt_train_type', ['fields' => 'all']);
        $train_type = !empty($train_types) ? $train_types[0] : null;
        
        // Create group key: route_id + direction
        $group_key = $route_id . '_' . $direction;
        
        if (!isset($grouped_services[$group_key])) {
            $grouped_services[$group_key] = [
                'route' => $route,
                'route_id' => $route_id,
                'direction' => $direction,
                'stations' => $route_stations,
                'services' => [],
            ];
        }
        
        // Get stop times for this service
        $stop_times = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $stoptimes_table WHERE service_post_id = %d ORDER BY stop_sequence ASC",
            $service->ID
        ), ARRAY_A);
        
        $stop_times_by_station = [];
        foreach ($stop_times as $st) {
            $stop_times_by_station[$st['station_post_id']] = $st;
        }
        
        $grouped_services[$group_key]['services'][] = [
            'service' => $service,
            'train_type' => $train_type,
            'stop_times' => $stop_times_by_station,
        ];
    }
    
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
            
            // Determine route label
            $route_label = $route->post_title;
            if ($direction === 'dit' || $direction === 'från') {
                // Try to extract start/end stations from route name or stations
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
        ?>
            <div class="mrt-timetable-group">
                <h3 class="mrt-route-header"><?php echo esc_html($route_label); ?></h3>
                
                <table class="mrt-overview-table">
                    <thead>
                        <tr>
                            <th class="mrt-station-col"><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                            <?php foreach ($services_list as $service_data): 
                                $service = $service_data['service'];
                                $train_type = $service_data['train_type'];
                                // Use service ID as number if no custom number is set
                                $service_number = $service->ID;
                            ?>
                                <th class="mrt-service-col">
                                    <div class="mrt-service-header">
                                        <?php if ($train_type): ?>
                                            <span class="mrt-train-type"><?php echo esc_html($train_type->name); ?></span>
                                        <?php endif; ?>
                                        <span class="mrt-service-number"><?php echo esc_html($service_number); ?></span>
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
                                <td class="mrt-station-name"><?php echo esc_html($station->post_title); ?></td>
                                <?php foreach ($services_list as $service_data): 
                                    $stop_time = isset($service_data['stop_times'][$station_id]) ? $service_data['stop_times'][$station_id] : null;
                                    
                                    if ($stop_time) {
                                        $arrival = $stop_time['arrival_time'];
                                        $departure = $stop_time['departure_time'];
                                        
                                        // Show time or X if null
                                        if ($departure) {
                                            $time_display = esc_html($departure);
                                        } elseif ($arrival) {
                                            $time_display = esc_html($arrival);
                                        } else {
                                            $time_display = 'X';
                                        }
                                    } else {
                                        $time_display = '—';
                                    }
                                ?>
                                    <td class="mrt-time-cell"><?php echo $time_display; ?></td>
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

