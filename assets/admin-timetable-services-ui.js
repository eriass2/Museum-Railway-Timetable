/**
 * Timetable Services UI: add/remove trips
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

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
        var utils = window.MRTAdminUtils;

        $('#mrt-new-service-route').on('change', function() {
            var routeId = $(this).val();
            var $destinationSelect = $('#mrt-new-service-end-station');

            if (!routeId) {
                $destinationSelect.empty();
                var defOpt = document.createElement('option');
                defOpt.value = '';
                defOpt.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.selectDestination) ? mrtAdmin.selectDestination : '— Select Destination —';
                $destinationSelect.append(defOpt);
                var disOpt = document.createElement('option');
                disOpt.value = '';
                disOpt.disabled = true;
                disOpt.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.selectRouteFirst) ? mrtAdmin.selectRouteFirst : 'Select a route first';
                $destinationSelect.append(disOpt);
                return;
            }

            utils.setSelectState($destinationSelect, 'loading');

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
                        utils.populateDestinationsSelect($destinationSelect, response.data.destinations);
                    } else {
                        utils.setSelectState($destinationSelect, 'error');
                    }
                },
                error: function() {
                    utils.setSelectState($destinationSelect, 'error');
                }
            });
        });

        $('#mrt-add-service-to-timetable').on('click', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var timetableId = $btn.data('timetable-id');
            var routeId = $('#mrt-new-service-route').val();
            var trainTypeId = $('#mrt-new-service-train-type').val();
            var endStationId = $('#mrt-new-service-end-station').val();

            if (!routeId) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseSelectRoute : 'Please select a route.');
                return;
            }

            if (!nonce) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.securityTokenMissing : 'Security token missing. Please refresh the page.');
                return;
            }

            $btn.prop('disabled', true).text('Adding...');
            if (!$btn.data('original-text')) {
                $btn.data('original-text', $btn.text());
            }

            var ajaxUrl = (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');

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
                    if (response.success) {
                        var $tbody = $('#mrt-timetable-services-tbody');
                        var $newRow = $tbody.find('.mrt-new-service-row');
                        var editUrlWithTimetable = response.data.edit_url + (response.data.edit_url.indexOf('?') > -1 ? '&' : '?') + 'timetable_id=' + timetableId;
                        var $row = $('<tr></tr>').attr('data-service-id', response.data.service_id);
                        var td1 = document.createElement('td');
                        td1.textContent = response.data.route_name || '';
                        var td2 = document.createElement('td');
                        td2.textContent = response.data.train_type_name || '';
                        var td3 = document.createElement('td');
                        td3.textContent = response.data.destination || response.data.direction || '—';
                        var td4 = document.createElement('td');
                        var editLink = document.createElement('a');
                        editLink.href = editUrlWithTimetable;
                        editLink.className = 'button button-small';
                        editLink.textContent = 'Edit';
                        var removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'button button-small mrt-delete-service-from-timetable';
                        removeBtn.setAttribute('data-service-id', response.data.service_id);
                        removeBtn.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.remove) ? mrtAdmin.remove : 'Remove';
                        td4.appendChild(editLink);
                        td4.appendChild(document.createTextNode(' '));
                        td4.appendChild(removeBtn);
                        $row.append(td1).append(td2).append(td3).append(td4);
                        $newRow.before($row);

                        var $successMsg = $('<div class="mrt-success-message notice notice-success is-dismissible"><p>Trip added successfully.</p></div>');
                        $('#mrt-timetable-services-box').before($successMsg);
                        setTimeout(function() {
                            $successMsg.fadeOut(300, function() { $(this).remove(); });
                        }, 3000);

                        $('#mrt-new-service-route').val('');
                        $('#mrt-new-service-train-type').val('');
                        var $destSel = $('#mrt-new-service-end-station');
                        $destSel.empty();
                        var defOpt = document.createElement('option');
                        defOpt.value = '';
                        defOpt.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.selectDestination) ? mrtAdmin.selectDestination : '— Select Destination —';
                        $destSel.append(defOpt);
                        var disOpt = document.createElement('option');
                        disOpt.value = '';
                        disOpt.disabled = true;
                        disOpt.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.selectRouteFirst) ? mrtAdmin.selectRouteFirst : 'Select a route first';
                        $destSel.append(disOpt);
                    } else {
                        var errMsg = (response.data && response.data.message) ? String(response.data.message) : 'Error adding trip.';
                        var $errP = document.createElement('p');
                        $errP.textContent = errMsg;
                        var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible"></div>').append($errP);
                        $('#mrt-timetable-services-box').before($errorMsg);
                        $btn.prop('disabled', false).text($btn.data('original-text') || 'Add Trip');
                    }
                },
                error: function(xhr, status, error) {
                    var networkErrorMsg = typeof mrtAdmin !== 'undefined' ? mrtAdmin.networkError : 'Network error. Please try again.';
                    var $errP = document.createElement('p');
                    $errP.textContent = networkErrorMsg;
                    var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible"></div>').append($errP);
                    $('#mrt-timetable-services-box').before($errorMsg);
                },
                complete: function() {
                    $btn.prop('disabled', false).text($btn.data('original-text') || 'Add Trip');
                }
            });
        });

        $container.on('click', '.mrt-delete-service-from-timetable', function() {
            if (!confirm(typeof mrtAdmin !== 'undefined' ? mrtAdmin.confirmRemoveTrip : 'Are you sure you want to remove this trip from the timetable?')) {
                return;
            }

            var $btn = $(this);
            var serviceId = $btn.data('service-id');
            var $row = $btn.closest('tr');

            $btn.prop('disabled', true);

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
                        var $successMsg = $('<div class="mrt-success-message notice notice-success is-dismissible"><p>Trip removed successfully.</p></div>');
                        $('#mrt-timetable-services-box').before($successMsg);
                        setTimeout(function() {
                            $successMsg.fadeOut(300, function() { $(this).remove(); });
                        }, 3000);
                    } else {
                        var errMsg = (response.data && response.data.message) ? String(response.data.message) : 'Error removing trip.';
                        var $errP = document.createElement('p');
                        $errP.textContent = errMsg;
                        var $errorMsg = $('<div class="mrt-error-message notice notice-error is-dismissible"></div>').append($errP);
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

    $(function() {
        initTimetableServicesUI();
    });

})(jQuery);
