/**
 * Train type icons and vehicle badges on trip cards.
 *
 * @package Museum_Railway_Timetable
 */
(function (global) {
	'use strict';

	var JW = global.MRTJourneyWizard;
	var SU = global.MRTStringUtils;

	function iconKey(label, slug, iconKey) {
		if (iconKey) {
			return iconKey;
		}
		var cfg = typeof mrtJourneyWizard !== 'undefined' ? mrtJourneyWizard : {};
		var slugMap = cfg.trainTypeSlugIcons || {};
		var slugLower = String(slug || '').toLowerCase();
		if (slugLower && slugMap[slugLower]) {
			return slugMap[slugLower];
		}
		var s = String(label || '').toLowerCase();
		if (s.indexOf('rälsbuss') !== -1 || s.indexOf('ralsbuss') !== -1 || s.indexOf('railbus') !== -1) {
			return 'railbus';
		}
		if (s === 'buss' || slugLower === 'buss') {
			return 'bus';
		}
		if (slugLower === 'ang-diesel' || (s.indexOf('ång') !== -1 && s.indexOf('diesel') !== -1)) {
			return 'diesel';
		}
		if (s.indexOf('ång') !== -1 || s.indexOf('steam') !== -1 || slugLower === 'angtag') {
			return 'steam';
		}
		if (s.indexOf('diesel') !== -1 || s.indexOf('elektrisk') !== -1 || s.indexOf('electric') !== -1 || slugLower === 'dieseltag') {
			return 'diesel';
		}
		return 'diesel';
	}

	function iconHtml(kind) {
		var icons = (typeof mrtJourneyWizard !== 'undefined' && mrtJourneyWizard.trainTypeIcons) || {};
		var url = icons[kind] || icons.diesel || '';
		if (!url) {
			return '<span class="mrt-journey-wizard__vehicle-mark" aria-hidden="true"></span>';
		}
		return '<img src="' + SU.escapeHtml(url) + '" class="mrt-journey-wizard__vehicle-icon mrt-train-type-icon-img mrt-train-type-icon-img--' + SU.escapeHtml(kind) + '" width="48" height="24" decoding="async" alt="" />';
	}

	function vehicleBadge(label, serviceName, slug, trainIconKey) {
		var text = label || serviceName || '';
		var kind = iconKey(text, slug, trainIconKey);
		return '<span class="mrt-journey-wizard__vehicle mrt-journey-wizard__vehicle--' + kind + '">' +
			iconHtml(kind) +
			'<span>' + SU.escapeHtml(text || 'Tåg') + '</span>' +
			'</span>';
	}

	function legVehicleBadge(leg) {
		var service = leg.service_name || leg.service_number || (leg.service_id ? String(leg.service_id) : '');
		var train = leg.train_type || '';
		var label = train && service ? (train + ' ' + service) : (train || service);
		return vehicleBadge(label, service, leg.train_type_slug, leg.train_type_icon);
	}

	JW.vehicle = {
		vehicleBadge: vehicleBadge,
		legVehicleBadge: legVehicleBadge,
	};
})(window);
