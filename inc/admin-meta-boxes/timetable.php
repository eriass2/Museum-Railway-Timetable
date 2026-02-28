<?php
/**
 * Timetable meta box
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render timetable meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_timetable_meta_box($post) {
    wp_nonce_field('mrt_save_timetable_meta', 'mrt_timetable_meta_nonce');
    
    $dates = MRT_get_timetable_dates($post->ID);
    // Ensure dates is always an array
    if (!is_array($dates)) {
        $dates = [];
    }
    // Filter out any empty or invalid dates
    $dates = array_filter($dates, function($date) {
        return !empty($date) && MRT_validate_date($date);
    });
    // Re-index array after filtering
    $dates = array_values($dates);
    
    wp_enqueue_script('jquery');
    ?>
    <div class="mrt-info-box">
        <p><strong><?php esc_html_e('💡 What is a Timetable?', 'museum-railway-timetable'); ?></strong></p>
        <p><?php esc_html_e('A timetable defines which days (dates) trains run. You can add dates using patterns (e.g., all Wednesdays in June-September) or add specific dates. You can also remove individual dates from patterns.', 'museum-railway-timetable'); ?></p>
    </div>

    <?php $timetable_type = get_post_meta($post->ID, 'mrt_timetable_type', true); ?>
    <table class="form-table mrt-form-table-mb">
        <tr>
            <th><label for="mrt_timetable_type"><?php esc_html_e('Timetable type', 'museum-railway-timetable'); ?></label></th>
            <td>
                <select name="mrt_timetable_type" id="mrt_timetable_type" class="mrt-meta-field">
                    <option value=""><?php esc_html_e('— None —', 'museum-railway-timetable'); ?></option>
                    <option value="green" <?php selected($timetable_type, 'green'); ?>><?php esc_html_e('Grön (Green)', 'museum-railway-timetable'); ?></option>
                    <option value="red" <?php selected($timetable_type, 'red'); ?>><?php esc_html_e('Röd (Red)', 'museum-railway-timetable'); ?></option>
                    <option value="yellow" <?php selected($timetable_type, 'yellow'); ?>><?php esc_html_e('Gul (Yellow)', 'museum-railway-timetable'); ?></option>
                    <option value="orange" <?php selected($timetable_type, 'orange'); ?>><?php esc_html_e('Orange', 'museum-railway-timetable'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Shows as "GRÖN TIDTABELL", "RÖD TIDTABELL" etc. in the timetable overview.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
    </table>
    
    <!-- Pattern-based date selection -->
    <div class="mrt-date-pattern-section">
        <h3 class="mrt-section-heading"><?php esc_html_e('Add Dates from Pattern', 'museum-railway-timetable'); ?></h3>
        <p class="description"><?php esc_html_e('Select a day of the week and a date range to automatically add all matching dates.', 'museum-railway-timetable'); ?></p>
        <table class="form-table mrt-form-table-mt">
            <tr>
                <th class="mrt-th-label-150"><label for="mrt-pattern-weekday"><?php esc_html_e('Day of Week', 'museum-railway-timetable'); ?></label></th>
                <td>
                    <select id="mrt-pattern-weekday" class="mrt-meta-field">
                        <option value=""><?php esc_html_e('— Select Day —', 'museum-railway-timetable'); ?></option>
                        <option value="1"><?php esc_html_e('Monday', 'museum-railway-timetable'); ?></option>
                        <option value="2"><?php esc_html_e('Tuesday', 'museum-railway-timetable'); ?></option>
                        <option value="3"><?php esc_html_e('Wednesday', 'museum-railway-timetable'); ?></option>
                        <option value="4"><?php esc_html_e('Thursday', 'museum-railway-timetable'); ?></option>
                        <option value="5"><?php esc_html_e('Friday', 'museum-railway-timetable'); ?></option>
                        <option value="6"><?php esc_html_e('Saturday', 'museum-railway-timetable'); ?></option>
                        <option value="0"><?php esc_html_e('Sunday', 'museum-railway-timetable'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="mrt-pattern-start-date"><?php esc_html_e('From Date', 'museum-railway-timetable'); ?></label></th>
                <td>
                    <input type="date" id="mrt-pattern-start-date" class="mrt-meta-field" />
                </td>
            </tr>
            <tr>
                <th><label for="mrt-pattern-end-date"><?php esc_html_e('To Date', 'museum-railway-timetable'); ?></label></th>
                <td>
                    <input type="date" id="mrt-pattern-end-date" class="mrt-meta-field" />
                </td>
            </tr>
        </table>
        <button type="button" id="mrt-add-pattern-dates" class="button button-primary"><?php esc_html_e('Add Dates from Pattern', 'museum-railway-timetable'); ?></button>
    </div>
    
    <!-- Single date addition -->
    <div class="mrt-date-single-section">
        <h3 class="mrt-section-heading"><?php esc_html_e('Add Single Date', 'museum-railway-timetable'); ?></h3>
        <p class="description"><?php esc_html_e('Add a specific date manually.', 'museum-railway-timetable'); ?></p>
        <p>
            <input type="date" id="mrt-single-date" class="mrt-meta-field mrt-single-date-input" />
            <button type="button" id="mrt-add-single-date" class="button"><?php esc_html_e('Add Date', 'museum-railway-timetable'); ?></button>
        </p>
    </div>
    
    <!-- Selected dates list -->
    <div class="mrt-selected-dates-section">
        <h3><?php esc_html_e('Selected Dates', 'museum-railway-timetable'); ?></h3>
        <p class="description"><?php esc_html_e('All dates when this timetable applies. Click "Remove" to remove individual dates.', 'museum-railway-timetable'); ?></p>
        <div id="mrt-timetable-dates-container" class="mrt-dates-container">
            <?php foreach ($dates as $index => $date): ?>
                <div class="mrt-date-row" data-date="<?php echo esc_attr($date); ?>">
                    <input type="hidden" name="mrt_timetable_dates[]" value="<?php echo esc_attr($date); ?>" />
                    <span class="mrt-date-display"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($date))); ?></span>
                    <span class="mrt-date-iso">(<?php echo esc_html($date); ?>)</span>
                    <button type="button" class="button button-small mrt-remove-date mrt-date-remove-button mrt-date-remove-btn"><?php esc_html_e('Remove', 'museum-railway-timetable'); ?></button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($dates)): ?>
            <p class="description" id="mrt-no-dates-message"><?php esc_html_e('No dates selected. Add dates using patterns or single date selection above.', 'museum-railway-timetable'); ?></p>
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Initialize Set from PHP-rendered dates first
        var selectedDates = new Set();
        $('#mrt-timetable-dates-container .mrt-date-row').each(function() {
            var date = $(this).data('date');
            if (date) {
                selectedDates.add(date);
            }
        });
        
        // Also add dates from PHP array (in case they're not in DOM yet)
        var phpDates = <?php echo json_encode($dates); ?>;
        if (Array.isArray(phpDates)) {
            phpDates.forEach(function(date) {
                if (date) {
                    selectedDates.add(date);
                }
            });
        }
        
        function updateDatesList() {
            var $container = $('#mrt-timetable-dates-container');
            $container.empty();
            
            if (selectedDates.size === 0) {
                $container.after('<p class="description" id="mrt-no-dates-message"><?php echo esc_js(__('No dates selected. Add dates using patterns or single date selection above.', 'museum-railway-timetable')); ?></p>');
                return;
            }
            
            $('#mrt-no-dates-message').remove();
            
            var sortedDates = Array.from(selectedDates).sort();
            sortedDates.forEach(function(date) {
                if (!date || typeof date !== 'string') {
                    return; // Skip invalid dates
                }
                
                // Format date for display
                var dateObj = new Date(date + 'T00:00:00');
                var formattedDate;
                if (isNaN(dateObj.getTime())) {
                    // Invalid date, just show the raw date string
                    formattedDate = date;
                } else {
                    try {
                        formattedDate = dateObj.toLocaleDateString('<?php echo esc_js(get_locale()); ?>', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric',
                            weekday: 'long'
                        });
                        // Fallback if locale formatting fails
                        if (!formattedDate || formattedDate === 'Invalid Date') {
                            formattedDate = dateObj.toLocaleDateString();
                        }
                    } catch (e) {
                        formattedDate = dateObj.toLocaleDateString();
                    }
                }
                
                var $row = $('<div class="mrt-date-row" data-date="' + date.replace(/"/g, '&quot;') + '">' +
                    '<input type="hidden" name="mrt_timetable_dates[]" value="' + date.replace(/"/g, '&quot;') + '" />' +
                    '<span class="mrt-date-display">' + formattedDate + '</span> ' +
                    '<span class="mrt-date-iso">(' + date + ')</span> ' +
                    '<button type="button" class="button button-small mrt-remove-date mrt-date-remove-button mrt-date-remove-btn"><?php echo esc_js(__('Remove', 'museum-railway-timetable')); ?></button>' +
                    '</div>');
                $container.append($row);
            });
        }
        
        // Add dates from pattern
        $('#mrt-add-pattern-dates').on('click', function() {
            var weekday = parseInt($('#mrt-pattern-weekday').val());
            var startDate = $('#mrt-pattern-start-date').val();
            var endDate = $('#mrt-pattern-end-date').val();
            
            if (!weekday || weekday === '' || !startDate || !endDate) {
                alert('<?php echo esc_js(__('Please select a day of week, start date, and end date.', 'museum-railway-timetable')); ?>');
                return;
            }
            
            var start = new Date(startDate + 'T00:00:00');
            var end = new Date(endDate + 'T00:00:00');
            
            if (start > end) {
                alert('<?php echo esc_js(__('Start date must be before or equal to end date.', 'museum-railway-timetable')); ?>');
                return;
            }
            
            var current = new Date(start);
            var added = 0;
            
            while (current <= end) {
                if (current.getDay() === weekday) {
                    var dateStr = current.toISOString().split('T')[0];
                    selectedDates.add(dateStr);
                    added++;
                }
                current.setDate(current.getDate() + 1);
            }
            
            updateDatesList();
            
            if (added > 0) {
                var $successMsg = $('<div class="mrt-success-message notice notice-success is-dismissible"><p><?php echo esc_js(__('Added', 'museum-railway-timetable')); ?> ' + added + ' <?php echo esc_js(__('dates from pattern.', 'museum-railway-timetable')); ?></p></div>');
                $('#mrt-timetable-dates-container').before($successMsg);
                setTimeout(function() {
                    $successMsg.fadeOut(300, function() { $(this).remove(); });
                }, 3000);
            } else {
                alert('<?php echo esc_js(__('No dates found matching the pattern.', 'museum-railway-timetable')); ?>');
            }
        });
        
        // Add single date
        $('#mrt-add-single-date').on('click', function() {
            var date = $('#mrt-single-date').val();
            if (!date) {
                alert('<?php echo esc_js(__('Please select a date.', 'museum-railway-timetable')); ?>');
                return;
            }
            
            if (selectedDates.has(date)) {
                alert('<?php echo esc_js(__('This date is already added.', 'museum-railway-timetable')); ?>');
                return;
            }
            
            selectedDates.add(date);
            updateDatesList();
            $('#mrt-single-date').val('');
            
            var $successMsg = $('<div class="mrt-success-message notice notice-success is-dismissible"><p><?php echo esc_js(__('Date added successfully.', 'museum-railway-timetable')); ?></p></div>');
            $('#mrt-timetable-dates-container').before($successMsg);
            setTimeout(function() {
                $successMsg.fadeOut(300, function() { $(this).remove(); });
            }, 3000);
        });
        
        // Remove date
        $(document).on('click', '.mrt-remove-date', function() {
            var $row = $(this).closest('.mrt-date-row');
            var date = $row.data('date');
            selectedDates.delete(date);
            updateDatesList();
        });
        
        // Initialize
        updateDatesList();
    });
    </script>
    <?php
}

/**
 * Save timetable meta box data
 *
 * @param int $post_id Post ID
 */
