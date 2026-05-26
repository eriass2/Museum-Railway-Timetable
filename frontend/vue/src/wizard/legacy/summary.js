/**
 * Summary step HTML (Din resa).
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var DU = global.MRTDateUtils;
	var tripCard = JW.tripCard;
	var prices = JW.prices;

	function renderSummary($root, state, cfg) {
		var $box = $root.find('[data-wizard-summary]');
		var dateText = state.date ? DU.formatYmdForDisplay(state.date, cfg) : '';
		var parts = [];

		if (state.outbound) {
			parts.push(
				tripCard.summaryLegCardHtml(
					cfg.outboundHeading || 'Utresa',
					state.outbound,
					(state.fromTitle || '') + ' → ' + (state.toTitle || ''),
					dateText,
					cfg
				)
			);
		}
		if (state.tripType === 'return' && state.inbound) {
			parts.push(
				tripCard.summaryLegCardHtml(
					cfg.returnHeading || 'Återresa',
					state.inbound,
					(state.toTitle || '') + ' → ' + (state.fromTitle || ''),
					dateText,
					cfg
				)
			);
		}

		var listHtml = parts.length
			? '<div class="mrt-journey-wizard__summary-list">' + parts.join('') + '</div>'
			: '';

		$box.html(
			listHtml +
				prices.buildPriceSection(state.tripType, cfg, prices.zonesForStationPair(state.from, state.to, cfg))
		);

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
