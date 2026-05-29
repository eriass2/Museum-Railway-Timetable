<?php
/**
 * Timetable deviations tab (all trips on one timetable).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One deviation row for the timetable table.
 *
 * @return array{service_id: int, date: string, trip_label: string, train_type_id: int, notice: string}
 */
function MRT_timetable_deviation_row_data( WP_Post $service, string $date ): array {
	$types   = get_post_meta( $service->ID, 'mrt_service_train_types_by_date', true );
	$notices = get_post_meta( $service->ID, 'mrt_service_notices_by_date', true );
	if ( ! is_array( $types ) ) {
		$types = array();
	}
	if ( ! is_array( $notices ) ) {
		$notices = array();
	}

	return array(
		'service_id'     => (int) $service->ID,
		'date'           => $date,
		'trip_label'     => (string) $service->post_title,
		'train_type_id'  => isset( $types[ $date ] ) ? (int) $types[ $date ] : 0,
		'notice'         => isset( $notices[ $date ] ) ? (string) $notices[ $date ] : '',
	);
}

/**
 * All deviation rows for a timetable, sorted by date then trip.
 *
 * @param WP_Post[] $services
 * @return array<int, array{service_id: int, date: string, trip_label: string, train_type_id: int, notice: string}>
 */
function MRT_collect_timetable_deviation_rows( array $services ): array {
	$rows = array();
	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		foreach ( MRT_service_deviation_dates( (int) $service->ID ) as $date ) {
			$rows[] = MRT_timetable_deviation_row_data( $service, $date );
		}
	}
	usort(
		$rows,
		static function ( array $a, array $b ): int {
			$cmp = strcmp( $a['date'], $b['date'] );
			return $cmp !== 0 ? $cmp : strcmp( $a['trip_label'], $b['trip_label'] );
		}
	);
	return $rows;
}

/**
 * Trip label for deviation dropdowns.
 */
function MRT_timetable_deviation_trip_label( WP_Post $service ): string {
	$route_id = (int) get_post_meta( $service->ID, 'mrt_service_route_id', true );
	$route    = $route_id > 0 ? get_post( $route_id ) : null;
	$dest     = MRT_get_service_destination( (int) $service->ID );
	$dest_txt = ! empty( $dest['destination'] ) ? (string) $dest['destination'] : '';
	if ( $route && $dest_txt !== '' ) {
		return $route->post_title . ' → ' . $dest_txt;
	}
	return (string) $service->post_title;
}

/**
 * Per-trip dates already used for deviations.
 *
 * @param WP_Post[] $services
 * @return array<int, string[]>
 */
function MRT_timetable_deviation_used_dates_by_service( array $services ): array {
	$out = array();
	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$out[ (int) $service->ID ] = MRT_service_deviation_dates( (int) $service->ID );
	}
	return $out;
}

/**
 * Render deviations tab panel.
 *
 * @param WP_Post $post              Timetable post.
 * @param int     $highlight_service Optional trip to highlight.
 */
