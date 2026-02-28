<?php
/**
 * Custom meta boxes for Stations and Services
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

require_once MRT_PATH . 'inc/admin-meta-boxes/station.php';
require_once MRT_PATH . 'inc/admin-meta-boxes/route.php';
require_once MRT_PATH . 'inc/admin-meta-boxes/timetable.php';
require_once MRT_PATH . 'inc/admin-meta-boxes/timetable-services.php';
require_once MRT_PATH . 'inc/admin-meta-boxes/timetable-overview.php';
require_once MRT_PATH . 'inc/admin-meta-boxes/service.php';
require_once MRT_PATH . 'inc/admin-meta-boxes/service-stoptimes.php';
require_once MRT_PATH . 'inc/admin-meta-boxes/hooks.php';

/**
 * Add meta boxes for stations and services
 */
add_action('add_meta_boxes', function() {
    // Station meta box
    add_meta_box(
        'mrt_station_details',
        __('Station Details', 'museum-railway-timetable'),
        'MRT_render_station_meta_box',
        'mrt_station',
        'normal',
        'high'
    );
    
    // Timetable meta box
    add_meta_box(
        'mrt_timetable_details',
        __('Timetable Details', 'museum-railway-timetable'),
        'MRT_render_timetable_meta_box',
        'mrt_timetable',
        'normal',
        'high'
    );
    
    // Timetable services meta box (to manage trips within timetable)
    add_meta_box(
        'mrt_timetable_services',
        __('Trips (Services)', 'museum-railway-timetable'),
        'MRT_render_timetable_services_box',
        'mrt_timetable',
        'normal',
        'default'
    );
    
    // Timetable overview preview
    add_meta_box(
        'mrt_timetable_overview',
        __('Timetable Overview', 'museum-railway-timetable'),
        'MRT_render_timetable_overview_box',
        'mrt_timetable',
        'normal',
        'low'
    );
    
    // Route meta box
    add_meta_box(
        'mrt_route_details',
        __('Route Details', 'museum-railway-timetable'),
        'MRT_render_route_meta_box',
        'mrt_route',
        'normal',
        'high'
    );
    
    // Service meta boxes
    add_meta_box(
        'mrt_service_details',
        __('Service Details', 'museum-railway-timetable'),
        'MRT_render_service_meta_box',
        'mrt_service',
        'normal',
        'high'
    );
    
    add_meta_box(
        'mrt_service_stoptimes',
        __('Stop Times', 'museum-railway-timetable'),
        'MRT_render_service_stoptimes_box',
        'mrt_service',
        'normal',
        'default'
    );
});
