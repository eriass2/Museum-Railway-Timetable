/**
 * Wizard context line (route / trip type / date).
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;

	function tripTypeText(state, cfg) {
		return state.tripType === 'return' ? (cfg.tripTypeReturn || 'Tur- och retur') : (cfg.tripTypeSingle || 'Enkel');
	}

	function contextLine(state, cfg, includeDate) {
		var routeTpl = includeDate ? (cfg.routeDateContext || '%1$s → %2$s | %3$s\n%4$s') : (cfg.routeContext || '%1$s → %2$s | %3$s');
		var out = routeTpl
			.replace('%1$s', state.fromTitle || '')
			.replace('%2$s', state.toTitle || '')
			.replace('%3$s', tripTypeText(state, cfg));
		if (includeDate) {
			out = out.replace('%4$s', global.MRTDateUtils.formatYmdForDisplay(state.date, cfg));
		}
		return out;
	}

	function updateContext($root, state, cfg) {
		$root.find('[data-wizard-context]').each(function () {
			$(this).text(contextLine(state, cfg, Boolean(state.date)));
		});
	}

	function cardRouteText(state, ctx) {
		if (ctx === 'return') {
			return (state.toTitle || '') + ' → ' + (state.fromTitle || '');
		}
		return (state.fromTitle || '') + ' → ' + (state.toTitle || '');
	}

	JW.context = {
		tripTypeText: tripTypeText,
		contextLine: contextLine,
		updateContext: updateContext,
		cardRouteText: cardRouteText,
	};
})(window, jQuery);
