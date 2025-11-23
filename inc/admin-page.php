<?php
if (!defined('ABSPATH')) { exit; }

// Add a top-level menu for the plugin settings
add_action('admin_menu', function () {
    // Main menu page
    add_menu_page(
        __('Museum Railway Timetable', 'museum-railway-timetable'),
        __('Railway Timetable', 'museum-railway-timetable'),
        'manage_options',
        'mrt_settings',
        'MRT_render_admin_page',
        'dashicons-calendar-alt'
    );
    
    // Add CPTs as submenus under main menu (WordPress automatically adds "Add New" links)
    add_submenu_page(
        'mrt_settings',
        __('Stations', 'museum-railway-timetable'),
        __('Stations', 'museum-railway-timetable'),
        'edit_posts',
        'edit.php?post_type=mrt_station'
    );
    
    add_submenu_page(
        'mrt_settings',
        __('Services', 'museum-railway-timetable'),
        __('Services', 'museum-railway-timetable'),
        'edit_posts',
        'edit.php?post_type=mrt_service'
    );
    
    add_submenu_page(
        'mrt_settings',
        __('Routes', 'museum-railway-timetable'),
        __('Routes', 'museum-railway-timetable'),
        'edit_posts',
        'edit.php?post_type=mrt_route'
    );
    
    // Train Types taxonomy
    add_submenu_page(
        'mrt_settings',
        __('Train Types', 'museum-railway-timetable'),
        __('Train Types', 'museum-railway-timetable'),
        'manage_categories',
        'edit-tags.php?taxonomy=mrt_train_type&post_type=mrt_service'
    );
});

// Register basic settings
add_action('admin_init', function () {
    register_setting('mrt_group', 'mrt_settings', [
        'type' => 'array',
        'sanitize_callback' => 'MRT_sanitize_settings',
        'default' => ['enabled' => true, 'note' => '']
    ]);

    add_settings_section(
        'mrt_main',
        __('General Settings', 'museum-railway-timetable'),
        function(){ echo '<p>' . esc_html__('Configure timetable display.', 'museum-railway-timetable') . '</p>'; },
        'mrt_settings'
    );

    add_settings_field(
        'mrt_enabled',
        __('Enable Plugin', 'museum-railway-timetable'),
        'MRT_render_enabled_field',
        'mrt_settings',
        'mrt_main'
    );

    add_settings_field(
        'mrt_note',
        __('Note', 'museum-railway-timetable'),
        'MRT_render_note_field',
        'mrt_settings',
        'mrt_main'
    );
});

/**
 * Sanitize plugin settings input
 *
 * @param array $input Raw input array
 * @return array Sanitized settings array
 */
function MRT_sanitize_settings($input) {
    return [
        'enabled' => !empty($input['enabled']),
        'note' => isset($input['note']) ? sanitize_text_field($input['note']) : '',
    ];
}

/**
 * Render the enabled checkbox field
 */
function MRT_render_enabled_field() {
    $opts = get_option('mrt_settings');
    echo '<input type="checkbox" name="mrt_settings[enabled]" value="1" ' . checked(!empty($opts['enabled']), true, false) . ' />';
}

/**
 * Render the note text field
 */
function MRT_render_note_field() {
    $opts = get_option('mrt_settings');
    echo '<input type="text" name="mrt_settings[note]" value="' . esc_attr($opts['note'] ?? '') . '" class="regular-text" />';
}

/**
 * Render the main admin settings page
 */
function MRT_render_admin_page() {
    if (!current_user_can('manage_options')) { return; }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Museum Railway Timetable', 'museum-railway-timetable'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mrt_group');
            do_settings_sections('mrt_settings');
            submit_button();
            ?>
        </form>
        
        <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
        <div style="margin-top: 2rem; padding: 1rem; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
            <h2><?php esc_html_e('Development Tools', 'museum-railway-timetable'); ?></h2>
            <p><?php esc_html_e('These tools are only available when WP_DEBUG is enabled.', 'museum-railway-timetable'); ?></p>
            <form method="post" action="" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to delete ALL timetable data? This cannot be undone!', 'museum-railway-timetable')); ?>');">
                <?php wp_nonce_field('mrt_clear_db', 'mrt_clear_db_nonce'); ?>
                <input type="hidden" name="mrt_action" value="clear_db" />
                <p>
                    <button type="submit" class="button button-secondary" style="background: #dc3545; color: #fff; border-color: #dc3545;">
                        <?php esc_html_e('Clear All Timetable Data', 'museum-railway-timetable'); ?>
                    </button>
                </p>
                <p class="description">
                    <?php esc_html_e('This will delete all Stations, Services, Routes, Stop Times, and Calendar entries. Use with caution!', 'museum-railway-timetable'); ?>
                </p>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Handle clear DB action (development only)
 */
add_action('admin_init', function() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    if (!isset($_POST['mrt_action']) || $_POST['mrt_action'] !== 'clear_db') {
        return;
    }
    
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (!isset($_POST['mrt_clear_db_nonce']) || !wp_verify_nonce($_POST['mrt_clear_db_nonce'], 'mrt_clear_db')) {
        wp_die(__('Security check failed.', 'museum-railway-timetable'));
    }
    
    global $wpdb;
    
    // Delete all CPTs
    $stations = get_posts(['post_type' => 'mrt_station', 'posts_per_page' => -1, 'fields' => 'ids']);
    $services = get_posts(['post_type' => 'mrt_service', 'posts_per_page' => -1, 'fields' => 'ids']);
    $routes = get_posts(['post_type' => 'mrt_route', 'posts_per_page' => -1, 'fields' => 'ids']);
    
    foreach ($stations as $id) {
        wp_delete_post($id, true);
    }
    foreach ($services as $id) {
        wp_delete_post($id, true);
    }
    foreach ($routes as $id) {
        wp_delete_post($id, true);
    }
    
    // Delete custom tables data
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mrt_stoptimes");
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mrt_calendar");
    
    // Delete train types
    $terms = get_terms(['taxonomy' => 'mrt_train_type', 'hide_empty' => false]);
    foreach ($terms as $term) {
        wp_delete_term($term->term_id, 'mrt_train_type');
    }
    
    wp_redirect(add_query_arg(['mrt_cleared' => '1'], admin_url('admin.php?page=mrt_settings')));
    exit;
});

// Show success message
add_action('admin_notices', function() {
    if (isset($_GET['mrt_cleared']) && $_GET['mrt_cleared'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('All timetable data has been cleared.', 'museum-railway-timetable') . '</p></div>';
    }
});
