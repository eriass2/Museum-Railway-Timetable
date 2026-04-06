<?php
/**
 * HTML rendering for journey AJAX (legacy table output)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render journey search results as HTML (direct connections table)
 *
 * @param array<int, array<string, mixed>> $connections Rows from MRT_find_connections / return
 * @param string                           $from_station_name From title
 * @param string                           $to_station_name To title
 * @param string                           $dateYmd Date Y-m-d
 * @param bool                             $is_return Return leg (to → from) heading
 * @return string HTML fragment
 */
function MRT_journey_render_search_results_html(
    $connections,
    $from_station_name,
    $to_station_name,
    $dateYmd,
    $is_return = false
) {
    ob_start();
    $date_display = date_i18n(get_option('date_format'), strtotime($dateYmd));
    ?>
    <h3 class="mrt-heading mrt-heading--xl mrt-mb-1">
        <?php
        $fmt = $is_return
            ? __('Return connections from %1$s to %2$s on %3$s', 'museum-railway-timetable')
            : __('Connections from %1$s to %2$s on %3$s', 'museum-railway-timetable');
        printf(
            esc_html($fmt),
            esc_html($from_station_name),
            esc_html($to_station_name),
            esc_html($date_display)
        );
        ?>
    </h3>
    <?php if (empty($connections)) : ?>
        <div class="mrt-alert mrt-alert-info mrt-empty">
            <p><strong><?php esc_html_e('No connections found.', 'museum-railway-timetable'); ?></strong></p>
            <p><?php esc_html_e('There are no direct connections between these stations on the selected date. Please try a different date or different stations.', 'museum-railway-timetable'); ?></p>
        </div>
    <?php else : ?>
        <div class="mrt-journey-table-container mrt-overflow-x-auto">
            <table class="mrt-table mrt-journey-table mrt-mt-sm">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Service', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Train Type', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Destination', 'museum-railway-timetable'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($connections as $conn) : ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($conn['service_name']); ?></strong>
                                <?php if (!empty($conn['route_name'])) : ?>
                                    <br><small class="mrt-text-tertiary mrt-font-italic"><?php echo esc_html($conn['route_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($conn['train_type']); ?></td>
                            <td>
                                <strong><?php echo esc_html($conn['from_departure'] ?: ($conn['from_arrival'] ?: '—')); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo esc_html($conn['to_arrival'] ?: ($conn['to_departure'] ?: '—')); ?></strong>
                            </td>
                            <td><?php echo esc_html(!empty($conn['destination']) ? $conn['destination'] : (!empty($conn['direction']) ? $conn['direction'] : '—')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php
    return (string) ob_get_clean();
}
