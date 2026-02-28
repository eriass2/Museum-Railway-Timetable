/**
 * Admin JavaScript for Museum Railway Timetable
 * Main: initAdminFeatures (route change, title preview, stops-here, save all)
 *
 * @package Museum_Railway_Timetable
 *
 * MODULES:
 * - admin-utils.js: populateDestinationsSelect, setSelectState, validateTimeFormat
 * - admin-route-ui.js: Route stations add/remove/reorder
 * - admin-stoptimes-ui.js: Stop times legacy inline editing
 * - admin-timetable-services-ui.js: Timetable add/remove trips
 */
(function($) {
    'use strict';

    var utils = window.MRTAdminUtils;

    $(function() {
        initAdminFeatures();

        if (typeof console !== 'undefined' && console.log && window.mrtDebug) {
            console.log('Museum Railway Timetable admin loaded.');
        }
    });

    function initAdminFeatures() {
        var $stoptimesContainer = $('#mrt-stoptimes-container');
        var $destinationSelect = $('#mrt_service_end_station_id');

        // Update stations list and destination dropdown when route changes in Service edit
        $('#mrt_service_route_id').on('change', function() {
            var routeId = $(this).val();
            var serviceId = $('#post_ID').val() || 0;
            var nonce = $('#mrt_stoptimes_nonce').val();

            if (!routeId) {
                if ($stoptimesContainer.length) {
                    $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-none">' +
                        (typeof mrtAdmin !== 'undefined' ? mrtAdmin.noRouteSelected : 'No route selected. Select a route to configure stop times.') +
                        '</td></tr>');
                    $('#mrt-save-all-stoptimes').closest('p').hide();
                }
                if ($destinationSelect.length) {
                    var defOpt = document.createElement('option');
                    defOpt.value = '';
                    defOpt.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.selectDestination) ? mrtAdmin.selectDestination : '— Select Destination —';
                    $destinationSelect.empty().append(defOpt);
                }
                return;
            }

            if ($stoptimesContainer.length) {
                $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" style="text-align: center; padding: 2rem;"><span class="spinner is-active" style="float: none; margin: 0;"></span> ' +
                    (typeof mrtAdmin !== 'undefined' ? mrtAdmin.loadingStations : 'Loading stations...') + '</td></tr>');
            }

            if ($destinationSelect.length) {
                utils.setSelectState($destinationSelect, 'loading');

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
                            utils.populateDestinationsSelect($destinationSelect, response.data.destinations);
                        } else {
                            utils.setSelectState($destinationSelect, 'error');
                        }
                    },
                    error: function() {
                        utils.setSelectState($destinationSelect, 'error');
                    }
                });
            }

            updateServiceTitlePreview(routeId, null);

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

                            $('#mrt-save-all-stoptimes').closest('p').show();
                        } else {
                            $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-none">' +
                                (typeof mrtAdmin !== 'undefined' ? mrtAdmin.noStationsOnRoute : 'No stations found on this route.') +
                                '</td></tr>');
                            $('#mrt-save-all-stoptimes').closest('p').hide();
                        }
                    },
                    error: function() {
                        $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-alert mrt-alert-error mrt-error">' +
                            (typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorLoadingStations : 'Error loading stations. Please refresh the page.') +
                            '</td></tr>');
                    }
                });
            }
        });

        $('#mrt_service_end_station_id').on('change', function() {
            var routeId = $('#mrt_service_route_id').val();
            var endStationId = $(this).val();
            updateServiceTitlePreview(routeId, endStationId);
        });

        function updateServiceTitlePreview(routeId, endStationId) {
            var $titleField = $('#title');
            if (!$titleField.length) return;

            if (!routeId) return;

            var routeName = $('#mrt_service_route_id option:selected').text();
            if (!routeName || routeName.indexOf('—') === 0) return;

            var newTitle = routeName;

            if (endStationId) {
                var destinationName = $('#mrt_service_end_station_id option:selected').text();
                if (destinationName && destinationName.indexOf('—') !== 0) {
                    destinationName = destinationName.replace(/\s*\(Start\)\s*$/i, '').replace(/\s*\(End\)\s*$/i, '');
                    newTitle = routeName + ' → ' + destinationName;
                }
            }

            if ($titleField.is(':visible') && !$titleField.data('user-edited')) {
                $titleField.val(newTitle);
            }
        }

        $('#title').on('input', function() {
            $(this).data('user-edited', true);
        });

        $(document).on('change', '.mrt-stops-here', function() {
            var $row = $(this).closest('tr');
            var stopsHere = $(this).is(':checked');
            $row.find('.mrt-time-field input, .mrt-option-field input').prop('disabled', !stopsHere);
            $row.find('.mrt-time-field, .mrt-option-field').css('opacity', stopsHere ? '1' : '0.5');
            if (!stopsHere) {
                $row.find('.mrt-arrival-time, .mrt-departure-time').val('');
            }
        });

        $(document).on('input blur', '.mrt-arrival-time, .mrt-departure-time', function() {
            var $input = $(this);
            var timeValue = $input.val();
            var $field = $input.closest('td');

            $input.removeClass('mrt-time-error');
            $field.find('.mrt-time-error-message').remove();

            if (timeValue && timeValue.trim() !== '') {
                if (!utils.validateTimeFormat(timeValue)) {
                    $input.addClass('mrt-time-error');
                    var errorText = (typeof mrtAdmin !== 'undefined' && mrtAdmin.invalidTimeFormat) ?
                        mrtAdmin.invalidTimeFormat :
                        'Invalid format. Use HH:MM (e.g., 09:15)';
                    var $errorMsg = $('<span class="mrt-time-error-message">' +
                        errorText +
                        '</span>');
                    $field.append($errorMsg);
                }
            }
        });

        $(document).on('click', '#mrt-save-all-stoptimes', function(e) {
            var hasErrors = false;
            $('.mrt-arrival-time, .mrt-departure-time').each(function() {
                var $input = $(this);
                var timeValue = $input.val();
                if (timeValue && timeValue.trim() !== '' && !utils.validateTimeFormat(timeValue)) {
                    hasErrors = true;
                    $input.trigger('blur');
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

        $('.mrt-stops-here').each(function() {
            var $row = $(this).closest('tr');
            var stopsHere = $(this).is(':checked');
            $row.find('.mrt-time-field input, .mrt-option-field input').prop('disabled', !stopsHere);
            $row.find('.mrt-time-field, .mrt-option-field').css('opacity', stopsHere ? '1' : '0.5');
        });

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
                    var $successMsg = $('<div class="mrt-success-message notice notice-success is-dismissible" style="margin: 1rem 0;"><p>' +
                        (response.data.message || 'Stop times saved successfully.') + '</p></div>');
                    $btn.closest('.mrt-stoptimes-box').before($successMsg);
                    setTimeout(function() {
                        $successMsg.fadeOut(300, function() { $(this).remove(); });
                    }, 3000);
                    $btn.prop('disabled', false).text(originalText).removeClass('mrt-saving');
                } else {
                    var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible" style="margin: 1rem 0;"><p>' +
                        (response.data.message || 'Error saving stop times.') + '</p></div>');
                    $btn.closest('.mrt-stoptimes-box').before($errorMsg);
                    $btn.prop('disabled', false).text(originalText).removeClass('mrt-saving');
                }
            }).fail(function() {
                var originalText = $btn.data('original-text') || 'Save Stop Times';
                var networkErrorMsg = typeof mrtAdmin !== 'undefined' ? mrtAdmin.networkError : 'Network error. Please try again.';
                var $errP = document.createElement('p');
                $errP.textContent = networkErrorMsg;
                var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible"></div>').append($errP);
                $btn.closest('.mrt-stoptimes-box').before($errorMsg);
                $btn.prop('disabled', false).text(originalText).removeClass('mrt-saving');
            });
        });
    }

})(jQuery);
