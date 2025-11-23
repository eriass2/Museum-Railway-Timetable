/**
 * Admin JavaScript for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    /**
     * Initialize admin functionality when DOM is ready
     */
    $(function() {
        // Initialize admin features
        initAdminFeatures();
        initStopTimesUI();
        initCalendarUI();

        // Debug logging (only in development)
        if (typeof console !== 'undefined' && console.log && window.mrtDebug) {
            console.log('Museum Railway Timetable admin loaded.');
        }
    });

    /**
     * Initialize admin-specific features
     */
    function initAdminFeatures() {
        // Add event handlers for admin interface
        // Example: Handle form submissions, toggle elements, etc.
        
        // Station picker auto-submit (if needed)
        $('.mrt-picker select').on('change', function() {
            // Auto-submit is handled by onchange attribute in HTML
            // This is a placeholder for future JavaScript enhancements
        });
    }

    /**
     * Initialize Stop Times UI
     */
    function initStopTimesUI() {
        var $container = $('#mrt-stoptimes-container');
        if (!$container.length) return;

        var nonce = $('#mrt_stoptimes_nonce').val();

        // Add stop time
        $('#mrt-add-stoptime').on('click', function() {
            var $btn = $(this);
            var editId = $btn.data('edit-id');
            
            if (editId) {
                // Update existing
                var data = {
                    action: 'mrt_update_stoptime',
                    nonce: nonce,
                    id: editId,
                    station_id: $('#mrt-new-station').val(),
                    sequence: $('#mrt-new-sequence').val(),
                    arrival: $('#mrt-new-arrival').val(),
                    departure: $('#mrt-new-departure').val(),
                    pickup: $('#mrt-new-pickup').is(':checked') ? 1 : 0,
                    dropoff: $('#mrt-new-dropoff').is(':checked') ? 1 : 0
                };

                if (!data.station_id || !data.sequence) {
                    alert('Please fill in Station and Sequence.');
                    return;
                }

                $btn.prop('disabled', true).text('Updating...');

                $.post(mrtAdmin.ajaxurl, data, function(response) {
                    if (response.success) {
                        // Reset form
                        $('#mrt-new-station').val('');
                        $('#mrt-new-sequence').val('1');
                        $('#mrt-new-arrival').val('');
                        $('#mrt-new-departure').val('');
                        $('#mrt-new-pickup').prop('checked', true);
                        $('#mrt-new-dropoff').prop('checked', true);
                        $btn.removeData('edit-id').text('Add Stop Time');
                        $('#mrt-cancel-stoptime').hide();
                        location.reload();
                    } else {
                        $btn.prop('disabled', false).text('Update Stop Time');
                        alert(response.data.message || 'Error updating stop time.');
                    }
                });
            } else {
                // Add new
                var serviceId = $btn.data('service-id');
                var data = {
                    action: 'mrt_add_stoptime',
                    nonce: nonce,
                    service_id: serviceId,
                    station_id: $('#mrt-new-station').val(),
                    sequence: $('#mrt-new-sequence').val(),
                    arrival: $('#mrt-new-arrival').val(),
                    departure: $('#mrt-new-departure').val(),
                    pickup: $('#mrt-new-pickup').is(':checked') ? 1 : 0,
                    dropoff: $('#mrt-new-dropoff').is(':checked') ? 1 : 0
                };

                if (!data.station_id || !data.sequence) {
                    alert('Please fill in Station and Sequence.');
                    return;
                }

                $btn.prop('disabled', true).text('Adding...');

                $.post(mrtAdmin.ajaxurl, data, function(response) {
                    if (response.success) {
                        // Reset form
                        $('#mrt-new-station').val('');
                        $('#mrt-new-sequence').val('1');
                        $('#mrt-new-arrival').val('');
                        $('#mrt-new-departure').val('');
                        $('#mrt-new-pickup').prop('checked', true);
                        $('#mrt-new-dropoff').prop('checked', true);
                        location.reload();
                    } else {
                        $btn.prop('disabled', false).text('Add Stop Time');
                        alert(response.data.message || 'Error adding stop time.');
                    }
                });
            }
        });

        // Edit stop time
        $container.on('click', '.mrt-edit-stoptime', function() {
            var id = $(this).data('id');
            $.post(mrtAdmin.ajaxurl, {
                action: 'mrt_get_stoptime',
                nonce: nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    var st = response.data;
                    // Populate form and show edit mode
                    $('#mrt-new-station').val(st.station_post_id);
                    $('#mrt-new-sequence').val(st.stop_sequence);
                    $('#mrt-new-arrival').val(st.arrival_time || '');
                    $('#mrt-new-departure').val(st.departure_time || '');
                    $('#mrt-new-pickup').prop('checked', st.pickup_allowed == 1);
                    $('#mrt-new-dropoff').prop('checked', st.dropoff_allowed == 1);
                    
                    // Change button to update
                    var $btn = $('#mrt-add-stoptime');
                    $btn.data('edit-id', id).text('Update Stop Time');
                    $('#mrt-cancel-stoptime').show();
                    
                    // Scroll to form
                    $('html, body').animate({
                        scrollTop: $('.mrt-add-stoptime-form').offset().top - 100
                    }, 500);
                }
            });
        });
        
        // Cancel edit stop time
        $('#mrt-cancel-stoptime').on('click', function() {
            $('#mrt-new-station').val('');
            $('#mrt-new-sequence').val('1');
            $('#mrt-new-arrival').val('');
            $('#mrt-new-departure').val('');
            $('#mrt-new-pickup').prop('checked', true);
            $('#mrt-new-dropoff').prop('checked', true);
            var $btn = $('#mrt-add-stoptime');
            $btn.removeData('edit-id').text('Add Stop Time');
            $('#mrt-cancel-stoptime').hide();
        });

        // Delete stop time
        $container.on('click', '.mrt-delete-stoptime', function() {
            if (!confirm('Are you sure you want to delete this stop time?')) {
                return;
            }
            var id = $(this).data('id');
            $.post(mrtAdmin.ajaxurl, {
                action: 'mrt_delete_stoptime',
                nonce: nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Error deleting stop time.');
                }
            });
        });
    }

    /**
     * Initialize Calendar UI
     */
    function initCalendarUI() {
        var $container = $('#mrt-calendar-container');
        if (!$container.length) return;

        var nonce = $('#mrt_calendar_nonce').val();

        // Add calendar entry
        $('#mrt-add-calendar').on('click', function() {
            var $btn = $(this);
            var editId = $btn.data('edit-id');
            
            if (editId) {
                // Update existing
                var data = {
                    action: 'mrt_update_calendar',
                    nonce: nonce,
                    id: editId,
                    start_date: $('#mrt-new-start-date').val(),
                    end_date: $('#mrt-new-end-date').val(),
                    mon: $('#mrt-new-mon').is(':checked') ? 1 : 0,
                    tue: $('#mrt-new-tue').is(':checked') ? 1 : 0,
                    wed: $('#mrt-new-wed').is(':checked') ? 1 : 0,
                    thu: $('#mrt-new-thu').is(':checked') ? 1 : 0,
                    fri: $('#mrt-new-fri').is(':checked') ? 1 : 0,
                    sat: $('#mrt-new-sat').is(':checked') ? 1 : 0,
                    sun: $('#mrt-new-sun').is(':checked') ? 1 : 0,
                    include_dates: $('#mrt-new-include-dates').val(),
                    exclude_dates: $('#mrt-new-exclude-dates').val()
                };

                if (!data.start_date || !data.end_date) {
                    alert('Please fill in Start Date and End Date.');
                    return;
                }

                $btn.prop('disabled', true).text('Updating...');

                $.post(mrtAdmin.ajaxurl, data, function(response) {
                    if (response.success) {
                        // Reset form
                        $('#mrt-new-start-date').val('');
                        $('#mrt-new-end-date').val('');
                        $('#mrt-new-mon, #mrt-new-tue, #mrt-new-wed, #mrt-new-thu, #mrt-new-fri, #mrt-new-sat, #mrt-new-sun').prop('checked', false);
                        $('#mrt-new-include-dates').val('');
                        $('#mrt-new-exclude-dates').val('');
                        $btn.removeData('edit-id').text('Add Calendar Entry');
                        $('#mrt-cancel-calendar').hide();
                        location.reload();
                    } else {
                        $btn.prop('disabled', false).text('Update Calendar Entry');
                        alert(response.data.message || 'Error updating calendar entry.');
                    }
                });
            } else {
                // Add new
                var serviceId = $btn.data('service-id');
                var data = {
                    action: 'mrt_add_calendar',
                    nonce: nonce,
                    service_id: serviceId,
                    start_date: $('#mrt-new-start-date').val(),
                    end_date: $('#mrt-new-end-date').val(),
                    mon: $('#mrt-new-mon').is(':checked') ? 1 : 0,
                    tue: $('#mrt-new-tue').is(':checked') ? 1 : 0,
                    wed: $('#mrt-new-wed').is(':checked') ? 1 : 0,
                    thu: $('#mrt-new-thu').is(':checked') ? 1 : 0,
                    fri: $('#mrt-new-fri').is(':checked') ? 1 : 0,
                    sat: $('#mrt-new-sat').is(':checked') ? 1 : 0,
                    sun: $('#mrt-new-sun').is(':checked') ? 1 : 0,
                    include_dates: $('#mrt-new-include-dates').val(),
                    exclude_dates: $('#mrt-new-exclude-dates').val()
                };

                if (!data.start_date || !data.end_date) {
                    alert('Please fill in Start Date and End Date.');
                    return;
                }

                $btn.prop('disabled', true).text('Adding...');

                $.post(mrtAdmin.ajaxurl, data, function(response) {
                    if (response.success) {
                        // Reset form
                        $('#mrt-new-start-date').val('');
                        $('#mrt-new-end-date').val('');
                        $('#mrt-new-mon, #mrt-new-tue, #mrt-new-wed, #mrt-new-thu, #mrt-new-fri, #mrt-new-sat, #mrt-new-sun').prop('checked', false);
                        $('#mrt-new-include-dates').val('');
                        $('#mrt-new-exclude-dates').val('');
                        location.reload();
                    } else {
                        $btn.prop('disabled', false).text('Add Calendar Entry');
                        alert(response.data.message || 'Error adding calendar entry.');
                    }
                });
            }
        });

        // Edit calendar entry
        $container.on('click', '.mrt-edit-calendar', function() {
            var id = $(this).data('id');
            $.post(mrtAdmin.ajaxurl, {
                action: 'mrt_get_calendar',
                nonce: nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    var cal = response.data;
                    // Populate form
                    $('#mrt-new-start-date').val(cal.start_date);
                    $('#mrt-new-end-date').val(cal.end_date);
                    $('#mrt-new-mon').prop('checked', cal.mon == 1);
                    $('#mrt-new-tue').prop('checked', cal.tue == 1);
                    $('#mrt-new-wed').prop('checked', cal.wed == 1);
                    $('#mrt-new-thu').prop('checked', cal.thu == 1);
                    $('#mrt-new-fri').prop('checked', cal.fri == 1);
                    $('#mrt-new-sat').prop('checked', cal.sat == 1);
                    $('#mrt-new-sun').prop('checked', cal.sun == 1);
                    $('#mrt-new-include-dates').val(cal.include_dates || '');
                    $('#mrt-new-exclude-dates').val(cal.exclude_dates || '');
                    
                    // Change button to update
                    var $btn = $('#mrt-add-calendar');
                    $btn.data('edit-id', id).text('Update Calendar Entry');
                    $('#mrt-cancel-calendar').show();
                    
                    // Scroll to form
                    $('html, body').animate({
                        scrollTop: $('.mrt-add-calendar-form').offset().top - 100
                    }, 500);
                }
            });
        });
        
        // Cancel edit calendar
        $('#mrt-cancel-calendar').on('click', function() {
            $('#mrt-new-start-date').val('');
            $('#mrt-new-end-date').val('');
            $('#mrt-new-mon, #mrt-new-tue, #mrt-new-wed, #mrt-new-thu, #mrt-new-fri, #mrt-new-sat, #mrt-new-sun').prop('checked', false);
            $('#mrt-new-include-dates').val('');
            $('#mrt-new-exclude-dates').val('');
            var $btn = $('#mrt-add-calendar');
            $btn.removeData('edit-id').text('Add Calendar Entry');
            $('#mrt-cancel-calendar').hide();
        });

        // Delete calendar entry
        $container.on('click', '.mrt-delete-calendar', function() {
            if (!confirm('Are you sure you want to delete this calendar entry?')) {
                return;
            }
            var id = $(this).data('id');
            $.post(mrtAdmin.ajaxurl, {
                action: 'mrt_delete_calendar',
                nonce: nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Error deleting calendar entry.');
                }
            });
        });
    }

})(jQuery);
