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
    
    // Localize script for AJAX
    wp_localize_script('mrt-admin', 'mrtAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
}
add_action('admin_enqueue_scripts', 'MRT_enqueue_admin_assets');

/**
 * Enqueue frontend assets for shortcodes
 */
function MRT_enqueue_frontend_assets() {
    // Check if any of our shortcodes are used on the page
    global $post;
    
    $shortcodes = ['museum_timetable', 'museum_timetable_picker', 'museum_timetable_month'];
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
}
add_action('wp_enqueue_scripts', 'MRT_enqueue_frontend_assets');

