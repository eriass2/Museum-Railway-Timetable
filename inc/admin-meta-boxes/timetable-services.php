<?php
/**
 * Timetable services meta box (trips within timetable)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

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
                    <th class="mrt-col-destination"><?php esc_html_e('Destination', 'museum-railway-timetable'); ?></th>
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
                    <tr class="mrt-row-hover" data-service-id="<?php echo esc_attr($service->ID); ?>">
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
                            $destination_data = MRT_get_service_destination($service->ID);
                            if (!empty($destination_data['destination'])) {
                                echo esc_html($destination_data['destination']);
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg('timetable_id', $post->ID, get_edit_post_link($service->ID))); ?>" class="button button-small">
                                <?php esc_html_e('Edit', 'museum-railway-timetable'); ?>
                            </a>
                            <input type="hidden" name="mrt_service_timetable_id" value="<?php echo esc_attr($post->ID); ?>" />
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
                        <select id="mrt-new-service-end-station" class="mrt-meta-field mrt-full-width">
                            <option value=""><?php esc_html_e('— Select Destination —', 'museum-railway-timetable'); ?></option>
                            <option value="" disabled><?php esc_html_e('Select a route first', 'museum-railway-timetable'); ?></option>
                        </select>
                        <p class="description mrt-description-small-mt"><?php esc_html_e('Select route first to see available destinations', 'museum-railway-timetable'); ?></p>
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
