<?php
/**
 * Admin Vue l10n: settings
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_settings(): array {
	return array(
		'settingsTitle'           => __( 'Inställningar', 'museum-railway-timetable' ),
		'settingsLoading'         => __( 'Laddar inställningar…', 'museum-railway-timetable' ),
		'settingsNoPermission'    => __( 'Du har inte behörighet att ändra inställningar.', 'museum-railway-timetable' ),
		'settingsLoadFailed'      => __( 'Kunde inte ladda inställningar.', 'museum-railway-timetable' ),
		'settingsSaveButton'      => __( 'Spara inställningar', 'museum-railway-timetable' ),
		'settingsEnabledLabel'    => __( 'Aktivera plugin', 'museum-railway-timetable' ),
		'settingsEnabledCheckbox' => __( 'Pluginet är aktivt', 'museum-railway-timetable' ),
		'settingsNote'            => __( 'Anteckning', 'museum-railway-timetable' ),
		'settingsMinTransfer'     => __( 'Min väntetid vid byte (min)', 'museum-railway-timetable' ),
		'settingsMaxTransfer'     => __( 'Max väntetid vid byte (min)', 'museum-railway-timetable' ),
		'settingsImportHint'      => __(
			'CSV-import/export finns under fliken Import/export i menyn.',
			'museum-railway-timetable'
		),
	);
}
