/**
 * Shared date/time helpers for Museum Railway Timetable (frontend + admin)
 *
 * @package Museum_Railway_Timetable
 */
(function(global) {
    'use strict';

    function pad2(n) {
        return (n < 10 ? '0' : '') + n;
    }

    global.MRTDateUtils = {
        /**
         * Format YYYY-MM-DD for display using localized month names (e.g. "June 1, 2026").
         *
         * @param {string} ymd
         * @param {string[]|{monthNames?: string[]}|undefined} monthNamesOrCfg Array of 12 names, or object with monthNames
         * @returns {string}
         */
        formatYmdForDisplay: function(ymd, monthNamesOrCfg) {
            if (!ymd || typeof ymd !== 'string') {
                return '';
            }
            var p = ymd.split('-');
            if (p.length !== 3) {
                return ymd;
            }
            var y = p[0];
            var mo = parseInt(p[1], 10);
            var day = parseInt(p[2], 10);
            var monthNames = Array.isArray(monthNamesOrCfg)
                ? monthNamesOrCfg
                : (monthNamesOrCfg && monthNamesOrCfg.monthNames);
            if (monthNames && monthNames[mo - 1]) {
                return monthNames[mo - 1] + ' ' + day + ', ' + y;
            }
            return ymd;
        },

        ymdFromParts: function(year, month, day) {
            return year + '-' + pad2(month) + '-' + pad2(day);
        },

        calendarMonthTitle: function(year, month, monthNames) {
            var label = monthNames && monthNames[month - 1] ? monthNames[month - 1] : String(month);
            return label + ' ' + year;
        },

        daysInMonth: function(year, month) {
            return new Date(year, month, 0).getDate();
        },

        monthStartColumn: function(year, month, startOfWeek) {
            var first = new Date(year, month - 1, 1);
            return (first.getDay() - startOfWeek + 7) % 7;
        },

        /**
         * Local “today” as calendar year + month (month 1–12).
         *
         * @returns {{ year: number, month: number }}
         */
        currentCalendarYearMonth: function() {
            var d = new Date();
            return { year: d.getFullYear(), month: d.getMonth() + 1 };
        },

        /**
         * Add whole calendar months; month is 1–12 (same convention as loadCalendar APIs).
         *
         * @param {number} year
         * @param {number} month
         * @param {number} delta e.g. -1 (previous month), +1 (next month)
         * @returns {{ year: number, month: number }}
         */
        addCalendarMonths: function(year, month, delta) {
            var d = new Date(year, month - 1 + delta, 1);
            return { year: d.getFullYear(), month: d.getMonth() + 1 };
        },

        validateHhMm: function(timeString) {
            if (!timeString || String(timeString).trim() === '') {
                return true;
            }
            var timePattern = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
            return timePattern.test(String(timeString).trim());
        }
    };
}(typeof window !== 'undefined' ? window : this));
