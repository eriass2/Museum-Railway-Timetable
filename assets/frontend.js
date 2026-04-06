/**
 * Frontend JavaScript for Museum Railway Timetable
 * Handles AJAX interactions for shortcodes
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

    function focusJourneyResultsHeading($results) {
        var $h = $results.find('#mrt-journey-results-heading');
        if ($h.length) {
            $h.attr('tabindex', '-1');
            $h.trigger('focus');
            $h.one('blur', function() {
                $(this).removeAttr('tabindex');
            });
        } else {
            $results.attr('tabindex', '-1');
            $results.trigger('focus');
            $results.one('blur', function() {
                $(this).removeAttr('tabindex');
            });
        }
    }

    function journeyPlannerResetSearchButton($searchBtn) {
        $searchBtn.prop('disabled', false).attr('aria-busy', 'false').text(api.msg('search', 'Search'));
    }

    function journeyPlannerPushUrlParams(fromStation, toStation, date) {
        if (!history.pushState) {
            return;
        }
        var url = new URL(window.location);
        url.searchParams.set('mrt_from', fromStation);
        url.searchParams.set('mrt_to', toStation);
        url.searchParams.set('mrt_date', date);
        history.pushState({}, '', url);
    }

    /**
     * Run journey search: validate, POST mrt_search_journey, update UI.
     */
    function journeyPlannerRunSearch($form, $results, $searchBtn) {
        var fromStation = $form.find('#mrt_from').val();
        var toStation = $form.find('#mrt_to').val();
        var date = $form.find('#mrt_date').val();

        if (!fromStation || !toStation || !date) {
            return;
        }

        if (fromStation === toStation) {
            showError($results, api.msg('errorSameStations', 'Please select different stations for departure and arrival.'));
            focusJourneyResultsHeading($results);
            return;
        }

        $searchBtn.prop('disabled', true).attr('aria-busy', 'true').text(api.msg('searching', 'Searching...'));
        $results.attr('aria-busy', 'true');
        $results.html('<div class="mrt-empty mrt-empty--loading">' + api.msg('loading', 'Loading...') + '</div>');

        $.ajax({
            url: api.getAjaxUrl(),
            type: 'POST',
            data: {
                action: 'mrt_search_journey',
                nonce: api.getNonce(),
                from_station: fromStation,
                to_station: toStation,
                date: date
            },
            success: function(response) {
                journeyPlannerResetSearchButton($searchBtn);

                if (response.success) {
                    $results.html(response.data.html);
                    $results.attr('aria-busy', 'false');
                    focusJourneyResultsHeading($results);
                    journeyPlannerPushUrlParams(fromStation, toStation, date);
                } else {
                    showError($results, response.data.message || api.msg('errorSearching', 'Error searching for connections.'));
                    focusJourneyResultsHeading($results);
                }
            },
            error: function() {
                journeyPlannerResetSearchButton($searchBtn);
                showError($results, api.msg('networkError', 'Network error. Please try again.'));
                focusJourneyResultsHeading($results);
            }
        });
    }

    /**
     * Initialize Journey Planner AJAX
     */
    function initJourneyPlanner() {
        var $planner = $('.mrt-journey-planner');
        if (!$planner.length) return;

        var $form = $planner.find('.mrt-journey-form');
        var $results = $planner.find('.mrt-journey-results');
        var $searchBtn = $form.find('button[type="submit"]');

        $form.on('submit', function(e) {
            e.preventDefault();
            journeyPlannerRunSearch($form, $results, $searchBtn);
        });
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

            if (!date) return;

            var trainType = $month.data('train-type') || '';

            $month.find('.mrt-day-clickable').removeClass('mrt-day-active').attr('aria-pressed', 'false');
            $day.addClass('mrt-day-active').attr('aria-pressed', 'true');

            var wasHidden = $container.hasClass('mrt-hidden');
            monthCalendarRevealContainer($container, wasHidden);
            monthCalendarLoadDay($month, $container, date, trainType);
        });
    }

    /**
     * Initialize all frontend features
     */
    function init() {
        initJourneyPlanner();
        initMonthCalendar();
    }

    // Initialize when DOM is ready
    $(document).ready(init);

    // Also initialize for dynamically loaded content (e.g., AJAX-loaded pages)
    $(document).on('mrt_reinit', init);

})(jQuery);
