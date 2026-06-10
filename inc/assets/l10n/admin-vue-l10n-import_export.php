<?php
/**
 * Admin Vue l10n: import_export
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_import_export(): array {
	return array(
		'importExportTitle'            => __( 'Import / export', 'museum-railway-timetable' ),
		'importExportNoPermission'     => __( 'Du har inte behörighet.', 'museum-railway-timetable' ),
		'importExportExportTitle'      => __( 'Exportera CSV (zip)', 'museum-railway-timetable' ),
		'importExportIncludeSettings'  => __( 'Inkludera inställningar', 'museum-railway-timetable' ),
		'importExportIncludePrices'    => __( 'Inkludera priser', 'museum-railway-timetable' ),
		'importExportDownloadButton'   => __( 'Ladda ner export', 'museum-railway-timetable' ),
		'importExportTemplateButton'   => __( 'Ladda ner tom mall', 'museum-railway-timetable' ),
		'importExportTemplateHint'     => __(
			'Zip med alla CSV-filer och kolumnrubriker — fyll i raderna och ladda upp igen.',
			'museum-railway-timetable'
		),
		'importExportTemplateSuccess'  => __( 'Mall nedladdad.', 'museum-railway-timetable' ),
		'importExportTemplateFailed'   => __( 'Kunde inte ladda ner mall', 'museum-railway-timetable' ),
		'importExportImportTitle'      => __( 'Importera zip eller CSV', 'museum-railway-timetable' ),
		'importExportImportHint'       => __(
			'Ladda upp zip med alla filer, eller en enskild CSV (t.ex. stoptimes.csv) för små ändringar. manifest.json är valfri.',
			'museum-railway-timetable'
		),
		'importExportSingleCsvHint'    => __(
			'Filen måste heta som i exportpaketet: stations.csv, stoptimes.csv, timetable_dates.csv m.fl. Slå ihop rekommenderas för enstaka filer.',
			'museum-railway-timetable'
		),
		'importExportModeMergeShort'   => __( 'Rekommenderas — uppdaterar det som finns i filen och behåller övrig data.', 'museum-railway-timetable' ),
		'importExportAdvancedMode'     => __( 'Avancerat importläge', 'museum-railway-timetable' ),
		'importExportModeMerge'        => __( 'Slå ihop (uppdatera befintlig data)', 'museum-railway-timetable' ),
		'importExportModeOverride'     => __( 'Ersätt (ta bort poster som saknas i filen)', 'museum-railway-timetable' ),
		'importExportOverrideConfirmTitle' => __( 'Bekräfta ersättningsimport', 'museum-railway-timetable' ),
		'importExportOverrideConfirmMessage' => __(
			'Ersättningsläge tar bort data som inte finns i importfilen. Vill du fortsätta?',
			'museum-railway-timetable'
		),
		'importExportExportSuccess'    => __( 'Export klar.', 'museum-railway-timetable' ),
		'importExportExportFailed'     => __( 'Export misslyckades', 'museum-railway-timetable' ),
		'importExportImportFailed'     => __( 'Import misslyckades', 'museum-railway-timetable' ),
		'importExportImportSuccess'    => __( 'Import klar (%1$s). %2$s', 'museum-railway-timetable' ),
		'importExportClearTitle'       => __( 'Radera all data', 'museum-railway-timetable' ),
		'importExportClearHint'        => __(
			'Tar bort alla stationer, rutter, tidtabeller, turer, stopptider, tågtyper, inställningar och priser. Exportera först om du vill behålla en kopia. Detta går inte att ångra.',
			'museum-railway-timetable'
		),
		'importExportClearMessage'     => __(
			'All plugin-data raderas permanent. Fortsätta?',
			'museum-railway-timetable'
		),
		'importExportClearConfirm'     => __( 'Radera allt', 'museum-railway-timetable' ),
		'importExportClearButton'      => __( 'Radera all data', 'museum-railway-timetable' ),
		'importExportClearSuccess'     => __( 'All data har raderats.', 'museum-railway-timetable' ),
	);
}
