/**
 * Connection time helpers.
 *
 * @package Museum_Railway_Timetable
 */
(function (global) {
	'use strict';

	var JW = global.MRTJourneyWizard;

	/**
	 * @param {object} conn
	 * @return {string}
	 */
	function arrivalAtDestination(conn) {
		return conn.to_arrival || conn.to_departure || conn.arrival || '';
	}

	/**
	 * @param {object} conn
	 * @return {string}
	 */
	function departureFromOrigin(conn) {
		return conn.from_departure || conn.from_arrival || conn.departure || '';
	}

	JW.connection = {
		arrivalAtDestination: arrivalAtDestination,
		departureFromOrigin: departureFromOrigin,
	};
})(window);
