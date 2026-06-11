<?php
/**
 * Admin Vue l10n module loader.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$l10n_dir = MRT_PATH . 'inc/assets/l10n/';
require_once $l10n_dir . 'admin-vue-l10n-common.php';
require_once $l10n_dir . 'admin-vue-l10n-nav.php';
require_once $l10n_dir . 'admin-vue-l10n-settings.php';
require_once $l10n_dir . 'admin-vue-l10n-prices.php';
require_once $l10n_dir . 'admin-vue-l10n-dashboard.php';
require_once $l10n_dir . 'admin-vue-l10n-stations.php';
require_once $l10n_dir . 'admin-vue-l10n-timetables.php';
require_once $l10n_dir . 'admin-vue-l10n-train_types.php';
require_once $l10n_dir . 'admin-vue-l10n-import_export.php';
require_once $l10n_dir . 'admin-vue-l10n-traffic.php';
require_once $l10n_dir . 'admin-vue-l10n-traffic_notices.php';
require_once $l10n_dir . 'admin-vue-l10n-feedback.php';
require_once $l10n_dir . 'editor/admin-vue-l10n-editor.php';
require_once $l10n_dir . 'admin-vue-l10n-mobile.php';
require_once $l10n_dir . 'admin-vue-l10n-stop_times.php';
require_once $l10n_dir . 'admin-vue-l10n-dev.php';
require_once $l10n_dir . 'admin-vue-l10n-setup.php';
require_once $l10n_dir . 'admin-vue-l10n-route_preview.php';
