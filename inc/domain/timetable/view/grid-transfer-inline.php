<?php
/**
 * Print-style inline transfer rows (tågbyte, anslutningsbuss) with vehicle icons.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/view/grid-connections.php';

/**
 * One vehicle block: icon + type label + service number (+ optional detail line).
 */
function MRT_render_transfer_vehicle_block_html(
	?WP_Term $train_type,
	string $type_label,
	string $service_number,
	string $detail = ''
): string {
	$html = '<div class="mrt-print-transfer-vehicle">';
	$icon = MRT_get_train_type_icon( $train_type );
	if ( $icon !== '' ) {
		$html .= '<span class="mrt-print-transfer-inline-icon">' . $icon . '</span>';
	}
	$html .= '<span class="mrt-print-transfer-inline-type">' . esc_html( $type_label ) . '</span>';
	$html .= '<span class="mrt-print-transfer-inline-number">' . esc_html( $service_number ) . '</span>';
	if ( $detail !== '' ) {
		$html .= '<span class="mrt-print-transfer-inline-detail">' . esc_html( $detail ) . '</span>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * @param array<string, mixed> $connection_data
 * @return array<int, array{service_number: string, time_display: string, destination: string}>
 */
function MRT_connection_buses_for_train_number( array $connection_data, string $train_number ): array {
	foreach ( $connection_data['train_to_bus'] as $row ) {
		if ( (string) $row['train']['service_number'] === $train_number ) {
			return $row['buses'];
		}
	}
	return array();
}

/**
 * @param array{service_number: string, time_display: string, destination: string} $bus
 */
function MRT_bus_transfer_detail_line( array $bus ): string {
	$parts = array();
	if ( $bus['time_display'] !== '' && $bus['time_display'] !== '—' ) {
		$parts[] = $bus['time_display'];
	}
	if ( ! empty( $bus['destination'] ) ) {
		$parts[] = '→ ' . $bus['destination'];
	}
	return implode( ' ', $parts );
}

/**
 * Anslutningsbuss row at junction station (same layout as tågbyte).
 *
 * @param array<string, mixed> $connection_data From MRT_build_rail_bus_connection_data.
 */
function MRT_render_grid_bus_transfer_row(
	WP_Post $station,
	array $services_list,
	array $service_classes,
	array $service_info,
	array $connection_data
): string {
	$junction_id = (int) ( $connection_data['junction_id'] ?? 0 );
	if ( $junction_id <= 0 || (int) $station->ID !== $junction_id ) {
		return '';
	}

	$bus_term = MRT_get_train_type_term_by_slug( 'buss' );
	ob_start();
	?>
	<div class="mrt-grid-row mrt-print-transfer-inline-row mrt-print-bus-transfer-row">
		<div class="mrt-grid-cell mrt-station-col mrt-transfer-station-col">
			<?php esc_html_e( 'Anslutningsbuss:', 'museum-railway-timetable' ); ?>
		</div>
		<?php
		foreach ( $services_list as $idx => $service_data ) :
			$train_number = (string) ( $service_info[ $idx ]['service_number'] ?? '' );
			$buses        = MRT_connection_buses_for_train_number( $connection_data, $train_number );
			?>
			<div class="mrt-grid-cell mrt-time-cell mrt-print-transfer-inline-cell <?php echo esc_attr( implode( ' ', $service_classes[ $idx ] ) ); ?>">
				<?php
				foreach ( $buses as $bus ) {
					$detail = MRT_bus_transfer_detail_line( $bus );
					echo MRT_render_transfer_vehicle_block_html(
						$bus_term,
						__( 'Buss', 'museum-railway-timetable' ),
						$bus['service_number'],
						$detail
					);
				}
				?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * @param array<string, mixed>|null $connection_data
 */
function MRT_maybe_render_bus_transfer_row(
	WP_Post $station,
	array $services_list,
	array $service_classes,
	array $service_info,
	?array $connection_data
): string {
	if ( ! $connection_data ) {
		return '';
	}
	return MRT_render_grid_bus_transfer_row(
		$station,
		$services_list,
		$service_classes,
		$service_info,
		$connection_data
	);
}
