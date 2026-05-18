<?php
/**
 * Journey wizard shortcode helpers.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output one station select field
 *
 * @param string     $id Input id/name suffix
 * @param string     $label Accessible label
 * @param array<int> $stations Station post IDs
 * @param int        $selected Selected ID
 * @param string     $placeholder Option text
 * @return void
 */
function MRT_render_journey_wizard_station_select( $id, $label, $stations, $selected, $placeholder ) {
	$field_id = 'mrt_wizard_' . $id;
	?>
	<div class="mrt-form-field">
		<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
		<select name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>" required>
			<option value=""><?php echo esc_html( $placeholder ); ?></option>
			<?php foreach ( $stations as $station_id ) : ?>
				<option value="<?php echo esc_attr( (string) $station_id ); ?>" <?php selected( $selected, $station_id ); ?>>
					<?php echo esc_html( get_the_title( $station_id ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}

/**
 * Route step: station fields, trip type, primary action
 *
 * @param array<int> $stations Station IDs
 * @return void
 */
function MRT_render_journey_wizard_route_form_fields( array $stations ) {
	MRT_render_journey_wizard_station_select(
		'from',
		__( 'Från', 'museum-railway-timetable' ),
		$stations,
		0,
		__( 'Var börjar du din resa?', 'museum-railway-timetable' )
	);
	MRT_render_journey_wizard_station_select(
		'to',
		__( 'Till', 'museum-railway-timetable' ),
		$stations,
		0,
		__( 'Vart vill du resa?', 'museum-railway-timetable' )
	);
	?>
	<fieldset class="mrt-form-field mrt-journey-wizard__trip-type">
		<legend class="mrt-sr-only"><?php esc_html_e( 'Restyp', 'museum-railway-timetable' ); ?></legend>
		<div class="mrt-journey-wizard__trip-type-toggle">
			<label class="mrt-journey-wizard__radio-label">
				<input type="radio" name="mrt_wizard_trip_type" value="single" checked>
				<span class="mrt-journey-wizard__radio-text" aria-hidden="true">→</span>
				<span class="mrt-journey-wizard__radio-text"><?php esc_html_e( 'Enkel', 'museum-railway-timetable' ); ?></span>
			</label>
			<label class="mrt-journey-wizard__radio-label">
				<input type="radio" name="mrt_wizard_trip_type" value="return">
				<span class="mrt-journey-wizard__radio-text" aria-hidden="true">↔</span>
				<span class="mrt-journey-wizard__radio-text"><?php esc_html_e( 'Tur och retur', 'museum-railway-timetable' ); ?></span>
			</label>
		</div>
	</fieldset>
	<div class="mrt-form-field mrt-journey-wizard__actions">
		<button type="button" class="mrt-btn mrt-btn--primary mrt-journey-wizard__cta" data-wizard-next="route">
			<?php esc_html_e( 'Sök resa', 'museum-railway-timetable' ); ?>
		</button>
	</div>
	<?php
}
