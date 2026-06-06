<?php
/**
 * Admin Vue l10n: timetables
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_timetables(): array {
	return array(
		'timetablesTitle'              => __( 'Tidtabeller', 'museum-railway-timetable' ),
		'timetablesLoading'            => __( 'Laddar tidtabeller…', 'museum-railway-timetable' ),
		'timetablesLoadFailed'         => __( 'Fel vid laddning', 'museum-railway-timetable' ),
		'timetablesCreateFailed'       => __( 'Kunde inte skapa', 'museum-railway-timetable' ),
		'timetablesDeleteFailed'       => __( 'Kunde inte ta bort', 'museum-railway-timetable' ),
		'timetablesLimitedRole'        => __(
			'Du kan öppna tidtabeller och ändra avvikelser eller avgångstider, men inte skapa nya tidtabeller eller grunddata. Kontakta en administratör om du behöver fler rättigheter.',
			'museum-railway-timetable'
		),
		'timetablesNewTitle'           => __( 'Ny tidtabell', 'museum-railway-timetable' ),
		'timetablesNamePlaceholder'    => __( 'Namn', 'museum-railway-timetable' ),
		'timetablesCreateButton'       => __( 'Skapa', 'museum-railway-timetable' ),
		'timetablesEmptyTitle'         => __( 'Inga tidtabeller', 'museum-railway-timetable' ),
		'timetablesEmptyMessage'       => __( 'Skapa en tidtabell ovan för att komma igång.', 'museum-railway-timetable' ),
		'timetablesColName'            => __( 'Namn', 'museum-railway-timetable' ),
		'timetablesColDates'           => __( 'Trafikdagar', 'museum-railway-timetable' ),
		'timetablesColTrips'           => __( 'Turer', 'museum-railway-timetable' ),
		'timetablesCardSummary'        => __( '%1$s trafikdagar · %2$s turer', 'museum-railway-timetable' ),
		'timetablesDeleteTitle'        => __( 'Ta bort tidtabell', 'museum-railway-timetable' ),
		'timetablesDeleteMessage'      => __(
			'«%s» och alla dess turer raderas permanent.',
			'museum-railway-timetable'
		),
	);
}
