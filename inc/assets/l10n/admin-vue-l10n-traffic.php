<?php
/**
 * Admin Vue l10n: traffic
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_traffic(): array {
	return array(
		'trafficTodayTitle'            => __( 'Trafik idag', 'museum-railway-timetable' ),
		'trafficTodayNoServices'       => __( 'Inga turer schemalagda.', 'museum-railway-timetable' ),
		'trafficTodayAllCancelled'     => __( 'All trafik (%1$s turer) är inställd.', 'museum-railway-timetable' ),
		'trafficTodaySummary'          => __( '%1$s turer · %2$s', 'museum-railway-timetable' ),
		'trafficTodayCancelTitle'      => __( 'Inställ trafik idag', 'museum-railway-timetable' ),
		'trafficTodayCancelMessage'    => __(
			'Alla %1$s turer den %2$s får meddelandet «Inställd». Detta påverkar visningen på webbplatsen.',
			'museum-railway-timetable'
		),
		'trafficTodayCancelButton'     => __( 'Inställ trafik', 'museum-railway-timetable' ),
		'trafficTodayCancelSuccess'    => __( '%s turer markerade som inställda.', 'museum-railway-timetable' ),
		'trafficTodayCancelNone'       => __( 'Ingen trafik att ställa in.', 'museum-railway-timetable' ),
		'trafficTodayCancelFailed'     => __( 'Kunde inte ställa in trafik', 'museum-railway-timetable' ),
		'trafficTodayOpenTimetable'    => __( 'Öppna tidtabell', 'museum-railway-timetable' ),
		'trafficTodayEditDeviations'   => __( 'Ändra avgångstid / avvikelser', 'museum-railway-timetable' ),
	);
}
