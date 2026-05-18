<?php
/**
 * Shared HTML for journey planner connections table (shortcode + AJAX)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Plain-text caption for the journey connections table
 *
 * @param string $from_name From station title
 * @param string $to_name To station title
 * @param string $selected_date Date Y-m-d
 * @param bool   $is_return     Return leg wording (AJAX return search)
 * @return string
 */
function MRT_journey_connections_table_caption( $from_name, $to_name, $selected_date, $is_return = false ) {
	$date_display = date_i18n( get_option( 'date_format' ), strtotime( $selected_date ) );

	if ( $is_return ) {
		return sprintf(
			/* translators: 1: departure station, 2: arrival station, 3: formatted date */
			__( 'Return train connections from %1$s to %2$s on %3$s', 'museum-railway-timetable' ),
			$from_name,
			$to_name,
			$date_display
		);
	}

	return sprintf(
		/* translators: 1: departure station, 2: arrival station, 3: formatted date */
		__( 'Train connections from %1$s to %2$s on %3$s', 'museum-railway-timetable' ),
		$from_name,
		$to_name,
		$date_display
	);
}

/**
 * Render connections table rows (caption + scoped headers)
 *
 * @param array<int, array<string, mixed>> $connections Planner table rows (flat connection or normalized row)
 * @param string                           $caption_text Accessible table caption (plain text)
 * @return void Outputs HTML
 */
function MRT_render_journey_connections_table( array $connections, string $caption_text ): void {
	?>
	<div class="mrt-journey-table-container mrt-overflow-x-auto">
		<table class="mrt-table mrt-journey-table mrt-mt-sm">
			<caption class="mrt-journey-table__caption"><?php echo esc_html( $caption_text ); ?></caption>
			<?php MRT_render_journey_connections_table_head(); ?>
			<tbody>
				<?php foreach ( $connections as $conn ) : ?>
					<?php MRT_render_journey_connections_table_row( $conn ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Render journey table header.
 */
function MRT_render_journey_connections_table_head(): void {
	?>
	<thead>
		<tr>
			<th scope="col"><?php esc_html_e( 'Service', 'museum-railway-timetable' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Train Type', 'museum-railway-timetable' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Departure', 'museum-railway-timetable' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Arrival', 'museum-railway-timetable' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Destination', 'museum-railway-timetable' ); ?></th>
		</tr>
	</thead>
	<?php
}

/**
 * Render one journey table row.
 *
 * @param array<string, mixed> $conn Connection row
 */
function MRT_render_journey_connections_table_row( array $conn ): void {
	?>
	<tr>
		<td><?php MRT_render_journey_connection_service_cell( $conn ); ?></td>
		<td><?php echo esc_html( $conn['train_type'] ); ?></td>
		<td><strong><?php echo esc_html( MRT_journey_connection_departure_display( $conn ) ); ?></strong></td>
		<td><strong><?php echo esc_html( MRT_journey_connection_arrival_display( $conn ) ); ?></strong></td>
		<td><?php echo esc_html( MRT_journey_connection_destination_display( $conn ) ); ?></td>
	</tr>
	<?php
}

/**
 * Render service name + route cell.
 *
 * @param array<string, mixed> $conn Connection row
 */
function MRT_render_journey_connection_service_cell( array $conn ): void {
	?>
	<strong><?php echo esc_html( $conn['service_name'] ); ?></strong>
	<?php if ( ! empty( $conn['route_name'] ) ) : ?>
		<br><small class="mrt-text-tertiary mrt-font-italic"><?php echo esc_html( $conn['route_name'] ); ?></small>
	<?php endif; ?>
	<?php
}

/**
 * Departure display text for a connection row.
 *
 * @param array<string, mixed> $conn Connection row
 */
function MRT_journey_connection_departure_display( array $conn ): string {
	$dep = $conn['from_departure'] ?: $conn['from_arrival'];
	return $dep ? MRT_format_time_display( $dep ) : '—';
}

/**
 * Arrival display text for a connection row.
 *
 * @param array<string, mixed> $conn Connection row
 */
function MRT_journey_connection_arrival_display( array $conn ): string {
	$arr = $conn['to_arrival'] ?: $conn['to_departure'];
	return $arr ? MRT_format_time_display( $arr ) : '—';
}

/**
 * Destination display text for a connection row.
 *
 * @param array<string, mixed> $conn Connection row
 */
function MRT_journey_connection_destination_display( array $conn ): string {
	if ( ! empty( $conn['destination'] ) ) {
		return (string) $conn['destination'];
	}
	return ! empty( $conn['direction'] ) ? (string) $conn['direction'] : '—';
}
