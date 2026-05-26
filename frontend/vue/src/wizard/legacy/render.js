/**
 * Shared journey wizard UI render helpers and class names (components 1–9).
 *
 * @package Museum_Railway_Timetable
 */
(function (global) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;

	/** @type {Record<string, string>} */
	var C = {
		btn: 'mrt-jw-btn',
		btnCta: 'mrt-jw-btn mrt-jw-btn--cta',
		btnSelect: 'mrt-jw-btn mrt-jw-btn--select',
		btnBack: 'mrt-jw-btn mrt-jw-btn--back',
		btnExpand: 'mrt-jw-btn mrt-jw-btn--expand',
		btnDay: 'mrt-jw-btn mrt-jw-btn--day',
		btnCalNav: 'mrt-jw-btn mrt-jw-btn--cal-nav',
		btnCalToday: 'mrt-jw-btn mrt-jw-btn--cal-today',
		btnPassed: 'mrt-jw-btn mrt-jw-btn--passed',
		card: 'mrt-jw-card',
		cardTrip: 'mrt-jw-card mrt-jw-card--trip',
		cardSummary: 'mrt-jw-card mrt-jw-card--summary',
		cardSelected: 'mrt-jw-card mrt-jw-card--selected',
		cardCalendar: 'mrt-jw-card mrt-jw-card--calendar',
		tripHead: 'mrt-jw-trip-head',
		tripCopy: 'mrt-jw-trip-head__copy',
		tripSide: 'mrt-jw-trip-head__side',
		tripTime: 'mrt-jw-typo mrt-jw-typo--time',
		tripRoute: 'mrt-jw-typo mrt-jw-typo--route',
		tripDate: 'mrt-jw-typo mrt-jw-typo--date',
		duration: 'mrt-jw-typo mrt-jw-typo--duration',
		vehicleRow: 'mrt-jw-vehicle-row',
		vehicle: 'mrt-jw-vehicle',
		vehicleSep: 'mrt-jw-vehicle-row__sep',
		vehicleMark: 'mrt-jw-vehicle__mark',
		notice: 'mrt-jw-notice',
		noticeWarn: 'mrt-jw-notice__warn',
		noticeDot: 'mrt-jw-notice__dot',
		prices: 'mrt-jw-prices',
		pricesCard: 'mrt-jw-prices mrt-jw-prices--in-card',
		pricesScroll: 'mrt-jw-prices__scroll',
		priceTable: 'mrt-jw-prices__table',
		priceRowActive: 'mrt-jw-prices__row--active',
		priceNote: 'mrt-jw-prices__note',
		stepHead: 'mrt-jw-step-head',
		context: 'mrt-jw-step-head__context',
		titleStep: 'mrt-jw-typo mrt-jw-typo--step-title',
		titleHero: 'mrt-jw-typo mrt-jw-typo--hero-title',
		panel: 'mrt-jw-panel',
		panelActive: 'mrt-jw-panel--active',
		expand: 'mrt-jw-expand',
		expandChevron: 'mrt-jw-expand__chevron',
		detail: 'mrt-jw-card__detail',
		timeline: 'mrt-jw-timeline',
		timelineRow: 'mrt-jw-timeline__row',
		timelineTime: 'mrt-jw-timeline__time',
		timelineNode: 'mrt-jw-timeline__node',
		timelineStation: 'mrt-jw-timeline__station',
		timelineLeg: 'mrt-jw-timeline__leg',
		timelineTransfer: 'mrt-jw-timeline__transfer',
		legDuration: 'mrt-jw-timeline__leg-duration',
		towards: 'mrt-jw-timeline__towards',
		calendarNav: 'mrt-jw-calendar__nav',
		calendarTitle: 'mrt-jw-typo mrt-jw-typo--cal-title',
		calendarGrid: 'mrt-jw-calendar__grid',
		legend: 'mrt-jw-calendar__legend',
		swatch: 'mrt-jw-calendar__swatch',
		tripList: 'mrt-jw-trip-list',
		selectedLabel: 'mrt-jw-selected-label',
		summaryHeading: 'mrt-jw-card__section-title',
	};

	function formatTripClock(time) {
		if (!time) {
			return '—';
		}
		return String(time).replace(':', '.');
	}

	function formatDuration(minutes, cfg) {
		var m = parseInt(minutes, 10);
		if (isNaN(m) || m < 0) {
			return '';
		}
		if (m >= 60) {
			var h = Math.floor(m / 60);
			var rest = m % 60;
			return rest ? (h + ' tim ' + rest + ' min') : (h + ' tim');
		}
		return (cfg.durationMinutes || '%d min').replace('%d', String(m));
	}

	function isWarningNotice(notice) {
		var n = String(notice || '').toLowerCase();
		return n.indexOf('brand') !== -1 || n.indexOf('ersatt') !== -1 || n.indexOf('varning') !== -1;
	}

	function noticeMarkerHtml(notice, cfg) {
		if (!notice) {
			return '';
		}
		if (isWarningNotice(notice)) {
			return '<span class="' + C.noticeWarn + '" role="img" aria-label="' +
				SU.escapeHtml(cfg.noticeLabel || '') + '">⚠</span>';
		}
		return '<span class="' + C.noticeDot + '" aria-label="' +
			SU.escapeHtml(cfg.noticeLabel || '') + '">!</span>';
	}

	function noticeBlockHtml(notice) {
		if (!notice) {
			return '';
		}
		var mod = isWarningNotice(notice) ? ' mrt-jw-notice--warning' : '';
		return '<p class="' + C.notice + mod + '">' + SU.escapeHtml(notice) + '</p>';
	}

	function tripHeadHtml(opts) {
		var dep = opts.dep || '—';
		var arr = opts.arr || '—';
		var html = '<div class="' + C.tripHead + '">';
		html += '<div class="' + C.tripCopy + '">';
		html += '<p class="' + C.tripTime + '">' + SU.escapeHtml(dep) + ' → ' + SU.escapeHtml(arr);
		html += opts.noticeMarker || '';
		html += '</p>';
		html += opts.noticeBlock || '';
		if (opts.route) {
			html += '<p class="' + C.tripRoute + '">' + SU.escapeHtml(opts.route) + '</p>';
		}
		if (opts.date) {
			html += '<p class="' + C.tripDate + '">' + SU.escapeHtml(opts.date) + '</p>';
		}
		if (opts.vehiclesHtml) {
			html += '<div class="' + C.vehicleRow + '">' + opts.vehiclesHtml + '</div>';
		}
		html += '</div><div class="' + C.tripSide + '">';
		if (opts.duration) {
			html += '<span class="' + C.duration + '">' + SU.escapeHtml(opts.duration) + '</span>';
		}
		html += opts.sideExtra || '';
		html += '</div></div>';
		return html;
	}

	function expandButtonHtml(label, attrs) {
		var html = '<button type="button" class="' + C.btnExpand + ' ' + C.expand + '" ' + (attrs || '') + '>';
		html += '<span>' + SU.escapeHtml(label) + '</span>';
		html += '<span class="' + C.expandChevron + '" aria-hidden="true"></span></button>';
		return html;
	}

	function vehicleSepHtml() {
		return '<span class="' + C.vehicleSep + '" aria-hidden="true">→</span>';
	}

	JW.render = {
		C: C,
		formatTripClock: formatTripClock,
		formatDuration: formatDuration,
		isWarningNotice: isWarningNotice,
		noticeMarkerHtml: noticeMarkerHtml,
		noticeBlockHtml: noticeBlockHtml,
		tripHeadHtml: tripHeadHtml,
		expandButtonHtml: expandButtonHtml,
		vehicleSepHtml: vehicleSepHtml,
	};
})(window);
