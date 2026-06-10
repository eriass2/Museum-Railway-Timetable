<?php
/**
 * Admin Vue l10n: dev
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_dev(): array {
	return array(
		'devTitle'              => __( 'Utvecklingsverktyg', 'museum-railway-timetable' ),
		'devNotAvailable'       => __(
			'Endast tillgängligt när WP_DEBUG eller MRT_DEVELOPMENT är aktivt.',
			'museum-railway-timetable'
		),
		'devDescription'        => __(
			'Reset, import och demosidor för lokal QA. Visas inte på produktion.',
			'museum-railway-timetable'
		),
		'devClearTitle'         => __( 'Radera all plugin-data', 'museum-railway-timetable' ),
		'devClearMessage'       => __(
			'Alla stationer, rutter, tidtabeller, turer och inställningar tas bort. Detta går inte att ångra.',
			'museum-railway-timetable'
		),
		'devClearConfirm'       => __( 'Radera allt', 'museum-railway-timetable' ),
		'devClearSuccess'       => __( 'All plugin-data har raderats.', 'museum-railway-timetable' ),
		'devImportSuccess'      => __( 'Lennakatten-demo har importerats.', 'museum-railway-timetable' ),
		'devDemoSuccess'        => __( 'Demosida skapad eller uppdaterad.', 'museum-railway-timetable' ),
		'devNavSuccess'         => __( 'Utvecklingsmeny uppdaterad.', 'museum-railway-timetable' ),
		'devPagesSuccess'       => __( 'Tidtabellssidor skapade eller uppdaterade.', 'museum-railway-timetable' ),
		'devDone'               => __( 'Klart.', 'museum-railway-timetable' ),
		'devClearButton'        => __( 'Rensa plugin-databas', 'museum-railway-timetable' ),
		'devImportButton'       => __( 'Importera Lennakatten-demo', 'museum-railway-timetable' ),
		'devDemoButton'         => __( 'Skapa demosida', 'museum-railway-timetable' ),
		'devNavButton'          => __( 'Sätt upp utvecklingsmeny', 'museum-railway-timetable' ),
		'devPagesButton'        => __( 'Skapa/uppdatera tidtabellssidor', 'museum-railway-timetable' ),
		'devComponentDemoLink'  => __( 'Komponentdemosida (PHP-admin)', 'museum-railway-timetable' ),
	);
}
