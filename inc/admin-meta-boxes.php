<?php
/**
 * Custom meta boxes for Stations and Services
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Add meta boxes for stations and services
 */
add_action('add_meta_boxes', function() {
    // Station meta box
    add_meta_box(
        'mrt_station_details',
        __('Station Details', 'museum-railway-timetable'),
        'MRT_render_station_meta_box',
        'mrt_station',
        'normal',
        'high'
    );
    
    // Service meta boxes
    add_meta_box(
        'mrt_service_details',
        __('Service Details', 'museum-railway-timetable'),
        'MRT_render_service_meta_box',
        'mrt_service',
        'normal',
        'high'
    );
    
    add_meta_box(
        'mrt_service_stoptimes',
        __('Stop Times', 'museum-railway-timetable'),
        'MRT_render_service_stoptimes_box',
        'mrt_service',
        'normal',
        'default'
    );
    
    add_meta_box(
        'mrt_service_calendar',
        __('Calendar (Service Schedule)', 'museum-railway-timetable'),
        'MRT_render_service_calendar_box',
        'mrt_service',
        'normal',
        'default'
    );
});

/**
 * Render station meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_station_meta_box($post) {
    wp_nonce_field('mrt_save_station_meta', 'mrt_station_meta_nonce');
    
    $station_type = get_post_meta($post->ID, 'mrt_station_type', true);
    $lat = get_post_meta($post->ID, 'mrt_lat', true);
    $lng = get_post_meta($post->ID, 'mrt_lng', true);
    $display_order = get_post_meta($post->ID, 'mrt_display_order', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="mrt_station_type"><?php esc_html_e('Station Type', 'museum-railway-timetable'); ?></label></th>
            <td>
                <select name="mrt_station_type" id="mrt_station_type" class="mrt-meta-field">
                    <option value=""><?php esc_html_e('— Select —', 'museum-railway-timetable'); ?></option>
                    <option value="station" <?php selected($station_type, 'station'); ?>><?php esc_html_e('Station', 'museum-railway-timetable'); ?></option>
                    <option value="halt" <?php selected($station_type, 'halt'); ?>><?php esc_html_e('Halt', 'museum-railway-timetable'); ?></option>
                    <option value="depot" <?php selected($station_type, 'depot'); ?>><?php esc_html_e('Depot', 'museum-railway-timetable'); ?></option>
                    <option value="museum" <?php selected($station_type, 'museum'); ?>><?php esc_html_e('Museum', 'museum-railway-timetable'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Type of station (station, halt, depot, or museum).', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_lat"><?php esc_html_e('Latitude', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="number" name="mrt_lat" id="mrt_lat" value="<?php echo esc_attr($lat); ?>" step="any" class="mrt-meta-field" />
                <p class="description"><?php esc_html_e('Latitude coordinate (e.g., 57.486). Optional.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_lng"><?php esc_html_e('Longitude', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="number" name="mrt_lng" id="mrt_lng" value="<?php echo esc_attr($lng); ?>" step="any" class="mrt-meta-field" />
                <p class="description"><?php esc_html_e('Longitude coordinate (e.g., 15.842). Optional.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_display_order"><?php esc_html_e('Display Order', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="number" name="mrt_display_order" id="mrt_display_order" value="<?php echo esc_attr($display_order ?: 0); ?>" min="0" class="mrt-meta-field" />
                <p class="description"><?php esc_html_e('Order for sorting in lists (lower numbers appear first).', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save station meta box data
 *
 * @param int $post_id Post ID
 */
add_action('save_post_mrt_station', function($post_id) {
    // Check nonce
    if (!isset($_POST['mrt_station_meta_nonce']) || !wp_verify_nonce($_POST['mrt_station_meta_nonce'], 'mrt_save_station_meta')) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save meta fields
    if (isset($_POST['mrt_station_type'])) {
        $type = sanitize_text_field($_POST['mrt_station_type']);
        $allowed_types = ['station', 'halt', 'depot', 'museum', ''];
        if (in_array($type, $allowed_types, true)) {
            update_post_meta($post_id, 'mrt_station_type', $type);
        }
    }
    
    if (isset($_POST['mrt_lat'])) {
        $lat = sanitize_text_field($_POST['mrt_lat']);
        if ($lat === '') {
            delete_post_meta($post_id, 'mrt_lat');
        } else {
            update_post_meta($post_id, 'mrt_lat', floatval($lat));
        }
    }
    
    if (isset($_POST['mrt_lng'])) {
        $lng = sanitize_text_field($_POST['mrt_lng']);
        if ($lng === '') {
            delete_post_meta($post_id, 'mrt_lng');
        } else {
            update_post_meta($post_id, 'mrt_lng', floatval($lng));
        }
    }
    
    if (isset($_POST['mrt_display_order'])) {
        $order = intval($_POST['mrt_display_order']);
        update_post_meta($post_id, 'mrt_display_order', $order);
    }
});

