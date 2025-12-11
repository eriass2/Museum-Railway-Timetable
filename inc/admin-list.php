<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Admin: Stations Overview list
 * - Shows each station with type, display order, services count (distinct services stopping there),
 *   and the next running day (within X days) where any of those services run.
 */

add_action('admin_menu', function () {
    add_submenu_page(
        'mrt_settings',
        __('Stations Overview', 'museum-railway-timetable'),
        __('Stations Overview', 'museum-railway-timetable'),
        'manage_options',
        'mrt_stations_overview',
        'MRT_render_stations_overview_page'
    );
});

/**
 * Get all service IDs that stop at a given station
 *
 * @param int $station_id Station post ID
 * @return array Array of service post IDs
 */
function MRT_get_services_for_station($station_id) {
    global $wpdb;
    
    // Validate input
    $station_id = intval($station_id);
    if ($station_id <= 0) {
        return [];
    }
    
    $table = $wpdb->prefix . 'mrt_stoptimes';
    $sql = $wpdb->prepare("SELECT DISTINCT service_post_id FROM $table WHERE station_post_id = %d", $station_id);
    $ids = $wpdb->get_col($sql);
    
    // Check for database errors
    if (MRT_check_db_error('MRT_get_services_for_station')) {
        return [];
    }
    
    return array_map('intval', $ids ?: []);
}

/**
 * Find next running day for a station by checking timetables for services that stop at the station.
 * Checks from 'today' up to +60 days (configurable via filter 'mrt_overview_days_ahead').
 *
 * @param int    $station_id Station post ID
 * @param string $train_type_slug Optional train type taxonomy slug
 * @return string Date in YYYY-MM-DD format or empty string if none found
 */
function MRT_next_running_day_for_station($station_id, $train_type_slug = '') {
    $days_ahead = apply_filters('mrt_overview_days_ahead', 60);
    $datetime = MRT_get_current_datetime();
    $tz_ts = $datetime['timestamp'];
    $services_here = MRT_get_services_for_station($station_id);
    if (!$services_here) return '';

    for ($i = 0; $i <= $days_ahead; $i++) {
        $dateYmd = date('Y-m-d', strtotime("+$i day", $tz_ts));
        $running = MRT_services_running_on_date($dateYmd, $train_type_slug);
        if ($running) {
            $intersect = array_intersect($services_here, $running);
            if (!empty($intersect)) {
                return $dateYmd;
            }
        }
    }
    return '';
}

/**
 * Render the stations overview admin page
 */
function MRT_render_stations_overview_page() {
    if (!current_user_can('manage_options')) return;

    // Query all stations, ordered by display_order then title
    $q = new WP_Query([
        'post_type' => 'mrt_station',
        'posts_per_page' => -1,
        'orderby' => [
            'meta_value_num' => 'ASC',
            'title' => 'ASC',
        ],
        'meta_key' => 'mrt_display_order',
        'order' => 'ASC',
        'fields' => 'ids',
        'nopaging' => true,
    ]);
    $station_ids = $q->posts;

    // Optional filter by train type (taxonomy slug)
    $train_type = isset($_GET['train_type']) ? sanitize_title($_GET['train_type']) : '';

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Stations Overview', 'museum-railway-timetable'); ?></h1>

                <form method="get" class="mrt-filter-form">
            <input type="hidden" name="page" value="mrt_stations_overview" />
            <label><?php echo esc_html__('Train type:', 'museum-railway-timetable'); ?></label>
            <?php
            $terms = get_terms([
                'taxonomy' => 'mrt_train_type',
                'hide_empty' => false,
            ]);
            $current_slug = isset($_GET['train_type']) ? sanitize_title($_GET['train_type']) : '';
            ?>
            <select name="train_type">
                <option value=""><?php echo esc_html__('All types', 'museum-railway-timetable'); ?></option>
                <?php if (!is_wp_error($terms)) :
                    foreach ($terms as $t):
                        $sel = selected($current_slug, $t->slug, false);
                        echo '<option value="'.esc_attr($t->slug).'" '.$sel.'>'.esc_html($t->name).'</option>';
                    endforeach;
                endif; ?>
            </select>
            <button class="button"><?php echo esc_html__('Filter', 'museum-railway-timetable'); ?></button>
            <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=mrt_stations_overview')); ?>"><?php echo esc_html__('Reset', 'museum-railway-timetable'); ?></a>
        </form>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Station', 'museum-railway-timetable'); ?></th>
                    <th><?php echo esc_html__('Type', 'museum-railway-timetable'); ?></th>
                    <th><?php echo esc_html__('Display order', 'museum-railway-timetable'); ?></th>
                    <th><?php echo esc_html__('Services (count)', 'museum-railway-timetable'); ?></th>
                    <th><?php echo esc_html__('Next running day', 'museum-railway-timetable'); ?></th>
                    <th><?php echo esc_html__('Actions', 'museum-railway-timetable'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$station_ids): ?>
                <tr><td colspan="6"><?php echo esc_html__('No stations found.', 'museum-railway-timetable'); ?></td></tr>
            <?php else: ?>
                <?php foreach ($station_ids as $sid):
                    $title = get_the_title($sid);
                    $type  = get_post_meta($sid, 'mrt_station_type', true);
                    $order = intval(get_post_meta($sid, 'mrt_display_order', true));
                    $services = MRT_get_services_for_station($sid);
                    $count = count($services);
                    $next = MRT_next_running_day_for_station($sid, $train_type);
                    $edit_link = get_edit_post_link($sid, '');
                ?>
                <tr>
                    <td><?php echo esc_html($title ?: ('#'.$sid)); ?></td>
                    <td><?php echo esc_html($type ?: ''); ?></td>
                    <td><?php echo esc_html($order); ?></td>
                    <td><?php echo esc_html($count); ?></td>
                    <td>
                        <?php
                        if ($next) {
                            $datetime = MRT_get_current_datetime();
                            echo esc_html(date_i18n(get_option('date_format'), strtotime($next, $datetime['timestamp'])));
                        } else {
                            echo '<span class="mrt-no-date">'.esc_html__('— none within range —', 'museum-railway-timetable').'</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($edit_link): ?>
                            <a class="button button-small" href="<?php echo esc_url($edit_link); ?>"><?php echo esc_html__('Edit station', 'museum-railway-timetable'); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <p class="mrt-note">
            <?php echo esc_html__('Note: Next running day looks ahead up to 60 days. Use the filter above to limit by train type slug.', 'museum-railway-timetable'); ?>
        </p>
    </div>
    <?php
}
