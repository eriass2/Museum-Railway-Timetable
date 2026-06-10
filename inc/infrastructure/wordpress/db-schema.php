<?php
/**
 * Custom table install and upgrades.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @return string */
function MRT_db_schema_version(): string {
	return '2';
}

/**
 * Create or upgrade {prefix}_mrt_stoptimes via dbDelta.
 */
function MRT_install_stoptimes_table(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table   = $wpdb->prefix . 'mrt_stoptimes';
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        service_post_id BIGINT UNSIGNED NOT NULL,
        station_post_id BIGINT UNSIGNED NOT NULL,
        stop_sequence INT NOT NULL,
        arrival_time CHAR(5) NULL,
        departure_time CHAR(5) NULL,
        pickup_allowed TINYINT(1) DEFAULT 1,
        dropoff_allowed TINYINT(1) DEFAULT 1,
        approximate_time TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id),
        KEY service_seq (service_post_id, stop_sequence),
        KEY station (station_post_id)
    ) $charset;";

	dbDelta( $sql );
	update_option( 'mrt_db_schema_version', MRT_db_schema_version() );
}

/**
 * Run pending schema upgrades on load (existing installs).
 */
function MRT_maybe_upgrade_db_schema(): void {
	$installed = (string) get_option( 'mrt_db_schema_version', '0' );
	if ( version_compare( $installed, MRT_db_schema_version(), '>=' ) ) {
		return;
	}
	MRT_install_stoptimes_table();
}
