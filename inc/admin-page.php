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
        __('Timetables', 'museum-railway-timetable'),
        __('Timetables', 'museum-railway-timetable'),
        'edit_posts',
        'edit.php?post_type=mrt_timetable'
    );
    
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
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 4px;">
            <h2><?php esc_html_e('Quick Start Guide', 'museum-railway-timetable'); ?></h2>
            <p><?php esc_html_e('Recommended workflow for managing timetables:', 'museum-railway-timetable'); ?></p>
            <ol style="margin-left: 1.5rem; line-height: 1.8;">
                <li><?php esc_html_e('Create Stations', 'museum-railway-timetable'); ?> - <?php esc_html_e('Go to Railway Timetable → Stations', 'museum-railway-timetable'); ?></li>
                <li><?php esc_html_e('Create Routes', 'museum-railway-timetable'); ?> - <?php esc_html_e('Go to Railway Timetable → Routes and add stations in order', 'museum-railway-timetable'); ?></li>
                <li><?php esc_html_e('Create Timetables', 'museum-railway-timetable'); ?> - <?php esc_html_e('Go to Railway Timetable → Timetables and add dates when the timetable applies', 'museum-railway-timetable'); ?></li>
                <li><?php esc_html_e('Add Trips to Timetable', 'museum-railway-timetable'); ?> - <?php esc_html_e('In the Timetable edit screen, use the "Trips (Services)" meta box to add trips directly. Select Route, Train Type, and Direction, then click "Add Trip".', 'museum-railway-timetable'); ?></li>
                <li><?php esc_html_e('Configure Stop Times', 'museum-railway-timetable'); ?> - <?php esc_html_e('Click "Edit" on any trip to configure arrival/departure times for each station.', 'museum-railway-timetable'); ?></li>
                <li><?php esc_html_e('View Overview', 'museum-railway-timetable'); ?> - <?php esc_html_e('Check the "Timetable Overview" meta box to see a visual preview of the timetable grouped by route and direction.', 'museum-railway-timetable'); ?></li>
            </ol>
            <p style="margin-top: 1rem;"><strong><?php esc_html_e('Tip:', 'museum-railway-timetable'); ?></strong> <?php esc_html_e('Trips are automatically named based on Route + Direction, so you don\'t need to enter a name manually.', 'museum-railway-timetable'); ?></p>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 4px;">
            <h2><?php esc_html_e('Shortcodes', 'museum-railway-timetable'); ?></h2>
            <p><?php esc_html_e('Use these shortcodes to display timetables on your pages and posts.', 'museum-railway-timetable'); ?></p>
            
            <div style="margin-top: 1.5rem;">
                <h3 style="margin-top: 0;">1. <?php esc_html_e('Simple Timetable', 'museum-railway-timetable'); ?></h3>
                <p><code>[museum_timetable station="Station Name" limit="5" show_arrival="1" train_type="steam"]</code></p>
                <p class="description">
                    <?php esc_html_e('Displays next departures from a specific station.', 'museum-railway-timetable'); ?><br>
                    <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                    • <code>station</code> - <?php esc_html_e('Station name (or use station_id)', 'museum-railway-timetable'); ?><br>
                    • <code>station_id</code> - <?php esc_html_e('Station post ID (alternative to station name)', 'museum-railway-timetable'); ?><br>
                    • <code>limit</code> - <?php esc_html_e('Number of departures to show (default: 5)', 'museum-railway-timetable'); ?><br>
                    • <code>show_arrival</code> - <?php esc_html_e('Show arrival times (0 or 1, default: 0)', 'museum-railway-timetable'); ?><br>
                    • <code>train_type</code> - <?php esc_html_e('Filter by train type slug (optional)', 'museum-railway-timetable'); ?>
                </p>
                <p><strong><?php esc_html_e('Example:', 'museum-railway-timetable'); ?></strong></p>
                <pre style="background: #fff; padding: 0.75rem; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto;">[museum_timetable station="Hultsfred" limit="10" show_arrival="1"]</pre>
            </div>
            
            <div style="margin-top: 1.5rem;">
                <h3>2. <?php esc_html_e('Station Picker', 'museum-railway-timetable'); ?></h3>
                <p><code>[museum_timetable_picker default_station="Station Name" limit="6" show_arrival="1"]</code></p>
                <p class="description">
                    <?php esc_html_e('Displays a dropdown to select a station and show its timetable.', 'museum-railway-timetable'); ?><br>
                    <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                    • <code>default_station</code> - <?php esc_html_e('Default selected station name', 'museum-railway-timetable'); ?><br>
                    • <code>limit</code> - <?php esc_html_e('Number of departures to show (default: 6)', 'museum-railway-timetable'); ?><br>
                    • <code>show_arrival</code> - <?php esc_html_e('Show arrival times (0 or 1, default: 0)', 'museum-railway-timetable'); ?><br>
                    • <code>train_type</code> - <?php esc_html_e('Filter by train type slug (optional)', 'museum-railway-timetable'); ?><br>
                    • <code>form_method</code> - <?php esc_html_e('Form submission method: "get" or "post" (default: "get")', 'museum-railway-timetable'); ?><br>
                    • <code>placeholder</code> - <?php esc_html_e('Placeholder text for dropdown', 'museum-railway-timetable'); ?>
                </p>
                <p><strong><?php esc_html_e('Example:', 'museum-railway-timetable'); ?></strong></p>
                <pre style="background: #fff; padding: 0.75rem; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto;">[museum_timetable_picker default_station="Hultsfred" limit="8"]</pre>
            </div>
            
            <div style="margin-top: 1.5rem;">
                <h3>3. <?php esc_html_e('Month View', 'museum-railway-timetable'); ?></h3>
                <p><code>[museum_timetable_month month="2025-06" train_type="" service="" legend="1" show_counts="1"]</code></p>
                <p class="description">
                    <?php esc_html_e('Displays a calendar month view showing which days have services running.', 'museum-railway-timetable'); ?><br>
                    <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                    • <code>month</code> - <?php esc_html_e('Month in YYYY-MM format (default: current month)', 'museum-railway-timetable'); ?><br>
                    • <code>train_type</code> - <?php esc_html_e('Filter by train type slug (optional)', 'museum-railway-timetable'); ?><br>
                    • <code>service</code> - <?php esc_html_e('Filter by exact service title (optional)', 'museum-railway-timetable'); ?><br>
                    • <code>legend</code> - <?php esc_html_e('Show legend (0 or 1, default: 1)', 'museum-railway-timetable'); ?><br>
                    • <code>show_counts</code> - <?php esc_html_e('Show service count per day (0 or 1, default: 1)', 'museum-railway-timetable'); ?><br>
                    • <code>start_monday</code> - <?php esc_html_e('Start week on Monday (0 or 1, default: 1)', 'museum-railway-timetable'); ?>
                </p>
                <p><strong><?php esc_html_e('Example:', 'museum-railway-timetable'); ?></strong></p>
                <pre style="background: #fff; padding: 0.75rem; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto;">[museum_timetable_month month="2025-06" train_type="steam" show_counts="1"]</pre>
            </div>
            
            <div style="margin-top: 1.5rem;">
                <h3>4. <?php esc_html_e('Timetable Overview', 'museum-railway-timetable'); ?></h3>
                <p><code>[museum_timetable_overview timetable_id="123"]</code></p>
                <p class="description">
                    <?php esc_html_e('Displays a complete timetable overview grouped by route and direction, showing all trips with train types and times. Similar to traditional printed timetables.', 'museum-railway-timetable'); ?>
                </p>
                <p class="description" style="margin-top: 0.75rem;">
                    <strong><?php esc_html_e('What it shows:', 'museum-railway-timetable'); ?></strong><br>
                    • <?php esc_html_e('All trips (services) in the timetable', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Grouped by route and direction (e.g., "Från Uppsala Ö Till Marielund")', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Train types for each trip (Ångtåg, Rälsbuss, Dieseltåg)', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Arrival/departure times for each station', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('"X" marker for null/unspecified times', 'museum-railway-timetable'); ?>
                </p>
                <p class="description" style="margin-top: 0.75rem;">
                    <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                    • <code>timetable_id</code> - <?php esc_html_e('Timetable post ID (recommended).', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e('How to find it:', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;1. <?php esc_html_e('Go to Railway Timetable → Timetables', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;2. <?php esc_html_e('Look in the "ID" column - the number is displayed there', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;3. <?php esc_html_e('Or click "Edit" and look at the URL - the number after "post=" is the ID', 'museum-railway-timetable'); ?><br>
                    • <code>timetable</code> - <?php esc_html_e('Timetable name (alternative to timetable_id). Use the exact title of the timetable.', 'museum-railway-timetable'); ?>
                </p>
                <p><strong><?php esc_html_e('Examples:', 'museum-railway-timetable'); ?></strong></p>
                <pre style="background: #fff; padding: 0.75rem; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto;">[museum_timetable_overview timetable_id="123"]</pre>
                <p class="description" style="margin-top: 0.5rem; font-size: 0.9em;">
                    <?php esc_html_e('Or use the timetable name:', 'museum-railway-timetable'); ?>
                </p>
                <pre style="background: #fff; padding: 0.75rem; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto;">[museum_timetable_overview timetable="Sommar 2025"]</pre>
                <p class="description" style="margin-top: 0.75rem; padding: 0.75rem; background: #fff3cd; border-left: 4px solid #ffc107;">
                    <strong><?php esc_html_e('Tip:', 'museum-railway-timetable'); ?></strong> <?php esc_html_e('You can preview how the timetable will look in the "Timetable Overview" meta box when editing a timetable in the admin.', 'museum-railway-timetable'); ?>
                </p>
            </div>
        </div>
        
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
                    <?php esc_html_e('This will delete all Stations, Services, Routes, Timetables, and Stop Times. Use with caution!', 'museum-railway-timetable'); ?>
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
