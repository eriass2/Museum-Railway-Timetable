/**
 * Apply hardcoded wizard presets on development debug pages.
 *
 * @package Museum_Railway_Timetable
 */
(function ($) {
	'use strict';

	var JW = window.MRTJourneyWizard;

	function applyPresetFields($root, state, preset) {
		if (preset.from && preset.to) {
			$root.find('#mrt_wizard_from').val(String(preset.from));
			$root.find('#mrt_wizard_to').val(String(preset.to));
		}
		var trip = preset.tripType === 'return' ? 'return' : 'single';
		$root.find('input[name="mrt_wizard_trip_type"][value="' + trip + '"]').prop('checked', true);
	}

	function applyCalendar(wctx, $root, preset) {
		if (!preset.calendarYear || !preset.calendarMonth) {
			return;
		}
		wctx.state.calYear = preset.calendarYear;
		wctx.state.calMonth = preset.calendarMonth;
		wctx.renderCalendarGrid(preset.calendarYear, preset.calendarMonth, preset.calendarDays || {});
	}

	function applyConnections(wctx, $root, cfg, preset) {
		var state = wctx.state;
		var fromId = state.from || 0;
		var toId = state.to || 0;
		if (preset.outboundConnections && preset.outboundConnections.length) {
			wctx.lastOutboundList = preset.outboundConnections;
			wctx.renderConnectionTable(
				$root.find('[data-wizard-outbound]'),
				preset.outboundConnections,
				'outbound',
				fromId,
				toId
			);
		}
		if (preset.returnConnections && preset.returnConnections.length) {
			wctx.lastReturnList = preset.returnConnections;
			wctx.renderConnectionTable(
				$root.find('[data-wizard-return]'),
				preset.returnConnections,
				'return',
				toId,
				fromId
			);
		}
	}

	function applyDebugPreset(wctx, $root, cfg) {
		var key = $root.attr('data-wizard-debug');
		if (!key || !cfg.debugPresets || !cfg.debugPresets[key]) {
			return;
		}
		var preset = cfg.debugPresets[key];
		var state = wctx.state;

		state.tripType = preset.tripType || 'single';
		state.from = preset.from || 0;
		state.to = preset.to || 0;
		state.fromTitle = preset.fromTitle || '';
		state.toTitle = preset.toTitle || '';
		state.date = preset.date || '';
		state.outbound = preset.outbound || null;
		state.inbound = preset.inbound || null;

		applyPresetFields($root, state, preset);
		JW.context.updateContext($root, state, cfg);
		wctx.buildStepNav();

		if (preset.step === 'date') {
			applyCalendar(wctx, $root, preset);
		}
		if (preset.outbound && preset.step === 'return') {
			var $sum = $root.find('[data-wizard-return-summary]');
			$sum.html(JW.tripCard.selectedTripHtml(preset.outbound, cfg, state));
		}
		applyConnections(wctx, $root, cfg, preset);

		if (preset.step === 'summary') {
			wctx.renderSummary();
		}

		wctx.showPanel(preset.step || 'route');
	}

	JW.debug = {
		applyDebugPreset: applyDebugPreset,
	};
}(jQuery));
