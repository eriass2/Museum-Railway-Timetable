<?php
if (!defined('ABSPATH')) { exit; }

add_action('init', function () {
    // Station post type
    register_post_type('mrt_station', [
        'labels' => [
            'name' => __('Stations', 'museum-railway-timetable'),
            'singular_name' => __('Station', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-location',
        'supports' => ['title', 'editor'],
        'show_in_rest' => true,
    ]);

    // Route post type (optional)
    register_post_type('mrt_route', [
        'labels' => [
            'name' => __('Routes', 'museum-railway-timetable'),
            'singular_name' => __('Route', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-randomize',
        'supports' => ['title', 'editor'],
        'show_in_rest' => true,
    ]);

    // Service (a scheduled trip)
    register_post_type('mrt_service', [
        'labels' => [
            'name' => __('Services', 'museum-railway-timetable'),
            'singular_name' => __('Service', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-clock',
        'supports' => ['title', 'editor'],
        'show_in_rest' => true,
    ]);

    // Train type taxonomy
    register_taxonomy('mrt_train_type', 'mrt_service', [
        'labels' => [
            'name' => __('Train Types', 'museum-railway-timetable'),
            'singular_name' => __('Train Type', 'museum-railway-timetable'),
        ],
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
    ]);
});
