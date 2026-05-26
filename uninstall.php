<?php
// Remove plugin options on uninstall; keep tables/data by default
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
delete_option('mrt_settings');
delete_option('mrt_price_matrix');
delete_option('mrt_components_demo_page_id');
delete_option('mrt_wizard_smoke_page_id');
delete_option('mrt_planner_smoke_page_id');
delete_option('mrt_dev_nav_menu_id');
