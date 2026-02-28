/**
 * Admin utilities for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    window.MRTAdminUtils = {
        /**
         * Escape HTML for safe insertion into HTML strings
         * @param {string} str - String to escape
         * @returns {string}
         */
        escapeHtml: function(str) {
            if (str == null) return '';
            var s = String(str);
            return s
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        /**
         * Populate a select element with destination options (XSS-safe via textContent)
         * @param {jQuery} $select - The select element
         * @param {Array} destinations - Array of {id, name}
         * @param {string} defaultLabel - Label for the empty option
         */
        populateDestinationsSelect: function($select, destinations, defaultLabel) {
            var label = defaultLabel || (typeof mrtAdmin !== 'undefined' && mrtAdmin.selectDestination) ? mrtAdmin.selectDestination : '— Select Destination —';
            var selectEl = $select[0];
            $select.empty();
            var defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = label;
            selectEl.appendChild(defaultOpt);
            if (destinations && destinations.length) {
                destinations.forEach(function(dest) {
                    var opt = document.createElement('option');
                    opt.value = dest.id;
                    opt.textContent = (dest.name != null ? String(dest.name) : '');
                    selectEl.appendChild(opt);
                });
            }
            $select.prop('disabled', false);
        },

        /**
         * Set select to loading or error state (XSS-safe)
         */
        setSelectState: function($select, state, label) {
            var text = label || (state === 'loading' ? ((typeof mrtAdmin !== 'undefined' && mrtAdmin.loading) ? mrtAdmin.loading : 'Loading...') : ((typeof mrtAdmin !== 'undefined' && mrtAdmin.errorLoadingDestinations) ? mrtAdmin.errorLoadingDestinations : 'Error loading destinations'));
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = text;
            $select.empty().append(opt).prop('disabled', state === 'loading');
        },

        /**
         * Validate time format (HH:MM)
         */
        validateTimeFormat: function(timeString) {
            if (!timeString || timeString.trim() === '') {
                return true;
            }
            var timePattern = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
            return timePattern.test(timeString.trim());
        }
    };

})(jQuery);
