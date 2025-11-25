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
        initRouteUI();
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
        
        // Update stations list when route changes in Service edit
        $('#mrt_service_route_id').on('change', function() {
            var routeId = $(this).val();
            if (routeId) {
                // Reload page to update stations list
                // Could be improved with AJAX in the future
                var $form = $(this).closest('form');
                if ($form.length && !$form.data('saving')) {
                    // Show message that page needs to reload
                    alert('Please save the service to update available stations from the selected route.');
                }
            }
        });
        
        // Handle "stops here" checkbox - enable/disable time fields
        $(document).on('change', '.mrt-stops-here', function() {
            var $row = $(this).closest('tr');
            var stopsHere = $(this).is(':checked');
            $row.find('.mrt-time-field input, .mrt-option-field input').prop('disabled', !stopsHere);
            $row.find('.mrt-time-field, .mrt-option-field').css('opacity', stopsHere ? '1' : '0.5');
            if (!stopsHere) {
                $row.find('.mrt-arrival-time, .mrt-departure-time').val('');
            }
        });
        
        // Initialize "stops here" state on page load
        $('.mrt-stops-here').each(function() {
            var $row = $(this).closest('tr');
            var stopsHere = $(this).is(':checked');
            $row.find('.mrt-time-field input, .mrt-option-field input').prop('disabled', !stopsHere);
            $row.find('.mrt-time-field, .mrt-option-field').css('opacity', stopsHere ? '1' : '0.5');
        });
        
        // Save all stop times
        $(document).on('click', '#mrt-save-all-stoptimes', function() {
            var $btn = $(this);
            if (!$btn.data('original-text')) {
                $btn.data('original-text', $btn.text());
            }
            var serviceId = $btn.data('service-id');
            var nonce = $('#mrt_stoptimes_nonce').val();
            
            var stops = [];
            $('#mrt-stoptimes-tbody .mrt-route-station-row').each(function() {
                var $row = $(this);
                var stationId = $row.data('station-id');
                var stopsHere = $row.find('.mrt-stops-here').is(':checked');
                
                stops.push({
                    station_id: stationId,
                    stops_here: stopsHere ? '1' : '0',
                    arrival: $row.find('.mrt-arrival-time').val(),
                    departure: $row.find('.mrt-departure-time').val(),
                    pickup: $row.find('.mrt-pickup').is(':checked') ? '1' : '0',
                    dropoff: $row.find('.mrt-dropoff').is(':checked') ? '1' : '0'
                });
            });
            
            $btn.prop('disabled', true).text('Saving...');
            
            $.post(mrtAdmin.ajaxurl, {
                action: 'mrt_save_all_stoptimes',
                nonce: nonce,
                service_id: serviceId,
                stops: stops
            }, function(response) {
                if (response.success) {
                    alert(response.data.message || 'Stop times saved successfully.');
                    location.reload();
                } else {
                    alert(response.data.message || 'Error saving stop times.');
                    $btn.prop('disabled', false).text($btn.data('original-text') || 'Save Stop Times');
                }
            }).fail(function() {
                alert('Network error. Please try again.');
                $btn.prop('disabled', false).text($btn.data('original-text') || 'Save Stop Times');
            });
        });
    }

    /**
     * Initialize Route UI
     */
    function initRouteUI() {
        var $container = $('#mrt-route-stations-container');
        if (!$container.length) return;

        // Add station to route
        $('#mrt-add-route-station').on('click', function() {
            var $select = $('#mrt-new-route-station');
            var stationId = $select.val();
            if (!stationId) {
                alert('Please select a station.');
                return;
            }

            // Get current stations
            var currentStations = $('#mrt_route_stations').val().split(',').filter(function(id) { return id; });
            if (currentStations.indexOf(stationId) !== -1) {
                alert('This station is already on the route.');
                return;
            }

            // Add to hidden field
            currentStations.push(stationId);
            $('#mrt_route_stations').val(currentStations.join(','));

            // Get station name
            var stationName = $select.find('option:selected').text();

            // Add row
            var $tbody = $('#mrt-route-stations-tbody');
            var $newRow = $tbody.find('.mrt-new-route-station-row');
            var newIndex = currentStations.length;

            var $row = $('<tr data-station-id="' + stationId + '">' +
                '<td>' + newIndex + '</td>' +
                '<td>' + stationName + '</td>' +
                '<td>' +
                '<button type="button" class="button button-small mrt-move-route-station-up" data-station-id="' + stationId + '" title="Move up">↑</button> ' +
                '<button type="button" class="button button-small mrt-move-route-station-down" data-station-id="' + stationId + '" title="Move down">↓</button> ' +
                '<button type="button" class="button button-small mrt-remove-route-station" data-station-id="' + stationId + '">Remove</button>' +
                '</td>' +
                '</tr>');
            
            $newRow.before($row);
            $select.val('').focus();
            
            // Update order numbers
            updateRouteStationOrders();
        });

        // Move station up
        $container.on('click', '.mrt-move-route-station-up', function() {
            var $row = $(this).closest('tr');
            var $prevRow = $row.prev();
            if ($prevRow.length && !$prevRow.hasClass('mrt-new-route-station-row')) {
                $row.insertBefore($prevRow);
                updateRouteStationOrders();
            }
        });

        // Move station down
        $container.on('click', '.mrt-move-route-station-down', function() {
            var $row = $(this).closest('tr');
            var $nextRow = $row.next();
            if ($nextRow.length && !$nextRow.hasClass('mrt-new-route-station-row')) {
                $row.insertAfter($nextRow);
                updateRouteStationOrders();
            }
        });

        // Remove station from route
        $container.on('click', '.mrt-remove-route-station', function() {
            var stationId = $(this).data('station-id');
            var currentStations = $('#mrt_route_stations').val().split(',').filter(function(id) { 
                return id && id != stationId; 
            });
            $('#mrt_route_stations').val(currentStations.join(','));
            $(this).closest('tr').remove();
            updateRouteStationOrders();
        });

        function updateRouteStationOrders() {
            var $rows = $('#mrt-route-stations-tbody tr:not(.mrt-new-route-station-row)');
            var totalRows = $rows.length;
            
            // Update order numbers and button states
            $rows.each(function(index) {
                $(this).find('td:first').text(index + 1);
                
                // Update up/down button states
                var $upBtn = $(this).find('.mrt-move-route-station-up');
                var $downBtn = $(this).find('.mrt-move-route-station-down');
                
                if (index === 0) {
                    $upBtn.prop('disabled', true);
                } else {
                    $upBtn.prop('disabled', false);
                }
                
                if (index === totalRows - 1) {
                    $downBtn.prop('disabled', true);
                } else {
                    $downBtn.prop('disabled', false);
                }
            });
            
            // Update hidden field with new order
            var stationIds = [];
            $rows.each(function() {
                stationIds.push($(this).data('station-id'));
            });
            $('#mrt_route_stations').val(stationIds.join(','));
            
            // Update next order number
            var nextOrder = totalRows + 1;
            $('.mrt-new-route-station-row td:first').text(nextOrder);
        }
    }

    /**
     * Initialize Stop Times UI with inline editing
     */
    function initStopTimesUI() {
        var $container = $('#mrt-stoptimes-container');
        if (!$container.length) return;

        var nonce = $('#mrt_stoptimes_nonce').val();
        var editingRow = null;

        // Click on row to edit (except new row and action buttons)
        $container.on('click', '.mrt-stoptime-row:not(.mrt-new-row)', function(e) {
            if ($(e.target).is('button, input, select') || $(e.target).closest('button, input, select').length) {
                return;
            }
            if (editingRow && editingRow[0] !== this) {
                cancelEditStopTime(editingRow);
            }
            startEditStopTime($(this));
        });

        // Save stop time
        $container.on('click', '.mrt-save-stoptime', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('.mrt-stoptime-row');
            var id = $row.data('stoptime-id');
            var serviceId = $row.data('service-id');
            
            var $stationField = $row.find('[data-field="station"]');
            var stationId = $stationField.find('select').length ? $stationField.find('select').val() : $stationField.find('input').val();
            
            var data = {
                action: id === 'new' ? 'mrt_add_stoptime' : 'mrt_update_stoptime',
                nonce: nonce,
                service_id: serviceId,
                station_id: stationId,
                sequence: $row.find('[data-field="sequence"] input').val(),
                arrival: $row.find('[data-field="arrival"] input').val(),
                departure: $row.find('[data-field="departure"] input').val(),
                pickup: $row.find('[data-field="pickup"] input[type="checkbox"]').is(':checked') ? 1 : 0,
                dropoff: $row.find('[data-field="dropoff"] input[type="checkbox"]').is(':checked') ? 1 : 0
            };

            if (id !== 'new') {
                data.id = id;
            }

            if (!data.station_id || !data.sequence) {
                alert('Please fill in Station and Sequence.');
                return;
            }

            $(this).prop('disabled', true).text('Saving...');

            $.post(mrtAdmin.ajaxurl, data, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Error saving stop time.');
                    $(this).prop('disabled', false).text('Save');
                }
            });
        });

        // Add new stop time
        $container.on('click', '.mrt-add-stoptime', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('.mrt-stoptime-row');
            var serviceId = $row.data('service-id');
            
            var data = {
                action: 'mrt_add_stoptime',
                nonce: nonce,
                service_id: serviceId,
                station_id: $row.find('[data-field="station"] select').val(),
                sequence: $row.find('[data-field="sequence"] input').val(),
                arrival: $row.find('[data-field="arrival"] input').val(),
                departure: $row.find('[data-field="departure"] input').val(),
                pickup: $row.find('[data-field="pickup"] input[type="checkbox"]').is(':checked') ? 1 : 0,
                dropoff: $row.find('[data-field="dropoff"] input[type="checkbox"]').is(':checked') ? 1 : 0
            };

            if (!data.station_id || !data.sequence) {
                alert('Please fill in Station and Sequence.');
                return;
            }

            $(this).prop('disabled', true).text('Adding...');

            $.post(mrtAdmin.ajaxurl, data, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Error adding stop time.');
                    $(this).prop('disabled', false).text('Add');
                }
            });
        });

        // Cancel edit
        $container.on('click', '.mrt-cancel-edit', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('.mrt-stoptime-row');
            cancelEditStopTime($row);
        });

        // Delete stop time
        $container.on('click', '.mrt-delete-stoptime', function(e) {
            e.stopPropagation();
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

        function startEditStopTime($row) {
            editingRow = $row;
            $row.addClass('mrt-editing');
            $row.find('.mrt-display').hide();
            $row.find('.mrt-input').show();
            $row.find('.mrt-save-stoptime, .mrt-cancel-edit').show();
            $row.find('.mrt-delete-stoptime').hide();
        }

        function cancelEditStopTime($row) {
            if (!$row.length) return;
            $row.removeClass('mrt-editing');
            $row.find('.mrt-display').show();
            $row.find('.mrt-input').hide();
            $row.find('.mrt-save-stoptime, .mrt-cancel-edit').hide();
            $row.find('.mrt-delete-stoptime').show();
            editingRow = null;
            // Reload to reset values
            if ($row.data('stoptime-id') !== 'new') {
                location.reload();
            }
        }
    }

    /**
     * Initialize Calendar UI with inline editing
     */
    function initCalendarUI() {
        var $container = $('#mrt-calendar-container');
        if (!$container.length) return;

        var nonce = $('#mrt_calendar_nonce').val();
        var editingRow = null;

        // Click on row to edit (except new row and action buttons)
        $container.on('click', '.mrt-calendar-row:not(.mrt-new-row)', function(e) {
            if ($(e.target).is('button, input, select, label') || $(e.target).closest('button, input, select, label').length) {
                return;
            }
            if (editingRow && editingRow[0] !== this) {
                cancelEditCalendar(editingRow);
            }
            startEditCalendar($(this));
        });

        // Save calendar entry
        $container.on('click', '.mrt-save-calendar', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('.mrt-calendar-row');
            var id = $row.data('calendar-id');
            var serviceId = $row.data('service-id');
            
            var startDate = $row.find('.mrt-start-date').val();
            var endDate = $row.find('.mrt-end-date').val();
            var days = {};
            $row.find('.mrt-day').each(function() {
                days[$(this).data('day')] = $(this).is(':checked') ? 1 : 0;
            });

            var data = {
                action: id === 'new' ? 'mrt_add_calendar' : 'mrt_update_calendar',
                nonce: nonce,
                service_id: serviceId,
                start_date: startDate,
                end_date: endDate,
                mon: days.mon || 0,
                tue: days.tue || 0,
                wed: days.wed || 0,
                thu: days.thu || 0,
                fri: days.fri || 0,
                sat: days.sat || 0,
                sun: days.sun || 0,
                include_dates: $row.find('[data-field="include"] input').val(),
                exclude_dates: $row.find('[data-field="exclude"] input').val()
            };

            if (id !== 'new') {
                data.id = id;
            }

            if (!data.start_date || !data.end_date) {
                alert('Please fill in Start Date and End Date.');
                return;
            }

            $(this).prop('disabled', true).text('Saving...');

            $.post(mrtAdmin.ajaxurl, data, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Error saving calendar entry.');
                    $(this).prop('disabled', false).text('Save');
                }
            });
        });

        // Add new calendar entry
        $container.on('click', '.mrt-add-calendar', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('.mrt-calendar-row');
            var serviceId = $row.data('service-id');
            
            var startDate = $row.find('.mrt-start-date').val();
            var endDate = $row.find('.mrt-end-date').val();
            var days = {};
            $row.find('.mrt-day').each(function() {
                days[$(this).data('day')] = $(this).is(':checked') ? 1 : 0;
            });

            var data = {
                action: 'mrt_add_calendar',
                nonce: nonce,
                service_id: serviceId,
                start_date: startDate,
                end_date: endDate,
                mon: days.mon || 0,
                tue: days.tue || 0,
                wed: days.wed || 0,
                thu: days.thu || 0,
                fri: days.fri || 0,
                sat: days.sat || 0,
                sun: days.sun || 0,
                include_dates: $row.find('[data-field="include"] input').val(),
                exclude_dates: $row.find('[data-field="exclude"] input').val()
            };

            if (!data.start_date || !data.end_date) {
                alert('Please fill in Start Date and End Date.');
                return;
            }

            $(this).prop('disabled', true).text('Adding...');

            $.post(mrtAdmin.ajaxurl, data, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Error adding calendar entry.');
                    $(this).prop('disabled', false).text('Add');
                }
            });
        });

        // Cancel edit
        $container.on('click', '.mrt-cancel-edit', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('.mrt-calendar-row');
            cancelEditCalendar($row);
        });

        // Delete calendar entry
        $container.on('click', '.mrt-delete-calendar', function(e) {
            e.stopPropagation();
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

        function startEditCalendar($row) {
            editingRow = $row;
            $row.addClass('mrt-editing');
            $row.find('.mrt-display').hide();
            $row.find('.mrt-input').show();
            $row.find('.mrt-save-calendar, .mrt-cancel-edit').show();
            $row.find('.mrt-delete-calendar').hide();
        }

        function cancelEditCalendar($row) {
            if (!$row.length) return;
            $row.removeClass('mrt-editing');
            $row.find('.mrt-display').show();
            $row.find('.mrt-input').hide();
            $row.find('.mrt-save-calendar, .mrt-cancel-edit').hide();
            $row.find('.mrt-delete-calendar').show();
            editingRow = null;
            // Reload to reset values
            if ($row.data('calendar-id') !== 'new') {
                location.reload();
            }
        }
    }

})(jQuery);
