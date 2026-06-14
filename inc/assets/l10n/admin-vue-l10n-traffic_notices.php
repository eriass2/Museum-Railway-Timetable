<?php
/**
 * Admin Vue l10n: public traffic notices messages.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_traffic_notices(): array {
	return array(
		'navTrafficNotices'           => __( 'Trafikmeddelanden', 'museum-railway-timetable' ),
		'trafficNoticesTitle'         => __( 'Trafikmeddelanden', 'museum-railway-timetable' ),
		'trafficNoticesIntro'         => __(
			'Generella meddelanden och tur-avvikelser slås ihop till samma publika feed som shortcoden [museum_traffic_notices] (90 dagar).',
			'museum-railway-timetable'
		),
		'trafficNoticesFeedTitle'     => __( 'Publik förhandsvisning', 'museum-railway-timetable' ),
		'trafficNoticesFeedIntro'     => __(
			'Samma lista som besökare ser på webben (Pågår nu / Kommande). Länkar går till redigering här eller under Tidtabeller → Avvikelser.',
			'museum-railway-timetable'
		),
		'trafficNoticesFeedEmpty'     => __( 'Inga störningar i feeden just nu.', 'museum-railway-timetable' ),
		'trafficNoticesFeedOngoing'   => __( 'Pågår nu', 'museum-railway-timetable' ),
		'trafficNoticesFeedUpcoming'  => __( 'Kommande', 'museum-railway-timetable' ),
		'trafficNoticesExpandMore'    => __( 'Mer information', 'museum-railway-timetable' ),
		'trafficNoticesExpandDetails' => __( 'Visa detaljer', 'museum-railway-timetable' ),
		'trafficNoticesRouteOther'    => __( 'Övrigt', 'museum-railway-timetable' ),
		'trafficNoticesVsDeviations'  => __(
			'Tur-avvikelser (inställd tur, ersättningsfordon) redigeras under Tidtabeller → välj tidtabell → Avvikelser.',
			'museum-railway-timetable'
		),
		'trafficNoticesNew'           => __( 'Nytt meddelande', 'museum-railway-timetable' ),
		'trafficNoticesColText'       => __( 'Text', 'museum-railway-timetable' ),
		'trafficNoticesColFrom'       => __( 'Gäller från', 'museum-railway-timetable' ),
		'trafficNoticesColTo'         => __( 'Gäller till', 'museum-railway-timetable' ),
		'trafficNoticesColActive'     => __( 'Aktiv', 'museum-railway-timetable' ),
		'trafficNoticesMoveUp'        => __( 'Upp', 'museum-railway-timetable' ),
		'trafficNoticesMoveDown'      => __( 'Ner', 'museum-railway-timetable' ),
		'trafficNoticesEdit'          => __( 'Redigera', 'museum-railway-timetable' ),
		'trafficNoticesDelete'        => __( 'Ta bort', 'museum-railway-timetable' ),
		'trafficNoticesEmpty'         => __( 'Inga meddelanden ännu.', 'museum-railway-timetable' ),
		'trafficNoticesTextLabel'     => __( 'Meddelande', 'museum-railway-timetable' ),
		'trafficNoticesEnabled'       => __( 'Aktiv', 'museum-railway-timetable' ),
		'trafficNoticesSave'          => __( 'Spara', 'museum-railway-timetable' ),
		'trafficNoticesSaved'         => __( 'Meddelanden sparade.', 'museum-railway-timetable' ),
		'trafficNoticesVisibleToday'  => __( 'Visas idag', 'museum-railway-timetable' ),
		'trafficNoticesHiddenToday'   => __( 'Visas inte idag', 'museum-railway-timetable' ),
		'trafficNoticesInactive'      => __( 'Inaktiv', 'museum-railway-timetable' ),
		'trafficNoticesCharCount'     => __( '%1$d / %2$d tecken', 'museum-railway-timetable' ),
		'trafficNoticesDeleteConfirm' => __( 'Ta bort meddelandet?', 'museum-railway-timetable' ),
		'trafficNoticesNoPermission'  => __(
			'Du har inte behörighet att redigera trafikmeddelanden.',
			'museum-railway-timetable'
		),
	);
}
