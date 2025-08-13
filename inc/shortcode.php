<?php
if (!defined('ABSPATH')) { exit; }

// Fetch all stations (ordered) for pickers and lists
function MRT_get_all_stations() {
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
    return $q->posts;
}

// Render a generic timetable table (reused by multiple shortcodes)
function MRT_render_timetable_table($rows, $show_arrival = false) {
    if (!$rows) return '<div class="mrt-none">'.esc_html__('No upcoming departures.', 'museum-railway-timetable').'</div>';
    ob_start();
    echo '<div class="mrt-timetable"><table class="mrt-table"><thead><tr>';
    echo '<th>'.esc_html__('Service', 'museum-railway-timetable').'</th>';
    if ($show_arrival) echo '<th>'.esc_html__('Arrives', 'museum-railway-timetable').'</th>';
    echo '<th>'.esc_html__('Departs', 'museum-railway-timetable').'</th>';
    echo '<th>'.esc_html__('Direction', 'museum-railway-timetable').'</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>'.esc_html($r['service_name']).'</td>';
        if ($show_arrival) echo '<td>'.esc_html($r['arrival_time'] ?? '').'</td>';
        echo '<td>'.esc_html($r['departure_time'] ?? '').'</td>';
        echo '<td>'.esc_html($r['direction']).'</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    return ob_get_clean();
}

// Resolve which services run on a given date (interval + weekday + include/exclude overrides)
function MRT_services_running_on_date($dateYmd, $train_type_slug = '', $service_title_exact = '') {
    global $wpdb;
    $calendar = $wpdb->prefix . 'mrt_calendar';

    $weekday = strtolower(date('D', strtotime($dateYmd))); // mon..sun
    $map = ['mon'=>'mon','tue'=>'tue','wed'=>'wed','thu'=>'thu','fri'=>'fri','sat'=>'sat','sun'=>'sun'];
    $col = $map[$weekday] ?? '';

    $sql = $wpdb->prepare("SELECT service_post_id, include_dates, exclude_dates, $col AS dow
        FROM $calendar
        WHERE %s BETWEEN start_date AND end_date", $dateYmd);
    $rows = $wpdb->get_results($sql, ARRAY_A);

    $ids = [];
    foreach ($rows as $r) {
        $include = array_filter(array_map('trim', explode(',', (string)$r['include_dates'])));
        $exclude = array_filter(array_map('trim', explode(',', (string)$r['exclude_dates'])));
        $run = false;

        if (in_array($dateYmd, $exclude, true)) {
            $run = false;
        } elseif (in_array($dateYmd, $include, true)) {
            $run = true;
        } elseif (intval($r['dow']) === 1) {
            $run = true;
        }

        if ($run) $ids[] = intval($r['service_post_id']);
    }

    $ids = array_values(array_unique($ids));
    if (!$ids) return [];

    // Filter by specific service title if provided
    if ($service_title_exact !== '') {
        $post = get_page_by_title($service_title_exact, OBJECT, 'mrt_service');
        if (!$post) return [];
        $ids = array_values(array_intersect($ids, [intval($post->ID)]));
        if (!$ids) return [];
    }

    // Filter by train type taxonomy
    if ($train_type_slug) {
        $q = new WP_Query([
            'post_type' => 'mrt_service',
            'post__in'  => $ids,
            'fields'    => 'ids',
            'nopaging'  => true,
            'tax_query' => [[
                'taxonomy' => 'mrt_train_type',
                'field' => 'slug',
                'terms' => sanitize_title($train_type_slug),
            ]]
        ]);
        return $q->posts;
    }
    return $ids;
}

// Get next departures from a station after a given time
function MRT_next_departures_for_station($station_id, $service_ids, $timeHHMM, $limit = 5, $with_arrival = false) {
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    if (!$service_ids) return [];

    $in = implode(',', array_map('intval', $service_ids));
    $col_time = $with_arrival ? "COALESCE(departure_time, arrival_time)" : "departure_time";

    $sql = $wpdb->prepare("
        SELECT s.service_post_id, s.arrival_time, s.departure_time, s.stop_sequence
        FROM $table s
        WHERE s.station_post_id = %d
          AND s.service_post_id IN ($in)
          AND (
              (s.departure_time IS NOT NULL AND s.departure_time >= %s)
              OR (s.departure_time IS NULL AND s.arrival_time IS NOT NULL AND s.arrival_time >= %s)
          )
        ORDER BY $col_time ASC
        LIMIT %d
    ", $station_id, $timeHHMM, $timeHHMM, $limit);

    $rows = $wpdb->get_results($sql, ARRAY_A);
    if (!$rows) return [];

    $out = [];
    foreach ($rows as $r) {
        $service_name = get_the_title($r['service_post_id']);
        $direction = get_post_meta($r['service_post_id'], 'mrt_direction', true);
        $out[] = [
            'service_id' => intval($r['service_post_id']),
            'service_name' => $service_name ?: ('#'.$r['service_post_id']),
            'arrival_time' => $r['arrival_time'],
            'departure_time' => $r['departure_time'],
            'direction' => $direction !== '' ? $direction : '',
        ];
    }
    return $out;
}

/* -----------------------------------------
   Shortcode 1: Simple timetable view
   [museum_timetable station="..." limit="5" show_arrival="1" train_type="steam"]
------------------------------------------*/
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
        $s = get_page_by_title(sanitize_text_field($atts['station']), OBJECT, 'mrt_station');
        if ($s) $station_id = intval($s->ID);
    }
    if (!$station_id) return '<div class="mrt-error">Station not found.</div>';

    $now_ts = current_time('timestamp');
    $today  = date('Y-m-d', $now_ts);
    $time   = date('H:i', $now_ts);

    $services_today = MRT_services_running_on_date($today, $atts['train_type']);
    if (!$services_today) return '<div class="mrt-none">'.esc_html__('No services today.', 'museum-railway-timetable').'</div>';

    $rows = MRT_next_departures_for_station($station_id, $services_today, $time, intval($atts['limit']), !!$atts['show_arrival']);
    return MRT_render_timetable_table($rows, !empty($atts['show_arrival']));
});


