<?php
/**
 * Dashboard: Routes overview table
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render routes overview table
 *
 * @param array $all_routes Array of route post objects
 */
function MRT_render_dashboard_routes($all_routes) {
    if (empty($all_routes)) {
        return;
    }
    ?>
    <div class="mrt-section">
        <h2><?php esc_html_e('Routes Overview', 'museum-railway-timetable'); ?></h2>
        <p class="description">
            <?php esc_html_e('Routes define which stations trains travel between and in what order. When creating a trip, you select a route to automatically get all its stations.', 'museum-railway-timetable'); ?>
        </p>
        <table class="widefat striped mrt-mt-1">
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

                    $services_using_route = get_posts([
                        'post_type' => 'mrt_service',
                        'posts_per_page' => -1,
                        'fields' => 'ids',
                        'meta_query' => [['key' => 'mrt_service_route_id', 'value' => $route->ID, 'compare' => '=']],
                    ]);
                    $services_count_for_route = count($services_using_route);

                    $station_names = [];
                    foreach ($route_stations as $station_id) {
                        $station = get_post($station_id);
                        if ($station) {
                            $station_names[] = $station->post_title;
                        }
                    }
                ?>
                    <tr class="mrt-row-hover">
                        <td><strong><?php echo esc_html($route->post_title); ?></strong></td>
                        <td>
                            <?php if (!empty($station_names)): ?>
                                <span title="<?php echo esc_attr(implode(' → ', $station_names)); ?>">
                                    <?php echo esc_html($stations_count_for_route); ?> <?php esc_html_e('stations', 'museum-railway-timetable'); ?>
                                    <span class="description mrt-block mrt-text-small mrt-mt-xs mrt-text-muted">
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
    <?php
}
