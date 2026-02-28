<?php
/**
 * Shortcode: Month view [museum_timetable_month]
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render month view shortcode output
 *
 * @param array $atts Shortcode attributes
 * @return string HTML
 */
function MRT_render_shortcode_month($atts) {
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
            $first_ts = strtotime(date('Y-m-01', $now_ts));
            $firstDay = date('Y-m-01', $first_ts);
        }
    } else {
        $first_ts = strtotime(date('Y-m-01', $now_ts));
        $firstDay = date('Y-m-01', $first_ts);
    }

    if (false === $first_ts) {
        return MRT_render_alert(__('Invalid date.', 'museum-railway-timetable'), 'error');
    }

    $year = intval(date('Y', $first_ts));
    $month = intval(date('m', $first_ts));
    $daysInMonth = intval(date('t', $first_ts));
    if ($year <= 0 || $month <= 0 || $month > 12 || $daysInMonth <= 0) {
        return MRT_render_alert(__('Invalid date.', 'museum-railway-timetable'), 'error');
    }

    $weekdayFirst = intval(date('N', $first_ts));
    $startMonday = !empty($atts['start_monday']);

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

    ob_start();
    echo '<div class="mrt-month mrt-my-1" data-train-type="' . esc_attr($atts['train_type']) . '">';
    echo '<div class="mrt-heading mrt-heading--lg mrt-font-semibold">' . esc_html(date_i18n('F Y', $first_ts)) . '</div>';
    echo '<table class="mrt-month-table"><thead><tr>';
    $headers = $startMonday
        ? [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')]
        : [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')];
    foreach ($headers as $h) echo '<th>' . esc_html($h) . '</th>';
    echo '</tr></thead><tbody>';

    $emptyCells = $startMonday ? ($weekdayFirst - 1) : (intval(date('w', $first_ts)));
    echo '<tr>';
    for ($i = 0; $i < $emptyCells; $i++) echo '<td class="mrt-empty"></td>';

    $colIndex = $emptyCells;
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $info = $dates[$d];
        $classes = ['mrt-day'];
        if ($info['running']) {
            $classes[] = 'mrt-running';
            $classes[] = 'mrt-day-clickable mrt-cursor-pointer';
        }
        $title = $info['running']
            ? sprintf(esc_attr__('Click to view timetable for %s', 'museum-railway-timetable'), esc_attr(date_i18n(get_option('date_format'), strtotime($info['ymd']))))
            : '';
        echo '<td class="' . esc_attr(implode(' ', $classes)) . '" data-date="' . esc_attr($info['ymd']) . '" title="' . $title . '">';
        echo '<div class="mrt-daynum">' . intval($d) . '</div>';
        if (!empty($atts['show_counts']) && $info['running']) {
            echo '<div class="mrt-dot">' . intval($info['count']) . '</div>';
        } elseif ($info['running']) {
            echo '<div class="mrt-dot">&bull;</div>';
        }
        echo '</td>';
        $colIndex++;
        if ($colIndex % 7 === 0 && $d < $daysInMonth) echo '</tr><tr>';
    }

    $remaining = (7 - ($colIndex % 7)) % 7;
    for ($i = 0; $i < $remaining; $i++) echo '<td class="mrt-empty"></td>';
    echo '</tr></tbody></table>';

    if (!empty($atts['legend'])) {
        echo '<div class="mrt-legend mrt-text-base mrt-text-primary mrt-mt-sm">';
        echo '<span class="mrt-legend-item mrt-inline-flex mrt-items-center mrt-gap-xs mrt-mr-sm"><span class="mrt-dot mrt-dot--green"></span> ' . esc_html__('Service day', 'museum-railway-timetable') . '</span>';
        if (!empty($atts['show_counts'])) {
            echo ' <span class="mrt-text-small mrt-opacity-85">(' . esc_html__('count per day', 'museum-railway-timetable') . ')</span>';
        }
        echo ' <span class="mrt-text-tertiary mrt-text-small">(' . esc_html__('Click to view timetable', 'museum-railway-timetable') . ')</span>';
        echo '</div>';
    }
    echo '<div class="mrt-box mrt-day-timetable-container mrt-mt-xl mrt-hidden"></div>';
    echo '</div>';
    return ob_get_clean();
}
