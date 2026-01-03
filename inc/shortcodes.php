<?php
/**
 * Shortcode registrations for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Shortcode 1: Simple timetable view
 * [museum_timetable station="..." limit="5" show_arrival="1" train_type="steam"]
 */
add_shortcode('museum_timetable', function ($atts) {
    $atts = shortcode_atts([
        'station' => '',
        'station_id' => '',
        'limit' => 5,
        'show_arrival' => 0,
        'train_type' => '',
    ], $atts, 'museum_timetable');

    $station_id = intval($atts['station_id']);
    if (!$station_id && $atts['station']) {
        $s = MRT_get_post_by_title($atts['station'], 'mrt_station');
        if ($s) {
            $station_id = intval($s->ID);
        }
    }
    
    if (!$station_id || $station_id <= 0) {
        return '<div class="mrt-error">'.esc_html__('Station not found.', 'museum-railway-timetable').'</div>';
    }

    $limit = intval($atts['limit']);
    if ($limit <= 0) {
        $limit = 5; // Default fallback
    }

    $datetime = MRT_get_current_datetime();
    $today = $datetime['date'];
    $time = $datetime['time'];

    $services_today = MRT_services_running_on_date($today, $atts['train_type']);
    if (empty($services_today)) {
        return '<div class="mrt-none">'.esc_html__('No services today.', 'museum-railway-timetable').'</div>';
    }

    $rows = MRT_next_departures_for_station($station_id, $services_today, $time, $limit, !!$atts['show_arrival']);
    return MRT_render_timetable_table($rows, !empty($atts['show_arrival']));
});

/**
 * Shortcode 2: Station picker + list
 * [museum_timetable_picker default_station="..." limit="6" show_arrival="1" train_type=""]
 */
