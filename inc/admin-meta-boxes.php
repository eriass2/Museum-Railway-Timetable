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
        <table class="widefat striped mrt-stoptimes-table">
            <thead>
                <tr>
                    <th style="width: 60px;"><?php esc_html_e('Seq', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                    <th style="width: 80px;"><?php esc_html_e('Pickup', 'museum-railway-timetable'); ?></th>
                    <th style="width: 80px;"><?php esc_html_e('Dropoff', 'museum-railway-timetable'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('Actions', 'museum-railway-timetable'); ?></th>
                </tr>
            </thead>
            <tbody id="mrt-stoptimes-tbody">
                <?php if (empty($stoptimes)): ?>
                    <tr class="mrt-empty-row">
                        <td colspan="7" class="mrt-none"><?php esc_html_e('No stop times defined. Add one below.', 'museum-railway-timetable'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($stoptimes as $st): 
                        $station = get_post($st['station_post_id']);
                        $station_name = $station ? $station->post_title : '#' . $st['station_post_id'];
                    ?>
                        <tr data-stoptime-id="<?php echo esc_attr($st['id']); ?>">
                            <td><?php echo esc_html($st['stop_sequence']); ?></td>
                            <td><?php echo esc_html($station_name); ?></td>
                            <td><?php echo esc_html($st['arrival_time'] ?: '—'); ?></td>
                            <td><?php echo esc_html($st['departure_time'] ?: '—'); ?></td>
                            <td><?php echo $st['pickup_allowed'] ? '✓' : '—'; ?></td>
                            <td><?php echo $st['dropoff_allowed'] ? '✓' : '—'; ?></td>
                            <td>
                                <button type="button" class="button button-small mrt-edit-stoptime" data-id="<?php echo esc_attr($st['id']); ?>"><?php esc_html_e('Edit', 'museum-railway-timetable'); ?></button>
                                <button type="button" class="button button-small mrt-delete-stoptime" data-id="<?php echo esc_attr($st['id']); ?>"><?php esc_html_e('Delete', 'museum-railway-timetable'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="mrt-add-stoptime-form" style="margin-top: 1rem; padding: 1rem; background: #f9f9f9; border: 1px solid #ddd;">
            <h4><?php esc_html_e('Add Stop Time', 'museum-railway-timetable'); ?></h4>
            <table class="form-table">
                <tr>
                    <th><label for="mrt-new-station"><?php esc_html_e('Station', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <select id="mrt-new-station" class="mrt-meta-field">
                            <option value=""><?php esc_html_e('— Select Station —', 'museum-railway-timetable'); ?></option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?php echo esc_attr($station->ID); ?>"><?php echo esc_html($station->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="mrt-new-sequence"><?php esc_html_e('Sequence', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <input type="number" id="mrt-new-sequence" min="1" value="1" class="mrt-meta-field" style="width: 80px;" />
                        <p class="description"><?php esc_html_e('Order along the route (1, 2, 3...).', 'museum-railway-timetable'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="mrt-new-arrival"><?php esc_html_e('Arrival Time', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <input type="text" id="mrt-new-arrival" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" class="mrt-meta-field" style="width: 100px;" />
                        <p class="description"><?php esc_html_e('HH:MM format. Leave empty for first stop.', 'museum-railway-timetable'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="mrt-new-departure"><?php esc_html_e('Departure Time', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <input type="text" id="mrt-new-departure" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" class="mrt-meta-field" style="width: 100px;" />
                        <p class="description"><?php esc_html_e('HH:MM format. Leave empty for last stop.', 'museum-railway-timetable'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Options', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <label><input type="checkbox" id="mrt-new-pickup" checked /> <?php esc_html_e('Pickup allowed', 'museum-railway-timetable'); ?></label><br />
                        <label><input type="checkbox" id="mrt-new-dropoff" checked /> <?php esc_html_e('Dropoff allowed', 'museum-railway-timetable'); ?></label>
                    </td>
                </tr>
            </table>
            <p>
                <button type="button" id="mrt-add-stoptime" class="button button-primary" data-service-id="<?php echo esc_attr($post->ID); ?>">
                    <?php esc_html_e('Add Stop Time', 'museum-railway-timetable'); ?>
                </button>
                <button type="button" id="mrt-cancel-stoptime" class="button" style="display: none;">
                    <?php esc_html_e('Cancel', 'museum-railway-timetable'); ?>
                </button>
            </p>
        </div>
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
        <table class="widefat striped mrt-calendar-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date Range', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Days', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Include Dates', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Exclude Dates', 'museum-railway-timetable'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('Actions', 'museum-railway-timetable'); ?></th>
                </tr>
            </thead>
            <tbody id="mrt-calendar-tbody">
                <?php if (empty($calendars)): ?>
                    <tr class="mrt-empty-row">
                        <td colspan="5" class="mrt-none"><?php esc_html_e('No calendar entries defined. Add one below.', 'museum-railway-timetable'); ?></td>
                    </tr>
                <?php else: ?>
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
                        <tr data-calendar-id="<?php echo esc_attr($cal['id']); ?>">
                            <td><?php echo esc_html($cal['start_date'] . ' to ' . $cal['end_date']); ?></td>
                            <td><?php echo esc_html($days_str); ?></td>
                            <td><?php echo esc_html($cal['include_dates'] ?: '—'); ?></td>
                            <td><?php echo esc_html($cal['exclude_dates'] ?: '—'); ?></td>
                            <td>
                                <button type="button" class="button button-small mrt-edit-calendar" data-id="<?php echo esc_attr($cal['id']); ?>"><?php esc_html_e('Edit', 'museum-railway-timetable'); ?></button>
                                <button type="button" class="button button-small mrt-delete-calendar" data-id="<?php echo esc_attr($cal['id']); ?>"><?php esc_html_e('Delete', 'museum-railway-timetable'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="mrt-add-calendar-form" style="margin-top: 1rem; padding: 1rem; background: #f9f9f9; border: 1px solid #ddd;">
            <h4><?php esc_html_e('Add Calendar Entry', 'museum-railway-timetable'); ?></h4>
            <table class="form-table">
                <tr>
                    <th><label for="mrt-new-start-date"><?php esc_html_e('Start Date', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <input type="date" id="mrt-new-start-date" class="mrt-meta-field" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="mrt-new-end-date"><?php esc_html_e('End Date', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <input type="date" id="mrt-new-end-date" class="mrt-meta-field" required />
                    </td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Days of Week', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <label><input type="checkbox" id="mrt-new-mon" /> <?php esc_html_e('Monday', 'museum-railway-timetable'); ?></label>
                        <label><input type="checkbox" id="mrt-new-tue" /> <?php esc_html_e('Tuesday', 'museum-railway-timetable'); ?></label>
                        <label><input type="checkbox" id="mrt-new-wed" /> <?php esc_html_e('Wednesday', 'museum-railway-timetable'); ?></label>
                        <label><input type="checkbox" id="mrt-new-thu" /> <?php esc_html_e('Thursday', 'museum-railway-timetable'); ?></label>
                        <label><input type="checkbox" id="mrt-new-fri" /> <?php esc_html_e('Friday', 'museum-railway-timetable'); ?></label>
                        <label><input type="checkbox" id="mrt-new-sat" /> <?php esc_html_e('Saturday', 'museum-railway-timetable'); ?></label>
                        <label><input type="checkbox" id="mrt-new-sun" /> <?php esc_html_e('Sunday', 'museum-railway-timetable'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><label for="mrt-new-include-dates"><?php esc_html_e('Include Dates', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <input type="text" id="mrt-new-include-dates" class="mrt-meta-field" placeholder="YYYY-MM-DD, YYYY-MM-DD" />
                        <p class="description"><?php esc_html_e('Comma-separated dates to include (overrides weekdays).', 'museum-railway-timetable'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="mrt-new-exclude-dates"><?php esc_html_e('Exclude Dates', 'museum-railway-timetable'); ?></label></th>
                    <td>
                        <input type="text" id="mrt-new-exclude-dates" class="mrt-meta-field" placeholder="YYYY-MM-DD, YYYY-MM-DD" />
                        <p class="description"><?php esc_html_e('Comma-separated dates to exclude.', 'museum-railway-timetable'); ?></p>
                    </td>
                </tr>
            </table>
            <p>
                <button type="button" id="mrt-add-calendar" class="button button-primary" data-service-id="<?php echo esc_attr($post->ID); ?>">
                    <?php esc_html_e('Add Calendar Entry', 'museum-railway-timetable'); ?>
                </button>
                <button type="button" id="mrt-cancel-calendar" class="button" style="display: none;">
                    <?php esc_html_e('Cancel', 'museum-railway-timetable'); ?>
                </button>
            </p>
        </div>
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

