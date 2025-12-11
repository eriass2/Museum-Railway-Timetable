<?php
if (!defined('ABSPATH')) { exit; }

add_action('init', function () {
    // Station post type
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
        'show_in_rest' => false, // Disable REST API (not needed for simple taxonomy)
    ]);
});

/**
 * Change "Title" column name to more descriptive names in list tables
 */
add_filter('manage_edit-mrt_station_columns', function($columns) {
    if (isset($columns['title'])) {
        $columns['title'] = __('Station Name', 'museum-railway-timetable');
    }
    return $columns;
});

add_filter('manage_edit-mrt_route_columns', function($columns) {
    if (isset($columns['title'])) {
        $columns['title'] = __('Route Name', 'museum-railway-timetable');
    }
    return $columns;
});

add_filter('manage_edit-mrt_service_columns', function($columns) {
    if (isset($columns['title'])) {
        $columns['title'] = __('Trip Name', 'museum-railway-timetable');
    }
    return $columns;
});

/**
 * Add ID column to Timetable list
 */
add_filter('manage_edit-mrt_timetable_columns', function($columns) {
    // Add ID column after title
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['mrt_timetable_id'] = __('ID', 'museum-railway-timetable');
        }
    }
    return $new_columns;
});

add_action('manage_mrt_timetable_posts_custom_column', function($column, $post_id) {
    if ($column === 'mrt_timetable_id') {
        echo '<code class="mrt-code-inline">' . esc_html($post_id) . '</code>';
    }
}, 10, 2);

/**
 * Change title field placeholder and label for custom post types
 */
add_filter('enter_title_here', function($title, $post) {
    if (!$post) {
        global $post_type;
        if ($post_type === 'mrt_station') {
            return __('Enter station name', 'museum-railway-timetable');
        } elseif ($post_type === 'mrt_route') {
            return __('Enter route name', 'museum-railway-timetable');
        } elseif ($post_type === 'mrt_service') {
            return __('Trip name (auto-generated from Route + Direction)', 'museum-railway-timetable');
        }
        return $title;
    }
    
    if ($post->post_type === 'mrt_station') {
        return __('Enter station name', 'museum-railway-timetable');
    } elseif ($post->post_type === 'mrt_route') {
        return __('Enter route name', 'museum-railway-timetable');
    } elseif ($post->post_type === 'mrt_service') {
        return __('Trip name (auto-generated from Route + Direction)', 'museum-railway-timetable');
    }
    
    return $title;
}, 10, 2);

/**
 * Change title field label text for custom post types
 */
add_action('admin_head', function() {
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }
    
    if ($screen->post_type === 'mrt_station') {
        echo '<script>
        jQuery(document).ready(function($) {
            $("#titlewrap label").text("' . esc_js(__('Station Name', 'museum-railway-timetable')) . '");
        });
        </script>';
    } elseif ($screen->post_type === 'mrt_route') {
        echo '<script>
        jQuery(document).ready(function($) {
            $("#titlewrap label").text("' . esc_js(__('Route Name', 'museum-railway-timetable')) . '");
        });
        </script>';
    } elseif ($screen->post_type === 'mrt_service') {
        echo '<script>
        jQuery(document).ready(function($) {
            $("#titlewrap label").text("' . esc_js(__('Trip Name', 'museum-railway-timetable')) . '");
        });
        </script>';
    }
});
