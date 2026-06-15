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
require_once $csv_dir . 'reader.php';
require_once $csv_dir . 'writer.php';
require_once $csv_dir . 'package/package.php';
require_once $csv_dir . 'package/manifest.php';
require_once $csv_dir . 'package/package-upload.php';
require_once $csv_dir . 'package/package-load.php';
require_once $csv_dir . 'export/template-export.php';
require_once $csv_dir . 'import/import-errors.php';
require_once $csv_dir . 'validate/validate-manifest.php';
require_once $csv_dir . 'validate/validate-codes.php';
require_once $csv_dir . 'validate/validate-codes-entities.php';
require_once $csv_dir . 'validate/validate-lines.php';
require_once MRT_PATH . 'inc/domain/line/line-route-resolve.php';
require_once MRT_PATH . 'inc/domain/line/line-route-definitions.php';
require_once $csv_dir . 'validate/validate-references.php';
require_once $csv_dir . 'codes-store.php';
require_once $csv_dir . 'entity-upsert.php';
require_once $csv_dir . 'fixture-read.php';
require_once $csv_dir . 'import/import-entities-lines.php';
require_once $csv_dir . 'import/import-entities.php';
require_once $csv_dir . 'import/import-entities-services.php';
require_once $csv_dir . 'import/import-brand-tokens.php';
require_once $csv_dir . 'ticket-copy-csv.php';
require_once $csv_dir . 'import/import-override.php';
require_once $csv_dir . 'import/importer.php';
require_once $csv_dir . 'export/exporter.php';
require_once $csv_dir . 'export/exporter-entities.php';