function MRT_render_timetable_deviations_panel( WP_Post $post, int $highlight_service = 0 ): void {
	$data            = MRT_get_timetable_services_box_data( $post );
	$services        = $data['services'];
	$train_types     = $data['all_train_types'];
	$timetable_dates = MRT_get_timetable_dates( (int) $post->ID );
	sort( $timetable_dates );
	$rows            = MRT_collect_timetable_deviation_rows( $services );
	$used_by_service = MRT_timetable_deviation_used_dates_by_service( $services );
	$config          = wp_json_encode(
		array(
			'services'   => array_map(
				static function ( WP_Post $service ): array {
					return array(
						'id'    => (int) $service->ID,
						'label' => MRT_timetable_deviation_trip_label( $service ),
					);
				},
				$services
			),
			'dates'      => array_values( $timetable_dates ),
			'trainTypes' => MRT_service_deviation_train_type_options( $train_types ),
			'usedDates'  => $used_by_service,
			'dateFormat' => get_option( 'date_format' ),
		),
		JSON_UNESCAPED_UNICODE
	);
	?>
	<div id="mrt-timetable-deviations" class="mrt-timetable-deviations-panel">
		<input type="hidden" name="mrt_timetable_deviations_compact" value="1" />
		<p class="description">
			<?php esc_html_e( 'Plan replacement train types or traveller messages per trip and traffic day. Save the timetable to apply changes.', 'museum-railway-timetable' ); ?>
		</p>
		<?php if ( $services === array() ) : ?>
			<p class="description mrt-text-error"><?php esc_html_e( 'Add trips on the Trips tab before creating deviations.', 'museum-railway-timetable' ); ?></p>
		<?php elseif ( $timetable_dates === array() ) : ?>
			<p class="description mrt-text-error"><?php esc_html_e( 'Add traffic days on the Traffic days tab before creating deviations.', 'museum-railway-timetable' ); ?></p>
		<?php else : ?>
			<table class="widefat striped mrt-timetable-deviations-table">
				<thead>
					<tr>
						<th class="mrt-w-140"><?php esc_html_e( 'Traffic day', 'museum-railway-timetable' ); ?></th>
						<th><?php esc_html_e( 'Trip', 'museum-railway-timetable' ); ?></th>
						<th class="mrt-w-180"><?php esc_html_e( 'Replacement train type', 'museum-railway-timetable' ); ?></th>
						<th><?php esc_html_e( 'Message', 'museum-railway-timetable' ); ?></th>
						<th class="mrt-w-80"><?php esc_html_e( 'Remove', 'museum-railway-timetable' ); ?></th>
					</tr>
				</thead>
				<tbody id="mrt-timetable-deviation-rows">
					<?php
					foreach ( $rows as $row ) {
						MRT_render_timetable_deviation_table_row( $row, $train_types, $highlight_service );
					}
					?>
				</tbody>
			</table>
			<?php MRT_render_timetable_deviation_add_row( $services, $timetable_dates, $train_types ); ?>
			<script type="application/json" id="mrt-timetable-deviation-config"><?php echo $config !== false ? esc_html( $config ) : '{}'; ?></script>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * One deviation table row.
 *
 * @param array{service_id: int, date: string, trip_label: string, train_type_id: int, notice: string} $row
 * @param array<int, WP_Term>                                                                          $train_types
 * @param int                                                                                          $highlight_service
 */
function MRT_render_timetable_deviation_table_row( array $row, array $train_types, int $highlight_service ): void {
	$service_id = (int) $row['service_id'];
	$date       = (string) $row['date'];
	$highlight  = $highlight_service > 0 && $highlight_service === $service_id ? ' mrt-deviation-row--highlight' : '';
	$name_base  = 'mrt_timetable_deviation[' . $service_id . '][' . $date . ']';
	?>
	<tr
		class="mrt-timetable-deviation-row<?php echo esc_attr( $highlight ); ?>"
		data-service-id="<?php echo esc_attr( (string) $service_id ); ?>"
		data-date="<?php echo esc_attr( $date ); ?>"
	>
		<td>
			<strong><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ); ?></strong>
			<span class="mrt-form-label__hint">(<?php echo esc_html( $date ); ?>)</span>
		</td>
		<td><?php echo esc_html( (string) $row['trip_label'] ); ?></td>
		<td>
			<select name="<?php echo esc_attr( $name_base . '[train_type]' ); ?>" class="mrt-input mrt-input--meta mrt-w-full">
				<option value=""><?php esc_html_e( '— Default train type —', 'museum-railway-timetable' ); ?></option>
				<?php foreach ( $train_types as $term ) : ?>
					<option value="<?php echo esc_attr( (string) $term->term_id ); ?>" <?php selected( (int) $row['train_type_id'], (int) $term->term_id ); ?>>
						<?php echo esc_html( $term->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
		<td>
			<textarea
				name="<?php echo esc_attr( $name_base . '[notice]' ); ?>"
				class="large-text"
				rows="2"
				placeholder="<?php esc_attr_e( 'Message to travellers', 'museum-railway-timetable' ); ?>"
			><?php echo esc_textarea( (string) $row['notice'] ); ?></textarea>
		</td>
		<td>
			<button type="button" class="button-link-delete mrt-timetable-deviation-remove">
				<?php esc_html_e( 'Remove', 'museum-railway-timetable' ); ?>
			</button>
		</td>
	</tr>
	<?php
}

/**
 * Add-deviation controls below the table.
 *
 * @param WP_Post[]             $services
 * @param string[]              $timetable_dates
 * @param array<int, WP_Term>   $train_types
 */
function MRT_render_timetable_deviation_add_row( array $services, array $timetable_dates, array $train_types ): void {
	?>
	<div class="mrt-timetable-deviation-add mrt-mt-1 mrt-box mrt-box-sm">
		<h3 class="mrt-heading mrt-mt-0"><?php esc_html_e( 'Add deviation', 'museum-railway-timetable' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Choose trip and traffic day, then click Add. Fill in replacement type and/or message in the table.', 'museum-railway-timetable' ); ?></p>
		<select id="mrt-add-deviation-service" class="mrt-input mrt-input--meta mrt-mr-sm">
			<option value=""><?php esc_html_e( '— Select trip —', 'museum-railway-timetable' ); ?></option>
			<?php foreach ( $services as $service ) : ?>
				<option value="<?php echo esc_attr( (string) $service->ID ); ?>">
					<?php echo esc_html( MRT_timetable_deviation_trip_label( $service ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<select id="mrt-add-deviation-date" class="mrt-input mrt-input--meta mrt-mr-sm" disabled>
			<option value=""><?php esc_html_e( '— Select traffic day —', 'museum-railway-timetable' ); ?></option>
		</select>
		<button type="button" class="button button-primary" id="mrt-add-timetable-deviation-btn" disabled>
			<?php esc_html_e( 'Add deviation', 'museum-railway-timetable' ); ?>
		</button>
	</div>
	<?php
	unset( $train_types, $timetable_dates );
}

/**
 * Persist deviations posted from the timetable workspace.
 *
 * @param int $timetable_id Timetable post ID.
 */
function MRT_save_timetable_deviations( int $timetable_id ): void {
	if ( empty( $_POST['mrt_timetable_deviations_compact'] ) ) {
		return;
	}

	$services = get_posts(
		array(
			'post_type'      => 'mrt_service',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'mrt_service_timetable_id',
					'value'   => $timetable_id,
					'compare' => '=',
				),
			),
		)
	);

	$posted = isset( $_POST['mrt_timetable_deviation'] ) && is_array( $_POST['mrt_timetable_deviation'] )
		? $_POST['mrt_timetable_deviation']
		: array();

	foreach ( $services as $service_id ) {
		$service_id = (int) $service_id;
		$by_type    = array();
		$by_notice  = array();
		$rows       = isset( $posted[ $service_id ] ) && is_array( $posted[ $service_id ] ) ? $posted[ $service_id ] : array();

		foreach ( $rows as $date => $row ) {
			$date = sanitize_text_field( (string) $date );
			if ( ! MRT_validate_date( $date ) || ! is_array( $row ) ) {
				continue;
			}
			$tid  = isset( $row['train_type'] ) ? (int) $row['train_type'] : 0;
			$text = isset( $row['notice'] ) ? sanitize_textarea_field( wp_unslash( (string) $row['notice'] ) ) : '';
			if ( $tid > 0 ) {
				$term = get_term( $tid, 'mrt_train_type' );
				if ( $term && ! is_wp_error( $term ) ) {
					$by_type[ $date ] = $tid;
				}
			}
			if ( $text !== '' ) {
				$by_notice[ $date ] = $text;
			}
		}

		$by_type !== array()
			? update_post_meta( $service_id, 'mrt_service_train_types_by_date', $by_type )
			: delete_post_meta( $service_id, 'mrt_service_train_types_by_date' );
		$by_notice !== array()
			? update_post_meta( $service_id, 'mrt_service_notices_by_date', $by_notice )
			: delete_post_meta( $service_id, 'mrt_service_notices_by_date' );
	}
}
