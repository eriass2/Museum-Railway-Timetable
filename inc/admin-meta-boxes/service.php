<?php
/**
 * Service meta box
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Setup hooks when editing service from timetable context
 *
 * @param int $timetable_id Timetable ID
 */
function MRT_service_meta_box_setup_editing_from_timetable($timetable_id) {
    add_action('admin_head', function() {
        global $post_type;
        if ($post_type === 'mrt_service' && isset($_GET['timetable_id'])) {
            echo '<style>#title, #title-prompt-text, #titlewrap { display: none !important; }</style>';
        }
    });
    add_action('edit_form_top', function() use ($timetable_id) {
        global $post_type;
        if ($post_type === 'mrt_service' && isset($_GET['timetable_id'])) {
            $timetable_edit_link = get_edit_post_link($timetable_id);
            if ($timetable_edit_link) {
                echo '<div class="mrt-alert mrt-alert-info mrt-info-box mrt-mb-1">';
                echo '<a href="' . esc_url($timetable_edit_link) . '" class="button mrt-back-button">← ' . esc_html__('Back to Timetable', 'museum-railway-timetable') . '</a>';
                echo '<span class="description">' . esc_html__('This trip belongs to a timetable. The title is automatically generated from Route + Destination.', 'museum-railway-timetable') . '</span>';
                echo '</div>';
            }
        }
    });
}

/**
 * Get available end stations for a route (for destination dropdown)
 *
 * @param int $route_id Route post ID
 * @return array [station_id => display_name]
 */
function MRT_get_service_available_end_stations($route_id) {
    $available = [];
    if (!$route_id) {
        return $available;
    }
    $end_stations = MRT_get_route_end_stations($route_id);
    $route_stations = get_post_meta($route_id, 'mrt_route_stations', true);
    if (!is_array($route_stations)) {
        $route_stations = [];
    }
    if ($end_stations['start'] > 0) {
        $s = get_post($end_stations['start']);
        if ($s) {
            $available[$end_stations['start']] = $s->post_title . ' (' . __('Start', 'museum-railway-timetable') . ')';
        }
    }
    if ($end_stations['end'] > 0) {
        $s = get_post($end_stations['end']);
        if ($s) {
            $available[$end_stations['end']] = $s->post_title . ' (' . __('End', 'museum-railway-timetable') . ')';
        }
    }
    foreach ($route_stations as $station_id) {
        if (!isset($available[$station_id])) {
            $s = get_post($station_id);
            if ($s) {
                $available[$station_id] = $s->post_title;
            }
        }
    }
    return $available;
}

/**
 * Render destination station field for service meta box
 *
 * @param int $route_id Route ID
 * @param int $end_station_id Selected end station ID
 */
