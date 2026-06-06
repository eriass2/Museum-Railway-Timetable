<?php
/**
 * Admin Vue l10n: mobile
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_mobile(): array {
	return array(
		'mobileLoading'              => __( 'Laddar…', 'museum-railway-timetable' ),
		'mobileDeviationsTitle'      => __( 'Avvikelser', 'museum-railway-timetable' ),
		'mobileNoDeviations'         => __( 'Inga avvikelser för denna tidtabell.', 'museum-railway-timetable' ),
		'mobileQuickDepartureTitle'  => __( 'Snabb avgångstid', 'museum-railway-timetable' ),
		'mobileQuickDepartureHint'   => __( 'Ändra avgångstid vid första hållplatsen (mobil).', 'museum-railway-timetable' ),
		'mobileTripLabel'            => __( 'Tur', 'museum-railway-timetable' ),
		'mobileDepartureSuffix'      => __( 'avgång', 'museum-railway-timetable' ),
		'mobileSaveDeparture'        => __( 'Spara avgångstid', 'museum-railway-timetable' ),
		'mobileDepartureSaved'       => __( 'Avgångstid sparad', 'museum-railway-timetable' ),
		'mobileStopTimesLoadFailed'  => __( 'Kunde inte ladda stopptider', 'museum-railway-timetable' ),
		'mobileSaveFailed'           => __( 'Kunde inte spara', 'museum-railway-timetable' ),
		'mobileCancelTitle'          => __( 'Inställ trafik', 'museum-railway-timetable' ),
		'mobileCancelHint'           => __(
			'Sätter meddelandet «Inställd» på alla turer som gäller %s.',
			'museum-railway-timetable'
		),
		'mobileCancelAllCancelled'   => __( 'All trafik är redan markerad som inställd.', 'museum-railway-timetable' ),
		'mobileCancelButton'         => __( 'Inställ trafik idag', 'museum-railway-timetable' ),
		'mobileCancelNoPermission'   => __( 'Du har inte behörighet att ställa in trafik.', 'museum-railway-timetable' ),
		'mobileCancelConfirmTitle'   => __( 'Inställ trafik', 'museum-railway-timetable' ),
		'mobileCancelConfirmMessage' => __(
			'Alla %1$s turer den %2$s får meddelandet «Inställd».',
			'museum-railway-timetable'
		),
		'mobileCancelSuccess'        => __( '%s turer markerade som inställda.', 'museum-railway-timetable' ),
		'mobileCancelNone'           => __( 'Ingen trafik att ställa in för detta datum.', 'museum-railway-timetable' ),
	);
}
