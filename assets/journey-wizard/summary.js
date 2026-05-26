/**
 * Summary step HTML (Din resa).
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;
	var DU = global.MRTDateUtils;
	var connApi = JW.connection;
	var tripCard = JW.tripCard;
	var prices = JW.prices;

	function renderSummary($root, state, cfg) {
		var $box = $root.find('[data-wizard-summary]');
		var parts = [];
		var ob = state.outbound;
		if (ob) {
			parts.push('<div class="mrt-journey-wizard__summary-card"><strong>' + SU.escapeHtml(cfg.outboundHeading) + '</strong><br>' +
				SU.escapeHtml(state.fromTitle) + ' → ' + SU.escapeHtml(state.toTitle) + '<br>' +
				SU.escapeHtml(DU.formatYmdForDisplay(state.date, cfg)) + '<br><span class="mrt-journey-wizard__trip-time">' +
				SU.escapeHtml(connApi.departureFromOrigin(ob)) + '→' +
				SU.escapeHtml(connApi.arrivalAtDestination(ob)) + '</span><div class="mrt-journey-wizard__vehicle-row">' +
				tripCard.cardBadgesHtml(ob) + '</div>' +
				'</div>');
		}
		if (state.tripType === 'return' && state.inbound) {
			var ib = state.inbound;
			parts.push('<div class="mrt-journey-wizard__summary-card"><strong>' + SU.escapeHtml(cfg.returnHeading) + '</strong><br>' +
				SU.escapeHtml(state.toTitle) + ' → ' + SU.escapeHtml(state.fromTitle) + '<br>' +
				SU.escapeHtml(DU.formatYmdForDisplay(state.date, cfg)) + '<br><span class="mrt-journey-wizard__trip-time">' +
				SU.escapeHtml(connApi.departureFromOrigin(ib)) + '→' +
				SU.escapeHtml(connApi.arrivalAtDestination(ib)) + '</span><div class="mrt-journey-wizard__vehicle-row">' +
				tripCard.cardBadgesHtml(ib) + '</div>' +
				'</div>');
		}
		$box.html(parts.join('') + prices.buildPriceSection(
			state.tripType,
			cfg,
			prices.zonesForStationPair(state.from, state.to, cfg)
		));

		var url = $root.attr('data-ticket-url') || '';
		var $tw = $root.find('[data-wizard-ticket-wrap]');
		var $ta = $root.find('[data-wizard-ticket]');
		if (url) {
			$tw.removeAttr('hidden');
			$ta.attr('href', url);
		} else {
			$tw.attr('hidden', 'hidden');
			$ta.attr('href', '#');
		}
	}

	JW.summary = {
		renderSummary: renderSummary,
	};
})(window, jQuery);
