<?php
/**
 * Prepare service information for timetable rendering
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