/**
 * Render service meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_service_meta_box($post) {
    wp_nonce_field('mrt_save_service_meta', 'mrt_service_meta_nonce');
    
    $direction = get_post_meta($post->ID, 'mrt_direction', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="mrt_direction"><?php esc_html_e('Direction', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="text" name="mrt_direction" id="mrt_direction" value="<?php echo esc_attr($direction); ?>" class="mrt-meta-field" />
                <p class="description"><?php esc_html_e('Direction of the service (e.g., "Northbound", "Southbound"). Optional.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save service meta box data
 *
 * @param int $post_id Post ID
 */
add_action('save_post_mrt_service', function($post_id) {
    // Check nonce
    if (!isset($_POST['mrt_service_meta_nonce']) || !wp_verify_nonce($_POST['mrt_service_meta_nonce'], 'mrt_save_service_meta')) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save direction field
    if (isset($_POST['mrt_direction'])) {
        $direction = sanitize_text_field($_POST['mrt_direction']);
        if ($direction === '') {
            delete_post_meta($post_id, 'mrt_direction');
        } else {
            update_post_meta($post_id, 'mrt_direction', $direction);
        }
    }
});

/**
 * Render service stop times meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_service_stoptimes_box($post) {
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    
    // Get all stop times for this service
    $stoptimes = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE service_post_id = %d ORDER BY stop_sequence ASC",
        $post->ID
    ), ARRAY_A);
    
    // Get all stations for dropdown
    $stations = get_posts([
        'post_type' => 'mrt_station',
        'posts_per_page' => -1,
        'orderby' => ['meta_value_num' => 'ASC', 'title' => 'ASC'],
        'meta_key' => 'mrt_display_order',
        'fields' => 'all',
    ]);
    
    wp_nonce_field('mrt_stoptimes_nonce', 'mrt_stoptimes_nonce');
    ?>
    <div id="mrt-stoptimes-container">
        <p class="description"><?php esc_html_e('Click on any row to edit. Click "Add New" to add a new stop time.', 'museum-railway-timetable'); ?></p>
        <table class="widefat striped mrt-stoptimes-table">
            <thead>
                <tr>
                    <th style="width: 60px;"><?php esc_html_e('Seq', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                    <th style="width: 80px;"><?php esc_html_e('Pickup', 'museum-railway-timetable'); ?></th>
                    <th style="width: 80px;"><?php esc_html_e('Dropoff', 'museum-railway-timetable'); ?></th>
                    <th style="width: 120px;"><?php esc_html_e('Actions', 'museum-railway-timetable'); ?></th>
                </tr>
            </thead>
            <tbody id="mrt-stoptimes-tbody">
                <?php if (!empty($stoptimes)): ?>
                    <?php foreach ($stoptimes as $st): 
                        $station = get_post($st['station_post_id']);
                        $station_name = $station ? $station->post_title : '#' . $st['station_post_id'];
                    ?>
                        <tr class="mrt-stoptime-row" data-stoptime-id="<?php echo esc_attr($st['id']); ?>" data-service-id="<?php echo esc_attr($post->ID); ?>">
                            <td class="mrt-edit-field" data-field="sequence">
                                <span class="mrt-display"><?php echo esc_html($st['stop_sequence']); ?></span>
                                <input type="number" class="mrt-input" value="<?php echo esc_attr($st['stop_sequence']); ?>" min="1" style="display: none; width: 60px;" />
                            </td>
                            <td class="mrt-edit-field" data-field="station">
                                <span class="mrt-display"><?php echo esc_html($station_name); ?></span>
                                <select class="mrt-input" style="display: none; width: 100%;">
                                    <option value=""><?php esc_html_e('— Select —', 'museum-railway-timetable'); ?></option>
                                    <?php foreach ($stations as $station): ?>
                                        <option value="<?php echo esc_attr($station->ID); ?>" <?php selected($st['station_post_id'], $station->ID); ?>><?php echo esc_html($station->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="mrt-edit-field" data-field="arrival">
                                <span class="mrt-display"><?php echo esc_html($st['arrival_time'] ?: '—'); ?></span>
                                <input type="text" class="mrt-input" value="<?php echo esc_attr($st['arrival_time']); ?>" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" style="display: none; width: 100px;" />
                            </td>
                            <td class="mrt-edit-field" data-field="departure">
                                <span class="mrt-display"><?php echo esc_html($st['departure_time'] ?: '—'); ?></span>
                                <input type="text" class="mrt-input" value="<?php echo esc_attr($st['departure_time']); ?>" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" style="display: none; width: 100px;" />
                            </td>
                            <td class="mrt-edit-field" data-field="pickup">
                                <span class="mrt-display"><?php echo $st['pickup_allowed'] ? '✓' : '—'; ?></span>
                                <input type="checkbox" class="mrt-input" <?php checked($st['pickup_allowed'], 1); ?> style="display: none;" />
                            </td>
                            <td class="mrt-edit-field" data-field="dropoff">
                                <span class="mrt-display"><?php echo $st['dropoff_allowed'] ? '✓' : '—'; ?></span>
                                <input type="checkbox" class="mrt-input" <?php checked($st['dropoff_allowed'], 1); ?> style="display: none;" />
                            </td>
                            <td>
                                <button type="button" class="button button-small mrt-save-stoptime" data-id="<?php echo esc_attr($st['id']); ?>" style="display: none;"><?php esc_html_e('Save', 'museum-railway-timetable'); ?></button>
                                <button type="button" class="button button-small mrt-cancel-edit" style="display: none;"><?php esc_html_e('Cancel', 'museum-railway-timetable'); ?></button>
                                <button type="button" class="button button-small mrt-delete-stoptime" data-id="<?php echo esc_attr($st['id']); ?>"><?php esc_html_e('Delete', 'museum-railway-timetable'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- Add new row -->
                <tr class="mrt-stoptime-row mrt-new-row" data-stoptime-id="new" data-service-id="<?php echo esc_attr($post->ID); ?>" style="background: #f9f9f9;">
                    <td class="mrt-edit-field" data-field="sequence">
                        <input type="number" class="mrt-input" value="1" min="1" style="width: 60px;" />
                    </td>
                    <td class="mrt-edit-field" data-field="station">
                        <select class="mrt-input" style="width: 100%;">
                            <option value=""><?php esc_html_e('— Select Station —', 'museum-railway-timetable'); ?></option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?php echo esc_attr($station->ID); ?>"><?php echo esc_html($station->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="mrt-edit-field" data-field="arrival">
                        <input type="text" class="mrt-input" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" style="width: 100px;" />
                    </td>
                    <td class="mrt-edit-field" data-field="departure">
                        <input type="text" class="mrt-input" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" style="width: 100px;" />
                    </td>
                    <td class="mrt-edit-field" data-field="pickup">
                        <input type="checkbox" class="mrt-input" checked />
                    </td>
                    <td class="mrt-edit-field" data-field="dropoff">
                        <input type="checkbox" class="mrt-input" checked />
                    </td>
                    <td>
                        <button type="button" class="button button-primary button-small mrt-add-stoptime"><?php esc_html_e('Add', 'museum-railway-timetable'); ?></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Render service calendar meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_service_calendar_box($post) {
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_calendar';
    
    // Get all calendar entries for this service
    $calendars = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE service_post_id = %d ORDER BY start_date ASC, end_date ASC",
        $post->ID
    ), ARRAY_A);
    
    wp_nonce_field('mrt_calendar_nonce', 'mrt_calendar_nonce');
    ?>
    <div id="mrt-calendar-container">
        <p class="description"><?php esc_html_e('Click on any row to edit. Click "Add New" to add a new calendar entry.', 'museum-railway-timetable'); ?></p>
        <table class="widefat striped mrt-calendar-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date Range', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Days', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Include Dates', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Exclude Dates', 'museum-railway-timetable'); ?></th>
                    <th style="width: 120px;"><?php esc_html_e('Actions', 'museum-railway-timetable'); ?></th>
                </tr>
            </thead>
            <tbody id="mrt-calendar-tbody">
                <?php if (!empty($calendars)): ?>
                    <?php foreach ($calendars as $cal): 
                        $days = [];
                        if ($cal['mon']) $days[] = __('Mon', 'museum-railway-timetable');
                        if ($cal['tue']) $days[] = __('Tue', 'museum-railway-timetable');
                        if ($cal['wed']) $days[] = __('Wed', 'museum-railway-timetable');
                        if ($cal['thu']) $days[] = __('Thu', 'museum-railway-timetable');
                        if ($cal['fri']) $days[] = __('Fri', 'museum-railway-timetable');
                        if ($cal['sat']) $days[] = __('Sat', 'museum-railway-timetable');
                        if ($cal['sun']) $days[] = __('Sun', 'museum-railway-timetable');
                        $days_str = !empty($days) ? implode(', ', $days) : __('None', 'museum-railway-timetable');
                    ?>
                        <tr class="mrt-calendar-row" data-calendar-id="<?php echo esc_attr($cal['id']); ?>" data-service-id="<?php echo esc_attr($post->ID); ?>" style="cursor: pointer;">
                            <td class="mrt-edit-field" data-field="daterange">
                                <span class="mrt-display"><?php echo esc_html($cal['start_date'] . ' to ' . $cal['end_date']); ?></span>
                                <div class="mrt-input" style="display: none;">
                                    <input type="date" class="mrt-start-date" value="<?php echo esc_attr($cal['start_date']); ?>" style="width: 48%;" />
                                    <input type="date" class="mrt-end-date" value="<?php echo esc_attr($cal['end_date']); ?>" style="width: 48%;" />
                                </div>
                            </td>
                            <td class="mrt-edit-field" data-field="days">
                                <span class="mrt-display"><?php echo esc_html($days_str); ?></span>
                                <div class="mrt-input" style="display: none;">
                                    <label><input type="checkbox" class="mrt-day" data-day="mon" <?php checked($cal['mon'], 1); ?> /> <?php esc_html_e('Mon', 'museum-railway-timetable'); ?></label>
                                    <label><input type="checkbox" class="mrt-day" data-day="tue" <?php checked($cal['tue'], 1); ?> /> <?php esc_html_e('Tue', 'museum-railway-timetable'); ?></label>
                                    <label><input type="checkbox" class="mrt-day" data-day="wed" <?php checked($cal['wed'], 1); ?> /> <?php esc_html_e('Wed', 'museum-railway-timetable'); ?></label>
                                    <label><input type="checkbox" class="mrt-day" data-day="thu" <?php checked($cal['thu'], 1); ?> /> <?php esc_html_e('Thu', 'museum-railway-timetable'); ?></label>
                                    <label><input type="checkbox" class="mrt-day" data-day="fri" <?php checked($cal['fri'], 1); ?> /> <?php esc_html_e('Fri', 'museum-railway-timetable'); ?></label>
                                    <label><input type="checkbox" class="mrt-day" data-day="sat" <?php checked($cal['sat'], 1); ?> /> <?php esc_html_e('Sat', 'museum-railway-timetable'); ?></label>
                                    <label><input type="checkbox" class="mrt-day" data-day="sun" <?php checked($cal['sun'], 1); ?> /> <?php esc_html_e('Sun', 'museum-railway-timetable'); ?></label>
                                </div>
                            </td>
                            <td class="mrt-edit-field" data-field="include">
                                <span class="mrt-display"><?php echo esc_html($cal['include_dates'] ?: '—'); ?></span>
                                <input type="text" class="mrt-input" value="<?php echo esc_attr($cal['include_dates']); ?>" placeholder="YYYY-MM-DD, YYYY-MM-DD" style="display: none; width: 100%;" />
                            </td>
                            <td class="mrt-edit-field" data-field="exclude">
                                <span class="mrt-display"><?php echo esc_html($cal['exclude_dates'] ?: '—'); ?></span>
                                <input type="text" class="mrt-input" value="<?php echo esc_attr($cal['exclude_dates']); ?>" placeholder="YYYY-MM-DD, YYYY-MM-DD" style="display: none; width: 100%;" />
                            </td>
                            <td>
                                <button type="button" class="button button-small mrt-save-calendar" data-id="<?php echo esc_attr($cal['id']); ?>" style="display: none;"><?php esc_html_e('Save', 'museum-railway-timetable'); ?></button>
                                <button type="button" class="button button-small mrt-cancel-edit" style="display: none;"><?php esc_html_e('Cancel', 'museum-railway-timetable'); ?></button>
                                <button type="button" class="button button-small mrt-delete-calendar" data-id="<?php echo esc_attr($cal['id']); ?>"><?php esc_html_e('Delete', 'museum-railway-timetable'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- Add new row -->
                <tr class="mrt-calendar-row mrt-new-row" data-calendar-id="new" data-service-id="<?php echo esc_attr($post->ID); ?>" style="background: #f9f9f9;">
                    <td class="mrt-edit-field" data-field="daterange">
                        <div class="mrt-input">
                            <input type="date" class="mrt-start-date" style="width: 48%;" />
                            <input type="date" class="mrt-end-date" style="width: 48%;" />
                        </div>
                    </td>
                    <td class="mrt-edit-field" data-field="days">
                        <div class="mrt-input">
                            <label><input type="checkbox" class="mrt-day" data-day="mon" /> <?php esc_html_e('Mon', 'museum-railway-timetable'); ?></label>
                            <label><input type="checkbox" class="mrt-day" data-day="tue" /> <?php esc_html_e('Tue', 'museum-railway-timetable'); ?></label>
                            <label><input type="checkbox" class="mrt-day" data-day="wed" /> <?php esc_html_e('Wed', 'museum-railway-timetable'); ?></label>
                            <label><input type="checkbox" class="mrt-day" data-day="thu" /> <?php esc_html_e('Thu', 'museum-railway-timetable'); ?></label>
                            <label><input type="checkbox" class="mrt-day" data-day="fri" /> <?php esc_html_e('Fri', 'museum-railway-timetable'); ?></label>
                            <label><input type="checkbox" class="mrt-day" data-day="sat" /> <?php esc_html_e('Sat', 'museum-railway-timetable'); ?></label>
                            <label><input type="checkbox" class="mrt-day" data-day="sun" /> <?php esc_html_e('Sun', 'museum-railway-timetable'); ?></label>
                        </div>
                    </td>
                    <td class="mrt-edit-field" data-field="include">
                        <input type="text" class="mrt-input" placeholder="YYYY-MM-DD, YYYY-MM-DD" style="width: 100%;" />
                    </td>
                    <td class="mrt-edit-field" data-field="exclude">
                        <input type="text" class="mrt-input" placeholder="YYYY-MM-DD, YYYY-MM-DD" style="width: 100%;" />
                    </td>
                    <td>
                        <button type="button" class="button button-primary button-small mrt-add-calendar"><?php esc_html_e('Add', 'museum-railway-timetable'); ?></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Remove editor support for all CPTs (only title needed, fields handled by meta boxes)
 */
add_action('init', function() {
    remove_post_type_support('mrt_station', 'editor');
    remove_post_type_support('mrt_service', 'editor');
    remove_post_type_support('mrt_route', 'editor');
}, 20);

/**
 * Explicitly disable Gutenberg/block editor for all CPTs
 */
add_filter('use_block_editor_for_post_type', function($use_block_editor, $post_type) {
    if (in_array($post_type, ['mrt_station', 'mrt_service', 'mrt_route'], true)) {
        return false;
    }
    return $use_block_editor;
}, 10, 2);

/**
 * Remove description field from train type taxonomy (only name needed)
 */
add_action('admin_head', function() {
    $screen = get_current_screen();
    if ($screen && ($screen->taxonomy === 'mrt_train_type')) {
        echo '<style>
            .term-description-wrap,
            .form-field.term-description-wrap,
            tr.term-description-wrap {
                display: none !important;
            }
        </style>';
    }
});

// Hide description column in taxonomy list table
add_filter('manage_edit-mrt_train_type_columns', function($columns) {
    if (isset($columns['description'])) {
        unset($columns['description']);
    }
    return $columns;
});

