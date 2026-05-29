<?php
/**
 * Admin: create public timetable index + per-timetable pages.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/timetable-pages.php';

/**
 * Add index page to primary nav menu when missing (idempotent).
 */
function MRT_append_timetables_index_to_nav_menu( int $page_id ): int {
	if ( $page_id <= 0 || ! function_exists( 'MRT_get_assigned_nav_menu_id_for_theme' ) ) {
		return 0;
	}
	$menu_id = MRT_get_assigned_nav_menu_id_for_theme();
	if ( $menu_id <= 0 ) {
		return 0;
	}
	if ( MRT_nav_menu_contains_page( $menu_id, $page_id ) ) {
		return 0;
	}
	$result = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => __( 'Tidtabeller', 'museum-railway-timetable' ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
	return is_wp_error( $result ) ? 0 : 1;
}

/**
 * Handle dashboard sync action.
 */
function MRT_handle_sync_timetable_pages_action(): void {
	MRT_verify_dashboard_action_nonce( 'mrt_sync_timetable_pages', 'mrt_sync_timetable_pages_nonce' );
	$result = MRT_sync_timetable_public_pages();
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ) );
	}
	MRT_redirect_dashboard_notice(
		array(
			'mrt_timetable_pages' => count( $result['timetable_page_ids'] ?? array() ),
		)
	);
}

/**
 * Dashboard form: sync timetable pages.
 */
function MRT_render_dashboard_sync_timetable_pages_button(): void {
	$index_id = (int) get_option( MRT_OPTION_TIMETABLES_INDEX_PAGE_ID, 0 );
	$index    = $index_id > 0 ? get_post( $index_id ) : null;
	?>
	<form method="post">
		<?php wp_nonce_field( 'mrt_sync_timetable_pages', 'mrt_sync_timetable_pages_nonce' ); ?>
		<input type="hidden" name="mrt_action" value="sync_timetable_pages" />
		<p>
			<button type="submit" class="button button-secondary">
				<?php esc_html_e( 'Create/update timetable pages', 'museum-railway-timetable' ); ?>
			</button>
			<span class="description">
				<?php esc_html_e( 'Publishes a Tidtabeller index page and one overview page per timetable (linkable URLs).', 'museum-railway-timetable' ); ?>
			</span>
		</p>
	</form>
	<?php if ( $index instanceof WP_Post ) : ?>
		<p>
			<a href="<?php echo esc_url( get_permalink( $index ) ); ?>"><?php esc_html_e( 'View timetable index', 'museum-railway-timetable' ); ?></a>
			&middot;
			<a href="<?php echo esc_url( get_edit_post_link( $index->ID, 'raw' ) ); ?>"><?php esc_html_e( 'Edit index page', 'museum-railway-timetable' ); ?></a>
		</p>
	<?php endif; ?>
	<?php
}

/**
 * Admin list of per-timetable public pages.
 */
function MRT_render_timetable_public_page_admin_links(): void {
	$timetables = MRT_get_published_timetables();
	if ( $timetables === array() ) {
		echo '<p class="description">' . esc_html__( 'No published timetables yet.', 'museum-railway-timetable' ) . '</p>';
		return;
	}
	echo '<ul class="ul-disc">';
	foreach ( $timetables as $timetable ) {
		$url = MRT_timetable_public_page_url( (int) $timetable->ID );
		echo '<li>';
		if ( $url !== '' ) {
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $timetable ) ) . '</a>';
		} else {
			echo esc_html( get_the_title( $timetable ) );
			echo ' <em>(' . esc_html__( 'no page yet', 'museum-railway-timetable' ) . ')</em>';
		}
		echo '</li>';
	}
	echo '</ul>';
}
