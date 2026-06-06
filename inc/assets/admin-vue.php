<?php
/**
 * Enqueue Vue admin bundle on app screens.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/assets/admin-vue-l10n-misc.php';

/**
 * Admin page slugs that mount Vue.
 *
 * @return string[]
 */
function MRT_admin_vue_page_slugs(): array {
	return array( MRT_ADMIN_APP_SLUG );
}

/**
 * Whether current admin screen is the Vue app.
 *
 * @param string $hook Page hook.
 */
function MRT_is_admin_vue_screen( string $hook ): bool {
	unset( $hook );
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
	return in_array( $page, MRT_admin_vue_page_slugs(), true );
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_common(): array {
	return array(
		'saved'      => __( 'Sparat.', 'museum-railway-timetable' ),
		'loading'    => __( 'Laddar…', 'museum-railway-timetable' ),
		'retry'      => __( 'Försök igen', 'museum-railway-timetable' ),
		'loadFailed' => __( 'Kunde inte ladda.', 'museum-railway-timetable' ),
		'saveFailed' => __( 'Kunde inte spara.', 'museum-railway-timetable' ),
		'genericError' => __( 'Fel', 'museum-railway-timetable' ),
		'trafficCancelledNotice' => __( 'Inställd', 'museum-railway-timetable' ),
		'confirm'    => __( 'Bekräfta', 'museum-railway-timetable' ),
		'cancel'     => __( 'Avbryt', 'museum-railway-timetable' ),
		'delete'     => __( 'Ta bort', 'museum-railway-timetable' ),
		'save'       => __( 'Spara', 'museum-railway-timetable' ),
		'edit'       => __( 'Redigera', 'museum-railway-timetable' ),
		'add'        => __( 'Lägg till', 'museum-railway-timetable' ),
		'yes'        => __( 'Ja', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_nav(): array {
	return array(
		'navBrand'           => __( 'Tidtabell', 'museum-railway-timetable' ),
		'navAria'            => __( 'Tidtabell admin', 'museum-railway-timetable' ),
		'navOverview'        => __( 'Översikt', 'museum-railway-timetable' ),
		'navStationsRoutes'  => __( 'Stationer & rutter', 'museum-railway-timetable' ),
		'navTimetables'      => __( 'Tidtabeller', 'museum-railway-timetable' ),
		'navShortcodes'      => __( 'Shortcodes', 'museum-railway-timetable' ),
		'navHelp'            => __( 'Hjälp', 'museum-railway-timetable' ),
		'navTrainTypes'      => __( 'Tågtyper', 'museum-railway-timetable' ),
		'navSettings'        => __( 'Inställningar', 'museum-railway-timetable' ),
		'navPrices'          => __( 'Priser', 'museum-railway-timetable' ),
		'navImportExport'    => __( 'Import/export', 'museum-railway-timetable' ),
		'navDev'             => __( 'Dev', 'museum-railway-timetable' ),
		'navComponentDemo'   => __( 'Komponentdemo', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
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

/**
 * @return array<string, string>
 */
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
	);
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_dashboard(): array {
	return array(
		'dashboardTitle'           => __( 'Museum Railway Timetable', 'museum-railway-timetable' ),
		'dashboardLoading'         => __( 'Laddar översikt…', 'museum-railway-timetable' ),
		'dashboardLoadFailed'      => __( 'Kunde inte ladda översikt.', 'museum-railway-timetable' ),
		'dashboardLimitedRole'     => __(
			'Begränsad behörighet: du kan ändra avvikelser och avgångstider, inte grunddata.',
			'museum-railway-timetable'
		),
		'dashboardStatStations'    => __( 'Stationer', 'museum-railway-timetable' ),
		'dashboardStatRoutes'      => __( 'Rutter', 'museum-railway-timetable' ),
		'dashboardStatTimetables'  => __( 'Tidtabeller', 'museum-railway-timetable' ),
		'dashboardStatServices'    => __( 'Turer', 'museum-railway-timetable' ),
		'dashboardStatTrainTypes'  => __( 'Tågtyper', 'museum-railway-timetable' ),
		'dashboardStatsAria'       => __( 'Statistik', 'museum-railway-timetable' ),
		'dashboardStatsSummary'    => __(
			'%1$s stationer · %2$s rutter · %3$s tidtabeller · %4$s turer · %5$s tågtyper',
			'museum-railway-timetable'
		),
		'dashboardWarningsTitle'   => __( 'Varningar', 'museum-railway-timetable' ),
		'dashboardNextTrafficTitle' => __( 'Nästa trafik', 'museum-railway-timetable' ),
		'dashboardColDate'         => __( 'Datum', 'museum-railway-timetable' ),
		'dashboardColTimetable'    => __( 'Tidtabell', 'museum-railway-timetable' ),
		'dashboardQuickstartTitle' => __( 'Snabbstart', 'museum-railway-timetable' ),
		'dashboardQuickStations'   => __( 'Stationer & rutter', 'museum-railway-timetable' ),
		'dashboardQuickTimetables' => __( 'Hantera tidtabeller', 'museum-railway-timetable' ),
		'dashboardQuickHelp'       => __( 'Hjälp & FAQ', 'museum-railway-timetable' ),
		'dashboardViewSite'        => __( 'Visa webbplats', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
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

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_timetables(): array {
	return array(
		'timetablesTitle'              => __( 'Tidtabeller', 'museum-railway-timetable' ),
		'timetablesLoading'            => __( 'Laddar tidtabeller…', 'museum-railway-timetable' ),
		'timetablesLoadFailed'         => __( 'Fel vid laddning', 'museum-railway-timetable' ),
		'timetablesCreateFailed'       => __( 'Kunde inte skapa', 'museum-railway-timetable' ),
		'timetablesDeleteFailed'       => __( 'Kunde inte ta bort', 'museum-railway-timetable' ),
		'timetablesLimitedRole'        => __(
			'Du kan öppna tidtabeller och ändra avvikelser eller avgångstider, men inte skapa nya tidtabeller eller grunddata. Kontakta en administratör om du behöver fler rättigheter.',
			'museum-railway-timetable'
		),
		'timetablesNewTitle'           => __( 'Ny tidtabell', 'museum-railway-timetable' ),
		'timetablesNamePlaceholder'    => __( 'Namn', 'museum-railway-timetable' ),
		'timetablesCreateButton'       => __( 'Skapa', 'museum-railway-timetable' ),
		'timetablesEmptyTitle'         => __( 'Inga tidtabeller', 'museum-railway-timetable' ),
		'timetablesEmptyMessage'       => __( 'Skapa en tidtabell ovan för att komma igång.', 'museum-railway-timetable' ),
		'timetablesColName'            => __( 'Namn', 'museum-railway-timetable' ),
		'timetablesColDates'           => __( 'Trafikdagar', 'museum-railway-timetable' ),
		'timetablesColTrips'           => __( 'Turer', 'museum-railway-timetable' ),
		'timetablesCardSummary'        => __( '%1$s trafikdagar · %2$s turer', 'museum-railway-timetable' ),
		'timetablesDeleteTitle'        => __( 'Ta bort tidtabell', 'museum-railway-timetable' ),
		'timetablesDeleteMessage'      => __(
			'«%s» och alla dess turer raderas permanent.',
			'museum-railway-timetable'
		),
	);
}

/**
 * @return array<string, string>
 */
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
		'trainTypesIconSteam'       => __( 'Ångtåg', 'museum-railway-timetable' ),
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

/**
 * @return array<string, string>
 */
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
		'importExportImportTitle'      => __( 'Importera zip', 'museum-railway-timetable' ),
		'importExportImportHint'       => __(
			'Ladda upp zip med CSV-filer. manifest.json är valfri. Vid fel visas fil och radnummer.',
			'museum-railway-timetable'
		),
		'importExportModeMergeShort'   => __( 'Rekommenderas — uppdaterar det som finns i filen och behåller övrig data.', 'museum-railway-timetable' ),
		'importExportAdvancedMode'     => __( 'Avancerat importläge', 'museum-railway-timetable' ),
		'importExportModeMerge'        => __( 'Slå ihop (uppdatera befintlig data)', 'museum-railway-timetable' ),
		'importExportModeOverride'     => __( 'Ersätt (ta bort poster som saknas i filen)', 'museum-railway-timetable' ),
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

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_traffic(): array {
	return array(
		'trafficTodayTitle'            => __( 'Trafik idag', 'museum-railway-timetable' ),
		'trafficTodayNoServices'       => __( 'Inga turer schemalagda.', 'museum-railway-timetable' ),
		'trafficTodayAllCancelled'     => __( 'All trafik (%1$s turer) är inställd.', 'museum-railway-timetable' ),
		'trafficTodaySummary'          => __( '%1$s turer · %2$s', 'museum-railway-timetable' ),
		'trafficTodayCancelTitle'      => __( 'Inställ trafik idag', 'museum-railway-timetable' ),
		'trafficTodayCancelMessage'    => __(
			'Alla %1$s turer den %2$s får meddelandet «Inställd». Detta påverkar visningen på webbplatsen.',
			'museum-railway-timetable'
		),
		'trafficTodayCancelButton'     => __( 'Inställ trafik', 'museum-railway-timetable' ),
		'trafficTodayCancelSuccess'    => __( '%s turer markerade som inställda.', 'museum-railway-timetable' ),
		'trafficTodayCancelNone'       => __( 'Ingen trafik att ställa in.', 'museum-railway-timetable' ),
		'trafficTodayCancelFailed'     => __( 'Kunde inte ställa in trafik', 'museum-railway-timetable' ),
		'trafficTodayOpenTimetable'    => __( 'Öppna tidtabell', 'museum-railway-timetable' ),
		'trafficTodayEditDeviations'   => __( 'Ändra avgångstid / avvikelser', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_script_localization(): array {
	return array_merge(
		MRT_admin_vue_l10n_common(),
		MRT_admin_vue_l10n_nav(),
		MRT_admin_vue_l10n_settings(),
		MRT_admin_vue_l10n_prices(),
		MRT_admin_vue_l10n_dashboard(),
		MRT_admin_vue_l10n_stations(),
		MRT_admin_vue_l10n_timetables(),
		MRT_admin_vue_l10n_train_types(),
		MRT_admin_vue_l10n_import_export(),
		MRT_admin_vue_l10n_traffic(),
		MRT_admin_vue_l10n_editor(),
		MRT_admin_vue_l10n_mobile(),
		MRT_admin_vue_l10n_stop_times(),
		MRT_admin_vue_l10n_dev(),
		MRT_admin_vue_l10n_setup(),
		MRT_admin_vue_l10n_route_preview()
	);
}

/**
 * @return array<string, mixed>
 */
function MRT_admin_vue_client_config(): array {
	$config = array_merge(
		MRT_rest_client_config(),
		array(
			'initialRoute'       => MRT_admin_app_initial_route(),
			'adminBase'          => admin_url( 'admin.php?page=' . MRT_ADMIN_APP_SLUG ),
			'canManage'          => current_user_can( 'manage_options' ),
			'canOperate'         => current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ),
			'isDevMode'          => MRT_is_development_mode(),
			'trainTypeIconUrls'  => MRT_train_type_icon_urls(),
			'strings'            => MRT_admin_vue_script_localization(),
			'help'               => MRT_admin_vue_help_content(),
			'importExportGuide'  => MRT_admin_vue_import_export_guide(),
		)
	);
	if ( MRT_is_development_mode() && function_exists( 'MRT_components_demo_menu_slug' ) ) {
		$config['componentDemoAdminUrl'] = admin_url(
			'admin.php?page=' . rawurlencode( MRT_components_demo_menu_slug() )
		);
	}
	return $config;
}

/**
 * Enqueue admin Vue bundle (fixed path assets/admin.js).
 */
function MRT_enqueue_admin_vue_assets(): void {
	$base_url = MRT_assets_base_url() . 'dist/vue/assets/admin.js';
	if ( ! is_readable( MRT_PATH . 'assets/dist/vue/assets/admin.js' ) ) {
		return;
	}
	wp_enqueue_script(
		'mrt-vue-admin',
		$base_url,
		array(),
		MRT_VERSION,
		true
	);
	wp_localize_script( 'mrt-vue-admin', 'mrtAdminVue', MRT_admin_vue_client_config() );
}

/**
 * @param string $hook Admin hook.
 */
function MRT_maybe_enqueue_admin_vue( string $hook ): void {
	if ( ! MRT_is_admin_vue_screen( $hook ) ) {
		return;
	}
	MRT_enqueue_admin_css( $hook );
	MRT_enqueue_admin_vue_assets();
}

add_action( 'admin_enqueue_scripts', 'MRT_maybe_enqueue_admin_vue', 20 );
