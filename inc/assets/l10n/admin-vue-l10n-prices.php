<?php
/**
 * Admin Vue l10n: prices
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_prices(): array {
	return array(
		'pricesTitle'        => __( 'Priser', 'museum-railway-timetable' ),
		'pricesLoading'      => __( 'Laddar priser…', 'museum-railway-timetable' ),
		'pricesNoPermission' => __( 'Du har inte behörighet att ändra priser.', 'museum-railway-timetable' ),
		'pricesLoadFailed'   => __( 'Kunde inte ladda priser.', 'museum-railway-timetable' ),
		'pricesSaveButton'   => __( 'Spara priser', 'museum-railway-timetable' ),
		'pricesDescription'  => __(
			'Priser i SEK per biljettyp, passagerarkategori och antal zoner.',
			'museum-railway-timetable'
		),
		'pricesTicketTypeCol' => __( 'Biljettyp', 'museum-railway-timetable' ),
		'pricesZonesCol'      => __( 'Zoner', 'museum-railway-timetable' ),
		'pricesSchemaSummary' => __( 'Prisstruktur (biljettyper, kategorier, zoner)', 'museum-railway-timetable' ),
		'pricesSchemaHint'    => __(
			'Nyckeln (kod) är låst efter skapande och används i CSV och beräkningar. Etiketten visas i admin och för besökare.',
			'museum-railway-timetable'
		),
		'pricesTicketTypesHeading' => __( 'Biljettyper', 'museum-railway-timetable' ),
		'pricesCategoriesHeading'  => __( 'Passagerarkategorier', 'museum-railway-timetable' ),
		'pricesZonesHeading'       => __( 'Priszoner', 'museum-railway-timetable' ),
		'pricesSchemaKeyCol'       => __( 'Nyckel', 'museum-railway-timetable' ),
		'pricesSchemaLabelCol'     => __( 'Etikett', 'museum-railway-timetable' ),
		'pricesNewTicketPlaceholder' => __( 'Ny biljettyp', 'museum-railway-timetable' ),
		'pricesNewCategoryPlaceholder' => __( 'Ny kategori', 'museum-railway-timetable' ),
		'pricesNewZonePlaceholder' => __( 'Zonnummer', 'museum-railway-timetable' ),
		'pricesZoneLabel'          => __( 'Zon', 'museum-railway-timetable' ),
		'pricesZoneCapHeading'     => __( 'Max zoner vid prislookup', 'museum-railway-timetable' ),
		'pricesZoneCapHint'        => __(
			'Antal zoner som används vid prisberäkning (t.ex. 3 enligt taxa 2026). Högre zonnummer i matrisen kan ha samma belopp.',
			'museum-railway-timetable'
		),
		'pricesAfternoonHeading'   => __( 'Eftermiddags-retur', 'museum-railway-timetable' ),
		'pricesAfternoonHint'      => __(
			'Fast pris per kategori när tur och retur avgår vid eller efter eftermiddagsgränsen (Inställningar). Ersätter zonmatrisen för returbiljett.',
			'museum-railway-timetable'
		),
		'pricesAfternoonAmountCol' => __( 'Pris (SEK)', 'museum-railway-timetable' ),
	);
}
