/**
 * Journey wizard shared namespace (no jQuery).
 *
 * @package Museum_Railway_Timetable
 */
(function (global) {
	'use strict';

	global.MRTJourneyWizard = global.MRTJourneyWizard || {};

	/**
	 * @return {{ SU: object, FA: object, DU: object }}
	 */
	global.MRTJourneyWizard.deps = function () {
		return {
			SU: global.MRTStringUtils,
			FA: global.MRTFrontendApi,
			DU: global.MRTDateUtils,
		};
	};
})(window);
