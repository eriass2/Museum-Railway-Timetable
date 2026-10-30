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
	return '4';
}

/**
 * True when every part of the service_seq index is unique.
 *
 * @param array<int, object|array<string, mixed>> $index_rows SHOW INDEX rows.
 */
function MRT_stop_sequence_index_is_unique( array $index_rows ): bool {
	if ( $index_rows === array() ) {
		return false;
	}
	foreach ( $index_rows as $row ) {
		$column     = 'Non_unique';
		$non_unique = is_object( $row ) ? ( $row->{$column} ?? null ) : ( $row[ $column ] ?? null );
		if ( (string) $non_unique === '1' ) {
			return false;
		}
	}
	return true;
}

/**
 * Create or upgrade {prefix}_mrt_stoptimes via dbDelta.
 */
function MRT_install_stoptimes_table(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	if ( MRT_stop_sequence_has_duplicates() ) {
		return;
	}
	MRT_drop_non_unique_stop_sequence_index();

	$table   = $wpdb->prefix . 'mrt_stoptimes';
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        service_post_id BIGINT UNSIGNED NOT NULL,
        station_post_id BIGINT UNSIGNED NOT NULL,
        stop_sequence INT NOT NULL,
        arrival_time CHAR(5) NULL,
        departure_time CHAR(5) NULL,
        ank_pickup_mode VARCHAR(16) NOT NULL DEFAULT 'none',
        ank_dropoff_mode VARCHAR(16) NOT NULL DEFAULT 'none',
        avg_pickup_mode VARCHAR(16) NOT NULL DEFAULT 'scheduled',
        avg_dropoff_mode VARCHAR(16) NOT NULL DEFAULT 'scheduled',
        approximate_time TINYINT(1) DEFAULT 0,
        in_service_timetable TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY service_seq (service_post_id, stop_sequence),
        KEY station (station_post_id)
    ) $charset;";

	dbDelta( $sql );
	if ( MRT_ensure_unique_stop_sequence() ) {
		update_option( 'mrt_db_schema_version', MRT_db_schema_version() );
	}
}

/**
 * Table name for stop times.
 */
function MRT_stoptimes_table_name(): string {
	global $wpdb;
	return $wpdb->prefix . 'mrt_stoptimes';
}

/**
 * Whether the stop-time table already exists.
 */
function MRT_stoptimes_table_exists(): bool {
	global $wpdb;
	$table = MRT_stoptimes_table_name();
	$found = $wpdb->get_var(
		$wpdb->prepare(
			'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
			$table
		)
	);
	return $found === $table;
}

/**
 * True when two rows share a service and stop sequence. Those rows are left untouched.
 */
function MRT_stop_sequence_has_duplicates(): bool {
	global $wpdb;
	if ( ! MRT_stoptimes_table_exists() ) {
		return false;
	}
	$table     = MRT_stoptimes_table_name();
	$duplicate = $wpdb->get_var(
		"SELECT 1 FROM $table GROUP BY service_post_id, stop_sequence HAVING COUNT(*) > 1 LIMIT 1"
	);
	return ! empty( $duplicate );
}

/**
 * Remove a non-unique service_seq index so dbDelta can create the unique one.
 */
function MRT_drop_non_unique_stop_sequence_index(): void {
	global $wpdb;
	if ( ! MRT_stoptimes_table_exists() ) {
		return;
	}
	$table   = MRT_stoptimes_table_name();
	$indexes = $wpdb->get_results( "SHOW INDEX FROM $table WHERE Key_name = 'service_seq'" );
	$indexes = is_array( $indexes ) ? $indexes : array();
	if ( $indexes === array() || MRT_stop_sequence_index_is_unique( $indexes ) ) {
		return;
	}
	$wpdb->query( "ALTER TABLE $table DROP INDEX service_seq" );
}

/**
 * One stop sequence per service. Existing rows are kept; a duplicate blocks the index.
 */
function MRT_ensure_unique_stop_sequence(): bool {
	global $wpdb;
	$table   = $wpdb->prefix . 'mrt_stoptimes';
	$indexes = $wpdb->get_results( "SHOW INDEX FROM $table WHERE Key_name = 'service_seq'" );
	$indexes = is_array( $indexes ) ? $indexes : array();
	if ( MRT_stop_sequence_index_is_unique( $indexes ) ) {
		return true;
	}

	$duplicate = $wpdb->get_var(
		"SELECT 1 FROM $table GROUP BY service_post_id, stop_sequence HAVING COUNT(*) > 1 LIMIT 1"
	);
	if ( ! empty( $duplicate ) ) {
		return false;
	}

	if ( $indexes !== array() ) {
		$wpdb->query( "ALTER TABLE $table DROP INDEX service_seq" );
	}
	$wpdb->query( "ALTER TABLE $table ADD UNIQUE KEY service_seq (service_post_id, stop_sequence)" );
	$after = $wpdb->get_results( "SHOW INDEX FROM $table WHERE Key_name = 'service_seq'" );
	return MRT_stop_sequence_index_is_unique( is_array( $after ) ? $after : array() );
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