add_action('save_post_mrt_timetable', function($post_id) {
    // Check nonce
    if (!isset($_POST['mrt_timetable_meta_nonce']) || !wp_verify_nonce($_POST['mrt_timetable_meta_nonce'], 'mrt_save_timetable_meta')) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save timetable type (green, red, yellow, orange)
    if (isset($_POST['mrt_timetable_type'])) {
        $type = sanitize_text_field($_POST['mrt_timetable_type']);
        $allowed = ['green', 'red', 'yellow', 'orange', ''];
        if (in_array($type, $allowed, true)) {
            update_post_meta($post_id, 'mrt_timetable_type', $type);
        }
    }

    // Save timetable dates (array)
    if (isset($_POST['mrt_timetable_dates']) && is_array($_POST['mrt_timetable_dates'])) {
        $dates = [];
        foreach ($_POST['mrt_timetable_dates'] as $date) {
            $date = sanitize_text_field($date);
            if (MRT_validate_date($date)) {
                $dates[] = $date;
            }
        }
        // Remove duplicates and sort
        $dates = array_unique($dates);
        sort($dates);
        if (!empty($dates)) {
            update_post_meta($post_id, 'mrt_timetable_dates', $dates);
            // Remove old single date field if it exists
            delete_post_meta($post_id, 'mrt_timetable_date');
        } else {
            // Only delete if explicitly empty array was sent
            // Don't delete if field wasn't sent at all (might be autosave or other issue)
            delete_post_meta($post_id, 'mrt_timetable_dates');
        }
    } else {
        // If mrt_timetable_dates is not set, keep existing dates (don't delete them)
        // This handles cases where the field might not be included in the form submission
    }
});
