/**
 * Journey wizard calendar grid (step 2).
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;
	var DU = global.MRTDateUtils;

	function dayButtonAriaLabel(ymd, st, cfg) {
		var human = DU.formatYmdForDisplay(ymd, cfg);
		if (st === 'ok') {
			return (cfg.dayDateOk || human).replace('%s', human);
		}
		if (st === 'traffic_no_match') {
			return (cfg.dayDateTraffic || human).replace('%s', human);
		}
		return (cfg.dayDateNone || human).replace('%s', human);
	}

	function orderedWeekdayHeaders(cfg, startOfWeek) {
		var abb = cfg.weekdayAbbrev.slice();
		var out = [];
		var i;
		for (i = 0; i < 7; i++) {
			out.push(abb[(startOfWeek + i) % 7]);
		}
		return out;
	}

	function calendarDayButton(ymd, dayNum, st, cfg, selectedDateYmd, $cal, onSelectOkDay) {
		var $btn = $('<button type="button" class="mrt-journey-wizard__day"></button>');
		$btn.text(String(dayNum));
		$btn.attr('aria-label', dayButtonAriaLabel(ymd, st, cfg));
		if (st === 'ok') {
			$btn.addClass('mrt-journey-wizard__day--ok');
			$btn.attr('aria-pressed', selectedDateYmd === ymd ? 'true' : 'false');
			$btn.on('click', function () {
				$cal.find('.mrt-journey-wizard__day--ok').each(function () {
					$(this).attr('aria-pressed', 'false').removeClass('is-selected');
				});
				$btn.attr('aria-pressed', 'true').addClass('is-selected');
				onSelectOkDay(ymd);
			});
		} else if (st === 'traffic_no_match') {
			$btn.addClass('mrt-journey-wizard__day--traffic');
			$btn.attr('disabled', 'disabled');
		} else {
			$btn.addClass('mrt-journey-wizard__day--none');
			$btn.attr('disabled', 'disabled');
		}
		if (selectedDateYmd === ymd && st === 'ok') {
			$btn.addClass('is-selected');
		}
		return $btn;
	}

	function calendarTable(cfg, startOfWeek) {
		var $table = $('<table></table>')
			.attr('role', 'grid')
			.attr('aria-label', cfg.calendarGridLabel || '');
		var $thead = $('<tr></tr>');
		orderedWeekdayHeaders(cfg, startOfWeek).forEach(function (ab) {
			$thead.append($('<th scope="col"></th>').text(ab));
		});
		return $table.append($('<thead></thead>').append($thead));
	}

	function padCalendarRow($row, col) {
		while (col > 0 && col < 7) {
			$row.append($('<td></td>'));
			col++;
		}
	}

	function appendCalendarDay($row, col, dayNum, args) {
		var ymd = DU.ymdFromParts(args.year, args.month, dayNum);
		var st = args.daysMap[ymd] || 'none';
		var $td = $('<td></td>');
		$td.append(calendarDayButton(ymd, dayNum, st, args.cfg, args.selectedDateYmd, args.$cal, args.onSelectOkDay));
		$row.append($td);
		return col + 1;
	}

	function appendCalendarDays($tb, args) {
		var col = 0;
		var $row = $('<tr></tr>');
		var d;
		var pad;
		for (pad = 0; pad < args.startCol; pad++) {
			$row.append($('<td></td>'));
			col++;
		}
		for (d = 1; d <= args.lastDay; d++) {
			if (col >= 7) {
				$tb.append($row);
				$row = $('<tr></tr>');
				col = 0;
			}
			col = appendCalendarDay($row, col, d, args);
		}
		padCalendarRow($row, col);
		$tb.append($row);
	}

	function renderCalendarGrid($root, year, month, daysMap, cfg, startOfWeek, selectedDateYmd, onSelectOkDay) {
		var $cal = $root.find('[data-wizard-calendar]');
		$cal.empty();
		$root.find('.mrt-journey-wizard__cal-title').text(DU.calendarMonthTitle(year, month, cfg.monthNames));

		var lastDay = DU.daysInMonth(year, month);
		var startCol = DU.monthStartColumn(year, month, startOfWeek);
		var $table = calendarTable(cfg, startOfWeek);
		var $tb = $('<tbody></tbody>');
		appendCalendarDays($tb, {
			year: year,
			month: month,
			daysMap: daysMap,
			cfg: cfg,
			startCol: startCol,
			lastDay: lastDay,
			selectedDateYmd: selectedDateYmd,
			$cal: $cal,
			onSelectOkDay: onSelectOkDay,
		});
		$table.append($tb);
		$cal.append($table);
	}

	JW.calendar = {
		renderCalendarGrid: renderCalendarGrid,
	};
})(window, jQuery);
