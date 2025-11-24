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
        'show_in_menu' => false, // Will be added as submenu under main menu
        'menu_icon' => 'dashicons-location',
        'supports' => ['title'], // Only title needed - meta fields handled by meta box
        'show_in_rest' => false, // Disable Gutenberg/block editor
    ]);

    // Route post type (optional)
    register_post_type('mrt_route', [
        'labels' => [
            'name' => __('Routes', 'museum-railway-timetable'),
            'singular_name' => __('Route', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false, // Will be added as submenu under main menu
        'menu_icon' => 'dashicons-randomize',
        'supports' => ['title'], // Only title needed - no meta fields currently used
        'show_in_rest' => true,
    ]);

    // Timetable (represents days with multiple trips)
    register_post_type('mrt_timetable', [
        'labels' => [
            'name' => __('Timetables', 'museum-railway-timetable'),
            'singular_name' => __('Timetable', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false, // Will be added as submenu under main menu
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => [], // No title required - dates handled by meta box
        'show_in_rest' => false, // Disable Gutenberg/block editor
    ]);

    // Service (a trip - belongs to a timetable)
    register_post_type('mrt_service', [
        'labels' => [
            'name' => __('Services', 'museum-railway-timetable'),
            'singular_name' => __('Service', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false, // Will be added as submenu under main menu
        'menu_icon' => 'dashicons-clock',
        'supports' => ['title'], // Only title needed - meta fields handled by meta box
        'show_in_rest' => false, // Disable Gutenberg/block editor
    ]);

    // Train type taxonomy
    register_taxonomy('mrt_train_type', 'mrt_service', [
        'labels' => [
            'name' => __('Train Types', 'museum-railway-timetable'),
            'singular_name' => __('Train Type', 'museum-railway-timetable'),
        ],
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => false, // Disable REST API (not needed for simple taxonomy)
    ]);
});
