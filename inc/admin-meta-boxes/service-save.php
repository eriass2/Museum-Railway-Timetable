<?php
/**
 * Service meta box save handler
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

add_action('save_post_mrt_service', 'MRT_save_service_meta_box');

/**
 * Save service meta box data
 *
 * @param int $post_id Post ID
 */
function MRT_save_service_meta_box($post_id) {
    if (!isset($_POST['mrt_service_meta_nonce']) || !wp_verify_nonce($_POST['mrt_service_meta_nonce'], 'mrt_save_service_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    MRT_save_service_timetable($post_id);
    MRT_save_service_route($post_id);
    MRT_save_service_train_type($post_id);
    MRT_save_service_number($post_id);
    MRT_save_service_train_types_by_date($post_id);
    MRT_save_service_end_station($post_id);
    MRT_save_service_direction_legacy($post_id);
}

function MRT_save_service_timetable($post_id) {
    if (!isset($_POST['mrt_service_timetable_id'])) return;
    $tid = intval($_POST['mrt_service_timetable_id']);
    $tid > 0 ? update_post_meta($post_id, 'mrt_service_timetable_id', $tid) : delete_post_meta($post_id, 'mrt_service_timetable_id');
}

function MRT_save_service_route($post_id) {
    if (!isset($_POST['mrt_service_route_id'])) return;
    $rid = intval($_POST['mrt_service_route_id']);
    $rid > 0 ? update_post_meta($post_id, 'mrt_service_route_id', $rid) : delete_post_meta($post_id, 'mrt_service_route_id');
}

function MRT_save_service_train_type($post_id) {
    if (!isset($_POST['mrt_service_train_type'])) return;
    $tid = intval($_POST['mrt_service_train_type']);
    wp_set_object_terms($post_id, $tid > 0 ? [$tid] : [], 'mrt_train_type');
}

function MRT_save_service_number($post_id) {
    if (!isset($_POST['mrt_service_number'])) return;
    $num = sanitize_text_field($_POST['mrt_service_number']);
    !empty($num) ? update_post_meta($post_id, 'mrt_service_number', $num) : delete_post_meta($post_id, 'mrt_service_number');
}

function MRT_save_service_train_types_by_date($post_id) {
    if (!isset($_POST['mrt_train_types_by_date']) || !is_array($_POST['mrt_train_types_by_date'])) return;
    $by_date = [];
    foreach ($_POST['mrt_train_types_by_date'] as $date => $tid) {
        $date = sanitize_text_field($date);
        $tid = intval($tid);
        if (MRT_validate_date($date) && $tid > 0) {
            $term = get_term($tid, 'mrt_train_type');
            if ($term && !is_wp_error($term)) {
                $by_date[$date] = $tid;
            }
        }
    }
    !empty($by_date) ? update_post_meta($post_id, 'mrt_service_train_types_by_date', $by_date) : delete_post_meta($post_id, 'mrt_service_train_types_by_date');
}

function MRT_save_service_end_station($post_id) {
    if (!isset($_POST['mrt_service_end_station_id'])) return;
    $eid = intval($_POST['mrt_service_end_station_id']);
    if ($eid > 0) {
        update_post_meta($post_id, 'mrt_service_end_station_id', $eid);
        $route_id = get_post_meta($post_id, 'mrt_service_route_id', true);
        if ($route_id) {
            $dir = MRT_calculate_direction_from_end_station($route_id, $eid);
            $dir ? update_post_meta($post_id, 'mrt_direction', $dir) : delete_post_meta($post_id, 'mrt_direction');
            $route = get_post($route_id);
            $route_name = $route ? $route->post_title : __('Route', 'museum-railway-timetable') . ' #' . $route_id;
            $end = get_post($eid);
            $dest = $end ? $end->post_title : '';
            if ($dest) {
                wp_update_post(['ID' => $post_id, 'post_title' => $route_name . ' → ' . $dest]);
            }
        }
    } else {
        delete_post_meta($post_id, 'mrt_service_end_station_id');
        delete_post_meta($post_id, 'mrt_direction');
    }
}

function MRT_save_service_direction_legacy($post_id) {
    if (!isset($_POST['mrt_direction'])) return;
    if (get_post_meta($post_id, 'mrt_service_end_station_id', true)) return;
    $dir = sanitize_text_field($_POST['mrt_direction']);
    if ($dir === '' || !in_array($dir, ['dit', 'från'], true)) {
        delete_post_meta($post_id, 'mrt_direction');
    } else {
        update_post_meta($post_id, 'mrt_direction', $dir);
    }
}
