/**
 * Timetable workspace: deviations tab + preview lazy load.
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    window.MRTAdminTimetableWorkspace = {
        init: function() {
            this.bindTimetableDeviations();
            this.highlightDeviationRow();
            if (window.MRTAdminTimetablePreview) {
                window.MRTAdminTimetablePreview.init();
            }
        },

        getConfig: function() {
            var el = document.getElementById('mrt-timetable-deviation-config');
            if (!el || !el.textContent) {
                return { services: [], dates: [], trainTypes: [], usedDates: {} };
            }
            try {
                return JSON.parse(el.textContent);
            } catch (e) {
                return { services: [], dates: [], trainTypes: [], usedDates: {} };
            }
        },

        formatDateLabel: function(date) {
            return date;
        },

        buildDeviationRow: function(serviceId, date, tripLabel, trainTypes) {
            var u = window.MRTAdminUtils;
            var nameBase = 'mrt_timetable_deviation[' + serviceId + '][' + date + ']';
            var $row = $('<tr class="mrt-timetable-deviation-row"></tr>');
            $row.attr('data-service-id', String(serviceId));
            $row.attr('data-date', date);

            var $dateCell = $('<td></td>');
            $dateCell.append($('<strong></strong>').text(date));
            $dateCell.append($('<span class="mrt-form-label__hint"></span>').text('(' + date + ')'));
            $row.append($dateCell);

            $row.append($('<td></td>').text(tripLabel));

            var $select = $('<select class="mrt-input mrt-input--meta mrt-w-full"></select>');
            $select.attr('name', nameBase + '[train_type]');
            $select.append($('<option value=""></option>').text(u.msg('defaultTrainType', '— Default train type —')));
            (trainTypes || []).forEach(function(tt) {
                $select.append($('<option></option>').attr('value', String(tt.id)).text(tt.name));
            });
            $row.append($('<td></td>').append($select));

            var $textarea = $('<textarea class="large-text" rows="2"></textarea>');
            $textarea.attr('name', nameBase + '[notice]');
            $textarea.attr('placeholder', u.msg('deviationNoticeShort', 'Message to travellers'));
            $row.append($('<td></td>').append($textarea));

            var $removeBtn = $('<button type="button" class="button-link-delete mrt-timetable-deviation-remove"></button>');
            $removeBtn.text(u.msg('remove', 'Remove'));
            $row.append($('<td></td>').append($removeBtn));

            return $row;
        },

        populateDateSelect: function($dateSelect, serviceId, cfg) {
            var used = (cfg.usedDates && cfg.usedDates[String(serviceId)]) ? cfg.usedDates[String(serviceId)] : [];
            var usedMap = {};
            used.forEach(function(d) { usedMap[d] = true; });

            $dateSelect.empty();
            $dateSelect.append(
                $('<option value=""></option>').text(
                    window.MRTAdminUtils.msg('selectTrafficDay', '— Select traffic day —')
                )
            );
            (cfg.dates || []).forEach(function(date) {
                if (!usedMap[date]) {
                    $dateSelect.append($('<option></option>').attr('value', date).text(date));
                }
            });
            $dateSelect.prop('disabled', $dateSelect.find('option').filter(function() { return this.value; }).length === 0);
        },

        bindTimetableDeviations: function() {
            var self = this;
            var $tbody = $('#mrt-timetable-deviation-rows');
            var $serviceSelect = $('#mrt-add-deviation-service');
            var $dateSelect = $('#mrt-add-deviation-date');
            var $addBtn = $('#mrt-add-timetable-deviation-btn');
            if (!$tbody.length || !$serviceSelect.length) {
                return;
            }

            var cfg = self.getConfig();

            $serviceSelect.on('change', function() {
                var serviceId = $(this).val();
                if (!serviceId) {
                    $dateSelect.prop('disabled', true).empty();
                    $addBtn.prop('disabled', true);
                    return;
                }
                self.populateDateSelect($dateSelect, serviceId, cfg);
                $addBtn.prop('disabled', true);
            });

            $dateSelect.on('change', function() {
                $addBtn.prop('disabled', !$(this).val());
            });

            $addBtn.on('click', function() {
                var serviceId = parseInt($serviceSelect.val(), 10);
                var date = $dateSelect.val();
                if (!serviceId || !date) {
                    return;
                }
                if ($tbody.find('[data-service-id="' + serviceId + '"][data-date="' + date + '"]').length) {
                    return;
                }
                var service = (cfg.services || []).find(function(s) { return String(s.id) === String(serviceId); });
                var label = service ? service.label : ('#' + serviceId);
                $tbody.append(self.buildDeviationRow(serviceId, date, label, cfg.trainTypes));
                if (!cfg.usedDates[String(serviceId)]) {
                    cfg.usedDates[String(serviceId)] = [];
                }
                cfg.usedDates[String(serviceId)].push(date);
                self.populateDateSelect($dateSelect, String(serviceId), cfg);
                $dateSelect.val('');
                $addBtn.prop('disabled', true);
            });

            $tbody.on('click', '.mrt-timetable-deviation-remove', function() {
                var $row = $(this).closest('.mrt-timetable-deviation-row');
                var serviceId = String($row.data('service-id'));
                var date = String($row.data('date'));
                $row.remove();
                if (cfg.usedDates[serviceId]) {
                    cfg.usedDates[serviceId] = cfg.usedDates[serviceId].filter(function(d) { return d !== date; });
                }
                if ($serviceSelect.val() === serviceId) {
                    self.populateDateSelect($dateSelect, serviceId, cfg);
                }
            });
        },

        highlightDeviationRow: function() {
            var $workspace = $('#mrt-timetable-workspace');
            if (!$workspace.length) {
                return;
            }
            var highlight = parseInt($workspace.data('highlight-service'), 10);
            if (!highlight) {
                return;
            }
            var $row = $('.mrt-timetable-deviation-row[data-service-id="' + highlight + '"]').first();
            if ($row.length) {
                window.setTimeout(function() {
                    $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 250);
            }
        }
    };

})(jQuery);
