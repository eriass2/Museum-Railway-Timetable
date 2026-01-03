<?php
/**
 * Asset enqueuing for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Enqueue admin assets
 *
 * @param string $hook Current admin page hook
 */
function MRT_enqueue_admin_assets($hook) {
    // Load on plugin admin pages or when editing services/stations
    $is_plugin_page = (strpos($hook, 'mrt_') !== false);
    $is_edit_page = in_array($hook, ['post.php', 'post-new.php']);
    
    if (!$is_plugin_page && !$is_edit_page) {
        return;
    }
    
    // For edit pages, only load if editing our CPTs
    if ($is_edit_page) {
        $post_type = get_post_type();
        if (!in_array($post_type, ['mrt_station', 'mrt_service', 'mrt_route', 'mrt_timetable'], true)) {
            return;
        }
    }

    // Enqueue admin CSS
    wp_enqueue_style(
        'mrt-admin',
        MRT_URL . 'assets/admin.css',
        [],
        MRT_VERSION
    );

    // Enqueue admin JavaScript
    wp_enqueue_script(
        'mrt-admin',
        MRT_URL . 'assets/admin.js',
        ['jquery'],
        MRT_VERSION,
        true
    );
    
    // Localize script for AJAX and translations
    wp_localize_script('mrt-admin', 'mrtAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'invalidTimeFormat' => __('Invalid format. Use HH:MM (e.g., 09:15)', 'museum-railway-timetable'),
        'fixTimeFormats' => __('Please fix invalid time formats before saving. Use HH:MM format (e.g., 09:15).', 'museum-railway-timetable'),
        'saveServiceToUpdateStations' => __('Please save the service to update available stations from the selected route.', 'museum-railway-timetable'),
        'pleaseSelectStation' => __('Please select a station.', 'museum-railway-timetable'),
        'stationAlreadyOnRoute' => __('This station is already on the route.', 'museum-railway-timetable'),
        'pleaseFillStationAndSequence' => __('Please fill in Station and Sequence.', 'museum-railway-timetable'),
        'errorSavingStopTime' => __('Error saving stop time.', 'museum-railway-timetable'),
        'errorAddingStopTime' => __('Error adding stop time.', 'museum-railway-timetable'),
        'confirmDeleteStopTime' => __('Are you sure you want to delete this stop time?', 'museum-railway-timetable'),
        'errorDeletingStopTime' => __('Error deleting stop time.', 'museum-railway-timetable'),
        'pleaseSelectRoute' => __('Please select a route.', 'museum-railway-timetable'),
        'securityTokenMissing' => __('Security token missing. Please refresh the page.', 'museum-railway-timetable'),
        'confirmRemoveTrip' => __('Are you sure you want to remove this trip from the timetable?', 'museum-railway-timetable'),
        'errorRemovingTrip' => __('Error removing trip.', 'museum-railway-timetable'),
        'networkError' => __('Network error. Please try again.', 'museum-railway-timetable'),
        'moveUp' => __('Move up', 'museum-railway-timetable'),
        'moveDown' => __('Move down', 'museum-railway-timetable'),
        'remove' => __('Remove', 'museum-railway-timetable'),
        'loadingStations' => __('Loading stations...', 'museum-railway-timetable'),
        'noRouteSelected' => __('No route selected. Select a route to configure stop times.', 'museum-railway-timetable'),
        'noStationsOnRoute' => __('No stations found on this route.', 'museum-railway-timetable'),
        'errorLoadingStations' => __('Error loading stations. Please refresh the page.', 'museum-railway-timetable'),
        'stopTimeSavedSuccessfully' => __('Stop time saved successfully.', 'museum-railway-timetable'),
        'stopTimeAddedSuccessfully' => __('Stop time added successfully.', 'museum-railway-timetable'),
        'endStationsSavedSuccessfully' => __('End stations saved successfully.', 'museum-railway-timetable'),
    ]);
}
add_action('admin_enqueue_scripts', 'MRT_enqueue_admin_assets');

/**
 * Enqueue frontend assets for shortcodes
 */
function MRT_enqueue_frontend_assets() {
    // Check if any of our shortcodes are used on the page
    global $post;
    
    $shortcodes = ['museum_timetable', 'museum_timetable_picker', 'museum_timetable_month', 'museum_timetable_overview', 'museum_journey_planner'];
    $has_shortcode = false;
    
    // Check in post content
    if (is_a($post, 'WP_Post') && !empty($post->post_content)) {
        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                $has_shortcode = true;
                break;
            }
        }
    }
    
    // Also check in widgets and other content areas
    if (!$has_shortcode) {
        // Check if shortcodes are registered (they might be used in widgets/blocks)
        // For now, we'll enqueue on all pages, but this could be optimized
        // by checking widget content or using a filter
        $has_shortcode = apply_filters('mrt_should_enqueue_frontend_assets', false);
    }

    if (!$has_shortcode) {
        return;
    }

    // Enqueue frontend CSS (same file for now, but could be separate)
    wp_enqueue_style(
        'mrt-frontend',
        MRT_URL . 'assets/admin.css', // Using same CSS file for now
        [],
        MRT_VERSION
    );
    
    // Enqueue frontend JavaScript
    wp_enqueue_script(
        'mrt-frontend',
        MRT_URL . 'assets/frontend.js',
        ['jquery'],
        MRT_VERSION,
        true
    );
    
    // Localize script for AJAX and translations
    wp_localize_script('mrt-frontend', 'mrtFrontend', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'search' => __('Search', 'museum-railway-timetable'),
        'searching' => __('Searching...', 'museum-railway-timetable'),
        'loading' => __('Loading...', 'museum-railway-timetable'),
        'errorSearching' => __('Error searching for connections.', 'museum-railway-timetable'),
        'errorLoading' => __('Error loading timetable.', 'museum-railway-timetable'),
        'errorSameStations' => __('Please select different stations for departure and arrival.', 'museum-railway-timetable'),
        'networkError' => __('Network error. Please try again.', 'museum-railway-timetable'),
    ]);
}
add_action('wp_enqueue_scripts', 'MRT_enqueue_frontend_assets');

