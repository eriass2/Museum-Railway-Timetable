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
    
    // Services (Trips) are managed via Timetables meta box, so we hide the separate list
    // Users can still access Services via Timetables or directly via URL if needed
    // add_submenu_page(
    //     'mrt_settings',
    //     __('Services', 'museum-railway-timetable'),
    //     __('Services', 'museum-railway-timetable'),
    //     'edit_posts',
    //     'edit.php?post_type=mrt_service'
    // );
    
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
 * Render the main admin settings page (Dashboard)
 */
function MRT_render_admin_page() {
    if (!current_user_can('manage_options')) { return; }
    
    // Get statistics
    $stations_count = wp_count_posts('mrt_station')->publish;
    $routes_count = wp_count_posts('mrt_route')->publish;
    $timetables_count = wp_count_posts('mrt_timetable')->publish;
    $services_count = wp_count_posts('mrt_service')->publish;
    $train_types_count = wp_count_terms(['taxonomy' => 'mrt_train_type', 'hide_empty' => false]);
    if (is_wp_error($train_types_count)) {
        $train_types_count = 0;
    }
    
    // Get all routes for overview
    $all_routes = get_posts([
        'post_type' => 'mrt_route',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Museum Railway Timetable', 'museum-railway-timetable'); ?></h1>
        
        <!-- Statistics Dashboard -->
        <div class="mrt-dashboard-stats">
            <div class="mrt-stat-card">
                <div class="mrt-stat-number"><?php echo esc_html($stations_count); ?></div>
                <div class="mrt-stat-label">
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=mrt_station')); ?>">
                        <?php esc_html_e('Stations', 'museum-railway-timetable'); ?>
                    </a>
                </div>
            </div>
            <div class="mrt-stat-card">
                <div class="mrt-stat-number"><?php echo esc_html($routes_count); ?></div>
                <div class="mrt-stat-label">
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=mrt_route')); ?>">
                        <?php esc_html_e('Routes', 'museum-railway-timetable'); ?>
                    </a>
                </div>
            </div>
            <div class="mrt-stat-card">
                <div class="mrt-stat-number"><?php echo esc_html($timetables_count); ?></div>
                <div class="mrt-stat-label">
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=mrt_timetable')); ?>">
                        <?php esc_html_e('Timetables', 'museum-railway-timetable'); ?>
                    </a>
                </div>
            </div>
            <div class="mrt-stat-card">
                <div class="mrt-stat-number"><?php echo esc_html($services_count); ?></div>
                <div class="mrt-stat-label">
                    <?php esc_html_e('Trips (Services)', 'museum-railway-timetable'); ?>
                    <span class="mrt-stat-subtitle">
                        <?php esc_html_e('Managed via Timetables', 'museum-railway-timetable'); ?>
                    </span>
                </div>
            </div>
            <div class="mrt-stat-card">
                <div class="mrt-stat-number"><?php echo esc_html($train_types_count); ?></div>
                <div class="mrt-stat-label">
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=mrt_train_type&post_type=mrt_service')); ?>">
                        <?php esc_html_e('Train Types', 'museum-railway-timetable'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Routes Overview -->
        <?php if (!empty($all_routes)): ?>
        <div class="mrt-settings-section">
            <h2><?php esc_html_e('Routes Overview', 'museum-railway-timetable'); ?></h2>
            <p class="description">
                <?php esc_html_e('Routes define which stations trains travel between and in what order. When creating a trip, you select a route to automatically get all its stations.', 'museum-railway-timetable'); ?>
            </p>
            <table class="widefat striped" style="margin-top: 1rem;">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Route Name', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Stations', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Used by Trips', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Actions', 'museum-railway-timetable'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_routes as $route): 
                        $route_stations = get_post_meta($route->ID, 'mrt_route_stations', true);
                        if (!is_array($route_stations)) {
                            $route_stations = [];
                        }
                        $stations_count_for_route = count($route_stations);
                        
                        // Count services using this route
                        $services_using_route = get_posts([
                            'post_type' => 'mrt_service',
                            'posts_per_page' => -1,
                            'fields' => 'ids',
                            'meta_query' => [[
                                'key' => 'mrt_service_route_id',
                                'value' => $route->ID,
                                'compare' => '=',
                            ]],
                        ]);
                        $services_count_for_route = count($services_using_route);
                        
                        // Get station names
                        $station_names = [];
                        if (!empty($route_stations)) {
                            foreach ($route_stations as $station_id) {
                                $station = get_post($station_id);
                                if ($station) {
                                    $station_names[] = $station->post_title;
                                }
                            }
                        }
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html($route->post_title); ?></strong></td>
                            <td>
                                <?php if (!empty($station_names)): ?>
                                    <span title="<?php echo esc_attr(implode(' → ', $station_names)); ?>">
                                        <?php echo esc_html($stations_count_for_route); ?> <?php esc_html_e('stations', 'museum-railway-timetable'); ?>
                                        <span class="description" style="display: block; font-size: 0.85em; margin-top: 0.25rem; color: #646970;">
                                            <?php echo esc_html(implode(' → ', array_slice($station_names, 0, 3))); ?>
                                            <?php if (count($station_names) > 3): ?>
                                                ... (+<?php echo esc_html(count($station_names) - 3); ?>)
                                            <?php endif; ?>
                                        </span>
                                    </span>
                                <?php else: ?>
                                    <span class="description"><?php esc_html_e('No stations added', 'museum-railway-timetable'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($services_count_for_route > 0): ?>
                                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=mrt_service&meta_key=mrt_service_route_id&meta_value=' . $route->ID)); ?>">
                                        <?php echo esc_html($services_count_for_route); ?> <?php esc_html_e('trip(s)', 'museum-railway-timetable'); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="description"><?php esc_html_e('Not used yet', 'museum-railway-timetable'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_post_link($route->ID)); ?>" class="button button-small">
                                    <?php esc_html_e('Edit', 'museum-railway-timetable'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="mrt-guide-section">
            <h2><?php esc_html_e('Quick Actions', 'museum-railway-timetable'); ?></h2>
            <div class="mrt-quick-actions">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=mrt_station')); ?>" class="button button-primary mrt-quick-action-button">
                    <strong><?php esc_html_e('➕ Add Station', 'museum-railway-timetable'); ?></strong>
                    <span><?php esc_html_e('Create a new station', 'museum-railway-timetable'); ?></span>
                </a>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=mrt_route')); ?>" class="button button-primary mrt-quick-action-button">
                    <strong><?php esc_html_e('➕ Add Route', 'museum-railway-timetable'); ?></strong>
                    <span><?php esc_html_e('Create a new route with stations', 'museum-railway-timetable'); ?></span>
                </a>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=mrt_timetable')); ?>" class="button button-primary mrt-quick-action-button">
                    <strong><?php esc_html_e('➕ Add Timetable', 'museum-railway-timetable'); ?></strong>
                    <span><?php esc_html_e('Create a new timetable with dates', 'museum-railway-timetable'); ?></span>
                </a>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=mrt_station&mrt_view=overview')); ?>" class="button mrt-quick-action-button">
                    <strong><?php esc_html_e('📊 Stations Overview', 'museum-railway-timetable'); ?></strong>
                    <span><?php esc_html_e('View all stations with statistics', 'museum-railway-timetable'); ?></span>
                </a>
            </div>
        </div>
        
        <!-- Settings Form -->
        <div class="mrt-settings-section">
            <h2><?php esc_html_e('Settings', 'museum-railway-timetable'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('mrt_group');
                do_settings_sections('mrt_settings');
                submit_button();
                ?>
            </form>
        </div>
        
        <!-- Quick Start Guide (Collapsible) -->
        <div class="mrt-guide-section">
            <h2 onclick="jQuery(this).next().slideToggle();">
                <?php esc_html_e('📖 Quick Start Guide', 'museum-railway-timetable'); ?>
                <span class="mrt-guide-toggle">(<?php esc_html_e('Click to expand', 'museum-railway-timetable'); ?>)</span>
            </h2>
            <div class="mrt-guide-content">
                <p><strong><?php esc_html_e('Recommended workflow:', 'museum-railway-timetable'); ?></strong></p>
                <ol>
                    <li><strong><?php esc_html_e('Stations', 'museum-railway-timetable'); ?></strong> - <?php esc_html_e('Create all stations where trains stop', 'museum-railway-timetable'); ?></li>
                    <li><strong><?php esc_html_e('Routes', 'museum-railway-timetable'); ?></strong> - <?php esc_html_e('Create routes and add stations in order (use ↑ ↓ buttons to reorder)', 'museum-railway-timetable'); ?></li>
                    <li><strong><?php esc_html_e('Timetables', 'museum-railway-timetable'); ?></strong> - <?php esc_html_e('Create timetables and add dates (YYYY-MM-DD) when they apply', 'museum-railway-timetable'); ?></li>
                    <li><strong><?php esc_html_e('Add Trips', 'museum-railway-timetable'); ?></strong> - <?php esc_html_e('In Timetable edit screen, use "Trips (Services)" meta box. Select Route, Train Type, Direction, then click "Add Trip"', 'museum-railway-timetable'); ?></li>
                    <li><strong><?php esc_html_e('Set Train Number', 'museum-railway-timetable'); ?></strong> - <?php esc_html_e('When editing a trip (Service), enter the train number (e.g., 71, 91, 73) in the "Train Number" field. This will be displayed in timetables instead of the service ID.', 'museum-railway-timetable'); ?></li>
                    <li><strong><?php esc_html_e('Configure Stop Times', 'museum-railway-timetable'); ?></strong> - <?php esc_html_e('Click "Edit" on any trip to set arrival/departure times for each station. Use P/A symbols for pickup/dropoff restrictions, and leave times empty for "X" (stops but time not specified).', 'museum-railway-timetable'); ?></li>
                </ol>
                <p class="mrt-tip-box">
                    <strong><?php esc_html_e('💡 Tip:', 'museum-railway-timetable'); ?></strong> <?php esc_html_e('Trips are automatically named based on Route + Direction. You don\'t need to enter a name manually!', 'museum-railway-timetable'); ?>
                </p>
            </div>
        </div>
        
        <div class="mrt-guide-section">
            <h2><?php esc_html_e('Shortcodes', 'museum-railway-timetable'); ?></h2>
            <p><?php esc_html_e('Use these shortcodes to display timetables on your pages and posts.', 'museum-railway-timetable'); ?></p>
            
            <div class="mrt-mt-1">
                <h3 class="mrt-section-heading">1. <?php esc_html_e('Month View', 'museum-railway-timetable'); ?></h3>
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
                <pre class="mrt-code-block">[museum_timetable_month month="2025-06" train_type="steam" show_counts="1"]</pre>
            </div>
            
            <div class="mrt-mt-1">
                <h3>4. <?php esc_html_e('Timetable Overview', 'museum-railway-timetable'); ?></h3>
                <p><code>[museum_timetable_overview timetable_id="123"]</code></p>
                <p class="description">
                    <?php esc_html_e('Displays a complete timetable overview grouped by route and direction, showing all trips with train types and times. Similar to traditional printed timetables.', 'museum-railway-timetable'); ?>
                </p>
                <p class="description mrt-description-mt-small">
                    <strong><?php esc_html_e('What it shows:', 'museum-railway-timetable'); ?></strong><br>
                    • <?php esc_html_e('All trips (services) in the timetable', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Grouped by route and direction (e.g., "Från Uppsala Ö Till Marielund")', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Train types with icons for each trip (🚂 Ångtåg, 🚌 Rälsbuss, 🚃 Dieseltåg)', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Train numbers displayed prominently (or service ID as fallback)', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Arrival/departure times in HH.MM format for each station', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Symbols: P (pickup only), A (dropoff only), X (no time), | (passes without stopping)', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Transfer information showing connecting trains at destination stations', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Direction arrows (↓) for first and last stations', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Special styling for express services (yellow vertical bar)', 'museum-railway-timetable'); ?>
                </p>
                <p class="description mrt-description-mt-small">
                    <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                    • <code>timetable_id</code> - <?php esc_html_e('Timetable post ID (recommended).', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e('How to find it:', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;1. <?php esc_html_e('Go to Railway Timetable → Timetables', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;2. <?php esc_html_e('Look in the "ID" column - the number is displayed there', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;3. <?php esc_html_e('Or click "Edit" and look at the URL - the number after "post=" is the ID', 'museum-railway-timetable'); ?><br>
                    • <code>timetable</code> - <?php esc_html_e('Timetable name (alternative to timetable_id). Use the exact title of the timetable.', 'museum-railway-timetable'); ?>
                </p>
                <p><strong><?php esc_html_e('Examples:', 'museum-railway-timetable'); ?></strong></p>
                <pre class="mrt-code-block">[museum_timetable_overview timetable_id="123"]</pre>
                <p class="description mrt-description-mt-small mrt-description-small-text">
                    <?php esc_html_e('Or use the timetable name:', 'museum-railway-timetable'); ?>
                </p>
                <pre class="mrt-code-block">[museum_timetable_overview timetable="Sommar 2025"]</pre>
                <p class="description mrt-description-mt-small mrt-tip-box">
                    <strong><?php esc_html_e('Tip:', 'museum-railway-timetable'); ?></strong> <?php esc_html_e('You can preview how the timetable will look in the "Timetable Overview" meta box when editing a timetable in the admin.', 'museum-railway-timetable'); ?>
                </p>
            </div>
            
            <div class="mrt-mt-1">
                <h3>3. <?php esc_html_e('Journey Planner (Reseplanerare)', 'museum-railway-timetable'); ?></h3>
                <p><code>[museum_journey_planner]</code></p>
                <p class="description">
                    <?php esc_html_e('Displays a journey planner where users can search for connections between two stations on a specific date.', 'museum-railway-timetable'); ?><br>
                    <strong><?php esc_html_e('What it shows:', 'museum-railway-timetable'); ?></strong><br>
                    • <?php esc_html_e('Dropdown to select departure station (From)', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Dropdown to select arrival station (To)', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Date picker (defaults to today\'s date)', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Search button to find connections', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Results table showing all available connections with departure/arrival times, train types, and service information', 'museum-railway-timetable'); ?>
                </p>
                <p class="description mrt-description-mt-small">
                    <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                    • <code>default_date</code> - <?php esc_html_e('Default date in YYYY-MM-DD format (optional, defaults to today)', 'museum-railway-timetable'); ?>
                </p>
                <p class="description mrt-description-mt-small">
                    <strong><?php esc_html_e('How it works:', 'museum-railway-timetable'); ?></strong><br>
                    • <?php esc_html_e('Users select a departure station and arrival station', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Users can choose a date (defaults to today)', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('After clicking "Search", the planner finds all services that:', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;1. <?php esc_html_e('Run on the selected date', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;2. <?php esc_html_e('Stop at both the departure and arrival stations', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;3. <?php esc_html_e('Have the departure station before the arrival station in the route sequence', 'museum-railway-timetable'); ?><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;4. <?php esc_html_e('Allow pickup at departure station and dropoff at arrival station', 'museum-railway-timetable'); ?><br>
                    • <?php esc_html_e('Results are sorted by departure time', 'museum-railway-timetable'); ?>
                </p>
                <p><strong><?php esc_html_e('Example:', 'museum-railway-timetable'); ?></strong></p>
                <pre class="mrt-code-block">[museum_journey_planner]</pre>
                <p class="description mrt-description-mt-small">
                    <?php esc_html_e('Or with a default date:', 'museum-railway-timetable'); ?>
                </p>
                <pre class="mrt-code-block">[museum_journey_planner default_date="2025-06-15"]</pre>
                <p class="description mrt-description-mt-small mrt-tip-box">
                    <strong><?php esc_html_e('Tip:', 'museum-railway-timetable'); ?></strong> <?php esc_html_e('The journey planner automatically shows today\'s date by default, but users can select any date to check future connections. Make sure you have created timetables with dates and services with stop times for the dates you want to support.', 'museum-railway-timetable'); ?>
                </p>
            </div>
        </div>
        
        <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
        <div class="mrt-warning-box">
            <h2><?php esc_html_e('Development Tools', 'museum-railway-timetable'); ?></h2>
            <p><?php esc_html_e('These tools are only available when WP_DEBUG is enabled.', 'museum-railway-timetable'); ?></p>
            <form method="post" action="" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to delete ALL timetable data? This cannot be undone!', 'museum-railway-timetable')); ?>');">
                <?php wp_nonce_field('mrt_clear_db', 'mrt_clear_db_nonce'); ?>
                <input type="hidden" name="mrt_action" value="clear_db" />
                <p>
                    <button type="submit" class="button button-secondary mrt-danger-button">
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
    $timetables = get_posts(['post_type' => 'mrt_timetable', 'posts_per_page' => -1, 'fields' => 'ids']);
    
    foreach ($stations as $id) {
        wp_delete_post($id, true);
    }
    foreach ($services as $id) {
        wp_delete_post($id, true);
    }
    foreach ($routes as $id) {
        wp_delete_post($id, true);
    }
    foreach ($timetables as $id) {
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
