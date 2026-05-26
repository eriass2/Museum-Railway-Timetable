/**
 * Price matrix rendering for summary step.
 *
 * @package Museum_Railway_Timetable
 */
(function (global) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;
	var PRICE_TYPE_KEYS = JW.PRICE_TYPE_KEYS;
	var PRICE_CAT_KEYS = JW.PRICE_CAT_KEYS;

	function matrixHasAnyPrice(matrix) {
		if (!matrix) {
			return false;
		}
		var ti;
		var ci;
		for (ti = 0; ti < PRICE_TYPE_KEYS.length; ti++) {
			var row = matrix[PRICE_TYPE_KEYS[ti]];
			if (!row) {
				continue;
			}
			for (ci = 0; ci < PRICE_CAT_KEYS.length; ci++) {
				var v = row[PRICE_CAT_KEYS[ci]];
				if (v !== null && v !== undefined && v !== '') {
					return true;
				}
			}
		}
		return false;
	}

	function formatPriceCell(v, cfg) {
		if (v === null || v === undefined || v === '') {
			return cfg.priceDash || '—';
		}
		var s = String(v).trim();
		if (/^\d+$/.test(s)) {
			return s + ' kr';
		}
		return s;
	}

	function zonesForStationPair(fromId, toId, cfg) {
		var map = cfg.priceStationZones || {};
		var fromZones = map[String(fromId)] || map[fromId] || [];
		var toZones = map[String(toId)] || map[toId] || [];
		var best = 4;
		if (!fromZones.length || !toZones.length) {
			return best;
		}
		fromZones.forEach(function (fz) {
			toZones.forEach(function (tz) {
				var span = Math.abs(parseInt(tz, 10) - parseInt(fz, 10)) + 1;
				if (!isNaN(span)) {
					best = Math.min(best, span);
				}
			});
		});
		return Math.max(1, Math.min(4, best));
	}

	function matrixForZone(cfg, zones) {
		var byZone = cfg.priceMatrixByZone || null;
		var zoneKey = String(Math.max(1, Math.min(4, parseInt(zones, 10) || 4)));
		var out = {};
		if (!byZone) {
			return cfg.priceMatrix || {};
		}
		PRICE_TYPE_KEYS.forEach(function (tk) {
			out[tk] = {};
			PRICE_CAT_KEYS.forEach(function (ck) {
				out[tk][ck] = byZone[tk] && byZone[tk][ck] ? byZone[tk][ck][zoneKey] : null;
			});
		});
		return out;
	}

	function buildPriceSection(tripType, cfg, zones, options) {
		options = options || {};
		var matrix = matrixForZone(cfg, zones);
		if (!matrix || !matrixHasAnyPrice(matrix)) {
			return '';
		}
		var C = JW.render.C;
		var tickets = cfg.priceTickets || {};
		var cats = cfg.priceCategories || {};
		var activeType = tripType === 'return' ? 'return' : 'single';
		var zoneText = (cfg.priceZoneLabel || '%d zones').replace('%d', String(zones || 4));
		var priceClass = C.prices + ' mrt-journey-wizard__prices mrt-mt-lg';
		if (options.compactTitle) {
			priceClass += ' ' + C.pricesCard + ' mrt-journey-wizard__prices--card';
		}
		var html = '<div class="' + priceClass + '">';
		if (options.compactTitle) {
			html += '<h4 class="mrt-heading mrt-heading--md">' + SU.escapeHtml(cfg.priceTitle || 'Priser') + '</h4>';
		} else {
			html += '<h4 class="mrt-heading mrt-heading--md">' + SU.escapeHtml(cfg.priceTitle || '') + ' <span>(' +
				SU.escapeHtml(zoneText) + ')</span></h4>';
		}
		html += '<div class="' + C.pricesScroll + ' mrt-journey-wizard__prices-scroll mrt-overflow-x-auto">';
		html += '<table class="mrt-table ' + C.priceTable + ' mrt-journey-wizard__price-table"><thead><tr><th scope="col"><span class="mrt-sr-only">' +
			SU.escapeHtml(cfg.priceTableTypeColumn || '') + '</span></th>';
		PRICE_CAT_KEYS.forEach(function (ck) {
			html += '<th scope="col">' + SU.escapeHtml(cats[ck] || ck) + '</th>';
		});
		html += '</tr></thead><tbody>';
		PRICE_TYPE_KEYS.forEach(function (tk) {
			var row = matrix[tk] || {};
			var rowClass = tk === activeType ? ' class="' + C.priceRowActive + ' mrt-journey-wizard__price-row--active"' : '';
			html += '<tr' + rowClass + '><th scope="row">' + SU.escapeHtml(tickets[tk] || tk) + '</th>';
			PRICE_CAT_KEYS.forEach(function (ck) {
				html += '<td>' + SU.escapeHtml(formatPriceCell(row[ck], cfg)) + '</td>';
			});
			html += '</tr>';
		});
		html += '</tbody></table></div>';
		if (cfg.priceNote) {
			html += '<p class="mrt-text-secondary ' + C.priceNote + ' mrt-journey-wizard__price-note mrt-mt-sm">' +
				SU.escapeHtml(cfg.priceNote) + '</p>';
		}
		html += '</div>';
		return html;
	}

	JW.prices = {
		buildPriceSection: buildPriceSection,
		zonesForStationPair: zonesForStationPair,
	};
})(window);
