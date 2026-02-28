<?php
/**
 * Timetable dates UI script (inline for locale/translations)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Output the timetable dates JavaScript
 *
 * @param array $dates Array of date strings (YYYY-MM-DD)
 */
function MRT_render_timetable_dates_script($dates) {
    $locale = get_locale();
    $no_dates_msg = esc_js(__('No dates selected. Add dates using patterns or single date selection above.', 'museum-railway-timetable'));
    $remove_label = esc_js(__('Remove', 'museum-railway-timetable'));
    $please_select = esc_js(__('Please select a day of week, start date, and end date.', 'museum-railway-timetable'));
    $start_before_end = esc_js(__('Start date must be before or equal to end date.', 'museum-railway-timetable'));
    $added_dates = esc_js(__('Added', 'museum-railway-timetable'));
    $dates_from_pattern = esc_js(__('dates from pattern.', 'museum-railway-timetable'));
    $no_dates_found = esc_js(__('No dates found matching the pattern.', 'museum-railway-timetable'));
    $please_select_date = esc_js(__('Please select a date.', 'museum-railway-timetable'));
    $date_already_added = esc_js(__('This date is already added.', 'museum-railway-timetable'));
    $date_added = esc_js(__('Date added successfully.', 'museum-railway-timetable'));
    ?>
    <script>
    jQuery(document).ready(function($) {
        var selectedDates = new Set();
        $('#mrt-timetable-dates-container .mrt-date-row').each(function() {
            var date = $(this).data('date');
            if (date) selectedDates.add(date);
        });
        var phpDates = <?php echo json_encode($dates); ?>;
        if (Array.isArray(phpDates)) {
            phpDates.forEach(function(date) {
                if (date) selectedDates.add(date);
            });
        }

        function updateDatesList() {
            var $container = $('#mrt-timetable-dates-container');
            $container.empty();
            if (selectedDates.size === 0) {
                $container.after('<p class="description" id="mrt-no-dates-message"><?php echo $no_dates_msg; ?></p>');
                return;
            }
            $('#mrt-no-dates-message').remove();
            var sortedDates = Array.from(selectedDates).sort();
            sortedDates.forEach(function(date) {
                if (!date || typeof date !== 'string') return;
                var dateObj = new Date(date + 'T00:00:00');
                var formattedDate;
                if (isNaN(dateObj.getTime())) {
                    formattedDate = date;
                } else {
                    try {
                        formattedDate = dateObj.toLocaleDateString('<?php echo esc_js($locale); ?>', {
                            year: 'numeric', month: 'long', day: 'numeric', weekday: 'long'
                        });
                        if (!formattedDate || formattedDate === 'Invalid Date') {
                            formattedDate = dateObj.toLocaleDateString();
                        }
                    } catch (e) {
                        formattedDate = dateObj.toLocaleDateString();
                    }
                }
                var $row = $('<div class="mrt-box mrt-box-sm mrt-form-row mrt-date-row" data-date="' + date.replace(/"/g, '&quot;') + '">' +
                    '<input type="hidden" name="mrt_timetable_dates[]" value="' + date.replace(/"/g, '&quot;') + '" />' +
                    '<span class="mrt-font-medium mrt-flex-1">' + formattedDate + '</span> ' +
                    '<span class="mrt-text-tertiary mrt-ml-sm">(' + date + ')</span> ' +
                    '<button type="button" class="button button-small mrt-remove-date mrt-date-remove-button mrt-ml-1"><?php echo $remove_label; ?></button>' +
                    '</div>');
                $container.append($row);
            });
        }

        $('#mrt-add-pattern-dates').on('click', function() {
            var weekday = parseInt($('#mrt-pattern-weekday').val());
            var startDate = $('#mrt-pattern-start-date').val();
            var endDate = $('#mrt-pattern-end-date').val();
            if (!weekday || weekday === '' || !startDate || !endDate) {
                alert('<?php echo $please_select; ?>');
                return;
            }
            var start = new Date(startDate + 'T00:00:00');
            var end = new Date(endDate + 'T00:00:00');
            if (start > end) {
                alert('<?php echo $start_before_end; ?>');
                return;
            }
            var current = new Date(start);
            var added = 0;
            while (current <= end) {
                if (current.getDay() === weekday) {
                    selectedDates.add(current.toISOString().split('T')[0]);
                    added++;
                }
                current.setDate(current.getDate() + 1);
            }
            updateDatesList();
            if (added > 0) {
                var $msg = $('<div class="mrt-success-message notice notice-success is-dismissible"><p><?php echo $added_dates; ?> ' + added + ' <?php echo $dates_from_pattern; ?></p></div>');
                $('#mrt-timetable-dates-container').before($msg);
                setTimeout(function() { $msg.fadeOut(300, function() { $(this).remove(); }); }, 3000);
            } else {
                alert('<?php echo $no_dates_found; ?>');
            }
        });

        $('#mrt-add-single-date').on('click', function() {
            var date = $('#mrt-single-date').val();
            if (!date) {
                alert('<?php echo $please_select_date; ?>');
                return;
            }
            if (selectedDates.has(date)) {
                alert('<?php echo $date_already_added; ?>');
                return;
            }
            selectedDates.add(date);
            updateDatesList();
            $('#mrt-single-date').val('');
            var $msg = $('<div class="mrt-success-message notice notice-success is-dismissible mrt-my-1"><p><?php echo $date_added; ?></p></div>');
            $('#mrt-timetable-dates-container').before($msg);
            setTimeout(function() { $msg.fadeOut(300, function() { $(this).remove(); }); }, 3000);
        });

        $(document).on('click', '.mrt-remove-date', function() {
            var $row = $(this).closest('.mrt-date-row');
            selectedDates.delete($row.data('date'));
            updateDatesList();
        });

        updateDatesList();
    });
    </script>
    <?php
}
