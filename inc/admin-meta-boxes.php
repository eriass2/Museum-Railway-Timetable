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
    
    // Timetable services meta box (to manage trips within timetable)
    add_meta_box(
        'mrt_timetable_services',
        __('Trips (Services)', 'museum-railway-timetable'),
        'MRT_render_timetable_services_box',
        'mrt_timetable',
        'normal',
        'default'
    );
    
    // Timetable overview preview
    add_meta_box(
        'mrt_timetable_overview',
        __('Timetable Overview', 'museum-railway-timetable'),
        'MRT_render_timetable_overview_box',
        'mrt_timetable',
        'normal',
        'low'
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
    <div class="mrt-info-box">
        <p><strong><?php esc_html_e('💡 What is a Station?', 'museum-railway-timetable'); ?></strong></p>
        <p><?php esc_html_e('A station is a physical location where trains can stop. Stations are used in Routes and Stop Times to define where trains travel and when they arrive/depart.', 'museum-railway-timetable'); ?></p>
    </div>
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
    <div class="mrt-info-box">
        <p><strong><?php esc_html_e('💡 What is a Route?', 'museum-railway-timetable'); ?></strong></p>
        <p><?php esc_html_e('A route defines which stations trains travel between and in what order. When you create a trip (service), you select a route, and all stations on that route become available for configuring stop times.', 'museum-railway-timetable'); ?></p>
        <p style="margin-top: 0.5rem;"><strong><?php esc_html_e('How to use:', 'museum-railway-timetable'); ?></strong></p>
        <ol style="margin: 0.5rem 0 0 1.5rem; line-height: 1.6;">
            <li><?php esc_html_e('Give your route a descriptive name, e.g., "Hultsfred → Västervik" or "Main Line"', 'museum-railway-timetable'); ?></li>
            <li><?php esc_html_e('Add stations in the order they appear on the route using the dropdown below', 'museum-railway-timetable'); ?></li>
            <li><?php esc_html_e('Use ↑ ↓ buttons to reorder stations if needed', 'museum-railway-timetable'); ?></li>
            <li><?php esc_html_e('When creating a trip in a Timetable, select this route to automatically get all its stations', 'museum-railway-timetable'); ?></li>
        </ol>
    </div>
    <div id="mrt-route-stations-container">
        <table class="widefat striped" id="mrt-route-stations-table">
            <thead>
                <tr>
                    <th class="mrt-col-order-60"><?php esc_html_e('Order', 'museum-railway-timetable'); ?></th>
                    <th><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                    <th class="mrt-col-actions-200"><?php esc_html_e('Actions', 'museum-railway-timetable'); ?></th>
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
                                <button type="button" class="button button-small mrt-move-route-station-up" data-station-id="<?php echo esc_attr($station_id); ?>" title="<?php esc_attr_e('Move up', 'museum-railway-timetable'); ?>" <?php echo $index === 0 ? 'disabled' : ''; ?>>↑</button>
                                <button type="button" class="button button-small mrt-move-route-station-down" data-station-id="<?php echo esc_attr($station_id); ?>" title="<?php esc_attr_e('Move down', 'museum-railway-timetable'); ?>" <?php echo $index === count($route_stations) - 1 ? 'disabled' : ''; ?>>↓</button>
                                <button type="button" class="button button-small mrt-remove-route-station" data-station-id="<?php echo esc_attr($station_id); ?>"><?php esc_html_e('Remove', 'museum-railway-timetable'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr class="mrt-new-route-station-row mrt-new-row">
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
    <div class="mrt-info-box">
        <p><strong><?php esc_html_e('💡 What is a Timetable?', 'museum-railway-timetable'); ?></strong></p>
        <p><?php esc_html_e('A timetable defines which days (dates) trains run. Add one or more dates (YYYY-MM-DD) when this timetable applies. Then add trips (services) to this timetable - those trips will run on all the dates you specify here.', 'museum-railway-timetable'); ?></p>
    </div>
    <table class="form-table">
        <tr>
            <th><label for="mrt_timetable_dates"><?php esc_html_e('Dates', 'museum-railway-timetable'); ?></label></th>
            <td>
                <div id="mrt-timetable-dates-container">
                    <?php foreach ($dates as $index => $date): ?>
                        <div class="mrt-date-row">
                            <input type="date" name="mrt_timetable_dates[]" value="<?php echo esc_attr($date); ?>" class="mrt-meta-field" required />
                            <?php if ($index > 0): ?>
                                <button type="button" class="button mrt-remove-date mrt-date-remove-button"><?php esc_html_e('Remove', 'museum-railway-timetable'); ?></button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="mrt-add-date" class="button mrt-add-date-button"><?php esc_html_e('Add Date', 'museum-railway-timetable'); ?></button>
                <p class="description"><?php esc_html_e('Add dates in YYYY-MM-DD format. Example: 2025-06-15. You can add multiple dates (e.g., all weekends in a month).', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
    </table>
    <script>
    jQuery(document).ready(function($) {
        $('#mrt-add-date').on('click', function() {
            var newRow = $('<div class="mrt-date-row"><input type="date" name="mrt_timetable_dates[]" class="mrt-meta-field" required /> <button type="button" class="button mrt-remove-date mrt-date-remove-button"><?php echo esc_js(__('Remove', 'museum-railway-timetable')); ?></button></div>');
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
 * Render timetable services meta box (to manage trips within timetable)
 *
 * @param WP_Post $post Current post object (Timetable)
 */
function MRT_render_timetable_services_box($post) {
    // Get all services that belong to this timetable
    $services = get_posts([
        'post_type' => 'mrt_service',
        'posts_per_page' => -1,
        'meta_query' => [[
            'key' => 'mrt_service_timetable_id',
            'value' => $post->ID,
            'compare' => '=',
        ]],
        'orderby' => 'title',
        'order' => 'ASC',
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
    
    // Get all train types for dropdown
    $all_train_types = get_terms([
        'taxonomy' => 'mrt_train_type',
        'hide_empty' => false,
    ]);
    
    ?>
    <div id="mrt-timetable-services-container">
        <?php wp_nonce_field('mrt_timetable_services_nonce', 'mrt_timetable_services_nonce'); ?>
        <p class="description">
            <?php esc_html_e('Manage trips (services) for this timetable. Add, edit, or remove trips directly here.', 'museum-railway-timetable'); ?>
        </p>
        
        <table class="widefat striped" id="mrt-timetable-services-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Route', 'museum-railway-timetable'); ?></th>
                    <th class="mrt-col-train-type"><?php esc_html_e('Train Type', 'museum-railway-timetable'); ?></th>
                    <th class="mrt-col-direction"><?php esc_html_e('Direction', 'museum-railway-timetable'); ?></th>
                    <th class="mrt-col-actions"><?php esc_html_e('Actions', 'museum-railway-timetable'); ?></th>
                </tr>
            </thead>
            <tbody id="mrt-timetable-services-tbody">
                <?php foreach ($services as $service): 
                    $route_id = get_post_meta($service->ID, 'mrt_service_route_id', true);
                    $direction = get_post_meta($service->ID, 'mrt_direction', true);
                    $train_types = wp_get_post_terms($service->ID, 'mrt_train_type', ['fields' => 'ids']);
                    $train_type_id = !empty($train_types) ? $train_types[0] : 0;
                ?>
                    <tr data-service-id="<?php echo esc_attr($service->ID); ?>">
                        <td>
                            <?php 
                            if ($route_id) {
                                $route = get_post($route_id);
                                echo $route ? esc_html($route->post_title) : '—';
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($train_type_id) {
                                $train_type = get_term($train_type_id, 'mrt_train_type');
                                echo $train_type ? esc_html($train_type->name) : '—';
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($direction === 'dit') {
                                esc_html_e('Dit', 'museum-railway-timetable');
                            } elseif ($direction === 'från') {
                                esc_html_e('Från', 'museum-railway-timetable');
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg('timetable_id', $post->ID, get_edit_post_link($service->ID))); ?>" class="button button-small">
                                <?php esc_html_e('Edit', 'museum-railway-timetable'); ?>
                            </a>
                            <button type="button" class="button button-small mrt-delete-service-from-timetable" data-service-id="<?php echo esc_attr($service->ID); ?>">
                                <?php esc_html_e('Remove', 'museum-railway-timetable'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="mrt-new-service-row mrt-new-row">
                    <td>
                        <select id="mrt-new-service-route" class="mrt-meta-field mrt-full-width" required>
                            <option value=""><?php esc_html_e('— Select Route —', 'museum-railway-timetable'); ?></option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo esc_attr($route->ID); ?>"><?php echo esc_html($route->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select id="mrt-new-service-train-type" class="mrt-meta-field mrt-full-width">
                            <option value=""><?php esc_html_e('— Select —', 'museum-railway-timetable'); ?></option>
                            <?php foreach ($all_train_types as $train_type): ?>
                                <option value="<?php echo esc_attr($train_type->term_id); ?>"><?php echo esc_html($train_type->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select id="mrt-new-service-direction" class="mrt-meta-field mrt-full-width">
                            <option value=""><?php esc_html_e('— Select —', 'museum-railway-timetable'); ?></option>
                            <option value="dit"><?php esc_html_e('Dit', 'museum-railway-timetable'); ?></option>
                            <option value="från"><?php esc_html_e('Från', 'museum-railway-timetable'); ?></option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="button button-primary button-small" id="mrt-add-service-to-timetable" data-timetable-id="<?php echo esc_attr($post->ID); ?>">
                            <?php esc_html_e('Add Trip', 'museum-railway-timetable'); ?>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Render timetable overview preview box
 *
 * @param WP_Post $post Current post object (Timetable)
 */
function MRT_render_timetable_overview_box($post) {
    ?>
    <div class="mrt-timetable-overview-preview">
        <p class="description">
            <?php esc_html_e('Preview of how the timetable will look when displayed. Services are grouped by route and direction.', 'museum-railway-timetable'); ?>
        </p>
        <?php echo MRT_render_timetable_overview($post->ID); ?>
    </div>
    <?php
}

/**
 * Render service meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_service_meta_box($post) {
    wp_nonce_field('mrt_save_service_meta', 'mrt_service_meta_nonce');
    
    $timetable_id = get_post_meta($post->ID, 'mrt_service_timetable_id', true);
    
    // If editing from timetable, get timetable_id from URL parameter
    if (empty($timetable_id) && isset($_GET['timetable_id'])) {
        $timetable_id = intval($_GET['timetable_id']);
    }
    
    $route_id = get_post_meta($post->ID, 'mrt_service_route_id', true);
    $direction = get_post_meta($post->ID, 'mrt_direction', true);
    
    // Get all timetables for dropdown
    $timetables = get_posts([
        'post_type' => 'mrt_timetable',
        'posts_per_page' => -1,
        'orderby' => 'date',
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
    
    // Check if editing from timetable
    $editing_from_timetable = isset($_GET['timetable_id']) && intval($_GET['timetable_id']) === intval($timetable_id);
    
    ?>
    <div class="mrt-info-box">
        <p><strong><?php esc_html_e('💡 What is a Trip (Service)?', 'museum-railway-timetable'); ?></strong></p>
        <p><?php esc_html_e('A trip represents one train journey. It belongs to a Timetable (which defines which days it runs) and uses a Route (which defines which stations are available). After selecting a Route, you can configure Stop Times to set arrival/departure times for each station.', 'museum-railway-timetable'); ?></p>
    </div>
    <table class="form-table">
        <tr>
            <th><label for="mrt_service_timetable_id"><?php esc_html_e('Timetable', 'museum-railway-timetable'); ?></label></th>
            <td>
                <?php if ($editing_from_timetable): ?>
                    <input type="hidden" name="mrt_service_timetable_id" value="<?php echo esc_attr($timetable_id); ?>" />
                    <?php 
                    $current_timetable = get_post($timetable_id);
                    $timetable_dates = get_post_meta($timetable_id, 'mrt_timetable_dates', true);
                    if (!is_array($timetable_dates)) {
                        $old_date = get_post_meta($timetable_id, 'mrt_timetable_date', true);
                        $timetable_dates = !empty($old_date) ? [$old_date] : [];
                    }
                    $display = $current_timetable ? ($current_timetable->post_title ?: __('Timetable', 'museum-railway-timetable') . ' #' . $timetable_id) : '';
                    if (!empty($timetable_dates)) {
                        $date_count = count($timetable_dates);
                        $first_date = date_i18n(get_option('date_format'), strtotime($timetable_dates[0]));
                        if ($date_count === 1) {
                            $display .= ' (' . $first_date . ')';
                        } else {
                            $display .= ' (' . $first_date . ' + ' . ($date_count - 1) . ' ' . __('more', 'museum-railway-timetable') . ')';
                        }
                    }
                    ?>
                    <strong><?php echo esc_html($display); ?></strong>
                    <p class="description"><?php esc_html_e('This trip belongs to the timetable you are editing. To change the timetable, go back to the timetable and remove this trip, then add it to another timetable.', 'museum-railway-timetable'); ?></p>
                <?php else: ?>
                    <select name="mrt_service_timetable_id" id="mrt_service_timetable_id" class="mrt-meta-field" required>
                        <option value=""><?php esc_html_e('— Select Timetable —', 'museum-railway-timetable'); ?></option>
                        <?php foreach ($timetables as $timetable): 
                        $timetable_dates = get_post_meta($timetable->ID, 'mrt_timetable_dates', true);
                        if (!is_array($timetable_dates)) {
                            // Try to migrate from old single date field
                            $old_date = get_post_meta($timetable->ID, 'mrt_timetable_date', true);
                            $timetable_dates = !empty($old_date) ? [$old_date] : [];
                        }
                        $display = $timetable->post_title ?: __('Timetable', 'museum-railway-timetable') . ' #' . $timetable->ID;
                        if (!empty($timetable_dates)) {
                            $date_count = count($timetable_dates);
                            $first_date = date_i18n(get_option('date_format'), strtotime($timetable_dates[0]));
                            if ($date_count === 1) {
                                $display .= ' (' . $first_date . ')';
                            } else {
                                $display .= ' (' . $first_date . ' + ' . ($date_count - 1) . ' ' . __('more', 'museum-railway-timetable') . ')';
                            }
                        }
                    ?>
                        <option value="<?php echo esc_attr($timetable->ID); ?>" <?php selected($timetable_id, $timetable->ID); ?>><?php echo esc_html($display); ?></option>
                    <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('⚠️ Required: Select the timetable this trip belongs to. The timetable defines which days (dates) the trip runs.', 'museum-railway-timetable'); ?></p>
                <?php endif; ?>
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
                <p class="description"><?php esc_html_e('⚠️ Required: Select the route this trip runs on. After selecting a route and saving, you can configure Stop Times below. Example: "Hultsfred - Västervik" or "Main Line".', 'museum-railway-timetable'); ?></p>
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
            <div class="mrt-info-box">
                <p><strong><?php esc_html_e('💡 How to configure Stop Times:', 'museum-railway-timetable'); ?></strong></p>
                <ol>
                    <li><?php esc_html_e('Check "Stops here" for each station where the train stops', 'museum-railway-timetable'); ?></li>
                    <li><?php esc_html_e('Fill in Arrival and/or Departure times (HH:MM format, e.g., 09:15)', 'museum-railway-timetable'); ?></li>
                    <li><?php esc_html_e('Times can be left empty if the train stops but the time is not fixed', 'museum-railway-timetable'); ?></li>
                    <li><?php esc_html_e('Select Pickup/Dropoff if passengers can board/alight', 'museum-railway-timetable'); ?></li>
                    <li><?php esc_html_e('Click "Save Stop Times" at the bottom to save all changes', 'museum-railway-timetable'); ?></li>
                </ol>
            </div>
            <table class="widefat striped mrt-stoptimes-table">
                <thead>
                    <tr>
                        <th class="mrt-col-order"><?php esc_html_e('Order', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Station', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-stops-here"><?php esc_html_e('Stops here', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-time"><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-time"><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-pickup-dropoff"><?php esc_html_e('Pickup', 'museum-railway-timetable'); ?></th>
                        <th class="mrt-col-pickup-dropoff"><?php esc_html_e('Dropoff', 'museum-railway-timetable'); ?></th>
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
                                <td class="mrt-time-field <?php echo $stops_here ? '' : 'mrt-field-disabled-opacity'; ?>">
                                    <input type="text" class="mrt-arrival-time mrt-time-input" value="<?php echo $st ? esc_attr($st['arrival_time']) : ''; ?>" placeholder="<?php esc_attr_e('HH:MM', 'museum-railway-timetable'); ?>" pattern="[0-2][0-9]:[0-5][0-9]" <?php echo $stops_here ? '' : 'disabled'; ?> />
                                    <p class="description mrt-description-small"><?php esc_html_e('Leave empty if train stops but time is not fixed', 'museum-railway-timetable'); ?></p>
                                </td>
                                <td class="mrt-time-field <?php echo $stops_here ? '' : 'mrt-field-disabled-opacity'; ?>">
                                    <input type="text" class="mrt-departure-time mrt-time-input" value="<?php echo $st ? esc_attr($st['departure_time']) : ''; ?>" placeholder="<?php esc_attr_e('HH:MM', 'museum-railway-timetable'); ?>" pattern="[0-2][0-9]:[0-5][0-9]" <?php echo $stops_here ? '' : 'disabled'; ?> />
                                    <p class="description mrt-description-small"><?php esc_html_e('Leave empty if train stops but time is not fixed', 'museum-railway-timetable'); ?></p>
                                </td>
                                <td class="mrt-option-field <?php echo $stops_here ? '' : 'mrt-field-disabled-opacity'; ?>">
                                    <label>
                                        <input type="checkbox" class="mrt-pickup" <?php checked($st ? $st['pickup_allowed'] : true, 1); ?> <?php echo $stops_here ? '' : 'disabled'; ?> />
                                        <?php esc_html_e('Pickup', 'museum-railway-timetable'); ?>
                                    </label>
                                </td>
                                <td class="mrt-option-field <?php echo $stops_here ? '' : 'mrt-field-disabled-opacity'; ?>">
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
            <p class="mrt-mt-1">
                <button type="button" id="mrt-save-all-stoptimes" class="button button-primary" data-service-id="<?php echo esc_attr($post->ID); ?>">
                    <?php esc_html_e('Save Stop Times', 'museum-railway-timetable'); ?>
                </button>
                <span class="description mrt-ml-1">
                    <?php esc_html_e('Configure which stations the train stops at, then click "Save Stop Times" to save all changes.', 'museum-railway-timetable'); ?>
                </span>
            </p>
            <?php endif; ?>
        <?php else: ?>
            <p class="description mrt-description-error">
                <?php esc_html_e('Please select a Route in Service Details above first. Then you can configure which stations this service stops at.', 'museum-railway-timetable'); ?>
            </p>
        <?php endif; ?>
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
        echo '<p class="description mrt-mb-1">';
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

