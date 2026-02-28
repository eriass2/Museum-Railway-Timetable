<?php
/**
 * Import Lennakatten 2026 test data from PDF
 * Creates stations, routes, train types, timetables and services
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

add_action('admin_menu', function() {
    add_submenu_page(
        'mrt_settings',
        __('Import Lennakatten', 'museum-railway-timetable'),
        __('Import Lennakatten', 'museum-railway-timetable'),
        'manage_options',
        'mrt_import_lennakatten',
        'MRT_render_import_page'
    );
});

function MRT_render_import_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $message = '';
    if (isset($_POST['mrt_import_lennakatten']) && check_admin_referer('mrt_import_lennakatten', 'mrt_import_nonce')) {
        $message = MRT_run_lennakatten_import();
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Import Lennakatten 2026 Test Data', 'museum-railway-timetable'); ?></h1>
        <p><?php esc_html_e('This imports test data from the Lennakatten folder 2026 PDF: stations, routes, train types, GRÖN timetable with services and stop times.', 'museum-railway-timetable'); ?></p>

        <?php if ($message): ?>
            <div class="notice notice-success"><p><?php echo wp_kses_post($message); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('mrt_import_lennakatten', 'mrt_import_nonce'); ?>
            <p>
                <input type="submit" name="mrt_import_lennakatten" class="button button-primary" value="<?php esc_attr_e('Run Import', 'museum-railway-timetable'); ?>" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Run the Lennakatten import
 * @return string Success/error message
 */
