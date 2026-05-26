/**
 * Journey wizard entry – init all .mrt-journey-wizard roots on the page.
 *
 * @package Museum_Railway_Timetable
 */
(function ($) {
	'use strict';

	var JW = window.MRTJourneyWizard;

	function initOne($root) {
		var cfg = typeof mrtJourneyWizard !== 'undefined' ? mrtJourneyWizard : null;
		if (!cfg) {
			return;
		}
		var startOfWeek = parseInt($root.data('startOfWeek'), 10);
		if (isNaN(startOfWeek) || startOfWeek < 0 || startOfWeek > 6) {
			startOfWeek = 1;
		}
		var wctx = JW.runtime.createRuntime($root, cfg, startOfWeek);
		JW.events.bindAll(wctx);
		wctx.buildStepNav();
		if (JW.debug && typeof JW.debug.applyDebugPreset === 'function') {
			JW.debug.applyDebugPreset(wctx, $root, cfg);
		} else {
			wctx.updateStepNav('route');
		}
	}

	$(function () {
		$('.mrt-journey-wizard').each(function () {
			initOne($(this));
		});
	});
}(jQuery));
