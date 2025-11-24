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
    
    // Timetable meta box
    add_meta_box(
        'mrt_timetable_details',
        __('Timetable Details', 'museum-railway-timetable'),
        'MRT_render_timetable_meta_box',
        'mrt_timetable',
        'normal',
        'high'
    );
    
    // Route meta box
    add_meta_box(
        'mrt_route_details',
        __('Route Details', 'museum-railway-timetable'),
        'MRT_render_route_meta_box',
        'mrt_route',
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
                <input type="number" name="mrt_lat" id="mrt_lat" value="<?php echo esc_attr($lat); ?>" step="any" class="mrt-meta-field" placeholder="<?php esc_attr_e('e.g., 57.486', 'museum-railway-timetable'); ?>" />
                <p class="description"><?php esc_html_e('Latitude coordinate (e.g., 57.486). Optional.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_lng"><?php esc_html_e('Longitude', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="number" name="mrt_lng" id="mrt_lng" value="<?php echo esc_attr($lng); ?>" step="any" class="mrt-meta-field" placeholder="<?php esc_attr_e('e.g., 15.842', 'museum-railway-timetable'); ?>" />
                <p class="description"><?php esc_html_e('Longitude coordinate (e.g., 15.842). Optional.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_display_order"><?php esc_html_e('Display Order', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="number" name="mrt_display_order" id="mrt_display_order" value="<?php echo esc_attr($display_order ?: 0); ?>" min="0" class="mrt-meta-field" placeholder="<?php esc_attr_e('e.g., 1, 2, 3', 'museum-railway-timetable'); ?>" />
                <p class="description"><?php esc_html_e('Order for sorting in lists (lower numbers appear first). Example: 1, 2, 3...', 'museum-railway-timetable'); ?></p>
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
 * Render route meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_route_meta_box($post) {
    wp_nonce_field('mrt_save_route_meta', 'mrt_route_meta_nonce');
    
    // Get stations on this route (stored as post meta array)
    $route_stations = get_post_meta($post->ID, 'mrt_route_stations', true);
    if (!is_array($route_stations)) {
        $route_stations = [];
    }
    
    // Get all stations for dropdown
    $all_stations = get_posts([
        'post_type' => 'mrt_station',
        'posts_per_page' => -1,
        'orderby' => ['meta_value_num' => 'ASC', 'title' => 'ASC'],
        'meta_key' => 'mrt_display_order',
        'fields' => 'all',
    ]);
    
    ?>
    <p class="description"><?php esc_html_e('Define which stations are on this route, in order. The route works in both directions.', 'museum-railway-timetable'); ?></p>
    <div id="mrt-route-stations-container">
        <table class="widefat striped" id="mrt-route-stations-table">
            <thead>
                <tr>
                    <th style="width: 60px;"><?php esc_html_e('Order', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('Actions', 'museum-railway-timetable'); ?></th>
                </tr>
            </thead>
            <tbody id="mrt-route-stations-tbody">
                <?php if (!empty($route_stations)): ?>
                    <?php foreach ($route_stations as $index => $station_id): 
                        $station = get_post($station_id);
                        if (!$station) continue;
                    ?>
                        <tr data-station-id="<?php echo esc_attr($station_id); ?>">
                            <td><?php echo esc_html($index + 1); ?></td>
                            <td><?php echo esc_html($station->post_title); ?></td>
                            <td>
                                <button type="button" class="button button-small mrt-remove-route-station" data-station-id="<?php echo esc_attr($station_id); ?>"><?php esc_html_e('Remove', 'museum-railway-timetable'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr class="mrt-new-route-station-row" style="background: #f9f9f9;">
                    <td><?php echo esc_html(count($route_stations) + 1); ?></td>
                    <td>
                        <select id="mrt-new-route-station" class="mrt-meta-field">
                            <option value=""><?php esc_html_e('— Select Station —', 'museum-railway-timetable'); ?></option>
                            <?php foreach ($all_stations as $station): ?>
                                <option value="<?php echo esc_attr($station->ID); ?>" <?php selected(in_array($station->ID, $route_stations)); ?>><?php echo esc_html($station->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="button button-primary button-small" id="mrt-add-route-station"><?php esc_html_e('Add', 'museum-railway-timetable'); ?></button>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="mrt_route_stations" id="mrt_route_stations" value="<?php echo esc_attr(implode(',', $route_stations)); ?>" />
    </div>
    <p class="description" style="margin-top: 1rem;">
        <?php esc_html_e('Example route name: "Hultsfred - Västervik" or "Main Line". Add stations in the order they appear on the route.', 'museum-railway-timetable'); ?>
    </p>
    <?php
}

/**
 * Save route meta box data
 *
 * @param int $post_id Post ID
 */
add_action('save_post_mrt_route', function($post_id) {
    // Check nonce
    if (!isset($_POST['mrt_route_meta_nonce']) || !wp_verify_nonce($_POST['mrt_route_meta_nonce'], 'mrt_save_route_meta')) {
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
    
    // Save route stations
    if (isset($_POST['mrt_route_stations'])) {
        $stations_str = sanitize_text_field($_POST['mrt_route_stations']);
        $stations = array_filter(array_map('intval', explode(',', $stations_str)));
        update_post_meta($post_id, 'mrt_route_stations', array_values($stations));
    }
});

/**
 * Render timetable meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_timetable_meta_box($post) {
    wp_nonce_field('mrt_save_timetable_meta', 'mrt_timetable_meta_nonce');
    
    $dates = get_post_meta($post->ID, 'mrt_timetable_dates', true);
    if (!is_array($dates)) {
        // Try to migrate from old single date field
        $old_date = get_post_meta($post->ID, 'mrt_timetable_date', true);
        $dates = !empty($old_date) ? [$old_date] : [date('Y-m-d')];
    }
    if (empty($dates)) {
        $dates = [date('Y-m-d')];
    }
    
    wp_enqueue_script('jquery');
    ?>
    <table class="form-table">
        <tr>
            <th><label for="mrt_timetable_dates"><?php esc_html_e('Dates', 'museum-railway-timetable'); ?></label></th>
            <td>
                <div id="mrt-timetable-dates-container">
                    <?php foreach ($dates as $index => $date): ?>
                        <div class="mrt-date-row" style="margin-bottom: 8px;">
                            <input type="date" name="mrt_timetable_dates[]" value="<?php echo esc_attr($date); ?>" class="mrt-meta-field" required />
                            <?php if ($index > 0): ?>
                                <button type="button" class="button mrt-remove-date" style="margin-left: 8px;"><?php esc_html_e('Remove', 'museum-railway-timetable'); ?></button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="mrt-add-date" class="button" style="margin-top: 8px;"><?php esc_html_e('Add Date', 'museum-railway-timetable'); ?></button>
                <p class="description"><?php esc_html_e('The dates (days) when this timetable applies (YYYY-MM-DD). This timetable can apply to multiple days and will contain multiple trips (services) for these days.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
    </table>
    <script>
    jQuery(document).ready(function($) {
        $('#mrt-add-date').on('click', function() {
            var newRow = $('<div class="mrt-date-row" style="margin-bottom: 8px;"><input type="date" name="mrt_timetable_dates[]" class="mrt-meta-field" required /> <button type="button" class="button mrt-remove-date" style="margin-left: 8px;"><?php echo esc_js(__('Remove', 'museum-railway-timetable')); ?></button></div>');
            $('#mrt-timetable-dates-container').append(newRow);
        });
        
        $(document).on('click', '.mrt-remove-date', function() {
            $(this).closest('.mrt-date-row').remove();
        });
    });
    </script>
    <?php
}

/**
 * Save timetable meta box data
 *
 * @param int $post_id Post ID
 */
add_action('save_post_mrt_timetable', function($post_id) {
    // Check nonce
    if (!isset($_POST['mrt_timetable_meta_nonce']) || !wp_verify_nonce($_POST['mrt_timetable_meta_nonce'], 'mrt_save_timetable_meta')) {
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
    
    // Save timetable dates (array)
    if (isset($_POST['mrt_timetable_dates']) && is_array($_POST['mrt_timetable_dates'])) {
        $dates = [];
        foreach ($_POST['mrt_timetable_dates'] as $date) {
            $date = sanitize_text_field($date);
            if (MRT_validate_date($date)) {
                $dates[] = $date;
            }
        }
        // Remove duplicates and sort
        $dates = array_unique($dates);
        sort($dates);
        if (!empty($dates)) {
            update_post_meta($post_id, 'mrt_timetable_dates', $dates);
            // Remove old single date field if it exists
            delete_post_meta($post_id, 'mrt_timetable_date');
        } else {
            delete_post_meta($post_id, 'mrt_timetable_dates');
        }
    }
});

/**
 * Render service meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_service_meta_box($post) {
    wp_nonce_field('mrt_save_service_meta', 'mrt_service_meta_nonce');
    
    $timetable_id = get_post_meta($post->ID, 'mrt_service_timetable_id', true);
    $route_id = get_post_meta($post->ID, 'mrt_service_route_id', true);
    $direction = get_post_meta($post->ID, 'mrt_direction', true);
    
    // Get all timetables for dropdown
    $timetables = get_posts([
        'post_type' => 'mrt_timetable',
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => 'mrt_timetable_date',
        'order' => 'DESC',
        'fields' => 'all',
    ]);
    
    // Get all routes for dropdown
    $routes = get_posts([
        'post_type' => 'mrt_route',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'all',
    ]);
    
    // Get train types for this service
    $train_types = wp_get_post_terms($post->ID, 'mrt_train_type', ['fields' => 'ids']);
    
    // Get all train types for dropdown
    $all_train_types = get_terms([
        'taxonomy' => 'mrt_train_type',
        'hide_empty' => false,
    ]);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="mrt_service_timetable_id"><?php esc_html_e('Timetable', 'museum-railway-timetable'); ?></label></th>
            <td>
                <select name="mrt_service_timetable_id" id="mrt_service_timetable_id" class="mrt-meta-field" required>
                    <option value=""><?php esc_html_e('— Select Timetable —', 'museum-railway-timetable'); ?></option>
                    <?php foreach ($timetables as $timetable): 
                        $timetable_date = get_post_meta($timetable->ID, 'mrt_timetable_date', true);
                        $display = $timetable->post_title;
                        if ($timetable_date) {
                            $display .= ' (' . date_i18n(get_option('date_format'), strtotime($timetable_date)) . ')';
                        }
                    ?>
                        <option value="<?php echo esc_attr($timetable->ID); ?>" <?php selected($timetable_id, $timetable->ID); ?>><?php echo esc_html($display); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Select the timetable (day) this trip belongs to. A timetable represents one day with multiple trips.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_service_route_id"><?php esc_html_e('Route', 'museum-railway-timetable'); ?></label></th>
            <td>
                <select name="mrt_service_route_id" id="mrt_service_route_id" class="mrt-meta-field" required>
                    <option value=""><?php esc_html_e('— Select Route —', 'museum-railway-timetable'); ?></option>
                    <?php foreach ($routes as $route): ?>
                        <option value="<?php echo esc_attr($route->ID); ?>" <?php selected($route_id, $route->ID); ?>><?php echo esc_html($route->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Select the route this service runs on. Example: "Hultsfred - Västervik" or "Main Line".', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_service_train_type"><?php esc_html_e('Train Type', 'museum-railway-timetable'); ?></label></th>
            <td>
                <select name="mrt_service_train_type" id="mrt_service_train_type" class="mrt-meta-field">
                    <option value=""><?php esc_html_e('— Select Train Type —', 'museum-railway-timetable'); ?></option>
                    <?php foreach ($all_train_types as $term): ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected(in_array($term->term_id, $train_types)); ?>><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Select the train type for this service. Example: "Steam", "Diesel", "Electric".', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_direction"><?php esc_html_e('Direction', 'museum-railway-timetable'); ?></label></th>
            <td>
                <select name="mrt_direction" id="mrt_direction" class="mrt-meta-field">
                    <option value=""><?php esc_html_e('— Select —', 'museum-railway-timetable'); ?></option>
                    <option value="dit" <?php selected($direction, 'dit'); ?>><?php esc_html_e('Dit', 'museum-railway-timetable'); ?></option>
                    <option value="från" <?php selected($direction, 'från'); ?>><?php esc_html_e('Från', 'museum-railway-timetable'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Direction of the service. Optional.', 'museum-railway-timetable'); ?></p>
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
    
    // Save timetable ID
    if (isset($_POST['mrt_service_timetable_id'])) {
        $timetable_id = intval($_POST['mrt_service_timetable_id']);
        if ($timetable_id > 0) {
            update_post_meta($post_id, 'mrt_service_timetable_id', $timetable_id);
        } else {
            delete_post_meta($post_id, 'mrt_service_timetable_id');
        }
    }
    
    // Save route field
    if (isset($_POST['mrt_service_route_id'])) {
        $route_id = intval($_POST['mrt_service_route_id']);
        if ($route_id > 0) {
            update_post_meta($post_id, 'mrt_service_route_id', $route_id);
        } else {
            delete_post_meta($post_id, 'mrt_service_route_id');
        }
    }
    
    // Save train type
    if (isset($_POST['mrt_service_train_type'])) {
        $train_type_id = intval($_POST['mrt_service_train_type']);
        if ($train_type_id > 0) {
            wp_set_object_terms($post_id, [$train_type_id], 'mrt_train_type');
        } else {
            wp_set_object_terms($post_id, [], 'mrt_train_type');
        }
    }
    
    // Save direction field (restricted to 'dit' or 'från')
    if (isset($_POST['mrt_direction'])) {
        $direction = sanitize_text_field($_POST['mrt_direction']);
        if ($direction === '' || !in_array($direction, ['dit', 'från'], true)) {
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
    
    // Get all stop times for this service (indexed by station_id for quick lookup)
    $stoptimes = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE service_post_id = %d ORDER BY stop_sequence ASC",
        $post->ID
    ), ARRAY_A);
    
    $stoptimes_by_station = [];
    foreach ($stoptimes as $st) {
        $stoptimes_by_station[$st['station_post_id']] = $st;
    }
    
    // Get service route
    $route_id = get_post_meta($post->ID, 'mrt_service_route_id', true);
    $route_stations = [];
    if ($route_id) {
        $route_stations = get_post_meta($route_id, 'mrt_route_stations', true);
        if (!is_array($route_stations)) {
            $route_stations = [];
        }
    }
    
    // Get stations on route
    $stations = [];
    if (!empty($route_stations)) {
        $stations = get_posts([
            'post_type' => 'mrt_station',
            'post__in' => $route_stations,
            'posts_per_page' => -1,
            'orderby' => 'post__in',
            'fields' => 'all',
        ]);
    }
    
    wp_nonce_field('mrt_stoptimes_nonce', 'mrt_stoptimes_nonce');
    ?>
    <div id="mrt-stoptimes-container">
        <?php if ($route_id && !empty($route_stations)): ?>
            <p class="description" style="color: #2271b1; font-weight: 600;">
                <?php esc_html_e('Configure which stations this service stops at and the times. Check "Stops here" for each station where the train stops.', 'museum-railway-timetable'); ?>
            </p>
            <table class="widefat striped mrt-stoptimes-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><?php esc_html_e('Order', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                        <th style="width: 100px;"><?php esc_html_e('Stops here', 'museum-railway-timetable'); ?></th>
                        <th style="width: 100px;"><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                        <th style="width: 100px;"><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                        <th style="width: 80px;"><?php esc_html_e('Pickup', 'museum-railway-timetable'); ?></th>
                        <th style="width: 80px;"><?php esc_html_e('Dropoff', 'museum-railway-timetable'); ?></th>
                    </tr>
                </thead>
                <tbody id="mrt-stoptimes-tbody">
                    <?php if (!empty($stations)): ?>
                        <?php foreach ($stations as $index => $station): 
                            $st = $stoptimes_by_station[$station->ID] ?? null;
                            $stops_here = $st !== null;
                            $sequence = $st ? $st['stop_sequence'] : ($index + 1);
                        ?>
                            <tr class="mrt-route-station-row" data-station-id="<?php echo esc_attr($station->ID); ?>" data-service-id="<?php echo esc_attr($post->ID); ?>" data-sequence="<?php echo esc_attr($sequence); ?>">
                                <td><?php echo esc_html($index + 1); ?></td>
                                <td><strong><?php echo esc_html($station->post_title); ?></strong></td>
                                <td>
                                    <input type="checkbox" class="mrt-stops-here" <?php checked($stops_here); ?> data-station-id="<?php echo esc_attr($station->ID); ?>" />
                                </td>
                                <td class="mrt-time-field" style="<?php echo $stops_here ? '' : 'opacity: 0.5;'; ?>">
                                    <input type="text" class="mrt-arrival-time" value="<?php echo $st ? esc_attr($st['arrival_time']) : ''; ?>" placeholder="<?php esc_attr_e('HH:MM', 'museum-railway-timetable'); ?>" pattern="[0-2][0-9]:[0-5][0-9]" style="width: 100px;" <?php echo $stops_here ? '' : 'disabled'; ?> />
                                    <p class="description" style="margin: 0; font-size: 11px; color: #666;"><?php esc_html_e('Leave empty if train stops but time is not fixed', 'museum-railway-timetable'); ?></p>
                                </td>
                                <td class="mrt-time-field" style="<?php echo $stops_here ? '' : 'opacity: 0.5;'; ?>">
                                    <input type="text" class="mrt-departure-time" value="<?php echo $st ? esc_attr($st['departure_time']) : ''; ?>" placeholder="<?php esc_attr_e('HH:MM', 'museum-railway-timetable'); ?>" pattern="[0-2][0-9]:[0-5][0-9]" style="width: 100px;" <?php echo $stops_here ? '' : 'disabled'; ?> />
                                    <p class="description" style="margin: 0; font-size: 11px; color: #666;"><?php esc_html_e('Leave empty if train stops but time is not fixed', 'museum-railway-timetable'); ?></p>
                                </td>
                                <td class="mrt-option-field" style="<?php echo $stops_here ? '' : 'opacity: 0.5;'; ?>">
                                    <label>
                                        <input type="checkbox" class="mrt-pickup" <?php checked($st ? $st['pickup_allowed'] : true, 1); ?> <?php echo $stops_here ? '' : 'disabled'; ?> />
                                        <?php esc_html_e('Pickup', 'museum-railway-timetable'); ?>
                                    </label>
                                </td>
                                <td class="mrt-option-field" style="<?php echo $stops_here ? '' : 'opacity: 0.5;'; ?>">
                                    <label>
                                        <input type="checkbox" class="mrt-dropoff" <?php checked($st ? $st['dropoff_allowed'] : true, 1); ?> <?php echo $stops_here ? '' : 'disabled'; ?> />
                                        <?php esc_html_e('Dropoff', 'museum-railway-timetable'); ?>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="mrt-none">
                                <?php esc_html_e('No route selected. Select a route in Service Details above to configure stop times.', 'museum-railway-timetable'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (!empty($stations)): ?>
            <p style="margin-top: 1rem;">
                <button type="button" id="mrt-save-all-stoptimes" class="button button-primary" data-service-id="<?php echo esc_attr($post->ID); ?>">
                    <?php esc_html_e('Save Stop Times', 'museum-railway-timetable'); ?>
                </button>
                <span class="description" style="margin-left: 1rem;">
                    <?php esc_html_e('Configure which stations the train stops at, then click "Save Stop Times" to save all changes.', 'museum-railway-timetable'); ?>
                </span>
            </p>
            <?php endif; ?>
        <?php else: ?>
            <p class="description" style="color: #d63638; font-weight: 600;">
                <?php esc_html_e('Please select a Route in Service Details above first. Then you can configure which stations this service stops at.', 'museum-railway-timetable'); ?>
            </p>
        <?php endif; ?>
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
 * Add help text for Route title field
 */
add_action('edit_form_after_title', function($post) {
    if ($post->post_type === 'mrt_route') {
        echo '<p class="description" style="margin-top: 0.5rem; margin-bottom: 1rem;">';
        esc_html_e('Example route name: "Hultsfred - Västervik" or "Main Line".', 'museum-railway-timetable');
        echo '</p>';
    }
});

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

