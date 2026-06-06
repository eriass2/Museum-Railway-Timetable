<?php
/**
 * Admin Vue l10n: route preview
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_route_preview(): array {
	return array(
		'routePreviewLabel' => __( 'Ruttens stationer', 'museum-railway-timetable' ),
		'routePreviewEmpty' => __( 'Inga stationer på rutten.', 'museum-railway-timetable' ),
		'routePreviewStart' => __( 'Start', 'museum-railway-timetable' ),
		'routePreviewEnd'   => __( 'Slut', 'museum-railway-timetable' ),
		'routePreviewBoth'  => __( 'Start/slut', 'museum-railway-timetable' ),
	);
}
