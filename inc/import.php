<?php
if (!defined('ABSPATH')) { exit; }

// Submenu for CSV import tools
add_action('admin_menu', function () {
    add_submenu_page(
        'mrt_settings',
        __('CSV Import', 'museum-railway-timetable'),
        __('CSV Import', 'museum-railway-timetable'),
        'manage_options',
        'mrt_import',
        'MRT_render_import_page'
    );
});

// Reusable help panel (fully translatable)
function MRT_import_help_panel() {
    ?>
    <div class="mrt-help-panel" style="margin-top:1rem;">
        <details open class="mrt-help-box" style="border:1px solid #ccd0d4; border-radius:6px; background:#fff;">
            <summary style="cursor:pointer; padding:.6rem .8rem; font-weight:600;">
                <?php echo esc_html__('📥 Import Guide – format & examples', 'museum-railway-timetable'); ?>
            </summary>
            <div style="padding:.8rem 1rem 1rem 1rem;">
                <p><strong><?php echo esc_html__('General', 'museum-railway-timetable'); ?></strong></p>
                <ul style="list-style:disc; padding-left:1.2rem;">
                    <li><?php echo esc_html__('CSV must have a header row and use comma as the delimiter.', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('Times: HH:MM (24h). Dates: YYYY-MM-DD.', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('Decimals for lat/lng must use a dot (e.g. 57.486), not a comma.', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('Services are created automatically if the title does not exist. Stations must already exist (via the stations import).', 'museum-railway-timetable'); ?></li>
                </ul>

                <hr />

                <h3 style="margin:.6rem 0;"><?php echo esc_html__('1) Stations', 'museum-railway-timetable'); ?></h3>
                <p><strong><?php echo esc_html__('Headers:', 'museum-railway-timetable'); ?></strong> <code>name,station_type,lat,lng,display_order</code></p>
                <ul style="list-style:disc; padding-left:1.2rem;">
                    <li><?php echo esc_html__('station_type: station | halt | depot | museum', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('lat/lng are optional (decimals with dot).', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('display_order is an integer used for sorting in lists.', 'museum-railway-timetable'); ?></li>
                </ul>
<pre style="white-space:pre; background:#f6f7f7; border:1px solid #e2e4e7; padding:.6rem; overflow:auto;">name,station_type,lat,lng,display_order
Hultsfred Museum,station,57.486,15.842,1
Skoghult Halt,halt,57.501,15.900,2
Depån,depot,57.480,15.830,99
</pre>

                <hr />

                <h3 style="margin:.6rem 0;"><?php echo esc_html__('2) Stop Times', 'museum-railway-timetable'); ?></h3>
                <p><strong><?php echo esc_html__('Headers:', 'museum-railway-timetable'); ?></strong> <code>service,station,sequence,arrive,depart,pickup,dropoff</code></p>
                <ul style="list-style:disc; padding-left:1.2rem;">
                    <li><?php echo esc_html__('service = the trip name (must match title exactly; will be created if needed).', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('station = the station name (must be imported first).', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('sequence = 1..n (order along the route).', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('arrive/depart = HH:MM. For first stop arrive can be empty, for last stop depart can be empty.', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('pickup/dropoff = 1 or 0 (optional; default 1).', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('Importer replaces existing stop times for a service (removes and inserts new).', 'museum-railway-timetable'); ?></li>
                </ul>
<pre style="white-space:pre; background:#f6f7f7; border:1px solid #e2e4e7; padding:.6rem; overflow:auto;">service,station,sequence,arrive,depart,pickup,dropoff
Steam Train A,Hultsfred Museum,1,,10:00,1,1
Steam Train A,Skoghult Halt,2,10:25,10:27,1,1
Steam Train A,Depån,3,10:45,,0,1
</pre>

                <hr />

                <h3 style="margin:.6rem 0;"><?php echo esc_html__('3) Calendar (service days)', 'museum-railway-timetable'); ?></h3>
                <p><strong><?php echo esc_html__('Headers:', 'museum-railway-timetable'); ?></strong> <code>service,start_date,end_date,mon,tue,wed,thu,fri,sat,sun,include_dates,exclude_dates</code></p>
                <ul style="list-style:disc; padding-left:1.2rem;">
                    <li><?php echo esc_html__('The interval defines the base, weekdays (0/1) specify which days apply within the interval.', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('include_dates/exclude_dates: comma-separated YYYY-MM-DD for extra/cancelled days.', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('You can have multiple rows for the same service with different periods.', 'museum-railway-timetable'); ?></li>
                </ul>
<pre style="white-space:pre; background:#f6f7f7; border:1px solid #e2e4e7; padding:.6rem; overflow:auto;">service,start_date,end_date,mon,tue,wed,thu,fri,sat,sun,include_dates,exclude_dates
Steam Train A,2025-06-01,2025-08-31,0,0,0,0,0,1,1,2025-06-06,
Steam Train B,2025-07-01,2025-07-31,0,0,0,0,1,1,0,,2025-07-20
</pre>

                <hr />

                <h3 style="margin:.6rem 0;"><?php echo esc_html__('Common mistakes & tips', 'museum-railway-timetable'); ?></h3>
                <ul style="list-style:disc; padding-left:1.2rem;">
                    <li><?php echo esc_html__('Excel may save with semicolons; choose “CSV (comma delimited)” or open in a text editor and replace ";" with ",".', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('Check that station names in stop times exactly match the titles in “Stations”.', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('Time format must be HH:MM (e.g. 09:05, not 9:5).', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('Lat/Lng should use a dot as the decimal separator.', 'museum-railway-timetable'); ?></li>
                    <li><?php echo esc_html__('Calendar: ensure start_date ≤ end_date and correct weekdays set to 1.', 'museum-railway-timetable'); ?></li>
                </ul>
            </div>
        </details>
    </div>
    <?php
}

// Minimal CSV parser for pasted text areas; expects header row and comma delimiter
function MRT_parse_csv($csv) {
    $lines = preg_split('/\R/u', trim($csv));
    if (!$lines || count($lines) < 2) return [];
    $headers = str_getcsv(array_shift($lines));
    $rows = [];
    foreach ($lines as $line) {
        if ('' === trim($line)) continue;
        $vals = str_getcsv($line);
        $row = [];
        foreach ($headers as $i => $h) {
            $key = sanitize_key($h);
            $row[$key] = $vals[$i] ?? '';
        }
        $rows[] = $row;
    }
    return $rows;
}

function MRT_validate_time_hhmm($s) {
    // Accept empty for first/last stop cases
    if ($s === '' || $s === null) return true;
    return (bool) preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $s);
}

function MRT_validate_date($s) {
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $s);
}

function MRT_render_import_page() {
    if (!current_user_can('manage_options')) { return; }
    $active = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'stations';
    $tabs = [
        'stations' => __('Stations', 'museum-railway-timetable'),
        'stoptimes' => __('Stop Times', 'museum-railway-timetable'),
        'calendar' => __('Calendar', 'museum-railway-timetable'),
    ];
    ?>
    <div class="wrap">
        <h1><?php _e('CSV Import', 'museum-railway-timetable'); ?></h1>
        <h2 class="nav-tab-wrapper">
            <?php foreach ($tabs as $key => $label): ?>
                <a class="nav-tab <?php echo $active === $key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=mrt_import&tab='.$key)); ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </h2>

        <?php MRT_import_help_panel(); ?>

        <?php
        // Sample CSV download buttons (secured via nonce and capability check)
        $dl_base = admin_url('admin-post.php');
        $nonce   = wp_create_nonce('mrt_download_csv');
        $links = [
            'stations'  => add_query_arg(['action'=>'mrt_download_csv','type'=>'stations','_wpnonce'=>$nonce], $dl_base),
            'stoptimes' => add_query_arg(['action'=>'mrt_download_csv','type'=>'stoptimes','_wpnonce'=>$nonce], $dl_base),
            'calendar'  => add_query_arg(['action'=>'mrt_download_csv','type'=>'calendar','_wpnonce'=>$nonce], $dl_base),
        ];
        ?>
        <div class="mrt-download-examples" style="margin:.75rem 0 1rem 0; display:flex; gap:.5rem; flex-wrap:wrap;">
            <a class="button" href="<?php echo esc_url($links['stations']); ?>"><?php echo esc_html__('Download sample: Stations CSV', 'museum-railway-timetable'); ?></a>
            <a class="button" href="<?php echo esc_url($links['stoptimes']); ?>"><?php echo esc_html__('Download sample: Stop Times CSV', 'museum-railway-timetable'); ?></a>
            <a class="button" href="<?php echo esc_url($links['calendar']); ?>"><?php echo esc_html__('Download sample: Calendar CSV', 'museum-railway-timetable'); ?></a>
        </div>

        <form method="post">
            <?php wp_nonce_field('mrt_import_'.$active, 'mrt_import_nonce'); ?>
            <p><textarea name="csv" rows="12" style="width:100%;" placeholder="<?php echo esc_attr__('Paste CSV here', 'museum-railway-timetable'); ?>"></textarea></p>
            <p><button class="button button-primary"><?php _e('Import', 'museum-railway-timetable'); ?></button></p>
            <input type="hidden" name="tab" value="<?php echo esc_attr($active); ?>" />
        </form>

        <div style="margin-top:1rem;">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csv'])) {
            $tab = sanitize_text_field($_POST['tab'] ?? 'stations');
            if (!wp_verify_nonce($_POST['mrt_import_nonce'] ?? '', 'mrt_import_'.$tab)) {
                echo '<div class="notice notice-error"><p>'.esc_html__('Nonce failed.', 'museum-railway-timetable').'</p></div>';
            } else {
                $csv = trim(stripslashes($_POST['csv']));
                $result = MRT_handle_csv_import($tab, $csv);
                echo $result ? '<div class="notice notice-success"><p>'.esc_html($result).'</p></div>' : '';
            }
        }
        ?>
        </div>
    </div>
    <?php
}

function MRT_handle_csv_import($tab, $csv) {
    // Dispatch import by selected tab type
    $rows = MRT_parse_csv($csv);
    if (!$rows) return __('No rows parsed. Check CSV headers.', 'museum-railway-timetable');

    switch ($tab) {
        case 'stations':
            return MRT_import_stations($rows);
        case 'stoptimes':
            return MRT_import_stoptimes($rows);
        case 'calendar':
            return MRT_import_calendar($rows);
        default:
            return __('Unknown tab.', 'museum-railway-timetable');
    }
}

// Stations importer
// headers: name,station_type,lat,lng,display_order
function MRT_import_stations($rows) {
    $created = 0; $updated = 0;
    foreach ($rows as $r) {
        $name = sanitize_text_field($r['name'] ?? '');
        if (!$name) continue;
        $type = sanitize_text_field($r['station_type'] ?? '');
        $lat  = isset($r['lat']) ? floatval($r['lat']) : null;
        $lng  = isset($r['lng']) ? floatval($r['lng']) : null;
        $order = isset($r['display_order']) ? intval($r['display_order']) : 0;

        // Find existing station by title
        $existing = get_page_by_title($name, OBJECT, 'mrt_station');
        if ($existing) {
            update_post_meta($existing->ID, 'mrt_station_type', $type);
            if ($lat !== null) update_post_meta($existing->ID, 'mrt_lat', $lat);
            if ($lng !== null) update_post_meta($existing->ID, 'mrt_lng', $lng);
            update_post_meta($existing->ID, 'mrt_display_order', $order);
            $updated++;
        } else {
            $id = wp_insert_post([
                'post_type' => 'mrt_station',
                'post_title' => $name,
                'post_status' => 'publish',
            ]);
            if ($id && !is_wp_error($id)) {
                update_post_meta($id, 'mrt_station_type', $type);
                if ($lat !== null) update_post_meta($id, 'mrt_lat', $lat);
                if ($lng !== null) update_post_meta($id, 'mrt_lng', $lng);
                update_post_meta($id, 'mrt_display_order', $order);
                $created++;
            }
        }
    }
    return sprintf(__('Stations: %d created, %d updated.', 'museum-railway-timetable'), $created, $updated);
}

// Helpers: lookups/creation by title
function MRT_get_station_id_by_name($name) {
    $post = get_page_by_title($name, OBJECT, 'mrt_station');
    return $post ? intval($post->ID) : 0;
}
function MRT_get_service_id_by_name($name) {
    $post = get_page_by_title($name, OBJECT, 'mrt_service');
    if ($post) return intval($post->ID);
    // Auto-create service if not found
    $id = wp_insert_post(['post_type' => 'mrt_service', 'post_title' => sanitize_text_field($name), 'post_status' => 'publish']);
    return is_wp_error($id) ? 0 : intval($id);
}

// Stop times importer
// headers: service,station,sequence,arrive,depart,pickup,dropoff
function MRT_import_stoptimes($rows) {
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_stoptimes';
    $inserted = 0; $skipped = 0;

    // Group by service; for simplicity we replace existing stop times per service
    $by_service = [];
    foreach ($rows as $r) {
        $by_service[ $r['service'] ?? '' ][] = $r;
    }

    foreach ($by_service as $serviceName => $items) {
        $service_id = MRT_get_service_id_by_name($serviceName);
        if (!$service_id) { $skipped += count($items); continue; }
        // Remove existing rows for this service
        $wpdb->delete($table, ['service_post_id' => $service_id]);

        foreach ($items as $r) {
            $station_id = MRT_get_station_id_by_name($r['station'] ?? '');
            if (!$station_id) { $skipped++; continue; }
            $seq  = intval($r['sequence'] ?? 0);
            $arr  = trim($r['arrive'] ?? '') ?: null;
            $dep  = trim($r['depart'] ?? '') ?: null;
            if (!MRT_validate_time_hhmm($arr) || !MRT_validate_time_hhmm($dep)) { $skipped++; continue; }
            $pick = isset($r['pickup']) ? intval($r['pickup']) : 1;
            $drop = isset($r['dropoff']) ? intval($r['dropoff']) : 1;

            $wpdb->insert($table, [
                'service_post_id' => $service_id,
                'station_post_id' => $station_id,
                'stop_sequence'   => $seq,
                'arrival_time'    => $arr,
                'departure_time'  => $dep,
                'pickup_allowed'  => $pick,
                'dropoff_allowed' => $drop,
            ]);
            if (!$wpdb->last_error) $inserted++;
        }
    }
    return sprintf(__('Stop times: %d inserted, %d skipped.', 'museum-railway-timetable'), $inserted, $skipped);
}

// Calendar importer
// headers: service,start_date,end_date,mon,tue,wed,thu,fri,sat,sun,include_dates,exclude_dates
function MRT_import_calendar($rows) {
    global $wpdb;
    $table = $wpdb->prefix . 'mrt_calendar';
    $inserted = 0; $skipped = 0;

    foreach ($rows as $r) {
        $service_id = MRT_get_service_id_by_name($r['service'] ?? '');
        if (!$service_id) { $skipped++; continue; }
        $start = sanitize_text_field($r['start_date'] ?? '');
        $end   = sanitize_text_field($r['end_date'] ?? '');
        if (!MRT_validate_date($start) || !MRT_validate_date($end)) { $skipped++; continue; }
        $days = ['mon','tue','wed','thu','fri','sat','sun'];
        $vals = [];
        foreach ($days as $d) { $vals[$d] = intval($r[$d] ?? 0); }

        $inc = sanitize_text_field($r['include_dates'] ?? '');
        $exc = sanitize_text_field($r['exclude_dates'] ?? '');

        $wpdb->insert($table, array_merge([
            'service_post_id' => $service_id,
            'start_date' => $start,
            'end_date'   => $end,
            'include_dates' => $inc ?: null,
            'exclude_dates' => $exc ?: null,
        ], $vals));

        if (!$wpdb->last_error) $inserted++; else $skipped++;
    }

    return sprintf(__('Calendar: %d inserted, %d skipped.', 'museum-railway-timetable'), $inserted, $skipped);
}

// Sample CSV generators (raw data; matches importer expectations)
function MRT_sample_csv_stations() {
    $rows = [
        'name,station_type,lat,lng,display_order',
        'Hultsfred Museum,station,57.486,15.842,1',
        'Skoghult Halt,halt,57.501,15.900,2',
        'Depån,depot,57.480,15.830,99',
    ];
    return implode("\n", $rows) . "\n";
}

function MRT_sample_csv_stoptimes() {
    $rows = [
        'service,station,sequence,arrive,depart,pickup,dropoff',
        'Steam Train A,Hultsfred Museum,1,,10:00,1,1',
        'Steam Train A,Skoghult Halt,2,10:25,10:27,1,1',
        'Steam Train A,Depån,3,10:45,,0,1',
    ];
    return implode("\n", $rows) . "\n";
}

function MRT_sample_csv_calendar() {
    $rows = [
        'service,start_date,end_date,mon,tue,wed,thu,fri,sat,sun,include_dates,exclude_dates',
        'Steam Train A,2025-06-01,2025-08-31,0,0,0,0,0,1,1,2025-06-06,',
        'Steam Train B,2025-07-01,2025-07-31,0,0,0,0,1,1,0,,2025-07-20',
    ];
    return implode("\n", $rows) . "\n";
}

// Admin-post handler to serve sample CSV downloads
add_action('admin_post_mrt_download_csv', 'MRT_handle_download_csv');
function MRT_handle_download_csv() {
    if ( ! current_user_can('manage_options') ) {
        wp_die( esc_html__('You do not have permission to access this resource.', 'museum-railway-timetable'), 403 );
    }
    if ( empty($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'mrt_download_csv') ) {
        wp_die( esc_html__('Nonce verification failed.', 'museum-railway-timetable'), 400 );
    }

    $type = isset($_GET['type']) ? sanitize_key($_GET['type']) : '';
    switch ($type) {
        case 'stations':
            $filename = 'stations-sample.csv';
            $csv = MRT_sample_csv_stations();
            break;
        case 'stoptimes':
            $filename = 'stoptimes-sample.csv';
            $csv = MRT_sample_csv_stoptimes();
            break;
        case 'calendar':
            $filename = 'calendar-sample.csv';
            $csv = MRT_sample_csv_calendar();
            break;
        default:
            wp_die( esc_html__('Unknown CSV type.', 'museum-railway-timetable'), 400 );
    }

    // Send as download with sane headers
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    echo $csv;
    exit;
}
