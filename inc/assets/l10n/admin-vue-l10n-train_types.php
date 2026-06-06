<?php
/**
 * Admin Vue l10n: train_types
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_train_types(): array {
	return array(
		'trainTypesTitle'           => __( 'Tågtyper', 'museum-railway-timetable' ),
		'trainTypesLoading'         => __( 'Laddar tågtyper…', 'museum-railway-timetable' ),
		'trainTypesEmptyTitle'      => __( 'Inga tågtyper ännu', 'museum-railway-timetable' ),
		'trainTypesEmptyMessage'    => __(
			'Skapa den första tågtypen nedan. Ikonen visas i tidtabeller och bokningsflödet.',
			'museum-railway-timetable'
		),
		'trainTypesColName'         => __( 'Namn', 'museum-railway-timetable' ),
		'trainTypesColIcon'         => __( 'Ikon', 'museum-railway-timetable' ),
		'trainTypesSlugLabel'       => __( 'Slug', 'museum-railway-timetable' ),
		'trainTypesNewTitle'        => __( 'Ny tågtyp', 'museum-railway-timetable' ),
		'trainTypesNameLabel'       => __( 'Namn', 'museum-railway-timetable' ),
		'trainTypesIconLabel'       => __( 'Ikon', 'museum-railway-timetable' ),
		'trainTypesIconPickerAria'  => __( 'Välj ikon för tågtyp', 'museum-railway-timetable' ),
		'trainTypesIconSteam'       => __( '�
ngtåg', 'museum-railway-timetable' ),
		'trainTypesIconDiesel'      => __( 'Diesel', 'museum-railway-timetable' ),
		'trainTypesIconRailbus'     => __( 'Rälsbuss', 'museum-railway-timetable' ),
		'trainTypesIconBus'         => __( 'Vägbuss', 'museum-railway-timetable' ),
		'trainTypesSlugOptional'    => __( 'Slug (valfritt)', 'museum-railway-timetable' ),
		'trainTypesSlugPlaceholder' => __( 't.ex. ralsbuss', 'museum-railway-timetable' ),
		'trainTypesCreateButton'    => __( 'Skapa', 'museum-railway-timetable' ),
		'trainTypesCreated'         => __( 'Tågtypen «%s» skapades.', 'museum-railway-timetable' ),
		'trainTypesSaved'           => __( '«%s» sparades.', 'museum-railway-timetable' ),
		'trainTypesDeleteTitle'     => __( 'Ta bort tågtyp', 'museum-railway-timetable' ),
		'trainTypesDeleteMessage'   => __( '«%s» tas bort från listan.', 'museum-railway-timetable' ),
		'trainTypesDeleteFallback'  => __( 'Tågtypen tas bort från listan.', 'museum-railway-timetable' ),
		'trainTypesRemoved'         => __( '«%s» borttagen.', 'museum-railway-timetable' ),
		'trainTypesRemovedFallback' => __( 'Borttagen.', 'museum-railway-timetable' ),
	);
}
