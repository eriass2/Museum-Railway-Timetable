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
                var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible" style="margin: 1rem 0;"><p>Network error. Please try again.</p></div>');
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
            var direction = $('#mrt-new-service-direction').val();

            if (window.mrtDebug) {
                console.log('MRT: Form values:', {
                    timetableId: timetableId,
                    routeId: routeId,
                    trainTypeId: trainTypeId,
                    direction: direction,
                    nonce: nonce ? 'Present' : 'Missing'
                });
            }

            if (!routeId) {
                alert('Please select a route.');
                if (window.mrtDebug) {
                    console.log('MRT: Validation failed - no route selected');
                }
                return;
            }

            if (!nonce) {
                alert('Security token missing. Please refresh the page.');
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
                    direction: direction
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
                            '<td>' + response.data.direction + '</td>' +
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
                        $('#mrt-new-service-direction').val('');
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
                    var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible" style="margin: 1rem 0;"><p>Network error. Please try again.</p></div>');
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
            if (!confirm('Are you sure you want to remove this trip from the timetable?')) {
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
                    alert('Error removing trip.');
                    $btn.prop('disabled', false);
                }
            });
        });
    }

})(jQuery);
