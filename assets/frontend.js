/**
 * Frontend JavaScript for Museum Railway Timetable
 * Handles AJAX interactions for shortcodes
 */

(function($) {
    'use strict';

    /**
     * Initialize Journey Planner AJAX
     */
    function initJourneyPlanner() {
        var $planner = $('.mrt-journey-planner');
        if (!$planner.length) return;

        var $form = $planner.find('.mrt-journey-form');
        var $results = $planner.find('.mrt-journey-results');
        var $searchBtn = $form.find('.mrt-journey-search');
        
        // Prevent default form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            searchJourney();
        });

        function searchJourney() {
            var fromStation = $form.find('#mrt_from').val();
            var toStation = $form.find('#mrt_to').val();
            var date = $form.find('#mrt_date').val();

            // Validation
            if (!fromStation || !toStation || !date) {
                return;
            }

            if (fromStation === toStation) {
                showError($results, typeof mrtFrontend !== 'undefined' ? mrtFrontend.errorSameStations : 'Please select different stations for departure and arrival.');
                return;
            }

            // Show loading state
            $searchBtn.prop('disabled', true).text(typeof mrtFrontend !== 'undefined' ? mrtFrontend.searching : 'Searching...');
            $results.html('<div class="mrt-loading">' + (typeof mrtFrontend !== 'undefined' ? mrtFrontend.loading : 'Loading...') + '</div>');

            // AJAX request
            $.ajax({
                url: (typeof mrtFrontend !== 'undefined' && mrtFrontend.ajaxurl) ? mrtFrontend.ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'mrt_search_journey',
                    from_station: fromStation,
                    to_station: toStation,
                    date: date
                },
                success: function(response) {
                    $searchBtn.prop('disabled', false).text(typeof mrtFrontend !== 'undefined' ? mrtFrontend.search : 'Search');
                    
                    if (response.success) {
                        $results.html(response.data.html);
                        
                        // Update URL without reload (optional)
                        if (history.pushState) {
                            var url = new URL(window.location);
                            url.searchParams.set('mrt_from', fromStation);
                            url.searchParams.set('mrt_to', toStation);
                            url.searchParams.set('mrt_date', date);
                            history.pushState({}, '', url);
                        }
                    } else {
                        showError($results, response.data.message || (typeof mrtFrontend !== 'undefined' ? mrtFrontend.errorSearching : 'Error searching for connections.'));
                    }
                },
                error: function() {
                    $searchBtn.prop('disabled', false).text(typeof mrtFrontend !== 'undefined' ? mrtFrontend.search : 'Search');
                    showError($results, typeof mrtFrontend !== 'undefined' ? mrtFrontend.networkError : 'Network error. Please try again.');
                }
            });
        }

        function showError($container, message) {
            $container.html('<div class="mrt-error">' + message + '</div>');
        }
    }

    /**
     * Initialize Timetable Picker AJAX
     */
    function initTimetablePicker() {
        var $picker = $('.mrt-picker');
        if (!$picker.length) return;

        var $select = $picker.find('select[name="mrt_station_id"]');
        var $form = $picker.closest('form');
        var $resultsContainer = $picker.next('.mrt-timetable-results');
        
        // Create results container if it doesn't exist
        if (!$resultsContainer.length) {
            $resultsContainer = $('<div class="mrt-timetable-results"></div>');
            $picker.after($resultsContainer);
        }

        // Remove onchange submit, use AJAX instead
        $select.off('change').on('change', function() {
            var stationId = $(this).val();
            
            if (!stationId) {
                $resultsContainer.empty();
                return;
            }

            // Get shortcode attributes from data attributes
            var limit = $picker.data('limit') || 6;
            var showArrival = $picker.data('show-arrival') || 0;
            var trainType = $picker.data('train-type') || '';

            // Show loading
            $resultsContainer.html('<div class="mrt-loading">' + (typeof mrtFrontend !== 'undefined' ? mrtFrontend.loading : 'Loading...') + '</div>');

            // AJAX request
            $.ajax({
                url: (typeof mrtFrontend !== 'undefined' && mrtFrontend.ajaxurl) ? mrtFrontend.ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'mrt_get_timetable_for_station',
                    station_id: stationId,
                    limit: limit,
                    show_arrival: showArrival ? 1 : 0,
                    train_type: trainType
                },
                success: function(response) {
                    if (response.success) {
                        $resultsContainer.html(response.data.html);
                    } else {
                        $resultsContainer.html('<div class="mrt-error">' + (response.data.message || (typeof mrtFrontend !== 'undefined' ? mrtFrontend.errorLoading : 'Error loading timetable.')) + '</div>');
                    }
                },
                error: function() {
                    $resultsContainer.html('<div class="mrt-error">' + (typeof mrtFrontend !== 'undefined' ? mrtFrontend.networkError : 'Network error. Please try again.') + '</div>');
                }
            });
        });
    }

    /**
     * Initialize all frontend features
     */
    function init() {
        initJourneyPlanner();
        initTimetablePicker();
    }

    // Initialize when DOM is ready
    $(document).ready(init);

    // Also initialize for dynamically loaded content (e.g., AJAX-loaded pages)
    $(document).on('mrt_reinit', init);

})(jQuery);

