/**
 * Trip cards for outbound / return / summary (uses JW.render components).
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;
	var R = JW.render;
	var C = R.C;
	var connApi = JW.connection;
	var vehicle = JW.vehicle;
	var ctxApi = JW.context;

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
		return parts.join(R.vehicleSepHtml());
	}

	function ariaChooseTrip(conn, dep, arr, cfg) {
		var s = cfg.btnChooseTripAria || '';
		return s
			.replace('%1$s', conn.service_name || '')
			.replace('%2$s', dep)
			.replace('%3$s', arr);
	}

	function selectButtonHtml(conn, idx, legCtx, dep, arr, cfg) {
		return '<button type="button" class="' + C.btnSelect + ' mrt-journey-wizard__btn-select" aria-label="' +
			SU.escapeHtml(ariaChooseTrip(conn, dep, arr, cfg)) + '" data-ctx="' + SU.escapeHtml(legCtx) +
			'" data-idx="' + String(idx) + '">' + SU.escapeHtml(cfg.selectTrip || 'Välj →') + '</button>';
	}

	function legTripHead(conn, dep, arr, routeText, dateText, cfg, sideExtra) {
		var notice = conn.notice || '';
		return R.tripHeadHtml({
			dep: dep,
			arr: arr,
			route: routeText,
			date: dateText || '',
			noticeMarker: R.noticeMarkerHtml(notice, cfg),
			noticeBlock: R.noticeBlockHtml(notice),
			vehiclesHtml: cardBadgesHtml(conn),
			duration: R.formatDuration(conn.duration_minutes, cfg),
			sideExtra: sideExtra || '',
		});
	}

	function cardHtml(conn, idx, legCtx, legFrom, legTo, cfg, state) {
		var dep = R.formatTripClock(connApi.departureFromOrigin(conn));
		var arr = R.formatTripClock(connApi.arrivalAtDestination(conn));
		var detailId = 'mrt-jw-detail-' + legCtx + '-' + idx;
		var attrs = 'aria-expanded="false" aria-controls="' + detailId + '" data-ctx="' + SU.escapeHtml(legCtx) +
			'" data-idx="' + String(idx) + '" data-leg-from="' + String(legFrom) + '" data-leg-to="' + String(legTo) + '"';
		var html = '<article class="' + C.cardTrip + ' mrt-journey-wizard__trip-card" data-wizard-card="' +
			SU.escapeHtml(legCtx) + '-' + idx + '">';
		html += legTripHead(conn, dep, arr, ctxApi.cardRouteText(state, legCtx), '', cfg, selectButtonHtml(conn, idx, legCtx, dep, arr, cfg));
		html += R.expandButtonHtml(connectionMeta(conn, cfg), attrs);
		html += '<div class="' + C.detail + ' mrt-journey-wizard__detail" id="' + detailId + '" hidden></div>';
		return html + '</article>';
	}

	function summaryLegCardHtml(heading, conn, routeText, dateText, cfg) {
		var dep = R.formatTripClock(connApi.departureFromOrigin(conn));
		var arr = R.formatTripClock(connApi.arrivalAtDestination(conn));
		var html = '<article class="' + C.cardSummary + ' mrt-journey-wizard__summary-card">';
		html += '<h4 class="' + C.summaryHeading + ' mrt-journey-wizard__summary-heading">' + SU.escapeHtml(heading) + '</h4>';
		html += legTripHead(conn, dep, arr, routeText, dateText, cfg, '');
		return html + '</article>';
	}

	function selectedTripHtml(conn, cfg, state) {
		var dep = R.formatTripClock(connApi.departureFromOrigin(conn));
		var arr = R.formatTripClock(connApi.arrivalAtDestination(conn));
		var html = '<div class="' + C.selectedLabel + ' mrt-journey-wizard__selected-label">' +
			SU.escapeHtml(cfg.selectedOutbound || 'Vald utresa') + '</div>';
		html += '<div class="' + C.cardSelected + ' mrt-journey-wizard__selected-card">';
		html += legTripHead(conn, dep, arr, ctxApi.cardRouteText(state, 'outbound'), '', cfg, '');
		return html + '</div>';
	}

	function renderConnectionCards($target, list, legCtx, legFrom, legTo, cfg, state) {
		var html = '<div class="' + C.tripList + ' mrt-journey-wizard__trip-list" data-wizard-conn-context="' +
			SU.escapeHtml(legCtx) + '">';
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
		summaryLegCardHtml: summaryLegCardHtml,
	};
})(window, jQuery);