add_shortcode('museum_timetable_picker', function ($atts) {
    $atts = shortcode_atts([
        'default_station' => '',
        'limit' => 6,
        'show_arrival' => 0,
        'train_type' => '',
        'form_method' => 'get',
        'placeholder' => __('Select station', 'museum-railway-timetable'),
    ], $atts, 'museum_timetable_picker');

    $method = strtolower($atts['form_method']) === 'post' ? 'post' : 'get';
    $param_station = 'mrt_station_id'; // query-param

    // Current selection
    $current_station_id = 0;
    if ($method === 'post' && !empty($_POST[$param_station])) {
        $current_station_id = intval($_POST[$param_station]);
    } elseif (!empty($_GET[$param_station])) {
        $current_station_id = intval($_GET[$param_station]);
    }

    if (!$current_station_id && !empty($atts['default_station'])) {
        $s = MRT_get_post_by_title($atts['default_station'], 'mrt_station');
        if ($s) {
            $current_station_id = intval($s->ID);
        }
    }
    
    $limit = intval($atts['limit']);
    if ($limit <= 0) {
        $limit = 6; // Default fallback
    }

    // Form + dropdown
    $stations = MRT_get_all_stations();
    ob_start();
    echo '<form class="mrt-picker" method="'.esc_attr($method).'" data-ajax-enabled="true" data-limit="'.esc_attr($limit).'" data-show-arrival="'.esc_attr($atts['show_arrival']).'" data-train-type="'.esc_attr($atts['train_type']).'">';
    echo '<label>'.esc_html__('Station', 'museum-railway-timetable').'</label> ';
    echo '<select name="'.esc_attr($param_station).'">';
    echo '<option value="">' . esc_html($atts['placeholder']) . '</option>';
    foreach ($stations as $sid) {
        $title = get_the_title($sid);
        $sel = selected($current_station_id, $sid, false);
        echo '<option value="'.intval($sid).'" '.$sel.'>'.esc_html($title).'</option>';
    }
    echo '</select>';
    echo '</form>';

    // List after selection (wrapped in container for AJAX updates)
    if ($current_station_id) {
        echo '<div class="mrt-timetable-results">';
        $now_ts = current_time('timestamp');
        $today  = date('Y-m-d', $now_ts);
        $time   = date('H:i', $now_ts);

        $services_today = MRT_services_running_on_date($today, $atts['train_type']);
        if (!empty($services_today)) {
            $rows = MRT_next_departures_for_station($current_station_id, $services_today, $time, $limit, !!$atts['show_arrival']);
            echo MRT_render_timetable_table($rows, !empty($atts['show_arrival']));
        } else {
            echo '<div class="mrt-none">'.esc_html__('No services today.', 'museum-railway-timetable').'</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="mrt-timetable-results"></div>';
    }
    return ob_get_clean();
});

/**
 * Shortcode 3: Month view of service days
 * [museum_timetable_month month="2025-06" train_type="" service="" legend="1" show_counts="1"]
 */
add_shortcode('museum_timetable_month', function ($atts) {
    $atts = shortcode_atts([
        'month' => '',
        'train_type' => '',
        'service' => '',
        'legend' => 1,
        'show_counts' => 1,
        'start_monday' => 1,
    ], $atts, 'museum_timetable_month');

    $datetime = MRT_get_current_datetime();
    $now_ts = $datetime['timestamp'];
    if (!empty($atts['month']) && preg_match('/^\d{4}-\d{2}$/', $atts['month'])) {
        $firstDay = $atts['month'] . '-01';
        $first_ts = strtotime($firstDay . ' 00:00:00', $now_ts);
        if (false === $first_ts) {
            // Invalid date, fall back to current month
            $first_ts = strtotime(date('Y-m-01', $now_ts));
            $firstDay = date('Y-m-01', $first_ts);
        }
    } else {
        $first_ts = strtotime(date('Y-m-01', $now_ts));
        $firstDay = date('Y-m-01', $first_ts);
    }
    
    if (false === $first_ts) {
        return '<div class="mrt-error">'.esc_html__('Invalid date.', 'museum-railway-timetable').'</div>';
    }

    $year  = intval(date('Y', $first_ts));
    $month = intval(date('m', $first_ts));
    $daysInMonth = intval(date('t', $first_ts));
    
    if ($year <= 0 || $month <= 0 || $month > 12 || $daysInMonth <= 0) {
        return '<div class="mrt-error">'.esc_html__('Invalid date.', 'museum-railway-timetable').'</div>';
    }

    $weekdayFirst = intval(date('N', $first_ts)); // 1..7 (Mon..Sun)
    $startMonday = !empty($atts['start_monday']);

    // Build date list with running flag
    $dates = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $ymd = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $service_ids = MRT_services_running_on_date($ymd, $atts['train_type'], $atts['service']);
        $dates[$d] = [
            'ymd' => $ymd,
            'count' => count($service_ids),
            'running' => !empty($service_ids),
        ];
    }

    // Render table: 7 columns
    ob_start();
    echo '<div class="mrt-month" data-train-type="'.esc_attr($atts['train_type']).'">';
    echo '<div class="mrt-month-header">'.esc_html(date_i18n('F Y', $first_ts)).'</div>';
    echo '<table class="mrt-month-table"><thead><tr>';
    if ($startMonday) {
        $headers = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')];
    } else {
        $headers = [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')];
    }
    foreach ($headers as $h) echo '<th>'.esc_html($h).'</th>';
    echo '</tr></thead><tbody>';

    $emptyCells = $startMonday ? ($weekdayFirst - 1) : (intval(date('w', $first_ts))); // 0..6
    echo '<tr>';
    for ($i = 0; $i < $emptyCells; $i++) echo '<td class="mrt-empty"></td>';

    $colIndex = $emptyCells;
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $info = $dates[$d];
        $classes = ['mrt-day'];
        if ($info['running']) {
            $classes[] = 'mrt-running';
            $classes[] = 'mrt-day-clickable';
        }
        $title = $info['running'] 
            ? sprintf(esc_attr__('Click to view timetable for %s', 'museum-railway-timetable'), esc_attr(date_i18n(get_option('date_format'), strtotime($info['ymd']))))
            : '';
        echo '<td class="'.esc_attr(implode(' ', $classes)).'" data-date="'.esc_attr($info['ymd']).'" title="'.$title.'">';
        echo '<div class="mrt-daynum">'.intval($d).'</div>';
        if (!empty($atts['show_counts']) && $info['running']) {
            echo '<div class="mrt-dot">'.intval($info['count']).'</div>';
        } elseif ($info['running']) {
            echo '<div class="mrt-dot">&bull;</div>';
        }
        echo '</td>';

        $colIndex++;
        if ($colIndex % 7 === 0 && $d < $daysInMonth) {
            echo '</tr><tr>';
        }
    }

    $remaining = (7 - ($colIndex % 7)) % 7;
    for ($i = 0; $i < $remaining; $i++) echo '<td class="mrt-empty"></td>';

    echo '</tr></tbody></table>';

    if (!empty($atts['legend'])) {
        echo '<div class="mrt-legend">';
        echo '<span class="mrt-legend-item"><span class="mrt-legend-dot"></span> '.esc_html__('Service day', 'museum-railway-timetable').'</span>';
        if (!empty($atts['show_counts'])) {
            echo ' <span class="mrt-legend-item-count">('.esc_html__('count per day', 'museum-railway-timetable').')</span>';
        }
        echo ' <span class="mrt-legend-item-click">('.esc_html__('Click to view timetable', 'museum-railway-timetable').')</span>';
        echo '</div>';
    }

    // Container for day timetable (shown when day is clicked)
    echo '<div class="mrt-day-timetable-container" style="display: none;"></div>';

    echo '</div>'; // .mrt-month
    return ob_get_clean();
});

