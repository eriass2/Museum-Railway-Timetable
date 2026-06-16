<?php
/**
 * Lennakatten traffic demo: public notices (B) + trip deviations (A) for dev/test.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/traffic-notices/public-notices.php';
require_once MRT_PATH . 'inc/import/csv/codes-store.php';

/**
 * Demo trafikmeddelanden (källa B) — speglas inte i CSV, appliceras efter import.
 *
 * @return list<array{id: string, text: string, enabled: bool, active_from: string, active_to: string, sort_order: int}>
 */
function MRT_lennakatten_reference_public_notices(): array {
	return array(
		array(
			'id'          => 'demo-sommarinfo',
			'text'        => "Sommartrafik: GRÖN tidtabell gäller lördagar 5 juli–16 augusti.\nSe trafikkalendern för alla trafikdagar.",
			'enabled'     => true,
			'active_from' => '2026-06-01',
			'active_to'   => '2026-09-30',
			'sort_order'  => 5,
		),
		array(
			'id'          => 'demo-baninfo',
			'text'        => "Buss ersätter tåg vid Selkné.\nBerörda anslutningar: B3, B4.",
			'enabled'     => true,
			'active_from' => '2026-07-01',
			'active_to'   => '2026-08-16',
			'sort_order'  => 10,
		),
		array(
			'id'          => 'demo-glassrea',
			'text'        => "Glassrea på Faringe station kl 14.\nGlassrea på stationen idag.",
			'enabled'     => true,
			'active_from' => '2026-06-06',
			'active_to'   => '2026-06-06',
			'sort_order'  => 20,
		),
	);
}

/**
 * Demo tur-avvikelser (källa A) keyed by service_code.
 *
 * @return array<string, array<string, string>>
 */
function MRT_lennakatten_reference_service_deviations(): array {
	return array(
		'green-71-out' => array(
			'2026-06-06' => 'Inställd',
			'2026-07-04' => 'Inställd',
		),
		'green-97-out' => array(
			'2026-06-06' => 'Inställd',
		),
		'green-75-out' => array(
			'2026-06-06' => 'Ersättningsbuss',
		),
		'green-b3-bus-out' => array(
			'2026-06-06' => 'Försenad trafik',
		),
	);
}

/**
 * Apply demo traffic notices and deviations after Lennakatten CSV import.
 */
function MRT_lennakatten_apply_traffic_demo_data(): void {
	$notices = MRT_lennakatten_reference_public_notices();
	update_option( MRT_OPTION_PUBLIC_NOTICES, $notices, false );
	MRT_lennakatten_apply_reference_service_deviations();
	MRT_lennakatten_apply_rolling_demo_deviations();
}

/**
 * Train + bus deviations on «today» so dev/Docker always shows Aktuellt (Tåg/Buss).
 *
 * @return array<string, string> service_code => notice text
 */
function MRT_lennakatten_rolling_demo_deviations(): array {
	return array(
		'green-71-out'     => 'Inställd',
		'green-b3-bus-out' => 'Försenad trafik',
	);
}

/**
 * Merge rolling demo deviations onto today's date (after reference dates).
 */
function MRT_lennakatten_apply_rolling_demo_deviations(): void {
	$dt    = MRT_get_current_datetime();
	$today = is_array( $dt ) ? trim( (string) ( $dt['date'] ?? '' ) ) : '';
	if ( $today === '' || ! MRT_validate_date( $today ) ) {
		return;
	}
	$meta_key = MRT_csv_code_meta_keys()['services'];
	foreach ( MRT_lennakatten_rolling_demo_deviations() as $service_code => $notice ) {
		MRT_lennakatten_merge_service_deviation_on_date( $service_code, $today, $notice, $meta_key );
	}
}

/**
 * @param string $meta_key CSV code meta key for services.
 */
function MRT_lennakatten_merge_service_deviation_on_date(
	string $service_code,
	string $date,
	string $notice,
	string $meta_key
): void {
	$service_id = MRT_csv_find_post_by_code( $service_code, MRT_POST_TYPE_SERVICE, $meta_key );
	if ( $service_id <= 0 ) {
		return;
	}
	$by_date = get_post_meta( $service_id, 'mrt_service_notices_by_date', true );
	if ( ! is_array( $by_date ) ) {
		$by_date = array();
	}
	$by_date[ $date ] = $notice;
	update_post_meta( $service_id, 'mrt_service_notices_by_date', $by_date );
}

/**
 * Write deviation meta on imported services (by CSV service_code).
 */
function MRT_lennakatten_apply_reference_service_deviations(): void {
	$meta_key = MRT_csv_code_meta_keys()['services'];
	foreach ( MRT_lennakatten_reference_service_deviations() as $service_code => $by_date ) {
		$service_id = MRT_csv_find_post_by_code( $service_code, MRT_POST_TYPE_SERVICE, $meta_key );
		if ( $service_id <= 0 ) {
			continue;
		}
		update_post_meta( $service_id, 'mrt_service_notices_by_date', $by_date );
	}
}
