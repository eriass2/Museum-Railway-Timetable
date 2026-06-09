<?php
/**
 * Admin Vue l10n: editor (merged)
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/admin-vue-l10n-editor-meta.php';
require_once __DIR__ . '/admin-vue-l10n-editor-dates.php';
require_once __DIR__ . '/admin-vue-l10n-editor-trips.php';
require_once __DIR__ . '/admin-vue-l10n-editor-deviations.php';
require_once __DIR__ . '/admin-vue-l10n-editor-stoptimes.php';

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_editor(): array {
	return array_merge(
		MRT_admin_vue_l10n_editor_meta(),
		MRT_admin_vue_l10n_editor_dates(),
		MRT_admin_vue_l10n_editor_trips(),
		MRT_admin_vue_l10n_editor_deviations(),
		MRT_admin_vue_l10n_editor_stoptimes()
	);
}
