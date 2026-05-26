/**
 * Frontend JavaScript for Museum Railway Timetable
 * Month calendar AJAX (journey search lives in journey-wizard.js).
 */

(function($) {
    'use strict';

    var api = window.MRTFrontendApi;

    function prefersReducedMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function showError($container, message) {
        var $div = $('<div class="mrt-alert mrt-alert-error" role="alert"></div>');
        $div.text(message);
        $container.html($div);
        $container.attr('aria-busy', 'false');
    }

    function monthCalendarRevealContainer($container, wasHidden) {
        $container.removeClass('mrt-hidden').attr('aria-busy', 'true');
        $container.html('<div class="mrt-empty mrt-empty--loading">' + api.msg('loading', 'Loading...') + '</div>');
        if (!wasHidden) {
            return;
        }
        if (prefersReducedMotion()) {
            $container.show();
        } else {
            $container.hide().slideDown(300);
        }
    }

    function monthCalendarLoadDay($month, $container, date, trainType) {
        $.ajax({
            url: api.getAjaxUrl(),
            type: 'POST',
            data: {
                action: 'mrt_get_timetable_for_date',
                nonce: api.getNonce(),
                date: date,
                train_type: trainType
            },
            success: function(response) {
                $container.attr('aria-busy', 'false');
                if (response.success) {
                    $container.html(response.data.html);
                } else {
                    var msg = response.data.message || api.msg('errorLoading', 'Error loading timetable.');
                    showError($container, msg);
                }
                $container.trigger('focus');
            },
            error: function() {
                var msg = api.msg('networkError', 'Network error. Please try again.');
                showError($container, msg);
                $container.trigger('focus');
            }
        });
    }

    /**
     * Initialize Month Calendar with clickable days
     */
    function initMonthCalendar() {
        var $month = $('.mrt-month');
        if (!$month.length) return;

        var $container = $month.find('.mrt-day-timetable-container');
        if (!$container.length) return;

        $month.on('click', '.mrt-day-clickable', function(e) {
            e.preventDefault();
            var $day = $(this);
            var date = $day.data('date');
            if (!date) {
                return;
            }
            var trainType = $month.data('train-type') || '';

            $month.find('.mrt-day-clickable').removeClass('mrt-day-active').attr('aria-pressed', 'false');
            $day.addClass('mrt-day-active').attr('aria-pressed', 'true');

            var wasHidden = $container.hasClass('mrt-hidden');
            monthCalendarRevealContainer($container, wasHidden);
            monthCalendarLoadDay($month, $container, date, trainType);
        });
    }

    function init() {
        initMonthCalendar();
    }

    $(document).ready(init);
    $(document).on('mrt_reinit', init);

})(jQuery);
