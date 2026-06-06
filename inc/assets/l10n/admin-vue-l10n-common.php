<?php
/**
 * Admin Vue l10n: common
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_common(): array {
	return array(
		'saved'      => __( 'Sparat.', 'museum-railway-timetable' ),
		'loading'    => __( 'Laddar…', 'museum-railway-timetable' ),
		'retry'      => __( 'Försök igen', 'museum-railway-timetable' ),
		'loadFailed' => __( 'Kunde inte ladda.', 'museum-railway-timetable' ),
		'saveFailed' => __( 'Kunde inte spara.', 'museum-railway-timetable' ),
		'genericError' => __( 'Fel', 'museum-railway-timetable' ),
		'trafficCancelledNotice' => __( 'Inställd', 'museum-railway-timetable' ),
		'confirm'    => __( 'Bekräfta', 'museum-railway-timetable' ),
		'cancel'     => __( 'Avbryt', 'museum-railway-timetable' ),
		'delete'     => __( 'Ta bort', 'museum-railway-timetable' ),
		'save'       => __( 'Spara', 'museum-railway-timetable' ),
		'edit'       => __( 'Redigera', 'museum-railway-timetable' ),
		'add'        => __( 'Lägg till', 'museum-railway-timetable' ),
		'backToList' => __( 'Tillbaka till listan', 'museum-railway-timetable' ),
		'discardChangesTitle'   => __( 'Osparade ändringar', 'museum-railway-timetable' ),
		'discardChangesMessage' => __(
			'Du har ändringar som inte är sparade. Vill du kasta dem?',
			'museum-railway-timetable'
		),
		'discardChangesConfirm' => __( 'Kasta ändringar', 'museum-railway-timetable' ),
		'yes'        => __( 'Ja', 'museum-railway-timetable' ),
	);
}
