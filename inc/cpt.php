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
