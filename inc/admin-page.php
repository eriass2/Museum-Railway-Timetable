<?php
if (!defined('ABSPATH')) { exit; }

// Add a top-level menu for the plugin settings
add_action('admin_menu', function () {
    add_menu_page(
        __('Museum Railway Timetable', 'museum-railway-timetable'),
        __('Railway Timetable', 'museum-railway-timetable'),
        'manage_options',
        'mrt_settings',
        'MRT_render_admin_page',
        'dashicons-calendar-alt'
    );
});

// Register basic settings
add_action('admin_init', function () {
    register_setting('mrt_group', 'mrt_settings', [
        'type' => 'array',
        'sanitize_callback' => 'MRT_sanitize_settings',
        'default' => ['enabled' => true, 'note' => '']
    ]);

    add_settings_section(
        'mrt_main',
        __('General Settings', 'museum-railway-timetable'),
        function(){ echo '<p>' . __('Configure timetable display.', 'museum-railway-timetable') . '</p>'; },
        'mrt_settings'
    );

    add_settings_field(
        'mrt_enabled',
        __('Enable Plugin', 'museum-railway-timetable'),
        'MRT_render_enabled_field',
        'mrt_settings',
        'mrt_main'
    );

    add_settings_field(
        'mrt_note',
        __('Note', 'museum-railway-timetable'),
        'MRT_render_note_field',
        'mrt_settings',
        'mrt_main'
    );
});

function MRT_sanitize_settings($input) {
    return [
        'enabled' => !empty($input['enabled']),
        'note' => isset($input['note']) ? sanitize_text_field($input['note']) : '',
    ];
}

function MRT_render_enabled_field() {
    $opts = get_option('mrt_settings');
    echo '<input type="checkbox" name="mrt_settings[enabled]" value="1" ' . checked(!empty($opts['enabled']), true, false) . ' />';
}

function MRT_render_note_field() {
    $opts = get_option('mrt_settings');
    echo '<input type="text" name="mrt_settings[note]" value="' . esc_attr($opts['note'] ?? '') . '" class="regular-text" />';
}

function MRT_render_admin_page() {
    if (!current_user_can('manage_options')) { return; }
    ?>
    <div class="wrap">
        <h1><?php _e('Museum Railway Timetable', 'museum-railway-timetable'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mrt_group');
            do_settings_sections('mrt_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
