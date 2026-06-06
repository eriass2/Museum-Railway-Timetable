<?php
/**
 * Option keys for development navigation and smoke pages.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option: wizard-only smoke page ID */
define( 'MRT_OPTION_WIZARD_SMOKE_PAGE_ID', 'mrt_wizard_smoke_page_id' );

/** Option: nav menu ID used for dev links (may match site primary) */
define( 'MRT_OPTION_DEV_NAV_MENU_ID', 'mrt_dev_nav_menu_id' );

/** Option: block-theme navigation post (wp_navigation) synced from the dev menu */
define( 'MRT_OPTION_DEV_WP_NAVIGATION_ID', 'mrt_dev_wp_navigation_id' );
