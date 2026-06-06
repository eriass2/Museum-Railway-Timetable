<?php
/**
 * Admin Vue l10n: stop times
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_stop_times(): array {
	return array(
		'stopTimesLoading'     => __( 'Laddar stopptider…', 'museum-railway-timetable' ),
		'stopTimesSaved'       => __( 'Stopptider sparade', 'museum-railway-timetable' ),
		'stopTimesSaveButton'  => __( 'Spara stopptider', 'museum-railway-timetable' ),
		'stopTimesOperatorHint' => __(
			'Som operatör kan du ändra tider och om tåget stannar. På/Av (påstigning/avstigning) kräver administratörsbehörighet.',
			'museum-railway-timetable'
		),
		'stopTimesColStops'    => __( 'Stannar', 'museum-railway-timetable' ),
		'stopTimesColStation'  => __( 'Station', 'museum-railway-timetable' ),
		'stopTimesColArrival'  => __( 'Ankomst', 'museum-railway-timetable' ),
		'stopTimesColDeparture' => __( 'Avgång', 'museum-railway-timetable' ),
		'stopTimesColPickup'   => __( 'På', 'museum-railway-timetable' ),
		'stopTimesColDropoff'  => __( 'Av', 'museum-railway-timetable' ),
		'stopTimesPickupLabel' => __( 'Påstigning', 'museum-railway-timetable' ),
		'stopTimesDropoffLabel' => __( 'Avstigning', 'museum-railway-timetable' ),
		'stopTimesGridEditTitle' => __( '%1$s · tur %2$s', 'museum-railway-timetable' ),
	);
}
