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
			'Generella meddelanden visas på startsidan via shortcoden [museum_traffic_notices]. Tur-avvikelser redigeras under Tidtabeller → Avvikelser.',
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
