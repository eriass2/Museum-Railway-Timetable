<?php
/**
 * Register custom post types and taxonomies
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

add_action('init', function () {
    MRT_register_station_post_type();
    MRT_register_route_post_type();
    MRT_register_timetable_post_type();
    MRT_register_service_post_type();
    MRT_register_train_type_taxonomy();
});

function MRT_register_station_post_type() {
    register_post_type('mrt_station', [
        'labels' => [
            'name' => __('Stations', 'museum-railway-timetable'),
            'singular_name' => __('Station', 'museum-railway-timetable'),
            'add_new' => __('Add New', 'museum-railway-timetable'),
            'add_new_item' => __('Add New Station', 'museum-railway-timetable'),
            'edit_item' => __('Edit Station', 'museum-railway-timetable'),
            'new_item' => __('New Station', 'museum-railway-timetable'),
            'view_item' => __('View Station', 'museum-railway-timetable'),
            'view_items' => __('View Stations', 'museum-railway-timetable'),
            'all_items' => __('All Stations', 'museum-railway-timetable'),
            'search_items' => __('Search Stations', 'museum-railway-timetable'),
            'not_found' => __('No stations found', 'museum-railway-timetable'),
            'not_found_in_trash' => __('No stations found in Trash', 'museum-railway-timetable'),
            'parent_item_colon' => __('Parent Station:', 'museum-railway-timetable'),
            'archives' => __('Station Archives', 'museum-railway-timetable'),
            'attributes' => __('Station Attributes', 'museum-railway-timetable'),
            'insert_into_item' => __('Insert into station', 'museum-railway-timetable'),
            'uploaded_to_this_item' => __('Uploaded to this station', 'museum-railway-timetable'),
            'filter_items_list' => __('Filter stations list', 'museum-railway-timetable'),
            'items_list_navigation' => __('Stations list navigation', 'museum-railway-timetable'),
            'items_list' => __('Stations list', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false,
        'menu_icon' => 'dashicons-location',
        'supports' => ['title'],
        'show_in_rest' => false,
    ]);
}

function MRT_register_route_post_type() {
    register_post_type('mrt_route', [
        'labels' => [
            'name' => __('Routes', 'museum-railway-timetable'),
            'singular_name' => __('Route', 'museum-railway-timetable'),
            'add_new' => __('Add New', 'museum-railway-timetable'),
            'add_new_item' => __('Add New Route', 'museum-railway-timetable'),
            'edit_item' => __('Edit Route', 'museum-railway-timetable'),
            'new_item' => __('New Route', 'museum-railway-timetable'),
            'view_item' => __('View Route', 'museum-railway-timetable'),
            'view_items' => __('View Routes', 'museum-railway-timetable'),
            'all_items' => __('All Routes', 'museum-railway-timetable'),
            'search_items' => __('Search Routes', 'museum-railway-timetable'),
            'not_found' => __('No routes found', 'museum-railway-timetable'),
            'not_found_in_trash' => __('No routes found in Trash', 'museum-railway-timetable'),
            'parent_item_colon' => __('Parent Route:', 'museum-railway-timetable'),
            'archives' => __('Route Archives', 'museum-railway-timetable'),
            'attributes' => __('Route Attributes', 'museum-railway-timetable'),
            'insert_into_item' => __('Insert into route', 'museum-railway-timetable'),
            'uploaded_to_this_item' => __('Uploaded to this route', 'museum-railway-timetable'),
            'filter_items_list' => __('Filter routes list', 'museum-railway-timetable'),
            'items_list_navigation' => __('Routes list navigation', 'museum-railway-timetable'),
            'items_list' => __('Routes list', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false,
        'menu_icon' => 'dashicons-randomize',
        'supports' => ['title'],
        'show_in_rest' => true,
    ]);
}

function MRT_register_timetable_post_type() {
    register_post_type('mrt_timetable', [
        'labels' => [
            'name' => __('Timetables', 'museum-railway-timetable'),
            'singular_name' => __('Timetable', 'museum-railway-timetable'),
            'add_new' => __('Add New', 'museum-railway-timetable'),
            'add_new_item' => __('Add New Timetable', 'museum-railway-timetable'),
            'edit_item' => __('Edit Timetable', 'museum-railway-timetable'),
            'new_item' => __('New Timetable', 'museum-railway-timetable'),
            'view_item' => __('View Timetable', 'museum-railway-timetable'),
            'view_items' => __('View Timetables', 'museum-railway-timetable'),
            'all_items' => __('All Timetables', 'museum-railway-timetable'),
            'search_items' => __('Search Timetables', 'museum-railway-timetable'),
            'not_found' => __('No timetables found', 'museum-railway-timetable'),
            'not_found_in_trash' => __('No timetables found in Trash', 'museum-railway-timetable'),
            'parent_item_colon' => __('Parent Timetable:', 'museum-railway-timetable'),
            'archives' => __('Timetable Archives', 'museum-railway-timetable'),
            'attributes' => __('Timetable Attributes', 'museum-railway-timetable'),
            'insert_into_item' => __('Insert into timetable', 'museum-railway-timetable'),
            'uploaded_to_this_item' => __('Uploaded to this timetable', 'museum-railway-timetable'),
            'filter_items_list' => __('Filter timetables list', 'museum-railway-timetable'),
            'items_list_navigation' => __('Timetables list navigation', 'museum-railway-timetable'),
            'items_list' => __('Timetables list', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => [],
        'show_in_rest' => false,
    ]);
}

function MRT_register_service_post_type() {
    register_post_type('mrt_service', [
        'labels' => [
            'name' => __('Services', 'museum-railway-timetable'),
            'singular_name' => __('Service', 'museum-railway-timetable'),
            'add_new' => __('Add New', 'museum-railway-timetable'),
            'add_new_item' => __('Add New Trip', 'museum-railway-timetable'),
            'edit_item' => __('Edit Trip', 'museum-railway-timetable'),
            'new_item' => __('New Trip', 'museum-railway-timetable'),
            'view_item' => __('View Trip', 'museum-railway-timetable'),
            'view_items' => __('View Trips', 'museum-railway-timetable'),
            'all_items' => __('All Trips', 'museum-railway-timetable'),
            'search_items' => __('Search Trips', 'museum-railway-timetable'),
            'not_found' => __('No trips found', 'museum-railway-timetable'),
            'not_found_in_trash' => __('No trips found in Trash', 'museum-railway-timetable'),
            'parent_item_colon' => __('Parent Trip:', 'museum-railway-timetable'),
            'archives' => __('Trip Archives', 'museum-railway-timetable'),
            'attributes' => __('Trip Attributes', 'museum-railway-timetable'),
            'insert_into_item' => __('Insert into trip', 'museum-railway-timetable'),
            'uploaded_to_this_item' => __('Uploaded to this trip', 'museum-railway-timetable'),
            'filter_items_list' => __('Filter trips list', 'museum-railway-timetable'),
            'items_list_navigation' => __('Trips list navigation', 'museum-railway-timetable'),
            'items_list' => __('Trips list', 'museum-railway-timetable'),
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false,
        'menu_icon' => 'dashicons-clock',
        'supports' => ['title'],
        'show_in_rest' => false,
    ]);
}

function MRT_register_train_type_taxonomy() {
    register_taxonomy('mrt_train_type', 'mrt_service', [
        'labels' => [
            'name' => __('Train Types', 'museum-railway-timetable'),
            'singular_name' => __('Train Type', 'museum-railway-timetable'),
            'add_new_item' => __('Add New Train Type', 'museum-railway-timetable'),
            'edit_item' => __('Edit Train Type', 'museum-railway-timetable'),
            'update_item' => __('Update Train Type', 'museum-railway-timetable'),
            'new_item_name' => __('New Train Type Name', 'museum-railway-timetable'),
            'search_items' => __('Search Train Types', 'museum-railway-timetable'),
            'popular_items' => __('Popular Train Types', 'museum-railway-timetable'),
            'all_items' => __('All Train Types', 'museum-railway-timetable'),
            'separate_items_with_commas' => __('Separate train types with commas', 'museum-railway-timetable'),
            'add_or_remove_items' => __('Add or remove train types', 'museum-railway-timetable'),
            'choose_from_most_used' => __('Choose from the most used train types', 'museum-railway-timetable'),
            'not_found' => __('No train types found', 'museum-railway-timetable'),
            'no_terms' => __('No train types', 'museum-railway-timetable'),
            'items_list_navigation' => __('Train types list navigation', 'museum-railway-timetable'),
            'items_list' => __('Train types list', 'museum-railway-timetable'),
            'back_to_items' => __('← Back to Train Types', 'museum-railway-timetable'),
        ],
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => false,
    ]);
}
