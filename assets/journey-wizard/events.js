/**
 * Wizard DOM event bindings.
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var FA = global.MRTFrontendApi;
	var DU = global.MRTDateUtils;
	var ctxApi = JW.context;
	var detail = JW.connectionDetail;

	function bindRouteNext(wctx) {
		var $root = wctx.$root;
		var cfg = wctx.cfg;
		var state = wctx.state;
		$root.on('click', '[data-wizard-next="route"]', function () {
			wctx.clearError();
			var from = parseInt($root.find('#mrt_wizard_from').val(), 10);
			var to = parseInt($root.find('#mrt_wizard_to').val(), 10);
			var trip = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
			if (!from || !to) {
				wctx.showError(cfg.pleaseStations);
				return;
			}
			if (from === to) {
				wctx.showError(FA.msg('errorSameStations', cfg.errorGeneric));
				return;
			}
			state.from = from;
			state.to = to;
			state.tripType = trip === 'return' ? 'return' : 'single';
			state.outbound = null;
			state.inbound = null;
			state.date = '';
			state.fromTitle = $root.find('#mrt_wizard_from option:selected').text();
			state.toTitle = $root.find('#mrt_wizard_to option:selected').text();
			ctxApi.updateContext($root, state, cfg);
			wctx.buildStepNav();
			wctx.showPanel('date');
			var cm0 = DU.currentCalendarYearMonth();
			wctx.loadCalendar(cm0.year, cm0.month);
		});
	}

	function bindCalendarNav(wctx) {
		var $root = wctx.$root;
		var state = wctx.state;
		$root.on('click', '.mrt-journey-wizard__cal-prev', function () {
			var cm = DU.addCalendarMonths(state.calYear, state.calMonth, -1);
			wctx.loadCalendar(cm.year, cm.month);
		});
		$root.on('click', '.mrt-journey-wizard__cal-next', function () {
			var cm = DU.addCalendarMonths(state.calYear, state.calMonth, 1);
			wctx.loadCalendar(cm.year, cm.month);
		});
		$root.on('click', '[data-wizard-current-month]', function () {
			var cm0 = DU.currentCalendarYearMonth();
			wctx.loadCalendar(cm0.year, cm0.month);
		});
	}

	function bindDetailAndSelect(wctx) {
		var $root = wctx.$root;
		var state = wctx.state;
		$root.on('click', '.mrt-jw-btn--expand, .mrt-journey-wizard__btn-detail', function () {
			detail.toggleDetailRow(wctx, $(this));
		});
		$root.on('click', '[data-wizard-passed-toggle]', function () {
			var $detail = $(this).closest('.mrt-jw-card__detail, .mrt-journey-wizard__detail');
			var $card = $(this).closest('.mrt-jw-card--trip, .mrt-journey-wizard__trip-card');
			var $btn = $card.find('.mrt-jw-btn--expand, .mrt-journey-wizard__btn-detail').first();
			var expandPassed = !$detail.hasClass('is-passed-expanded');
			detail.loadDetailIntoCard(wctx, $btn, expandPassed);
		});
		$root.on('click', '.mrt-jw-btn--select, .mrt-journey-wizard__btn-select', function () {
			var legCtx = $(this).attr('data-ctx');
			var idx = parseInt($(this).attr('data-idx'), 10);
			var list = legCtx === 'return' ? wctx.lastReturnList : wctx.lastOutboundList;
			var conn = list[idx];
			if (!conn) {
				return;
			}
			if (legCtx === 'outbound') {
				state.outbound = conn;
				state.inbound = null;
				if (state.tripType === 'return') {
					wctx.showPanel('return');
					wctx.loadReturnConnections();
				} else {
					wctx.showPanel('summary');
					wctx.renderSummary();
				}
			} else {
				state.inbound = conn;
				wctx.showPanel('summary');
				wctx.renderSummary();
			}
		});
	}

	function bindBack(wctx) {
		var $root = wctx.$root;
		var state = wctx.state;
		$root.on('click', '[data-wizard-back]', function () {
			var step = $(this).attr('data-wizard-back');
			wctx.clearError();
			if (step === 'date') {
				state.date = '';
				wctx.showPanel('route');
			} else if (step === 'outbound') {
				state.outbound = null;
				state.inbound = null;
				wctx.showPanel('date');
				wctx.loadCalendar(state.calYear, state.calMonth);
			} else if (step === 'return') {
				state.inbound = null;
				wctx.showPanel('outbound');
				wctx.loadOutboundConnections();
			} else if (step === 'summary') {
				if (state.tripType === 'return') {
					wctx.showPanel('return');
					wctx.loadReturnConnections();
				} else {
					wctx.showPanel('outbound');
					wctx.loadOutboundConnections();
				}
			}
		});
	}

	function bindTripType(wctx) {
		var $root = wctx.$root;
		var state = wctx.state;
		$root.on('change', 'input[name="mrt_wizard_trip_type"]', function () {
			state.tripType = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
		});
	}

	function bindAll(wctx) {
		bindRouteNext(wctx);
		bindCalendarNav(wctx);
		bindDetailAndSelect(wctx);
		bindBack(wctx);
		bindTripType(wctx);
	}

	JW.events = {
		bindAll: bindAll,
	};
})(window, jQuery);