/* -----------------------------------------
   Shortcode 2: Station picker + list
   [museum_timetable_picker default_station="..." limit="6" show_arrival="1" train_type=""]
------------------------------------------*/
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

    if (!$current_station_id && $atts['default_station']) {
        $s = get_page_by_title(sanitize_text_field($atts['default_station']), OBJECT, 'mrt_station');
        if ($s) $current_station_id = intval($s->ID);
    }

    // Form + dropdown
    $stations = MRT_get_all_stations();
    ob_start();
    echo '<form class="mrt-picker" method="'.esc_attr($method).'">';
    echo '<label>'.esc_html__('Station', 'museum-railway-timetable').'</label> ';
    echo '<select name="'.esc_attr($param_station).'" onchange="this.form.submit()">';
    echo '<option value="">' . esc_html($atts['placeholder']) . '</option>';
    foreach ($stations as $sid) {
        $title = get_the_title($sid);
        $sel = selected($current_station_id, $sid, false);
        echo '<option value="'.intval($sid).'" '.$sel.'>'.esc_html($title).'</option>';
    }
    echo '</select>';
    echo '</form>';

    // List after selection
    if ($current_station_id) {
        $now_ts = current_time('timestamp');
        $today  = date('Y-m-d', $now_ts);
        $time   = date('H:i', $now_ts);

        $services_today = MRT_services_running_on_date($today, $atts['train_type']);
        if ($services_today) {
            $rows = MRT_next_departures_for_station($current_station_id, $services_today, $time, intval($atts['limit']), !!$atts['show_arrival']);
            echo MRT_render_timetable_table($rows, !empty($atts['show_arrival']));
        } else {
            echo '<div class="mrt-none">'.esc_html__('No services today.', 'museum-railway-timetable').'</div>';
        }
    }
    return ob_get_clean();
});


/* -----------------------------------------
   Shortcode 3: Month view of service days
   [museum_timetable_month month="2025-06" train_type="" service="" legend="1" show_counts="1"]
------------------------------------------*/
add_shortcode('museum_timetable_month', function ($atts) {
    $atts = shortcode_atts([
        'month' => '',
        'train_type' => '',
        'service' => '',
        'legend' => 1,
        'show_counts' => 1,
        'start_monday' => 1,
    ], $atts, 'museum_timetable_month');

    $now_ts = current_time('timestamp');
    if ($atts['month'] and preg_match('/^\d{4}-\d{2}$/', $atts['month'])) {
        $firstDay = $atts['month'] . '-01';
        $first_ts = strtotime($firstDay . ' 00:00:00', $now_ts);
    } else {
        $first_ts = strtotime(date('Y-m-01', $now_ts));
        $firstDay = date('Y-m-01', $first_ts);
    }

    $year  = intval(date('Y', $first_ts));
    $month = intval(date('m', $first_ts));
    $daysInMonth = intval(date('t', $first_ts));

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
    echo '<div class="mrt-month">';
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
        if ($info['running']) $classes[] = 'mrt-running';
        $title = $info['running'] ? esc_attr__('Services running', 'museum-railway-timetable') : '';
        echo '<td class="'.esc_attr(implode(' ', $classes)).'" title="'.$title.'">';
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
        echo '</div>';
    }

    echo '</div>'; // .mrt-month
    return ob_get_clean();
});