function MRT_run_lennakatten_import() {
    global $wpdb;

    $stations_data = [
        ['Uppsala Östra', 1],
        ['Fyrislund', 2],
        ['Årsta', 3],
        ['Skölsta', 4],
        ['Bärby', 5],
        ['Gunsta', 6],
        ['Marielund', 7],
        ['Lövstahagen', 8],
        ['Selknä', 9, true],  // bus_suffix
        ['Löt', 10],
        ['Länna', 11],
        ['Almunge', 12],
        ['Moga', 13],
        ['Faringe', 14],
        ['Fjällnora', 15, true],  // bus_suffix
        ['Linnés Hammarby', 16, true],  // bus_suffix
    ];

    $station_ids = [];
    foreach ($stations_data as $s) {
        $name = $s[0];
        $order = $s[1];
        $bus_suffix = isset($s[2]) && $s[2];

        $existing = get_page_by_title($name, OBJECT, 'mrt_station');
        if ($existing) {
            $station_ids[$name] = $existing->ID;
            update_post_meta($existing->ID, 'mrt_display_order', $order);
            update_post_meta($existing->ID, 'mrt_station_bus_suffix', $bus_suffix ? '1' : '0');
        } else {
            $id = wp_insert_post([
                'post_type' => 'mrt_station',
                'post_title' => $name,
                'post_status' => 'publish',
            ]);
            if ($id && !is_wp_error($id)) {
                $station_ids[$name] = $id;
                update_post_meta($id, 'mrt_display_order', $order);
                update_post_meta($id, 'mrt_station_bus_suffix', $bus_suffix ? '1' : '0');
            }
        }
    }

    // Train types
    $train_types = ['Ångtåg' => 'angtag', 'Rälsbuss' => 'ralsbuss', 'Dieseltåg' => 'dieseltag', 'Buss' => 'buss', 'Ång/diesel' => 'ang-diesel'];
    $train_type_ids = [];
    foreach ($train_types as $name => $slug) {
        $term = term_exists($name, 'mrt_train_type');
        if (!$term) {
            $term = wp_insert_term($name, 'mrt_train_type', ['slug' => $slug]);
        }
        if (!is_wp_error($term)) {
            $train_type_ids[$name] = is_array($term) ? $term['term_id'] : $term;
        }
    }

    // Route: Uppsala Östra → Faringe (full line)
    $route_stations = [
        'Uppsala Östra', 'Fyrislund', 'Årsta', 'Skölsta', 'Bärby', 'Gunsta', 'Marielund',
        'Lövstahagen', 'Selknä', 'Löt', 'Länna', 'Almunge', 'Moga', 'Faringe'
    ];
    $route_station_ids = array_values(array_filter(array_map(function($n) use ($station_ids) {
        return $station_ids[$n] ?? null;
    }, $route_stations)));

    $route_title = 'Uppsala Östra – Faringe';
    $route = get_page_by_title($route_title, OBJECT, 'mrt_route');
    if (!$route) {
        $route_id = wp_insert_post([
            'post_type' => 'mrt_route',
            'post_title' => $route_title,
            'post_status' => 'publish',
        ]);
        if ($route_id && !is_wp_error($route_id)) {
            update_post_meta($route_id, 'mrt_route_stations', array_values($route_station_ids));
            update_post_meta($route_id, 'mrt_route_start_station', $route_station_ids[0]);
            update_post_meta($route_id, 'mrt_route_end_station', end($route_station_ids));
        }
    } else {
        $route_id = $route->ID;
        update_post_meta($route_id, 'mrt_route_stations', array_values($route_station_ids));
        update_post_meta($route_id, 'mrt_route_start_station', $route_station_ids[0]);
        update_post_meta($route_id, 'mrt_route_end_station', end($route_station_ids));
    }

    // Reverse route: Faringe → Uppsala Östra
    $route_rev_station_ids = array_reverse($route_station_ids);
    $route_rev_title = 'Faringe – Uppsala Östra';
    $route_rev = get_page_by_title($route_rev_title, OBJECT, 'mrt_route');
    if (!$route_rev) {
        $route_rev_id = wp_insert_post([
            'post_type' => 'mrt_route',
            'post_title' => $route_rev_title,
            'post_status' => 'publish',
        ]);
        if ($route_rev_id && !is_wp_error($route_rev_id)) {
            update_post_meta($route_rev_id, 'mrt_route_stations', array_values($route_rev_station_ids));
            update_post_meta($route_rev_id, 'mrt_route_start_station', $route_rev_station_ids[0]);
            update_post_meta($route_rev_id, 'mrt_route_end_station', end($route_rev_station_ids));
        }
    } else {
        $route_rev_id = $route_rev->ID;
        update_post_meta($route_rev->ID, 'mrt_route_stations', array_values($route_rev_station_ids));
        update_post_meta($route_rev->ID, 'mrt_route_start_station', $route_rev_station_ids[0]);
        update_post_meta($route_rev->ID, 'mrt_route_end_station', end($route_rev_station_ids));
    }

    // GRÖN Timetable: sample dates (lördagar 30/5–26/9)
    $timetable_dates = [];
    foreach (['2026-05-30','2026-05-31','2026-06-06','2026-06-13','2026-06-20','2026-07-04','2026-07-11','2026-07-18','2026-08-01','2026-08-08','2026-08-15','2026-09-05','2026-09-12','2026-09-19','2026-09-26'] as $d) {
        $timetable_dates[] = $d;
    }
    $timetable_dates = array_unique(array_filter($timetable_dates));
    sort($timetable_dates);

    $timetable = get_posts(['post_type' => 'mrt_timetable', 'posts_per_page' => 1, 'meta_key' => 'mrt_timetable_type', 'meta_value' => 'green']);
    if (empty($timetable)) {
        $timetable_id = wp_insert_post([
            'post_type' => 'mrt_timetable',
            'post_title' => 'GRÖN TIDTABELL 2026',
            'post_status' => 'publish',
        ]);
        if ($timetable_id && !is_wp_error($timetable_id)) {
            update_post_meta($timetable_id, 'mrt_timetable_dates', array_slice($timetable_dates, 0, 20));
            update_post_meta($timetable_id, 'mrt_timetable_type', 'green');
        }
    } else {
        $timetable_id = $timetable[0]->ID;
        update_post_meta($timetable_id, 'mrt_timetable_dates', array_slice($timetable_dates, 0, 20));
        update_post_meta($timetable_id, 'mrt_timetable_type', 'green');
    }

    // GRÖN services Uppsala → Faringe (14 stations, from PDF)
    // Format: [num, train_type, [times...], [symbols P|X|'']]
    // Times: [h,m] or [arr_h,arr_m,dep_h,dep_m] for Marielund
    $services_out = [
        ['71', 'Ångtåg', [[10,0],[10,3],[10,5],[10,9],[10,23],[10,24],[10,35,10,45],[10,46],[10,50],[10,54],[10,57],[11,10],[11,14],[11,25]], ['P','P','X','','X','','','P','','X','','','X','']],
        ['93', 'Rälsbuss', [[11,10],[11,13],[11,15],[11,18],[11,28],[11,29],[11,37],[11,42],[11,43],[11,47],[11,50],[11,54],[12,4],[12,7],[12,17]], ['P','P','X','','X','','','X','','','X','','','X','']],
        ['75', 'Ångtåg', [[12,38],[12,41],[12,43],[12,47],[13,0],[13,1],[13,10,13,32],[13,33],[13,37],[13,41],[13,47],[14,0],[14,4],[14,15]], ['P','P','X','','X','','','X','','','X','','','X','']],
        ['63', 'Dieseltåg', [[14,10],[14,13],[14,15],[14,19],[14,30],[14,31],[14,40,15,10],[15,11],[15,15],[15,18],[15,21],[15,31],[15,34],[15,43]], ['P','P','X','','X','','','X','','','X','','','X','']],
        ['65', 'Dieseltåg', [[15,55],[15,58],[16,0],[16,4],[16,13],[16,14],[16,23,17,0],[17,1],[17,4],[17,8],[17,11],[17,22],[17,26],[17,37]], ['P','P','X','','X','','','X','','','X','','','X','']],
        ['79', 'Ång/diesel', [[18,7],[18,10],[18,12],[18,16],[18,25],[18,26],[18,35,18,50],[18,51],[18,54],[18,57],[19,1],[19,12],[19,16],[19,27]], ['X','X','X','','X','','','X','','','X','','','X','']],
    ];

    $table = $wpdb->prefix . 'mrt_stoptimes';
    $created_services = 0;

    foreach ($services_out as $svc) {
        $num = $svc[0];
        $train_type = $svc[1];
        $times = $svc[2];
        $pickup = $svc[3];
        $dropoff = $svc[4];

        $title = "Uppsala Östra – Faringe $num";
        $existing = get_posts(['post_type' => 'mrt_service', 'title' => $title, 'posts_per_page' => 1]);
        if (!empty($existing)) {
            continue;
        }

        $service_id = wp_insert_post([
            'post_type' => 'mrt_service',
            'post_title' => $title,
            'post_status' => 'publish',
        ]);
        if (!$service_id || is_wp_error($service_id)) continue;

        wp_set_object_terms($service_id, $train_type, 'mrt_train_type');
        update_post_meta($service_id, 'mrt_service_timetable_id', $timetable_id);
        update_post_meta($service_id, 'mrt_service_route_id', $route_id);
        update_post_meta($service_id, 'mrt_service_number', $num);
        update_post_meta($service_id, 'mrt_service_end_station_id', $station_ids['Faringe'] ?? 0);

        $seq = 0;
        $n = count($route_station_ids);
        for ($i = 0; $i < $n; $i++) {
            $st_id = $route_station_ids[$i];
            $t = isset($times[$i]) ? $times[$i] : null;
            $arr = $dep = null;
            if (is_array($t)) {
                if (count($t) >= 4) {
                    $arr = sprintf('%02d:%02d', $t[0], $t[1]);
                    $dep = sprintf('%02d:%02d', $t[2], $t[3]);
                } else {
                    $arr = sprintf('%02d:%02d', $t[0], $t[1]);
                    $dep = $arr;
                }
            }
            $sym = $pickup[$i] ?? '';
            $pu = ($sym === 'P' || $sym === 'X' || $sym === '') ? 1 : 0;
            $do = ($sym === 'X' || $sym === '') ? 1 : 0;
            if ($sym === 'P') $do = 0;

            $wpdb->insert($table, [
                'service_post_id' => $service_id,
                'station_post_id' => $st_id,
                'stop_sequence' => $seq,
                'arrival_time' => $arr,
                'departure_time' => $dep,
                'pickup_allowed' => $pu,
                'dropoff_allowed' => $do,
            ], ['%d','%d','%d','%s','%s','%d','%d']);
            $seq++;
        }
        $created_services++;
    }

    // GRÖN services Faringe → Uppsala (14 stations)
    $services_in = [
        ['70', 'Ångtåg', [[7,55],[8,2],[8,14],[8,25],[8,27],[8,31],[8,34],[8,38,8,53],[8,58],[9,1],[9,8],[9,12],[9,14],[9,23]], ['X','X','','X','X','X','X','','X','','X','X','X','']],
        ['60', 'Dieseltåg', [[9,40],[9,47],[9,57],[10,8],[10,10],[10,14],[10,17],[10,20,11,45],[11,50],[11,53],[12,0],[12,4],[12,6],[12,17]], ['X','X','','X','X','X','X','','X','','X','X','X','']],
        ['62', 'Dieseltåg', [[12,27],[12,34],[12,41],[12,54],[12,56],[13,1],[13,4],[13,7,13,15],[13,20],[13,23],[13,30],[13,34],[13,36],[13,47]], ['X','X','','','','X','X','','X','','','X','X','']],
        ['96', 'Rälsbuss', [[14,25],[14,31],[14,36],[14,46],[14,47],[14,52],[14,55],[14,58,15,5],[15,10],[15,13],[15,20],[15,24],[15,26],[15,37]], ['X','X','','X','X','X','X','','X','','','','X','']],
        ['78', 'Ång/diesel', [[16,13],[16,20],[16,28],[16,41],[16,43],[16,48],[16,51],[16,55,17,15],[17,20],[17,23],[17,30],[17,34],[17,36],[17,47]], ['X','X','','X','X','X','X','','X','','','','X','']],
    ];

    $rev_stations = array_reverse($route_stations);
    foreach ($services_in as $svc) {
        $num = $svc[0];
        $train_type = $svc[1];
        $times = $svc[2];
        $pickup = $svc[3];

        $title = "Faringe – Uppsala Östra $num";
        $existing = get_posts(['post_type' => 'mrt_service', 'title' => $title, 'posts_per_page' => 1]);
        if (!empty($existing)) continue;

        $service_id = wp_insert_post([
            'post_type' => 'mrt_service',
            'post_title' => $title,
            'post_status' => 'publish',
        ]);
        if (!$service_id || is_wp_error($service_id)) continue;

        wp_set_object_terms($service_id, $train_type, 'mrt_train_type');
        update_post_meta($service_id, 'mrt_service_timetable_id', $timetable_id);
        update_post_meta($service_id, 'mrt_service_route_id', $route_rev_id);
        update_post_meta($service_id, 'mrt_service_number', $num);
        update_post_meta($service_id, 'mrt_service_end_station_id', $station_ids['Uppsala Östra'] ?? 0);

        $seq = 0;
        $n = count($route_rev_station_ids);
        for ($i = 0; $i < $n; $i++) {
            $st_id = $route_rev_station_ids[$i];
            $t = isset($times[$i]) ? $times[$i] : null;
            $arr = $dep = null;
            if (is_array($t)) {
                if (count($t) >= 4) {
                    $arr = sprintf('%02d:%02d', $t[0], $t[1]);
                    $dep = sprintf('%02d:%02d', $t[2], $t[3]);
                } else {
                    $arr = sprintf('%02d:%02d', $t[0], $t[1]);
                    $dep = $arr;
                }
            }
            $sym = $pickup[$i] ?? '';
            $pu = ($sym === 'P' || $sym === 'X' || $sym === '') ? 1 : 0;
            $do = 1;

            $wpdb->insert($table, [
                'service_post_id' => $service_id,
                'station_post_id' => $st_id,
                'stop_sequence' => $seq,
                'arrival_time' => $arr,
                'departure_time' => $dep,
                'pickup_allowed' => $pu,
                'dropoff_allowed' => $do,
            ], ['%d','%d','%d','%s','%s','%d','%d']);
            $seq++;
        }
        $created_services++;
    }

    $station_count = count($station_ids);
    $dates_count = count(MRT_get_timetable_dates($timetable_id));

    return sprintf(
        __('Import complete. Stations: %d, Routes: 2, Train types: %d, Timetable: GRÖN (ID %d, %d dates), Services created: %d.', 'museum-railway-timetable'),
        $station_count,
        count($train_type_ids),
        $timetable_id,
        $dates_count,
        $created_services
    );
}
