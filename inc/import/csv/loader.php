<?php
/**
 * CSV import module loader.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$csv_dir = MRT_PATH . 'inc/import/csv/';
require_once $csv_dir . 'schema.php';
require_once $csv_dir . 'slugify.php';
require_once $csv_dir . 'symbol-map.php';
require_once $csv_dir . 'reader.php';
require_once $csv_dir . 'writer.php';
require_once $csv_dir . 'package.php';
require_once $csv_dir . 'validate-manifest.php';
require_once $csv_dir . 'validate-codes.php';
require_once $csv_dir . 'validate-codes-entities.php';
require_once $csv_dir . 'validate-references.php';
require_once $csv_dir . 'codes-store.php';
require_once $csv_dir . 'build-lennakatten.php';
require_once $csv_dir . 'build-lennakatten-routes.php';
require_once $csv_dir . 'import-entities.php';
require_once $csv_dir . 'import-entities-services.php';
require_once $csv_dir . 'import-override.php';
require_once $csv_dir . 'importer.php';
require_once $csv_dir . 'exporter.php';
require_once $csv_dir . 'exporter-entities.php';
