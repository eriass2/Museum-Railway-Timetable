/**
 * Expandable stop timeline on trip cards.
 *
 * @package Museum_Railway_Timetable
 */
(function (global, $) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;
	var tripCard = JW.tripCard;
	var vehicle = JW.vehicle;

	function formatTripClock(time) {
		if (!time) {
			return '';
		}
		return String(time).replace(':', '.');
	}

	function stationTime(s) {
		return formatTripClock(s.departure_time || s.arrival_time || '');
	}

	function timelineStopsHtml(stops, cfg, expanded) {
		var html = '';
		var visibleStops = expanded ? stops : stops.filter(function (_, i) {
			return i === 0 || i === stops.length - 1;
		});
		visibleStops.forEach(function (stop, i) {
			var isTerminal = i === 0 || i === visibleStops.length - 1;
			html += '<div class="mrt-journey-wizard__timeline-row' + (isTerminal ? ' is-terminal' : '') + '">';
			html += '<time class="mrt-journey-wizard__timeline-time">' + SU.escapeHtml(stationTime(stop)) + '</time>';
			html += '<span class="mrt-journey-wizard__timeline-node" aria-hidden="true"></span>';
			html += '<span class="mrt-journey-wizard__timeline-station">' + SU.escapeHtml(stop.station_title || '') + '</span>';
			html += '</div>';
		});
		if (!expanded && stops.length > 2) {
			html += '<button type="button" class="mrt-journey-wizard__passed-toggle" data-wizard-passed-toggle>' +
				'∨ ' + SU.escapeHtml(cfg.showStops || 'visa passerade stationer') +
				'</button>';
		} else if (expanded && stops.length > 2) {
			html += '<button type="button" class="mrt-journey-wizard__passed-toggle" data-wizard-passed-toggle>' +
				'∧ ' + SU.escapeHtml(cfg.hideStops || 'dölj passerade stationer') +
				'</button>';
		}
		return html;
	}

	function buildStopsDetailHtml(detail, notice, cfg, leg, expanded) {
		var html = '';
		if (notice) {
			html += '<p class="mrt-journey-wizard__notice"><strong>' + SU.escapeHtml(cfg.noticeLabel) + ':</strong> ' + SU.escapeHtml(notice) + '</p>';
		}
		if (leg) {
			html += '<div class="mrt-journey-wizard__timeline-leg">';
			if (leg.duration_minutes) {
				html += '<span class="mrt-journey-wizard__leg-duration">' +
					SU.escapeHtml((cfg.durationMinutes || '%d min').replace('%d', String(leg.duration_minutes))) +
					'</span>';
			}
			html += vehicle.legVehicleBadge(leg);
			if (leg.destination || leg.direction) {
				html += '<span class="mrt-journey-wizard__towards">' +
					SU.escapeHtml((cfg.towards || 'mot %s').replace('%s', leg.destination || leg.direction)) +
					'</span>';
			}
			html += '</div>';
		}
		html += '<div class="mrt-journey-wizard__timeline">';
		html += timelineStopsHtml(detail.stops || [], cfg, Boolean(expanded));
		html += '</div>';
		return html;
	}

	function showDetailError($cell, $btn, cfg) {
		$cell.html('<div class="mrt-alert mrt-alert-error"></div>');
		$cell.find('.mrt-alert').text(cfg.errorGeneric);
		$btn.attr('aria-expanded', 'true');
		$cell.removeAttr('hidden');
	}

	function multiLegSegmentHtml(res, leg, title, cfg, expanded) {
		var detail = res.data.detail || {};
		var notice = res.data.notice || '';
		var html = '<div class="mrt-journey-wizard__detail-segment mrt-mb-sm">';
		html += '<h4 class="mrt-journey-wizard__detail-title">' + SU.escapeHtml(title) + '</h4>';
		html += buildStopsDetailHtml(detail, notice, cfg, leg, expanded);
		return html + '</div>';
	}

	function multiLegTransferHtml(legIndex, conn, cfg) {
		if (!conn.legs || legIndex >= conn.legs.length - 1) {
			return '';
		}
		var wait = conn.transfer_wait_minutes;
		var label = cfg.transferTrip || 'Byte';
		if (wait !== null && wait !== undefined && wait !== '') {
			label = (cfg.transferWait || '%d min byte').replace('%d', String(wait));
		}
		return '<div class="mrt-journey-wizard__transfer-block">' + SU.escapeHtml(label) + '</div>';
	}

	function detailPricesHtml(wctx, legCtx) {
		var state = wctx.state;
		var cfg = wctx.cfg;
		var fromId = legCtx === 'return' ? state.to : state.from;
		var toId = legCtx === 'return' ? state.from : state.to;
		var zones = JW.prices.zonesForStationPair(fromId, toId, cfg);
		return JW.prices.buildPriceSection(state.tripType, cfg, zones, { compactTitle: true });
	}

	function finishMultiLegDetail($cell, $btn, html, wctx, legCtx) {
		$cell.html(html + detailPricesHtml(wctx, legCtx) + '</div>');
		$btn.attr('aria-expanded', 'true');
		$cell.removeAttr('hidden');
	}

	function loadMultiLegDetailRows(conn, $cell, $btn, cfg, ajaxPost, expanded, wctx, legCtx) {
		var legTpl = cfg.legSegmentLabel || 'Train %d';
		var multiHtml = '<div class="mrt-journey-wizard__detail mrt-journey-wizard__detail--multi">';
		var legIndex = 0;
		function loadNextLeg() {
			if (legIndex >= conn.legs.length) {
				finishMultiLegDetail($cell, $btn, multiHtml, wctx, legCtx);
				return;
			}
			var leg = conn.legs[legIndex];
			var title = legTpl.replace('%d', String(legIndex + 1));
			ajaxPost('mrt_journey_connection_detail', {
				from_station: leg.from_station_id,
				to_station: leg.to_station_id,
				service_id: leg.service_id,
			}).done(function (res) {
				if (!res || !res.success || !res.data) {
					showDetailError($cell, $btn, cfg);
					return;
				}
				multiHtml += multiLegSegmentHtml(res, leg, title, cfg, expanded);
				multiHtml += multiLegTransferHtml(legIndex, conn, cfg);
				legIndex += 1;
				loadNextLeg();
			}).fail(function () {
				showDetailError($cell, $btn, cfg);
			});
		}
		loadNextLeg();
	}

	function loadDetailIntoCard(wctx, $btn, expanded) {
		var cfg = wctx.cfg;
		var legCtx = $btn.attr('data-ctx');
		var idx = parseInt($btn.attr('data-idx'), 10);
		var legFrom = parseInt($btn.attr('data-leg-from'), 10);
		var legTo = parseInt($btn.attr('data-leg-to'), 10);
		var list = legCtx === 'return' ? wctx.lastReturnList : wctx.lastOutboundList;
		var conn = list[idx];
		if (!conn) {
			return;
		}
		var $card = $btn.closest('.mrt-journey-wizard__trip-card');
		var $detail = $card.find('.mrt-journey-wizard__detail').first();
		$detail.removeAttr('hidden');
		$detail.toggleClass('is-passed-expanded', Boolean(expanded));
		$detail.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
		$card.addClass('is-expanded');

		if (conn.legs && conn.legs.length > 1) {
			loadMultiLegDetailRows(conn, $detail, $btn, cfg, wctx.ajaxPost, expanded, wctx, legCtx);
			return;
		}

		wctx.ajaxPost('mrt_journey_connection_detail', {
			from_station: legFrom,
			to_station: legTo,
			service_id: conn.service_id,
		}).done(function (res) {
			if (!res || !res.success || !res.data) {
				$detail.html('<div class="mrt-alert mrt-alert-error"></div>');
				$detail.find('.mrt-alert').text(cfg.errorGeneric);
				return;
			}
			var detail = res.data.detail || {};
			var notice = res.data.notice || '';
			var html = buildStopsDetailHtml(detail, notice, cfg, tripCard.connectionLegs(conn)[0], expanded);
			$detail.html(html + detailPricesHtml(wctx, legCtx));
			$btn.attr('aria-expanded', 'true');
		}).fail(function () {
			$detail.html('<div class="mrt-alert mrt-alert-error"></div>');
			$detail.find('.mrt-alert').text(cfg.errorGeneric);
			$btn.attr('aria-expanded', 'true');
		});
	}

	function toggleDetailRow(wctx, $btn) {
		var $card = $btn.closest('.mrt-journey-wizard__trip-card');
		var $detail = $card.find('.mrt-journey-wizard__detail').first();
		if ($detail.html()) {
			var nextExpanded = $detail.is('[hidden]');
			$detail.prop('hidden', !nextExpanded);
			$btn.attr('aria-expanded', nextExpanded ? 'true' : 'false');
			$card.toggleClass('is-expanded', nextExpanded);
			return;
		}
		loadDetailIntoCard(wctx, $btn, false);
	}

	JW.connectionDetail = {
		loadDetailIntoCard: loadDetailIntoCard,
		toggleDetailRow: toggleDetailRow,
	};
})(window, jQuery);