function MRT_render_service_destination_field($route_id, $end_station_id) {
    $available_end_stations = MRT_get_service_available_end_stations($route_id);
    if (empty($available_end_stations)) {
        $all_stations = get_posts([
            'post_type' => 'mrt_station',
            'posts_per_page' => -1,
            'orderby' => ['meta_value_num' => 'ASC', 'title' => 'ASC'],
            'meta_key' => 'mrt_display_order',
            'fields' => 'all',
        ]);
        $available_end_stations = [];
        foreach ($all_stations as $s) {
            $available_end_stations[$s->ID] = $s->post_title;
        }
    }
    $stations = $available_end_stations;
    ?>
    <tr>
        <th><label for="mrt_service_end_station_id"><?php esc_html_e('Destination Station', 'museum-railway-timetable'); ?></label></th>
        <td>
            <select name="mrt_service_end_station_id" id="mrt_service_end_station_id" class="mrt-meta-field">
                <option value=""><?php esc_html_e('— Select Destination —', 'museum-railway-timetable'); ?></option>
                <?php foreach ($stations as $sid => $sname): ?>
                    <option value="<?php echo esc_attr($sid); ?>" <?php selected($end_station_id, $sid); ?>><?php echo esc_html($sname); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e('Select the destination station for this trip. The direction will be calculated automatically based on the route and destination.', 'museum-railway-timetable'); ?></p>
            <?php if ($end_station_id && $route_id):
                $calculated_direction = MRT_calculate_direction_from_end_station($route_id, $end_station_id);
                if ($calculated_direction): ?>
                <p class="description mrt-description-tertiary">
                    <strong><?php esc_html_e('Calculated direction:', 'museum-railway-timetable'); ?></strong>
                    <?php echo $calculated_direction === 'dit' ? esc_html__('Dit', 'museum-railway-timetable') : esc_html__('Från', 'museum-railway-timetable'); ?>
                </p>
                <?php endif;
            endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Get formatted timetable display label for dropdown
 *
 * @param int $timetable_id Timetable ID
 * @param WP_Post|null $timetable Timetable post (optional)
 * @return string Display label
 */
function MRT_get_timetable_display_label($timetable_id, $timetable = null) {
    $timetable = $timetable ?: get_post($timetable_id);
    $display = $timetable ? ($timetable->post_title ?: __('Timetable', 'museum-railway-timetable') . ' #' . $timetable_id) : '';
    $timetable_dates = MRT_get_timetable_dates($timetable_id);
    if (!empty($timetable_dates)) {
        $date_count = count($timetable_dates);
        $first_date = date_i18n(get_option('date_format'), strtotime($timetable_dates[0]));
        $display .= ($date_count === 1) ? ' (' . $first_date . ')' : ' (' . $first_date . ' + ' . ($date_count - 1) . ' ' . __('more', 'museum-railway-timetable') . ')';
    }
    return $display;
}

/**
 * Render service meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_service_meta_box($post) {
    wp_nonce_field('mrt_save_service_meta', 'mrt_service_meta_nonce');

    $timetable_id = get_post_meta($post->ID, 'mrt_service_timetable_id', true);
    if (empty($timetable_id) && isset($_GET['timetable_id'])) {
        $timetable_id = intval($_GET['timetable_id']);
    }

    $route_id = get_post_meta($post->ID, 'mrt_service_route_id', true);
    $end_station_id = get_post_meta($post->ID, 'mrt_service_end_station_id', true);

    $timetables = get_posts([
        'post_type' => 'mrt_timetable',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'fields' => 'all',
    ]);
    $routes = get_posts([
        'post_type' => 'mrt_route',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'all',
    ]);
    $train_types = wp_get_post_terms($post->ID, 'mrt_train_type', ['fields' => 'ids']);
    $all_train_types = get_terms(['taxonomy' => 'mrt_train_type', 'hide_empty' => false]);

    $editing_from_timetable = isset($_GET['timetable_id']) && intval($_GET['timetable_id']) === intval($timetable_id);
    if ($editing_from_timetable && $timetable_id) {
        MRT_service_meta_box_setup_editing_from_timetable($timetable_id);
    }

    ?>
    <div class="mrt-alert mrt-alert-info mrt-info-box">
        <p><strong><?php esc_html_e('💡 What is a Trip (Service)?', 'museum-railway-timetable'); ?></strong></p>
        <p><?php esc_html_e('A trip represents one train journey. It belongs to a Timetable (which defines which days it runs) and uses a Route (which defines which stations are available). After selecting a Route, you can configure Stop Times to set arrival/departure times for each station.', 'museum-railway-timetable'); ?></p>
    </div>
    <div class="mrt-box mrt-mt-1">
    <h3 class="mrt-section-heading"><?php esc_html_e('Trip Details', 'museum-railway-timetable'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="mrt_service_timetable_id"><?php esc_html_e('Timetable', 'museum-railway-timetable'); ?></label></th>
            <td>
                <?php if ($editing_from_timetable): ?>
                    <input type="hidden" name="mrt_service_timetable_id" value="<?php echo esc_attr($timetable_id); ?>" />
                    <strong><?php echo esc_html(MRT_get_timetable_display_label($timetable_id)); ?></strong>
                    <p class="description"><?php esc_html_e('This trip belongs to the timetable you are editing. To change the timetable, go back to the timetable and remove this trip, then add it to another timetable.', 'museum-railway-timetable'); ?></p>
                <?php else: ?>
                    <select name="mrt_service_timetable_id" id="mrt_service_timetable_id" class="mrt-meta-field" required>
                        <option value=""><?php esc_html_e('— Select Timetable —', 'museum-railway-timetable'); ?></option>
                        <?php foreach ($timetables as $timetable): ?>
                        <option value="<?php echo esc_attr($timetable->ID); ?>" <?php selected($timetable_id, $timetable->ID); ?>><?php echo esc_html(MRT_get_timetable_display_label($timetable->ID, $timetable)); ?></option>
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
                <p class="description"><?php esc_html_e('Select the default train type for this service. You can override this for specific dates below. Example: "Steam", "Diesel", "Electric".', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_service_number"><?php esc_html_e('Train Number', 'museum-railway-timetable'); ?></label></th>
            <td>
                <?php
                $service_number = get_post_meta($post->ID, 'mrt_service_number', true);
                ?>
                <input type="text" name="mrt_service_number" id="mrt_service_number" value="<?php echo esc_attr($service_number); ?>" class="mrt-meta-field" placeholder="<?php esc_attr_e('e.g., 71, 91, 73', 'museum-railway-timetable'); ?>" />
                <p class="description"><?php esc_html_e('Enter the train number displayed in timetables (e.g., 71, 91, 73). If left empty, the service ID will be used as fallback.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <?php
        // Get date-specific train types
        $train_types_by_date = get_post_meta($post->ID, 'mrt_service_train_types_by_date', true);
        if (!is_array($train_types_by_date)) {
            $train_types_by_date = [];
        }
        
        // Get timetable dates if timetable is set
        $timetable_dates = $timetable_id ? MRT_get_timetable_dates($timetable_id) : [];
        sort($timetable_dates);
        ?>
        <tr>
            <th><label><?php esc_html_e('Date-Specific Train Types', 'museum-railway-timetable'); ?></label></th>
            <td>
                <p class="description"><?php esc_html_e('Override the default train type for specific dates. Leave empty to use the default train type.', 'museum-railway-timetable'); ?></p>
                <?php if (empty($timetable_dates)): ?>
                    <p class="description mrt-description-error">
                        <?php esc_html_e('⚠️ Please select a timetable first to see available dates.', 'museum-railway-timetable'); ?>
                    </p>
                <?php else: ?>
                    <div id="mrt-train-types-by-date-container" class="mrt-train-types-container">
                        <?php foreach ($timetable_dates as $date): ?>
                            <?php
                            $date_formatted = date_i18n(get_option('date_format'), strtotime($date));
                            $train_type_id = isset($train_types_by_date[$date]) ? intval($train_types_by_date[$date]) : 0;
                            ?>
                            <div class="mrt-box mrt-box-sm mrt-train-type-date-row">
                                <label class="mrt-train-type-label">
                                    <?php echo esc_html($date_formatted); ?>
                                    <span>(<?php echo esc_html($date); ?>)</span>
                                </label>
                                <select name="mrt_train_types_by_date[<?php echo esc_attr($date); ?>]" class="mrt-meta-field mrt-train-type-select">
                                    <option value=""><?php esc_html_e('— Use Default —', 'museum-railway-timetable'); ?></option>
                                    <?php foreach ($all_train_types as $term): ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($train_type_id, $term->term_id); ?>><?php echo esc_html($term->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php MRT_render_service_destination_field($route_id, $end_station_id); ?>
    </table>
    </div>
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
    
    // Save train number
    if (isset($_POST['mrt_service_number'])) {
        $service_number = sanitize_text_field($_POST['mrt_service_number']);
        if (!empty($service_number)) {
            update_post_meta($post_id, 'mrt_service_number', $service_number);
        } else {
            delete_post_meta($post_id, 'mrt_service_number');
        }
    }
    
    // Save date-specific train types
    if (isset($_POST['mrt_train_types_by_date']) && is_array($_POST['mrt_train_types_by_date'])) {
        $train_types_by_date = [];
        foreach ($_POST['mrt_train_types_by_date'] as $date => $train_type_id) {
            $date = sanitize_text_field($date);
            $train_type_id = intval($train_type_id);
            
            // Validate date format
            if (MRT_validate_date($date) && $train_type_id > 0) {
                // Verify train type exists
                $term = get_term($train_type_id, 'mrt_train_type');
                if ($term && !is_wp_error($term)) {
                    $train_types_by_date[$date] = $train_type_id;
                }
            }
        }
        
        if (!empty($train_types_by_date)) {
            update_post_meta($post_id, 'mrt_service_train_types_by_date', $train_types_by_date);
        } else {
            delete_post_meta($post_id, 'mrt_service_train_types_by_date');
        }
    } else {
        // If field is not set, keep existing values (don't delete them)
    }
    
    // Save end station and calculate direction
    if (isset($_POST['mrt_service_end_station_id'])) {
        $end_station_id = intval($_POST['mrt_service_end_station_id']);
        if ($end_station_id > 0) {
            update_post_meta($post_id, 'mrt_service_end_station_id', $end_station_id);
            
            // Calculate and save direction based on route and end station
            $route_id = get_post_meta($post_id, 'mrt_service_route_id', true);
            if ($route_id) {
                $calculated_direction = MRT_calculate_direction_from_end_station($route_id, $end_station_id);
                if ($calculated_direction) {
                    update_post_meta($post_id, 'mrt_direction', $calculated_direction);
                } else {
                    delete_post_meta($post_id, 'mrt_direction');
                }
                
                // Update service title based on route and destination
                $route = get_post($route_id);
                $route_name = $route ? $route->post_title : __('Route', 'museum-railway-timetable') . ' #' . $route_id;
                $end_station = get_post($end_station_id);
                $destination_name = $end_station ? $end_station->post_title : '';
                if ($destination_name) {
                    $new_title = $route_name . ' → ' . $destination_name;
                    wp_update_post([
                        'ID' => $post_id,
                        'post_title' => $new_title,
                    ]);
                }
            }
        } else {
            delete_post_meta($post_id, 'mrt_service_end_station_id');
            delete_post_meta($post_id, 'mrt_direction');
        }
    }
    
    // Legacy: Save direction field if still used (for backward compatibility)
    if (isset($_POST['mrt_direction'])) {
        $direction = sanitize_text_field($_POST['mrt_direction']);
        if ($direction === '' || !in_array($direction, ['dit', 'från'], true)) {
            // Only delete if no end_station_id is set
            if (!get_post_meta($post_id, 'mrt_service_end_station_id', true)) {
                delete_post_meta($post_id, 'mrt_direction');
            }
        } else {
            // Only update if no end_station_id is set (backward compatibility)
            if (!get_post_meta($post_id, 'mrt_service_end_station_id', true)) {
                update_post_meta($post_id, 'mrt_direction', $direction);
            }
        }
    }
});
