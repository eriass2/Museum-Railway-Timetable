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
 * Localized strings for Vue admin (phase 5 i18n).
 *
 * @return array<string, string>
 */
function MRT_admin_vue_script_localization(): array {
	return array_merge(
		MRT_admin_vue_l10n_common(),
		MRT_admin_vue_l10n_nav(),
		MRT_admin_vue_l10n_settings(),
		MRT_admin_vue_l10n_prices()
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
