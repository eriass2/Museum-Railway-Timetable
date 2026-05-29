<?php
/**
 * Timetable admin workspace (tabbed: dates, trips, deviations, preview).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed workspace tab slugs.
 *
 * @return string[]
 */
function MRT_timetable_workspace_tab_ids(): array {
	return array( 'dates', 'trips', 'deviations', 'preview' );
}

/**
 * Active tab from query string.
 */
function MRT_timetable_workspace_active_tab(): string {
	$tab = isset( $_GET['mrt_tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['mrt_tab'] ) ) : 'dates';
	return in_array( $tab, MRT_timetable_workspace_tab_ids(), true ) ? $tab : 'dates';
}

/**
 * Highlighted trip on deviations tab (optional).
 */
function MRT_timetable_workspace_highlight_service_id(): int {
	return isset( $_GET['mrt_service'] ) ? (int) $_GET['mrt_service'] : 0;
}

/**
 * Tab nav link preserving post context.
 *
 * @param int    $post_id Timetable ID.
 * @param string $tab     Tab slug.
 */
function MRT_timetable_workspace_tab_url( int $post_id, string $tab ): string {
	return add_query_arg( 'mrt_tab', $tab, get_edit_post_link( $post_id ) );
}

/**
 * Render tab navigation.
 *
 * @param int    $post_id    Timetable ID.
 * @param string $active_tab Active tab slug.
 */
function MRT_render_timetable_workspace_tabs( int $post_id, string $active_tab ): void {
	$labels = array(
		'dates'      => __( 'Traffic days', 'museum-railway-timetable' ),
		'trips'      => __( 'Trips', 'museum-railway-timetable' ),
		'deviations' => __( 'Deviations', 'museum-railway-timetable' ),
		'preview'    => __( 'Preview', 'museum-railway-timetable' ),
	);
	?>
	<h2 class="nav-tab-wrapper mrt-timetable-workspace-tabs">
		<?php foreach ( MRT_timetable_workspace_tab_ids() as $tab ) : ?>
			<a
				href="<?php echo esc_url( MRT_timetable_workspace_tab_url( $post_id, $tab ) ); ?>"
				class="nav-tab<?php echo $active_tab === $tab ? ' nav-tab-active' : ''; ?>"
				data-mrt-tab="<?php echo esc_attr( $tab ); ?>"
			>
				<?php echo esc_html( $labels[ $tab ] ?? $tab ); ?>
			</a>
		<?php endforeach; ?>
	</h2>
	<?php
}

/**
 * Single tab panel wrapper.
 *
 * @param string $tab_id      Panel slug.
 * @param string $active_tab  Active tab slug.
 * @param string $panel_html  Panel inner HTML.
 */
function MRT_render_timetable_workspace_panel( string $tab_id, string $active_tab, string $panel_html ): void {
	$hidden = $active_tab !== $tab_id ? ' hidden' : '';
	?>
	<div
		id="mrt-timetable-panel-<?php echo esc_attr( $tab_id ); ?>"
		class="mrt-timetable-workspace-panel<?php echo esc_attr( $hidden ); ?>"
		data-mrt-panel="<?php echo esc_attr( $tab_id ); ?>"
		<?php echo $hidden !== '' ? ' hidden' : ''; ?>
	>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted admin panel HTML
		echo $panel_html;
		?>
	</div>
	<?php
}

/**
 * Render unified timetable workspace meta box.
 *
 * @param WP_Post $post Timetable post.
 */
function MRT_render_timetable_workspace_box( WP_Post $post ): void {
	$active_tab = MRT_timetable_workspace_active_tab();
	$highlight  = MRT_timetable_workspace_highlight_service_id();

	ob_start();
	MRT_render_timetable_dates_panel( $post );
	$dates_html = (string) ob_get_clean();

	ob_start();
	MRT_render_timetable_services_box( $post );
	$trips_html = (string) ob_get_clean();

	ob_start();
	MRT_render_timetable_deviations_panel( $post, $highlight );
	$deviations_html = (string) ob_get_clean();

	ob_start();
	MRT_render_timetable_overview_box( $post );
	$preview_html = (string) ob_get_clean();
	?>
	<div
		id="mrt-timetable-workspace"
		class="mrt-timetable-workspace"
		data-active-tab="<?php echo esc_attr( $active_tab ); ?>"
		data-highlight-service="<?php echo esc_attr( (string) $highlight ); ?>"
	>
		<?php MRT_render_timetable_workspace_tabs( (int) $post->ID, $active_tab ); ?>
		<?php
		MRT_render_timetable_workspace_panel( 'dates', $active_tab, $dates_html );
		MRT_render_timetable_workspace_panel( 'trips', $active_tab, $trips_html );
		MRT_render_timetable_workspace_panel( 'deviations', $active_tab, $deviations_html );
		MRT_render_timetable_workspace_panel( 'preview', $active_tab, $preview_html );
		?>
	</div>
	<?php
}

/**
 * Dates panel (type + traffic days).
 *
 * @param WP_Post $post Timetable post.
 */
function MRT_render_timetable_dates_panel( WP_Post $post ): void {
	wp_enqueue_script( 'jquery' );
	wp_nonce_field( 'mrt_save_timetable_meta', 'mrt_timetable_meta_nonce' );

	$dates = MRT_get_timetable_dates( $post->ID );
	if ( ! is_array( $dates ) ) {
		$dates = array();
	}
	$dates = array_values(
		array_filter(
			$dates,
			static function ( $d ) {
				return ! empty( $d ) && MRT_validate_date( $d );
			}
		)
	);

	MRT_render_info_box(
		__( 'Traffic days', 'museum-railway-timetable' ),
		'<p>' . esc_html__( 'Define which calendar days this timetable applies. Use a weekday pattern or add single dates.', 'museum-railway-timetable' ) . '</p>'
	);
	MRT_render_timetable_date_sections( $post, $dates );
	MRT_render_timetable_dates_script( $dates );
}
