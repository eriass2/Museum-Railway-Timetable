<?php
/**
 * Admin Vue l10n: feedback
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_feedback(): array {
	return array(
		'feedbackTitle'           => __( 'Feedback', 'museum-railway-timetable' ),
		'feedbackIntro'           => __( 'Rapporter från reseplanerarens feedbackknapp.', 'museum-railway-timetable' ),
		'feedbackEmpty'           => __( 'Inga rapporter ännu.', 'museum-railway-timetable' ),
		'feedbackColType'         => __( 'Typ', 'museum-railway-timetable' ),
		'feedbackColMessage'      => __( 'Beskrivning', 'museum-railway-timetable' ),
		'feedbackColStep'         => __( 'Steg', 'museum-railway-timetable' ),
		'feedbackColStatus'       => __( 'Status', 'museum-railway-timetable' ),
		'feedbackLoadFailed'      => __( 'Kunde inte ladda feedback', 'museum-railway-timetable' ),
		'feedbackExportButton'    => __( 'Exportera CSV', 'museum-railway-timetable' ),
		'feedbackExportSuccess'   => __( 'CSV exporterad.', 'museum-railway-timetable' ),
		'feedbackExportFailed'    => __( 'Kunde inte exportera CSV', 'museum-railway-timetable' ),
		'feedbackTypeBug'         => __( 'Fel', 'museum-railway-timetable' ),
		'feedbackTypeSuggestion'  => __( 'Förslag', 'museum-railway-timetable' ),
		'feedbackEmailPrefix'     => __( 'E-post:', 'museum-railway-timetable' ),
		'feedbackOpenPage'        => __( 'Öppna sida', 'museum-railway-timetable' ),
		'feedbackStatusNew'       => __( 'Ny', 'museum-railway-timetable' ),
		'feedbackStatusRead'      => __( 'Läst', 'museum-railway-timetable' ),
		'feedbackStatusResolved'  => __( 'Åtgärdad', 'museum-railway-timetable' ),
		'feedbackStatusDismissed' => __( 'Avvisad', 'museum-railway-timetable' ),
	);
}
