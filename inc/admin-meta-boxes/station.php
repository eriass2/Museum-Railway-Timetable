<?php
/**
 * Station meta box
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

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
    <div class="mrt-alert mrt-alert-info mrt-info-box">
        <p><strong><?php esc_html_e('💡 What is a Station?', 'museum-railway-timetable'); ?></strong></p>
        <p><?php esc_html_e('A station is a physical location where trains can stop. Stations are used in Routes and Stop Times to define where trains travel and when they arrive/depart.', 'museum-railway-timetable'); ?></p>
    </div>
    <?php
    // Show related routes using this station
    $all_routes = get_posts([
        'post_type' => 'mrt_route',
        'posts_per_page' => -1,
        'fields' => 'all',
    ]);
    $routes_using_station = [];
    foreach ($all_routes as $route) {
        $route_stations = get_post_meta($route->ID, 'mrt_route_stations', true);
        if (is_array($route_stations) && in_array($post->ID, $route_stations)) {
            $routes_using_station[] = $route;
        }
    }
    
    if (!empty($routes_using_station)) {
        echo '<div class="mrt-alert mrt-alert-info mrt-info-box mrt-mb-1">';
        echo '<p><strong>' . esc_html__('Used in Routes:', 'museum-railway-timetable') . '</strong></p>';
        echo '<p class="description">' . esc_html__('This station is used in the following routes:', 'museum-railway-timetable') . '</p>';
        echo '<ul class="mrt-list-indent">';
        foreach ($routes_using_station as $route) {
            echo '<li><a href="' . esc_url(get_edit_post_link($route->ID)) . '">' . esc_html($route->post_title) . '</a></li>';
        }
        echo '</ul>';
        echo '</div>';
    }
    ?>
    <div class="mrt-box mrt-mt-1">
    <h3 class="mrt-section-heading"><?php esc_html_e('Station Details', 'museum-railway-timetable'); ?></h3>
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
            <th><label for="mrt_station_bus_suffix"><?php esc_html_e('Bus stop marker', 'museum-railway-timetable'); ?></label></th>
            <td>
                <?php $bus_suffix = get_post_meta($post->ID, 'mrt_station_bus_suffix', true); ?>
                <label>
                    <input type="checkbox" name="mrt_station_bus_suffix" id="mrt_station_bus_suffix" value="1" <?php checked($bus_suffix, '1'); ?> />
                    <?php esc_html_e('Show asterisk (*) in timetable (e.g. "Från Selknä*" for bus connections)', 'museum-railway-timetable'); ?>
                </label>
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
    </div>
    <?php
}

add_action('save_post_mrt_station', function($post_id) {
    if (!isset($_POST['mrt_station_meta_nonce']) || !wp_verify_nonce($_POST['mrt_station_meta_nonce'], 'mrt_save_station_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['mrt_station_type'])) {
        $type = sanitize_text_field($_POST['mrt_station_type']);
        $allowed_types = ['station', 'halt', 'depot', 'museum', ''];
        if (in_array($type, $allowed_types, true)) {
            update_post_meta($post_id, 'mrt_station_type', $type);
        }
    }
    if (isset($_POST['mrt_station_bus_suffix'])) {
        update_post_meta($post_id, 'mrt_station_bus_suffix', '1');
    } else {
        update_post_meta($post_id, 'mrt_station_bus_suffix', '0');
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
