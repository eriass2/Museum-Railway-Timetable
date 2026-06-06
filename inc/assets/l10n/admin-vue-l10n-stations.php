<?php
/**
 * Admin Vue l10n: stations
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_stations(): array {
	return array(
		'stationsTitle'              => __( 'Stationer & rutter', 'museum-railway-timetable' ),
		'stationsLoading'            => __( 'Laddar stationer och rutter…', 'museum-railway-timetable' ),
		'stationsNavAria'            => __( 'Stationer eller rutter', 'museum-railway-timetable' ),
		'stationsTabStations'        => __( 'Stationer', 'museum-railway-timetable' ),
		'stationsTabRoutes'          => __( 'Rutter', 'museum-railway-timetable' ),
		'stationsNewStation'         => __( 'Ny station', 'museum-railway-timetable' ),
		'stationsCreateMoreFields'   => __( 'Fler uppgifter (valfritt)', 'museum-railway-timetable' ),
		'stationsNewRoute'           => __( 'Ny rutt', 'museum-railway-timetable' ),
		'stationsRouteCreateMoreFields' => __( 'Stationer och ändpunkter (valfritt)', 'museum-railway-timetable' ),
		'stationsEmptyStationsTitle' => __( 'Inga stationer', 'museum-railway-timetable' ),
		'stationsEmptyStationsMsg'   => __( 'Lägg till din första station ovan.', 'museum-railway-timetable' ),
		'stationsEmptyRoutesTitle'   => __( 'Inga rutter', 'museum-railway-timetable' ),
		'stationsEmptyRoutesMsg'     => __( 'Skapa en rutt ovan och koppla stationer i redigeringsvyn.', 'museum-railway-timetable' ),
		'stationsColName'            => __( 'Namn', 'museum-railway-timetable' ),
		'stationsColType'            => __( 'Typ', 'museum-railway-timetable' ),
		'stationsColLat'             => __( 'Lat', 'museum-railway-timetable' ),
		'stationsColLng'             => __( 'Lng', 'museum-railway-timetable' ),
		'stationsColBus'             => __( 'Buss', 'museum-railway-timetable' ),
		'stationsColZones'           => __( 'Priszoner', 'museum-railway-timetable' ),
		'stationsColOrder'           => __( 'Ordning', 'museum-railway-timetable' ),
		'stationsColStations'        => __( 'Stationer', 'museum-railway-timetable' ),
		'stationsTypeStation'        => __( 'Station', 'museum-railway-timetable' ),
		'stationsTypeHalt'           => __( 'Hållplats', 'museum-railway-timetable' ),
		'stationsTypeDepot'          => __( 'Depot', 'museum-railway-timetable' ),
		'stationsTypeMuseum'         => __( 'Museum', 'museum-railway-timetable' ),
		'stationsRouteSaved'         => __( 'Rutten «%s» sparades.', 'museum-railway-timetable' ),
		'stationsStationSaved'       => __( '«%s» sparades.', 'museum-railway-timetable' ),
		'stationsDeleteStationTitle' => __( 'Ta bort station', 'museum-railway-timetable' ),
		'stationsDeleteStationMsg'   => __(
			'Stationen «%s» tas bort om den inte används i rutter eller turer.',
			'museum-railway-timetable'
		),
		'stationsDeleteRouteTitle'   => __( 'Ta bort rutt', 'museum-railway-timetable' ),
		'stationsDeleteRouteMsg'     => __(
			'Rutten «%s» tas bort om inga turer använder den.',
			'museum-railway-timetable'
		),
		'stationsDeleteStationFailed' => __( 'Kunde inte ta bort station.', 'museum-railway-timetable' ),
		'stationsDeleteRouteFailed'   => __( 'Kunde inte ta bort rutt.', 'museum-railway-timetable' ),
		'stationsEditRouteTitle'     => __( 'Redigera rutt: %s', 'museum-railway-timetable' ),
		'stationsRouteNameLabel'     => __( 'Namn', 'museum-railway-timetable' ),
		'stationsRouteEndpointsLegend' => __( 'Ändstationer', 'museum-railway-timetable' ),
		'stationsRouteEndpointsHint' => __(
			'Välj start- och slutstation bland rutts stationer. Används för att visa riktning (dit/från) i tidtabellen.',
			'museum-railway-timetable'
		),
		'stationsRouteOrderLegend'   => __( 'Stationer i ordning', 'museum-railway-timetable' ),
		'stationsRouteOrderHint'     => __(
			'Ordningen bestämmer i vilken följd tåget passerar hållplatserna. Använd pilarna för att ändra ordning.',
			'museum-railway-timetable'
		),
		'stationsRouteStart'         => __( 'Startstation', 'museum-railway-timetable' ),
		'stationsRouteEnd'           => __( 'Slutstation', 'museum-railway-timetable' ),
		'stationsRouteMoveUp'        => __( 'Flytta upp', 'museum-railway-timetable' ),
		'stationsRouteMoveDown'      => __( 'Flytta ner', 'museum-railway-timetable' ),
		'stationsRouteRemoveStation' => __( 'Ta bort från rutt', 'museum-railway-timetable' ),
		'stationsRouteEmptyStations' => __( 'Inga stationer ännu. Lägg till en station nedan.', 'museum-railway-timetable' ),
		'stationsAddStationPrompt'   => __( 'Lägg till station...', 'museum-railway-timetable' ),
		'stationsSaveRoute'          => __( 'Spara rutt', 'museum-railway-timetable' ),
	);
}
