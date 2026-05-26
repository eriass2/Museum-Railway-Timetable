/**
 * Wizard runtime: step nav, panels, AJAX loaders.
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;
	var FA = global.MRTFrontendApi;
	var DU = global.MRTDateUtils;
	var connApi = JW.connection;
	var calendar = JW.calendar;
	var tripCard = JW.tripCard;
	var summary = JW.summary;
	var ctxApi = JW.context;

	function attachErrorsAjax(wctx, $err, cfg) {
		wctx.showError = function (msg) {
			$err.html('<div class="mrt-alert mrt-alert-error"></div>');
			$err.find('.mrt-alert').text(msg);
		};
		wctx.clearError = function () {
			$err.empty();
		};
		wctx.ajaxPost = function (action, data) {
			return FA.post(action, data, {
				ajaxurl: cfg.ajaxurl,
				nonce: cfg.nonce,
			});
		};
	}

	function attachStepNav(wctx, $root, cfg, state, $stepsOl) {
		wctx.getStepSequence = function () {
			var seq = ['route', 'date', 'outbound'];
			if (state.tripType === 'return') {
				seq.push('return');
			}
			seq.push('summary');
			return seq;
		};
		wctx.buildStepNav = function () {
			var seq = wctx.getStepSequence();
			var labels = {
				route: cfg.stepRoute,
				date: cfg.stepDate,
				outbound: cfg.stepOutbound,
				return: cfg.stepReturn,
				summary: cfg.stepSummary,
			};
			$stepsOl.empty();
			seq.forEach(function (key, i) {
				var $li = $('<li></li>').text((i + 1) + '. ' + (labels[key] || key));
				$li.attr('data-wizard-indicator', key);
				$stepsOl.append($li);
			});
		};
		wctx.updateStepNav = function (name) {
			var seq = wctx.getStepSequence();
			var idx = seq.indexOf(name);
			$stepsOl.find('li').each(function (i) {
				var $li = $(this);
				$li.toggleClass('is-active', i === idx);
				$li.toggleClass('is-done', i < idx);
				if (i === idx) {
					$li.attr('aria-current', 'step');
				} else {
					$li.removeAttr('aria-current');
				}
			});
		};
	}

	function attachShowPanel(wctx, $root) {
		wctx.showPanel = function (name) {
			var $visible = null;
			ctxApi.updateContext($root, wctx.state, wctx.cfg);
			$root.find('.mrt-jw-panel, .mrt-journey-wizard__panel').each(function () {
				var $p = $(this);
				var step = $p.attr('data-wizard-step');
				if (step === name) {
					$p.removeAttr('hidden').addClass('mrt-jw-panel--active mrt-journey-wizard__panel--active');
					$visible = $p;
				} else {
					$p.attr('hidden', 'hidden').removeClass('mrt-jw-panel--active mrt-journey-wizard__panel--active');
				}
			});
			wctx.updateStepNav(name);
			if ($visible && $visible.length) {
				var $h = $visible.find('h2, h3').first();
				if ($h.length) {
					$h.attr('tabindex', '-1');
					$h.trigger('focus');
					$h.one('blur', function () {
						$h.removeAttr('tabindex');
					});
				}
			}
		};
	}

	function attachCalendar(wctx, $root, cfg, startOfWeek, state) {
		wctx.renderCalendarGrid = function (year, month, daysMap) {
			calendar.renderCalendarGrid($root, year, month, daysMap, cfg, startOfWeek, state.date, function (ymd) {
				state.date = ymd;
				wctx.clearError();
				wctx.showPanel('outbound');
				wctx.loadOutboundConnections();
			});
		};
		wctx.loadCalendar = function (year, month) {
			state.calYear = year;
			state.calMonth = month;
			var $calHost = $root.find('[data-wizard-calendar]');
			$calHost.attr('aria-busy', 'true');
			$calHost.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
			wctx.ajaxPost('mrt_journey_calendar_month', {
				from_station: state.from,
				to_station: state.to,
				year: year,
				month: month,
			}).done(function (res) {
				if (!res || !res.success || !res.data) {
					$calHost.attr('aria-busy', 'false');
					wctx.showError(cfg.errorGeneric);
					return;
				}
				wctx.renderCalendarGrid(res.data.year, res.data.month, res.data.days || {});
				$calHost.attr('aria-busy', 'false');
			}).fail(function () {
				$calHost.attr('aria-busy', 'false');
				wctx.showError(FA.msg('networkError', cfg.errorGeneric));
			});
		};
	}

	function attachConnTable(wctx, cfg, state) {
		wctx.renderConnectionTable = function ($target, list, legCtx, legFrom, legTo) {
			if (legCtx === 'outbound') {
				wctx.lastOutboundList = list;
			} else {
				wctx.lastReturnList = list;
			}
			tripCard.renderConnectionCards($target, list, legCtx, legFrom, legTo, cfg, state);
		};
	}

	function attachLoadOutbound(wctx, $root, cfg, state) {
		wctx.loadOutboundConnections = function () {
			var $box = $root.find('[data-wizard-outbound]');
			$box.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
			wctx.ajaxPost('mrt_search_journey', {
				from_station: state.from,
				to_station: state.to,
				date: state.date,
				trip_type: 'single',
			}).done(function (res) {
				if (!res || !res.success) {
					var msg = (res && res.data && res.data.message) ? res.data.message : cfg.errorGeneric;
					$box.html('<div class="mrt-alert mrt-alert-error"></div>');
					$box.find('.mrt-alert').text(msg);
					return;
				}
				var conns = res.data.connections || [];
				if (!conns.length) {
					$box.html('<div class="mrt-alert mrt-alert-info"><p>' + SU.escapeHtml(cfg.noConnections) + '</p></div>');
					return;
				}
				wctx.renderConnectionTable($box, conns, 'outbound', state.from, state.to);
			}).fail(function () {
				$box.html('<div class="mrt-alert mrt-alert-error"></div>');
				$box.find('.mrt-alert').text(FA.msg('networkError', cfg.errorGeneric));
			});
		};
	}

	function attachLoadReturn(wctx, $root, cfg, state) {
		wctx.loadReturnConnections = function () {
			var arr = connApi.arrivalAtDestination(state.outbound);
			if (!arr) {
				wctx.showError(cfg.errorGeneric);
				return;
			}
			var $sum = $root.find('[data-wizard-return-summary]');
			$sum.html(tripCard.selectedTripHtml(state.outbound, cfg, state));

			var $box = $root.find('[data-wizard-return]');
			$box.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
			wctx.ajaxPost('mrt_search_journey', {
				from_station: state.from,
				to_station: state.to,
				date: state.date,
				trip_type: 'return',
				outbound_arrival: arr,
			}).done(function (res) {
				if (!res || !res.success) {
					var msg = (res && res.data && res.data.message) ? res.data.message : cfg.errorGeneric;
					$box.html('<div class="mrt-alert mrt-alert-error"></div>');
					$box.find('.mrt-alert').text(msg);
					return;
				}
				var conns = res.data.connections || [];
				if (!conns.length) {
					$box.html('<div class="mrt-alert mrt-alert-info"><p>' + SU.escapeHtml(cfg.noConnections) + '</p></div>');
					return;
				}
				wctx.renderConnectionTable($box, conns, 'return', state.to, state.from);
			}).fail(function () {
				$box.html('<div class="mrt-alert mrt-alert-error"></div>');
				$box.find('.mrt-alert').text(FA.msg('networkError', cfg.errorGeneric));
			});
		};
	}

	function attachSummary(wctx, $root, cfg, state) {
		wctx.renderSummary = function () {
			summary.renderSummary($root, state, cfg);
		};
	}

	/**
	 * @param {jQuery} $root
	 * @param {object} cfg
	 * @param {number} startOfWeek
	 * @return {object} wctx
	 */
	function createRuntime($root, cfg, startOfWeek) {
		var state = {
			tripType: 'single',
			from: 0,
			to: 0,
			fromTitle: '',
			toTitle: '',
			date: '',
			calYear: 0,
			calMonth: 0,
			outbound: null,
			inbound: null,
		};
		var lastOutboundList = [];
		var lastReturnList = [];
		var $err = $root.find('.mrt-journey-wizard__errors');
		var $stepsOl = $root.find('[data-wizard-steps]');

		var wctx = {
			$root: $root,
			cfg: cfg,
			startOfWeek: startOfWeek,
			state: state,
			lastOutboundList: lastOutboundList,
			lastReturnList: lastReturnList,
			$err: $err,
			$stepsOl: $stepsOl,
		};

		attachErrorsAjax(wctx, $err, cfg);
		attachStepNav(wctx, $root, cfg, state, $stepsOl);
		attachShowPanel(wctx, $root);
		attachCalendar(wctx, $root, cfg, startOfWeek, state);
		attachConnTable(wctx, cfg, state);
		attachLoadOutbound(wctx, $root, cfg, state);
		attachLoadReturn(wctx, $root, cfg, state);
		attachSummary(wctx, $root, cfg, state);

		state.tripType = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
		return wctx;
	}

	JW.runtime = {
		createRuntime: createRuntime,
	};
})(window, jQuery);