/**
 * Shortcode 4: Timetable Overview
 * [museum_timetable_overview timetable_id="123" timetable="Timetable Name"]
 */
add_shortcode('museum_timetable_overview', function ($atts) {
    $atts = shortcode_atts([
        'timetable_id' => '',
        'timetable' => '',
    ], $atts, 'museum_timetable_overview');
    
    $timetable_id = intval($atts['timetable_id']);
    
    // If no ID provided, try to find by title
    if (!$timetable_id && !empty($atts['timetable'])) {
        $timetable_post = MRT_get_post_by_title($atts['timetable'], 'mrt_timetable');
        if ($timetable_post) {
            $timetable_id = intval($timetable_post->ID);
        }
    }
    
    if (!$timetable_id || $timetable_id <= 0) {
        return '<div class="mrt-error">' . esc_html__('Timetable not found.', 'museum-railway-timetable') . '</div>';
    }
    
    return MRT_render_timetable_overview($timetable_id);
});

/**
 * Shortcode 5: Journey Planner (Reseplanerare)
 * [museum_journey_planner]
 */
add_shortcode('museum_journey_planner', function ($atts) {
    $atts = shortcode_atts([
        'default_date' => '',
    ], $atts, 'museum_journey_planner');
    
    // Get current date as default
    $datetime = MRT_get_current_datetime();
    $default_date = !empty($atts['default_date']) && MRT_validate_date($atts['default_date']) 
        ? $atts['default_date'] 
        : $datetime['date'];
    
    // Get selected values from GET/POST
    $from_station_id = isset($_GET['mrt_from']) ? intval($_GET['mrt_from']) : 0;
    $to_station_id = isset($_GET['mrt_to']) ? intval($_GET['mrt_to']) : 0;
    $selected_date = isset($_GET['mrt_date']) && MRT_validate_date($_GET['mrt_date']) 
        ? sanitize_text_field($_GET['mrt_date']) 
        : $default_date;
    
    // Get all stations
    $stations = MRT_get_all_stations();
    
    ob_start();
    ?>
    <div class="mrt-journey-planner">
        <form class="mrt-journey-form" method="get" action="" data-ajax-enabled="true">
            <div class="mrt-journey-fields">
                <div class="mrt-journey-field">
                    <label for="mrt_from"><?php esc_html_e('From', 'museum-railway-timetable'); ?></label>
                    <select name="mrt_from" id="mrt_from" required>
                        <option value=""><?php esc_html_e('Select station', 'museum-railway-timetable'); ?></option>
                        <?php foreach ($stations as $station_id): 
                            $station_name = get_the_title($station_id);
                            $selected = selected($from_station_id, $station_id, false);
                        ?>
                            <option value="<?php echo esc_attr($station_id); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html($station_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mrt-journey-field">
                    <label for="mrt_to"><?php esc_html_e('To', 'museum-railway-timetable'); ?></label>
                    <select name="mrt_to" id="mrt_to" required>
                        <option value=""><?php esc_html_e('Select station', 'museum-railway-timetable'); ?></option>
                        <?php foreach ($stations as $station_id): 
                            $station_name = get_the_title($station_id);
                            $selected = selected($to_station_id, $station_id, false);
                        ?>
                            <option value="<?php echo esc_attr($station_id); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html($station_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mrt-journey-field">
                    <label for="mrt_date"><?php esc_html_e('Date', 'museum-railway-timetable'); ?></label>
                    <input type="date" name="mrt_date" id="mrt_date" value="<?php echo esc_attr($selected_date); ?>" required>
                </div>
                
                <div class="mrt-journey-field">
                    <button type="submit" class="mrt-journey-search"><?php esc_html_e('Search', 'museum-railway-timetable'); ?></button>
                </div>
            </div>
        </form>
        
        <?php if ($from_station_id > 0 && $to_station_id > 0 && $from_station_id !== $to_station_id): 
            $connections = MRT_find_connections($from_station_id, $to_station_id, $selected_date);
            $from_station_name = get_the_title($from_station_id);
            $to_station_name = get_the_title($to_station_id);
        ?>
            <div class="mrt-journey-results">
                <h3 class="mrt-journey-results-title">
                    <?php 
                    printf(
                        esc_html__('Connections from %s to %s on %s', 'museum-railway-timetable'),
                        esc_html($from_station_name),
                        esc_html($to_station_name),
                        esc_html(date_i18n(get_option('date_format'), strtotime($selected_date)))
                    );
                    ?>
                </h3>
                
                <?php 
                // Check if services run on this date
                $services_on_date = MRT_services_running_on_date($selected_date);
                if (empty($services_on_date)): ?>
                    <div class="mrt-error">
                        <p><strong><?php esc_html_e('No services running.', 'museum-railway-timetable'); ?></strong></p>
                        <p><?php esc_html_e('There are no services running on the selected date. Please try a different date.', 'museum-railway-timetable'); ?></p>
                    </div>
                <?php elseif (empty($connections)): ?>
                    <div class="mrt-none">
                        <p><strong><?php esc_html_e('No connections found.', 'museum-railway-timetable'); ?></strong></p>
                        <p><?php esc_html_e('There are no direct connections between these stations on the selected date. Please try a different date or different stations.', 'museum-railway-timetable'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="mrt-journey-table-container">
                        <table class="mrt-table mrt-journey-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Service', 'museum-railway-timetable'); ?></th>
                                    <th><?php esc_html_e('Train Type', 'museum-railway-timetable'); ?></th>
                                    <th><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                                    <th><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                                    <th><?php esc_html_e('Direction', 'museum-railway-timetable'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($connections as $conn): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html($conn['service_name']); ?></strong>
                                            <?php if (!empty($conn['route_name'])): ?>
                                                <br><small class="mrt-route-name"><?php echo esc_html($conn['route_name']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html($conn['train_type']); ?></td>
                                        <td>
                                            <strong><?php echo esc_html($conn['from_departure'] ?: ($conn['from_arrival'] ?: '—')); ?></strong>
                                        </td>
                                        <td>
                                            <strong><?php echo esc_html($conn['to_arrival'] ?: ($conn['to_departure'] ?: '—')); ?></strong>
                                        </td>
                                        <td><?php echo esc_html($conn['direction']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($from_station_id > 0 && $to_station_id > 0 && $from_station_id === $to_station_id): ?>
            <div class="mrt-error">
                <?php esc_html_e('Please select different stations for departure and arrival.', 'museum-railway-timetable'); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});

