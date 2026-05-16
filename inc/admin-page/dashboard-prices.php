<?php
/**
 * Dashboard: public journey price matrix (option mrt_price_matrix)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render price matrix table header rows.
 *
 * @param array<string, string> $clabels Category labels
 * @param array<int>            $zones Zone keys
 */
function MRT_render_price_matrix_table_head( array $clabels, array $zones ): void {
	?>
	<tr>
		<th><?php esc_html_e( 'Ticket type', 'museum-railway-timetable' ); ?></th>
		<?php foreach ( $clabels as $clabel ) : ?>
			<th colspan="<?php echo esc_attr( (string) count( $zones ) ); ?>"><?php echo esc_html( $clabel ); ?></th>
		<?php endforeach; ?>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Zones', 'museum-railway-timetable' ); ?></th>
		<?php foreach ( $clabels as $clabel ) : ?>
			<?php foreach ( $zones as $zone ) : ?>
				<th><?php echo esc_html( (string) $zone ); ?></th>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</tr>
	<?php
}

/**
 * Render price matrix table body rows.
 *
 * @param array<string, array<string, array<int, int|null>>> $matrix Price matrix
 * @param array<string, string>                            $tlabels Ticket type labels
 * @param array<int>                                       $zones Zone keys
 */
function MRT_render_price_matrix_table_body( array $matrix, array $tlabels, array $zones ): void {
	foreach ( MRT_price_ticket_type_keys() as $tkey ) {
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $tlabels[ $tkey ] ?? $tkey ); ?></th>
			<?php
			foreach ( MRT_price_category_keys() as $ckey ) :
				foreach ( $zones as $zone ) :
					$name = sprintf( 'mrt_price_matrix[%s][%s][%s]', $tkey, $ckey, $zone );
					$raw  = $matrix[ $tkey ][ $ckey ][ $zone ] ?? null;
					$val  = ( $raw === null || $raw === '' ) ? '' : (int) $raw;
					?>
				<td>
					<input type="number" min="0" step="1" class="small-text"
						name="<?php echo esc_attr( $name ); ?>"
						value="<?php echo esc_attr( (string) $val ); ?>"
						placeholder="—" />
				</td>
					<?php
				endforeach;
			endforeach;
			?>
		</tr>
		<?php
	}
}

/**
 * Render price matrix settings fields
 */
function MRT_render_price_matrix_field() {
	$matrix  = MRT_get_price_matrix();
	$tlabels = MRT_price_ticket_type_labels();
	$clabels = MRT_price_category_labels();
	$zones   = MRT_price_zone_keys();
	?>
	<p class="description"><?php esc_html_e( 'Prices in SEK by ticket type, passenger category, and number of zones. Boundary stations count as either adjacent zone.', 'museum-railway-timetable' ); ?></p>
	<table class="widefat striped mrt-price-matrix-table mrt-mt-sm">
		<thead>
			<?php MRT_render_price_matrix_table_head( $clabels, $zones ); ?>
		</thead>
		<tbody>
			<?php MRT_render_price_matrix_table_body( $matrix, $tlabels, $zones ); ?>
		</tbody>
	</table>
	<?php
}
