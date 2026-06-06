<?php
/**
 * Admin help: price zones and ticket pricing.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string[]
 */
function MRT_admin_vue_help_price_zones_steps(): array {
	return array(
		__(
			'Tilldela priszoner (1–3) per station under Stationer & rutter. Gränsstationer kan ha två zoner (t.ex. 1 och 2).',
			'museum-railway-timetable'
		),
		__(
			'Fyll i prismatrisen under Priser — biljettyp × kategori × zon. Reseplaneraren räknar antal zoner längs utresan.',
			'museum-railway-timetable'
		),
		__(
			'Eftermiddags-retur: gräns (t.ex. kl 15) under Inställningar, fast pris per kategori under Priser.',
			'museum-railway-timetable'
		),
		__(
			'Översikten varnar om tom matris, stationer utan zon eller saknade eftermiddagspriser.',
			'museum-railway-timetable'
		),
	);
}
