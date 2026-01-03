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
        initTimetableServicesUI();

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
        
        // Update stations list and destination dropdown when route changes in Service edit
        $('#mrt_service_route_id').on('change', function() {
            var routeId = $(this).val();
            var serviceId = $('#post_ID').val() || 0;
            var $stoptimesContainer = $('#mrt-stoptimes-container');
            var $destinationSelect = $('#mrt_service_end_station_id');
            var nonce = $('#mrt_stoptimes_nonce').val();
            
            if (!routeId) {
                // Clear Stop Times table and destination dropdown
                if ($stoptimesContainer.length) {
                    $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-none">' + 
                        (typeof mrtAdmin !== 'undefined' ? mrtAdmin.noRouteSelected : 'No route selected. Select a route to configure stop times.') + 
                        '</td></tr>');
                    $('#mrt-save-all-stoptimes').closest('p').hide();
                }
                if ($destinationSelect.length) {
                    $destinationSelect.html('<option value=""><?php echo esc_js(__('— Select Destination —', 'museum-railway-timetable')); ?></option>');
                }
                return;
            }
            
            // Show loading state
            if ($stoptimesContainer.length) {
                $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" style="text-align: center; padding: 2rem;"><span class="spinner is-active" style="float: none; margin: 0;"></span> ' + 
                    (typeof mrtAdmin !== 'undefined' ? mrtAdmin.loadingStations : 'Loading stations...') + '</td></tr>');
            }
            
            // Update destination dropdown
            if ($destinationSelect.length) {
                $destinationSelect.prop('disabled', true).html('<option value=""><?php echo esc_js(__('Loading...', 'museum-railway-timetable')); ?></option>');
                
                $.ajax({
                    url: (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'),
                    type: 'POST',
                    data: {
                        action: 'mrt_get_route_destinations',
                        nonce: $('#mrt_service_meta_nonce').val() || $('#mrt_timetable_services_nonce').val() || nonce,
                        route_id: routeId
                    },
                    success: function(response) {
                        if (response.success && response.data.destinations) {
                            var options = '<option value=""><?php echo esc_js(__('— Select Destination —', 'museum-railway-timetable')); ?></option>';
                            response.data.destinations.forEach(function(dest) {
                                options += '<option value="' + dest.id + '">' + dest.name + '</option>';
                            });
                            $destinationSelect.html(options).prop('disabled', false);
                        } else {
                            $destinationSelect.html('<option value=""><?php echo esc_js(__('Error loading destinations', 'museum-railway-timetable')); ?></option>').prop('disabled', false);
                        }
                    },
                    error: function() {
                        $destinationSelect.html('<option value=""><?php echo esc_js(__('Error loading destinations', 'museum-railway-timetable')); ?></option>').prop('disabled', false);
                    }
                });
            }
            
            // Update Stop Times table
            if ($stoptimesContainer.length && nonce) {
                $.ajax({
                    url: (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'),
                    type: 'POST',
                    data: {
                        action: 'mrt_get_route_stations_for_stoptimes',
                        nonce: nonce,
                        route_id: routeId,
                        service_id: serviceId
                    },
                    success: function(response) {
                        if (response.success && response.data.has_stations) {
                            var $tbody = $('#mrt-stoptimes-tbody');
                            $tbody.empty();
                            
                            response.data.stations.forEach(function(station, index) {
                                var stopsHere = station.stops_here;
                                var disabledAttr = stopsHere ? '' : 'disabled';
                                var opacityClass = stopsHere ? '' : 'mrt-field-disabled-opacity';
                                
                                var row = '<tr class="mrt-route-station-row" data-station-id="' + station.id + '" data-service-id="' + serviceId + '" data-sequence="' + station.sequence + '">' +
                                    '<td>' + (index + 1) + '</td>' +
                                    '<td><strong>' + station.name + '</strong></td>' +
                                    '<td>' +
                                    '<input type="checkbox" class="mrt-stops-here" ' + (stopsHere ? 'checked' : '') + ' data-station-id="' + station.id + '" />' +
                                    '</td>' +
                                    '<td class="mrt-time-field ' + opacityClass + '">' +
                                    '<input type="text" class="mrt-arrival-time mrt-time-input" value="' + (station.arrival_time || '') + '" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" ' + disabledAttr + ' />' +
                                    '<p class="description mrt-description-small">Leave empty if train stops but time is not fixed</p>' +
                                    '</td>' +
                                    '<td class="mrt-time-field ' + opacityClass + '">' +
                                    '<input type="text" class="mrt-departure-time mrt-time-input" value="' + (station.departure_time || '') + '" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" ' + disabledAttr + ' />' +
                                    '<p class="description mrt-description-small">Leave empty if train stops but time is not fixed</p>' +
                                    '</td>' +
                                    '<td class="mrt-option-field ' + opacityClass + '">' +
                                    '<label><input type="checkbox" class="mrt-pickup" ' + (station.pickup_allowed ? 'checked' : '') + ' ' + disabledAttr + ' /> Pickup</label>' +
                                    '</td>' +
                                    '<td class="mrt-option-field ' + opacityClass + '">' +
                                    '<label><input type="checkbox" class="mrt-dropoff" ' + (station.dropoff_allowed ? 'checked' : '') + ' ' + disabledAttr + ' /> Dropoff</label>' +
                                    '</td>' +
                                    '</tr>';
                                $tbody.append(row);
                            });
                            
                            // Show save button
                            $('#mrt-save-all-stoptimes').closest('p').show();
                        } else {
                            $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-none">' + 
                                (typeof mrtAdmin !== 'undefined' ? mrtAdmin.noStationsOnRoute : 'No stations found on this route.') + 
                                '</td></tr>');
                            $('#mrt-save-all-stoptimes').closest('p').hide();
                        }
                    },
                    error: function() {
                        $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-error">' + 
                            (typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorLoadingStations : 'Error loading stations. Please refresh the page.') + 
                            '</td></tr>');
                    }
                });
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
        
        // Validate time format (HH:MM) in real-time
        function validateTimeFormat(timeString) {
            if (!timeString || timeString.trim() === '') {
                return true; // Empty is allowed
            }
            // Match HH:MM format (00:00 to 23:59)
            var timePattern = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
            return timePattern.test(timeString.trim());
        }
        
        // Real-time validation for time inputs
        $(document).on('input blur', '.mrt-arrival-time, .mrt-departure-time', function() {
            var $input = $(this);
            var timeValue = $input.val();
            var $field = $input.closest('td');
            
            // Remove previous error styling
            $input.removeClass('mrt-time-error');
            $field.find('.mrt-time-error-message').remove();
            
            if (timeValue && timeValue.trim() !== '') {
                if (!validateTimeFormat(timeValue)) {
                    // Invalid format
                    $input.addClass('mrt-time-error');
                    var errorText = (typeof mrtAdmin !== 'undefined' && mrtAdmin.invalidTimeFormat) ? 
                        mrtAdmin.invalidTimeFormat : 
                        'Invalid format. Use HH:MM (e.g., 09:15)';
                    var $errorMsg = $('<span class="mrt-time-error-message" style="display: block; color: #d63638; font-size: 11px; margin-top: 2px;">' + 
                        errorText + 
                        '</span>');
                    $field.append($errorMsg);
                }
            }
        });
        
        // Also validate before saving
        $(document).on('click', '#mrt-save-all-stoptimes', function(e) {
            var hasErrors = false;
            $('.mrt-arrival-time, .mrt-departure-time').each(function() {
                var $input = $(this);
                var timeValue = $input.val();
                if (timeValue && timeValue.trim() !== '' && !validateTimeFormat(timeValue)) {
                    hasErrors = true;
                    $input.trigger('blur'); // Show error message
                }
            });
            
            if (hasErrors) {
                e.preventDefault();
                var errorMsg = (typeof mrtAdmin !== 'undefined' && mrtAdmin.fixTimeFormats) ? 
                    mrtAdmin.fixTimeFormats : 
                    'Please fix invalid time formats before saving. Use HH:MM format (e.g., 09:15).';
                alert(errorMsg);
                return false;
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
            
            var originalText = $btn.data('original-text') || $btn.text();
            $btn.prop('disabled', true).text('Saving...').addClass('mrt-saving');
            
            $.post(mrtAdmin.ajaxurl, {
                action: 'mrt_save_all_stoptimes',
                nonce: nonce,
                service_id: serviceId,
                stops: stops
            }, function(response) {
                var originalText = $btn.data('original-text') || 'Save Stop Times';
                if (response.success) {
                    // Show success message
                    var $successMsg = $('<div class="mrt-success-message notice notice-success is-dismissible" style="margin: 1rem 0;"><p>' + 
                        (response.data.message || 'Stop times saved successfully.') + '</p></div>');
                    $btn.closest('.mrt-stoptimes-box').before($successMsg);
                    // Auto-dismiss after 3 seconds
                    setTimeout(function() {
                        $successMsg.fadeOut(300, function() { $(this).remove(); });
                    }, 3000);
                    $btn.prop('disabled', false).text(originalText).removeClass('mrt-saving');
                } else {
                    // Show error message
                    var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible" style="margin: 1rem 0;"><p>' + 
                        (response.data.message || 'Error saving stop times.') + '</p></div>');
                    $btn.closest('.mrt-stoptimes-box').before($errorMsg);
                    $btn.prop('disabled', false).text(originalText).removeClass('mrt-saving');
                }
            }).fail(function() {
                var originalText = $btn.data('original-text') || 'Save Stop Times';
                var networkErrorMsg = typeof mrtAdmin !== 'undefined' ? mrtAdmin.networkError : 'Network error. Please try again.';
                var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible" style="margin: 1rem 0;"><p>' + networkErrorMsg + '</p></div>');
                $btn.closest('.mrt-stoptimes-box').before($errorMsg);
                $btn.prop('disabled', false).text(originalText).removeClass('mrt-saving');
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
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseSelectStation : 'Please select a station.');
                return;
            }

            // Get current stations
            var currentStations = $('#mrt_route_stations').val().split(',').filter(function(id) { return id; });
            if (currentStations.indexOf(stationId) !== -1) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.stationAlreadyOnRoute : 'This station is already on the route.');
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
                '<button type="button" class="button button-small mrt-move-route-station-up" data-station-id="' + stationId + '" title="' + (typeof mrtAdmin !== 'undefined' ? mrtAdmin.moveUp : 'Move up') + '">↑</button> ' +
                '<button type="button" class="button button-small mrt-move-route-station-down" data-station-id="' + stationId + '" title="' + (typeof mrtAdmin !== 'undefined' ? mrtAdmin.moveDown : 'Move down') + '">↓</button> ' +
                '<button type="button" class="button button-small mrt-remove-route-station" data-station-id="' + stationId + '">' + (typeof mrtAdmin !== 'undefined' ? mrtAdmin.remove : 'Remove') + '</button>' +
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
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseFillStationAndSequence : 'Please fill in Station and Sequence.');
                return;
            }

            $(this).prop('disabled', true).text('Saving...');

            $.post(mrtAdmin.ajaxurl, data, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || (typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorSavingStopTime : 'Error saving stop time.'));
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
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseFillStationAndSequence : 'Please fill in Station and Sequence.');
                return;
            }

            $(this).prop('disabled', true).text('Adding...');

            $.post(mrtAdmin.ajaxurl, data, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || (typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorAddingStopTime : 'Error adding stop time.'));
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
            if (!confirm(typeof mrtAdmin !== 'undefined' ? mrtAdmin.confirmDeleteStopTime : 'Are you sure you want to delete this stop time?')) {
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
                    alert(response.data.message || (typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorDeletingStopTime : 'Error deleting stop time.'));
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
            
            var stoptimeId = $row.data('stoptime-id');
            if (stoptimeId && stoptimeId !== 'new') {
                // Reload original values from server for existing stop times
                var id = $row.data('id');
                if (id) {
                    $.post(mrtAdmin.ajaxurl, {
                        action: 'mrt_get_stoptime',
                        nonce: nonce,
                        id: id
                    }, function(response) {
                        if (response.success && response.data) {
                            var st = response.data;
                            $row.find('.mrt-arrival-time').val(st.arrival_time || '');
                            $row.find('.mrt-departure-time').val(st.departure_time || '');
                            $row.find('.mrt-pickup').prop('checked', st.pickup_allowed == 1);
                            $row.find('.mrt-dropoff').prop('checked', st.dropoff_allowed == 1);
                        }
                        // Exit edit mode
                        $row.removeClass('mrt-editing');
                        $row.find('.mrt-display').show();
                        $row.find('.mrt-input').hide();
                        $row.find('.mrt-save-stoptime, .mrt-cancel-edit').hide();
                        $row.find('.mrt-delete-stoptime').show();
                        editingRow = null;
                    });
                } else {
                    // No ID, just exit edit mode
                    $row.removeClass('mrt-editing');
                    $row.find('.mrt-display').show();
                    $row.find('.mrt-input').hide();
                    $row.find('.mrt-save-stoptime, .mrt-cancel-edit').hide();
                    $row.find('.mrt-delete-stoptime').show();
                    editingRow = null;
                }
            } else {
                // New row or no ID, just exit edit mode
                $row.removeClass('mrt-editing');
                $row.find('.mrt-display').show();
                $row.find('.mrt-input').hide();
                $row.find('.mrt-save-stoptime, .mrt-cancel-edit').hide();
                $row.find('.mrt-delete-stoptime').show();
                editingRow = null;
            }
        }
    }


    /**
     * Initialize Timetable Services UI
     */
    function initTimetableServicesUI() {
        var $container = $('#mrt-timetable-services-container');
        if (!$container.length) {
            if (window.mrtDebug) {
                console.log('MRT: Timetable services container not found');
            }
            return;
        }

        if (window.mrtDebug) {
            console.log('MRT: Initializing timetable services UI');
        }

        var nonce = $('#mrt_timetable_services_nonce').val();
        if (window.mrtDebug) {
            console.log('MRT: Nonce value:', nonce ? 'Found' : 'NOT FOUND');
        }

        // Load destinations when route is selected
        $('#mrt-new-service-route').on('change', function() {
            var routeId = $(this).val();
            var $destinationSelect = $('#mrt-new-service-end-station');
            
            if (!routeId) {
                $destinationSelect.html('<option value=""><?php echo esc_js(__('— Select Destination —', 'museum-railway-timetable')); ?></option><option value="" disabled><?php echo esc_js(__('Select a route first', 'museum-railway-timetable')); ?></option>');
                return;
            }
            
            $destinationSelect.prop('disabled', true).html('<option value=""><?php echo esc_js(__('Loading...', 'museum-railway-timetable')); ?></option>');
            
            $.ajax({
                url: (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'),
                type: 'POST',
                data: {
                    action: 'mrt_get_route_destinations',
                    nonce: nonce,
                    route_id: routeId
                },
                success: function(response) {
                    if (response.success && response.data.destinations) {
                        var options = '<option value=""><?php echo esc_js(__('— Select Destination —', 'museum-railway-timetable')); ?></option>';
                        response.data.destinations.forEach(function(dest) {
                            options += '<option value="' + dest.id + '">' + dest.name + '</option>';
                        });
                        $destinationSelect.html(options).prop('disabled', false);
                    } else {
                        $destinationSelect.html('<option value=""><?php echo esc_js(__('Error loading destinations', 'museum-railway-timetable')); ?></option>').prop('disabled', false);
                    }
                },
                error: function() {
                    $destinationSelect.html('<option value=""><?php echo esc_js(__('Error loading destinations', 'museum-railway-timetable')); ?></option>').prop('disabled', false);
                }
            });
        });
        
        // Add service to timetable
        $('#mrt-add-service-to-timetable').on('click', function(e) {
            e.preventDefault();
            if (window.mrtDebug) {
                console.log('MRT: Add trip button clicked');
            }
            
            var $btn = $(this);
            var timetableId = $btn.data('timetable-id');
            var routeId = $('#mrt-new-service-route').val();
            var trainTypeId = $('#mrt-new-service-train-type').val();
            var endStationId = $('#mrt-new-service-end-station').val();

            if (window.mrtDebug) {
                console.log('MRT: Form values:', {
                    timetableId: timetableId,
                    routeId: routeId,
                    trainTypeId: trainTypeId,
                    endStationId: endStationId,
                    nonce: nonce ? 'Present' : 'Missing'
                });
            }

            if (!routeId) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseSelectRoute : 'Please select a route.');
                if (window.mrtDebug) {
                    console.log('MRT: Validation failed - no route selected');
                }
                return;
            }

            if (!nonce) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.securityTokenMissing : 'Security token missing. Please refresh the page.');
                if (window.mrtDebug) {
                    console.error('MRT: Nonce is missing!');
                }
                return;
            }

            $btn.prop('disabled', true).text('Adding...');
            if (window.mrtDebug) {
                console.log('MRT: Sending AJAX request...');
            }

            // Use mrtAdmin.ajaxurl if available, otherwise fall back to global ajaxurl
            var ajaxUrl = (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
            if (window.mrtDebug) {
                console.log('MRT: Using AJAX URL:', ajaxUrl);
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mrt_add_service_to_timetable',
                    nonce: nonce,
                    timetable_id: timetableId,
                    route_id: routeId,
                    train_type_id: trainTypeId,
                    end_station_id: endStationId
                },
                success: function(response) {
                    if (window.mrtDebug) {
                        console.log('MRT: AJAX success response:', response);
                    }
                    if (response.success) {
                        if (window.mrtDebug) {
                            console.log('MRT: Service created successfully:', response.data);
                        }
                        // Add row to table
                        var $tbody = $('#mrt-timetable-services-tbody');
                        var $newRow = $tbody.find('.mrt-new-service-row');
                        var editUrlWithTimetable = response.data.edit_url + (response.data.edit_url.indexOf('?') > -1 ? '&' : '?') + 'timetable_id=' + timetableId;
                        var $row = $('<tr data-service-id="' + response.data.service_id + '">' +
                            '<td>' + response.data.route_name + '</td>' +
                            '<td>' + response.data.train_type_name + '</td>' +
                            '<td>' + (response.data.destination || response.data.direction || '—') + '</td>' +
                            '<td>' +
                            '<a href="' + editUrlWithTimetable + '" class="button button-small">Edit</a> ' +
                            '<button type="button" class="button button-small mrt-delete-service-from-timetable" data-service-id="' + response.data.service_id + '">Remove</button>' +
                            '</td>' +
                            '</tr>');
                        $newRow.before($row);
                        if (window.mrtDebug) {
                            console.log('MRT: Row added to table');
                        }
                        
                        // Show success message
                        var $successMsg = $('<div class="mrt-success-message notice notice-success is-dismissible" style="margin: 1rem 0;"><p>Trip added successfully.</p></div>');
                        $('#mrt-timetable-services-box').before($successMsg);
                        setTimeout(function() {
                            $successMsg.fadeOut(300, function() { $(this).remove(); });
                        }, 3000);

                        // Clear form
                        $('#mrt-new-service-route').val('');
                        $('#mrt-new-service-train-type').val('');
                        $('#mrt-new-service-end-station').html('<option value=""><?php echo esc_js(__('— Select Destination —', 'museum-railway-timetable')); ?></option><option value="" disabled><?php echo esc_js(__('Select a route first', 'museum-railway-timetable')); ?></option>');
                        if (window.mrtDebug) {
                            console.log('MRT: Form cleared');
                        }
                    } else {
                        if (window.mrtDebug) {
                            console.error('MRT: Server returned error:', response.data);
                        }
                        var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible" style="margin: 1rem 0;"><p>' + 
                            (response.data.message || 'Error adding trip.') + '</p></div>');
                        $('#mrt-timetable-services-box').before($errorMsg);
                        $btn.prop('disabled', false).text($btn.data('original-text') || 'Add Trip');
                    }
                },
                error: function(xhr, status, error) {
                    if (window.mrtDebug) {
                        console.error('MRT: AJAX error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                    }
                    var networkErrorMsg = typeof mrtAdmin !== 'undefined' ? mrtAdmin.networkError : 'Network error. Please try again.';
                var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible" style="margin: 1rem 0;"><p>' + networkErrorMsg + '</p></div>');
                    $('#mrt-timetable-services-box').before($errorMsg);
                },
                complete: function() {
                    $btn.prop('disabled', false).text($btn.data('original-text') || 'Add Trip');
                    if (window.mrtDebug) {
                        console.log('MRT: AJAX request completed');
                    }
                }
            });
        });

        // Remove service from timetable
        $container.on('click', '.mrt-delete-service-from-timetable', function() {
            if (!confirm(typeof mrtAdmin !== 'undefined' ? mrtAdmin.confirmRemoveTrip : 'Are you sure you want to remove this trip from the timetable?')) {
                return;
            }

            var $btn = $(this);
            var serviceId = $btn.data('service-id');
            var $row = $btn.closest('tr');

            $btn.prop('disabled', true);

            // Use mrtAdmin.ajaxurl if available, otherwise fall back to global ajaxurl
            var ajaxUrl = (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mrt_remove_service_from_timetable',
                    nonce: nonce,
                    service_id: serviceId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(function() {
                            $(this).remove();
                        });
                        // Show success message
                        var $successMsg = $('<div class="mrt-success-message notice notice-success is-dismissible" style="margin: 1rem 0;"><p>Trip removed successfully.</p></div>');
                        $('#mrt-timetable-services-box').before($successMsg);
                        setTimeout(function() {
                            $successMsg.fadeOut(300, function() { $(this).remove(); });
                        }, 3000);
                    } else {
                        var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible" style="margin: 1rem 0;"><p>' + 
                            (response.data.message || 'Error removing trip.') + '</p></div>');
                        $('#mrt-timetable-services-box').before($errorMsg);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorRemovingTrip : 'Error removing trip.');
                    $btn.prop('disabled', false);
                }
            });
        });
    }

})(jQuery);
