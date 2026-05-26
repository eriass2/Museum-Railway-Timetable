/**
 * Trip cards for outbound / return steps (valj-utresa mockup).
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;
	var connApi = JW.connection;
	var vehicle = JW.vehicle;
	var ctxApi = JW.context;

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

	function ariaChooseTrip(conn, dep, arr, cfg) {
		var s = cfg.btnChooseTripAria || '';
		return s
			.replace('%1$s', conn.service_name || '')
			.replace('%2$s', dep)
			.replace('%3$s', arr);
	}

	function connectionLegs(conn) {
		if (conn.legs && conn.legs.length) {
			return conn.legs;
		}
		return [{
			service_id: conn.service_id,
			service_name: conn.service_name,
			train_type: conn.train_type,
			from_station_id: conn.from_station_id,
			to_station_id: conn.to_station_id,
			from_departure: connApi.departureFromOrigin(conn),
			to_arrival: connApi.arrivalAtDestination(conn),
			destination: conn.destination,
			direction: conn.direction,
		}];
	}

	function connectionMeta(conn, cfg) {
		if (conn.connection_type === 'transfer' || (conn.legs && conn.legs.length > 1)) {
			return cfg.transferTrip || 'Byte';
		}
		return cfg.directTrip || 'Direktresa';
	}

	function cardBadgesHtml(conn) {
		var legs = connectionLegs(conn);
		var parts = [];
		legs.forEach(function (leg) {
			parts.push(vehicle.legVehicleBadge(leg));
		});
		if (!parts.length) {
			parts.push(
				vehicle.vehicleBadge(conn.train_type, conn.service_name, conn.train_type_slug, conn.train_type_icon)
			);
		}
		return parts.join('<span class="mrt-journey-wizard__vehicle-sep" aria-hidden="true">→</span>');
	}

	function cardNoticeDotHtml(notice, cfg) {
		return notice ? '<span class="mrt-journey-wizard__notice-dot" aria-label="' + SU.escapeHtml(cfg.noticeLabel || '') + '">!</span>' : '';
	}

	function cardCopyHtml(conn, dep, arr, legCtx, cfg, state) {
		var notice = conn.notice || '';
		var html = '<div class="mrt-journey-wizard__trip-copy">';
		html += '<p class="mrt-journey-wizard__trip-time">' + SU.escapeHtml(dep) + ' → ' + SU.escapeHtml(arr);
		html += cardNoticeDotHtml(notice, cfg);
		html += '</p>';
		if (notice) {
			html += '<p class="mrt-journey-wizard__notice">' + SU.escapeHtml(notice) + '</p>';
		}
		html += '<p class="mrt-journey-wizard__trip-route">' + SU.escapeHtml(ctxApi.cardRouteText(state, legCtx)) + '</p>';
		html += '<div class="mrt-journey-wizard__vehicle-row">' + cardBadgesHtml(conn) + '</div>';
		return html + '</div>';
	}

	function cardSideHtml(conn, idx, legCtx, dep, arr, cfg) {
		var duration = formatDuration(conn.duration_minutes, cfg);
		var html = '<div class="mrt-journey-wizard__trip-side">';
		if (duration) {
			html += '<span class="mrt-journey-wizard__duration">' + SU.escapeHtml(duration) + '</span>';
		}
		html += '<button type="button" class="mrt-journey-wizard__btn-select" aria-label="' +
			SU.escapeHtml(ariaChooseTrip(conn, dep, arr, cfg)) + '" data-ctx="' + SU.escapeHtml(legCtx) +
			'" data-idx="' + String(idx) + '">' + SU.escapeHtml(cfg.selectTrip || 'Välj →') + '</button>';
		return html + '</div>';
	}

	function cardDetailButtonHtml(conn, idx, legCtx, legFrom, legTo, detailId, cfg) {
		var html = '<button type="button" class="mrt-journey-wizard__btn-detail" aria-label="' +
			SU.escapeHtml((cfg.btnShowStopsAria || '').replace('%s', conn.service_name || connectionMeta(conn, cfg))) +
			'" aria-expanded="false" aria-controls="' + detailId + '" data-ctx="' + SU.escapeHtml(legCtx) +
			'" data-idx="' + String(idx) + '" data-leg-from="' + String(legFrom) + '" data-leg-to="' + String(legTo) + '">';
		html += '<span>' + SU.escapeHtml(connectionMeta(conn, cfg)) + '</span><span class="mrt-journey-wizard__chevron" aria-hidden="true"></span>';
		return html + '</button>';
	}

	function cardHtml(conn, idx, legCtx, legFrom, legTo, cfg, state) {
		var dep = formatTripClock(connApi.departureFromOrigin(conn));
		var arr = formatTripClock(connApi.arrivalAtDestination(conn));
		var detailId = 'mrt-jw-detail-' + legCtx + '-' + idx;
		var html = '<article class="mrt-journey-wizard__trip-card" data-wizard-card="' + SU.escapeHtml(legCtx) + '-' + idx + '">';
		html += '<div class="mrt-journey-wizard__trip-main">';
		html += cardCopyHtml(conn, dep, arr, legCtx, cfg, state);
		html += cardSideHtml(conn, idx, legCtx, dep, arr, cfg);
		html += '</div>';
		html += cardDetailButtonHtml(conn, idx, legCtx, legFrom, legTo, detailId, cfg);
		html += '<div class="mrt-journey-wizard__detail" id="' + detailId + '" hidden></div>';
		html += '</article>';
		return html;
	}

	function selectedTripHtml(conn, cfg, state) {
		var dep = connApi.departureFromOrigin(conn) || '—';
		var arr = connApi.arrivalAtDestination(conn) || '—';
		var duration = formatDuration(conn.duration_minutes, cfg);
		var html = '<div class="mrt-journey-wizard__selected-label">' + SU.escapeHtml(cfg.selectedOutbound || 'Vald utresa') + '</div>';
		html += '<div class="mrt-journey-wizard__selected-card">';
		html += '<div><strong>' + SU.escapeHtml(dep) + '→' + SU.escapeHtml(arr) + '</strong>';
		html += '<span> • ' + SU.escapeHtml(state.fromTitle || '') + ' → ' + SU.escapeHtml(state.toTitle || '') + '</span></div>';
		if (duration) {
			html += '<strong>' + SU.escapeHtml(duration) + '</strong>';
		}
		html += '<div class="mrt-journey-wizard__vehicle-row">' + cardBadgesHtml(conn) + '</div>';
		html += '</div>';
		return html;
	}

	function renderConnectionCards($target, list, legCtx, legFrom, legTo, cfg, state) {
		var html = '<div class="mrt-journey-wizard__trip-list" data-wizard-conn-context="' + SU.escapeHtml(legCtx) + '">';
		list.forEach(function (conn, idx) {
			html += cardHtml(conn, idx, legCtx, legFrom, legTo, cfg, state);
		});
		html += '</div>';
		$target.empty().append(html);
	}

	JW.tripCard = {
		connectionLegs: connectionLegs,
		cardBadgesHtml: cardBadgesHtml,
		renderConnectionCards: renderConnectionCards,
		selectedTripHtml: selectedTripHtml,
	};
})(window, jQuery);
