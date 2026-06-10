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
		'pricesHelpIntro'    => __(
			'Tilldela priszoner på stationer först, fyll sedan i matrisen nedan.',
			'museum-railway-timetable'
		),
		'pricesHelpLink'     => __( 'Läs om priszoner och biljetter', 'museum-railway-timetable' ),
		'pricesOnboardingTitle' => __( 'Kom igång med priser', 'museum-railway-timetable' ),
		'pricesOnboardingStep1' => __( 'Tilldela priszoner på varje station (Stationer & rutter).', 'museum-railway-timetable' ),
		'pricesOnboardingStep2' => __( 'Fyll i prismatrisen nedan — rader är biljettyper × kategorier, kolumner är antal zoner.', 'museum-railway-timetable' ),
		'pricesOnboardingStep3' => __( 'Kontrollera förhandsvisningen längre ner innan du sparar.', 'museum-railway-timetable' ),
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
			'Antal zoner som används vid prisberäkning (t.ex. 3 enligt Lennakatten taxa 2026). Matriskolumner utöver cap kan finnas för andra operatörer.',
			'museum-railway-timetable'
		),
		'pricesZoneCapStatus'      => __(
			'Reseplaneraren använder max %1$d zoner vid prislookup (inställt under Prisstruktur).',
			'museum-railway-timetable'
		),
		'pricesMatrixZoneCapNotice' => __(
			'Matriskolumner över zon %1$d används inte vid prisberäkning. De kan spegla samma belopp som zon %1$d (t.ex. vid import).',
			'museum-railway-timetable'
		),
		'pricesZoneBeyondCapTitle' => __(
			'Används inte vid prislookup (max %1$d zoner).',
			'museum-railway-timetable'
		),
		'pricesAfternoonHeading'   => __( 'Eftermiddags-retur', 'museum-railway-timetable' ),
		'pricesAfternoonRule'      => __(
			'Returbiljett där tur och retur avgår vid eller efter gränsen får fast pris per kategori (ersätter zonmatrisen).',
			'museum-railway-timetable'
		),
		'pricesAfternoonThreshold' => __( 'Avgångsgräns (tur och retur)', 'museum-railway-timetable' ),
		'pricesAfternoonThresholdActive' => __(
			'Aktiv när båda benen avgår vid eller efter kl %1$s.',
			'museum-railway-timetable'
		),
		'pricesAfternoonDisabledHint' => __(
			'Gräns 00:00 inaktiverar eftermiddags-retur (ingen särskild taxa).',
			'museum-railway-timetable'
		),
		'pricesAfternoonPublicNote' => __(
			'Eftermiddagsbiljett gäller tur och retur med avgång vid eller efter kl %1$s.',
			'museum-railway-timetable'
		),
		'pricesAfternoonCompareTitle' => __( 'Jämförelse returpris', 'museum-railway-timetable' ),
		'pricesAfternoonCompareHint' => __(
			'Visar normal retur enligt zonmatris jämfört med eftermiddags-retur.',
			'museum-railway-timetable'
		),
		'pricesAfternoonCompareNormal' => __( 'Normal retur (zonmatris)', 'museum-railway-timetable' ),
		'pricesAfternoonCompareAfternoon' => __( 'Eftermiddags-retur', 'museum-railway-timetable' ),
		'pricesAfternoonCompareCol' => __( 'Retur', 'museum-railway-timetable' ),
		'pricesAfternoonAmountCol' => __( 'Pris (SEK)', 'museum-railway-timetable' ),
		'pricesEmptyMatrix'      => __(
			'Prismatrisen är tom. Importera taxa via Import/export eller fyll i beloppen nedan.',
			'museum-railway-timetable'
		),
		'pricesUnsaved'          => __( 'Du har osparade prisändringar.', 'museum-railway-timetable' ),
		'pricesPreviewTitle'     => __( 'Förhandsvisning (besökare)', 'museum-railway-timetable' ),
		'pricesPreviewHint'      => __(
			'Visar hur priserna presenteras i reseplaneraren för vald zon och biljettyp.',
			'museum-railway-timetable'
		),
		'pricesPreviewZone'      => __( 'Förhandsvisningszon', 'museum-railway-timetable' ),
		'pricesPreviewType'      => __( 'Biljettyp', 'museum-railway-timetable' ),
		'pricesCopyZoneHeading'  => __( 'Kopiera zonpriser', 'museum-railway-timetable' ),
		'pricesCopyZoneHint'     => __(
			'Kopiera alla belopp från en zonkolumn till en annan (t.ex. zon 2 → zon 3).',
			'museum-railway-timetable'
		),
		'pricesCopyZoneFrom'     => __( 'Från zon', 'museum-railway-timetable' ),
		'pricesCopyZoneTo'       => __( 'Till zon', 'museum-railway-timetable' ),
		'pricesCopyZoneButton'   => __( 'Kopiera', 'museum-railway-timetable' ),
		'pricesDeleteTicketTitle' => __( 'Ta bort biljettyp', 'museum-railway-timetable' ),
		'pricesDeleteTicketMsg'  => __(
			'Biljettypen «%s» och alla tillhörande priser tas bort från matrisen.',
			'museum-railway-timetable'
		),
		'pricesDeleteCategoryTitle' => __( 'Ta bort kategori', 'museum-railway-timetable' ),
		'pricesDeleteCategoryMsg' => __(
			'Kategorin «%s» och alla tillhörande priser tas bort från matrisen.',
			'museum-railway-timetable'
		),
		'pricesDeleteZoneTitle'  => __( 'Ta bort zon', 'museum-railway-timetable' ),
		'pricesDeleteZoneMsg'    => __(
			'Zon %1$s och alla priser i den kolumnen tas bort.',
			'museum-railway-timetable'
		),
	);
}
