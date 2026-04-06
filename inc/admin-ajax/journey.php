<?php
/**
 * AJAX handlers for journey search and calendar (frontend)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * JSON success when no services run on the selected date
 *
 * @param string $trip_type single|return
 * @return void
 */
function MRT_journey_ajax_send_no_services_response($trip_type) {
    $html = MRT_render_alert(
        __('No services are running on the selected date.', 'museum-railway-timetable'),
        'error'
    );
    wp_send_json_success([
        'html' => $html,
        'trip_type' => $trip_type,
        'connections' => [],
    ]);
}

/**
 * Search for journey connections via AJAX (frontend)
 *
 * POST: nonce, from_station, to_station, date; optional trip_type (single|return),
 *       for return: outbound_arrival (HH:MM), optional outbound_service_id, min_turnaround_minutes.
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_search_journey() {
    if (!MRT_journey_ajax_verify_nonce()) {
        wp_send_json_error([
            'message' => __('Security check failed. Please refresh the page.', 'museum-railway-timetable'),
        ]);
        return;
    }
    $params = MRT_journey_ajax_parse_trip_search_params();
    if (is_wp_error($params)) {
        wp_send_json_error(['message' => $params->get_error_message()]);
        return;
    }
    $services_on_date = MRT_services_running_on_date($params['date']);
    if (empty($services_on_date)) {
        MRT_journey_ajax_send_no_services_response($params['trip_type']);
        return;
    }
    if ($params['trip_type'] === 'return') {
        $connections = MRT_find_return_connections(
            (int) $params['from'],
            (int) $params['to'],
            $params['date'],
            $params['outbound_arrival'],
            (int) $params['min_turnaround_minutes']
        );
        $from_name = get_the_title($params['to']);
        $to_name = get_the_title($params['from']);
        $is_return = true;
    } else {
        $connections = MRT_find_connections(
            (int) $params['from'],
            (int) $params['to'],
            $params['date']
        );
        $from_name = get_the_title($params['from']);
        $to_name = get_the_title($params['to']);
        $is_return = false;
    }
    $html = MRT_journey_render_search_results_html(
        $connections,
        $from_name,
        $to_name,
        $params['date'],
        $is_return
    );
    wp_send_json_success([
        'html' => $html,
        'trip_type' => $params['trip_type'],
        'connections' => $connections,
    ]);
}

/**
 * Calendar month cell states for a station pair (frontend)
 *
 * POST: nonce, from_station, to_station, year, month (1–12)
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_journey_calendar_month() {
    if (!MRT_journey_ajax_verify_nonce()) {
        wp_send_json_error([
            'message' => __('Security check failed. Please refresh the page.', 'museum-railway-timetable'),
        ]);
        return;
    }
    $parsed = MRT_journey_ajax_parse_calendar_month_params();
    if (is_wp_error($parsed)) {
        wp_send_json_error(['message' => $parsed->get_error_message()]);
        return;
    }
    $days = MRT_get_journey_calendar_month(
        $parsed['from'],
        $parsed['to'],
        $parsed['year'],
        $parsed['month']
    );
    wp_send_json_success([
        'year' => $parsed['year'],
        'month' => $parsed['month'],
        'days' => $days,
    ]);
}

/**
 * Stops and duration for one service between two stations (frontend wizard / API)
 *
 * POST: nonce, from_station, to_station, service_id
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_journey_connection_detail() {
    if (!MRT_journey_ajax_verify_nonce()) {
        wp_send_json_error([
            'message' => __('Security check failed. Please refresh the page.', 'museum-railway-timetable'),
        ]);
        return;
    }
    $parsed = MRT_journey_ajax_parse_connection_detail_params();
    if (is_wp_error($parsed)) {
        wp_send_json_error(['message' => $parsed->get_error_message()]);
        return;
    }
    $detail = MRT_get_connection_journey_detail(
        $parsed['service_id'],
        $parsed['from'],
        $parsed['to']
    );
    $notice = MRT_get_service_notice($parsed['service_id'], null);
    wp_send_json_success([
        'detail' => $detail,
        'notice' => $notice,
    ]);
}
