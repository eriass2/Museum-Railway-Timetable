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
 * @param mixed $value Shortcode boolean attribute.
 */
function MRT_journey_wizard_shortcode_bool( $value ): bool {
	if ( is_bool( $value ) ) {
		return $value;
	}
	$normalized = strtolower( trim( (string) $value ) );
	return in_array( $normalized, array( '1', 'true', 'yes', 'on' ), true );
}

/**
 * @return array{ticket_url: string, hero: array{image: string, subtitle: string}, timetable_id: int, embedded: bool}
 */
function MRT_journey_wizard_parse_shortcode_atts( $atts ): array {
	$atts = shortcode_atts(
		array(
			'ticket_url'    => '',
			'hero_image'    => '',
			'hero_subtitle' => '',
			'timetable_id'  => '',
			'timetable'     => '',
			'embedded'      => '',
		),
		(array) $atts,
		'museum_journey_wizard'
	);

	$timetable_id = MRT_journey_wizard_resolve_timetable_id( $atts );

	return array(
		'ticket_url'   => esc_url( $atts['ticket_url'] ),
		'hero'         => array(
			'image'    => is_string( $atts['hero_image'] ) ? $atts['hero_image'] : '',
			'subtitle' => is_string( $atts['hero_subtitle'] ) ? $atts['hero_subtitle'] : '',
		),
		'timetable_id' => $timetable_id,
		'embedded'     => MRT_journey_wizard_shortcode_bool( $atts['embedded'] ),
	);
}

/**
 * @return array<string, string>
 */
function MRT_journey_wizard_step_element_ids( string $u ): array {
	return array(
		'route_title' => $u . '-route-t',
		'route_panel' => $u . '-route-p',
		'date_title'  => $u . '-date-t',
		'date_panel'  => $u . '-date-p',
		'out_title'   => $u . '-out-t',
		'out_panel'   => $u . '-out-p',
		'ret_title'   => $u . '-ret-t',
		'ret_panel'   => $u . '-ret-p',
		'sum_title'   => $u . '-sum-t',
		'sum_panel'   => $u . '-sum-p',
	);
}

/**
 * Render journey wizard outer shell and step panels.
 *
 * @param string                $uid Unique element id prefix
 * @param array<string, string> $ids Step element ids
 * @param array<int>            $stations Station post IDs
 * @param array<string, mixed>  $hero Hero settings
 * @param int                   $timetable_id Optional timetable ID
 * @param string                $ticket_url Ticket purchase URL
 * @param string                $hero_attr Hero background HTML attributes
 * @param bool                  $embedded Compact layout inside page content (no full-bleed hero).
 * @return string HTML
 */
function MRT_render_journey_wizard_shell( $uid, array $ids, array $stations, array $hero, $timetable_id, $ticket_url, $hero_attr, $embedded = false ) {
	$root_class = 'mrt-journey-wizard mrt-my-lg';
	if ( $embedded ) {
		$root_class .= ' mrt-journey-wizard--embedded';
	}
	ob_start();
	?>
	<div
		class="<?php echo esc_attr( $root_class ); ?>"
		data-ticket-url="<?php echo $ticket_url ? esc_attr( $ticket_url ) : ''; ?>"
		data-start-of-week="<?php echo esc_attr( (string) (int) get_option( 'start_of_week', '1' ) ); ?>"
	>
		<section class="mrt-journey-wizard__hero"<?php echo $hero_attr; ?>>
			<div class="mrt-journey-wizard__hero-inner">
				<noscript>
					<p class="mrt-alert mrt-alert-info"><?php esc_html_e( 'This planner needs JavaScript enabled.', 'museum-railway-timetable' ); ?></p>
				</noscript>
				<div id="<?php echo esc_attr( $uid ); ?>-errors" class="mrt-journey-wizard__errors" role="alert" aria-live="assertive" aria-relevant="additions text"></div>
				<nav class="mrt-journey-wizard__nav" aria-label="<?php esc_attr_e( 'Trip planner steps', 'museum-railway-timetable' ); ?>">
					<ol class="mrt-journey-wizard__steps" data-wizard-steps></ol>
				</nav>
				<div class="mrt-journey-wizard__panels">
					<?php
					MRT_render_journey_wizard_step_route(
						$stations,
						$ids['route_title'],
						$ids['route_panel'],
						$hero,
						$timetable_id
					);
					MRT_render_journey_wizard_step_date( $ids['date_title'], $ids['date_panel'] );
					MRT_render_journey_wizard_step_placeholders(
						$ids['out_title'],
						$ids['out_panel'],
						$ids['ret_title'],
						$ids['ret_panel'],
						$ids['sum_title'],
						$ids['sum_panel']
					);
					?>
				</div>
			</div>
		</section>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render [museum_journey_wizard]
 *
 * @param array|string $atts Shortcode attributes
 * @return string HTML
 */
function MRT_render_shortcode_journey_wizard( $atts ) {
	$parsed     = MRT_journey_wizard_parse_shortcode_atts( $atts );
	$ticket_url = $parsed['ticket_url'];
	$hero       = $parsed['hero'];
	$timetable_id = isset( $parsed['timetable_id'] ) ? intval( $parsed['timetable_id'] ) : 0;
	$embedded     = ! empty( $parsed['embedded'] );
	$hero_image   = isset( $hero['image'] ) && is_string( $hero['image'] ) ? trim( $hero['image'] ) : '';
	$hero_attr    = MRT_journey_wizard_hero_bg_attr( $hero_image );

	$stations = MRT_get_all_stations();
	if ( empty( $stations ) ) {
		return '<p class="mrt-alert mrt-alert-info">' . esc_html__( 'No stations are available.', 'museum-railway-timetable' ) . '</p>';
	}
	$uid = wp_unique_id( 'mrtjw' );
	$ids = MRT_journey_wizard_step_element_ids( $uid );

	return MRT_render_journey_wizard_shell( $uid, $ids, $stations, $hero, $timetable_id, $ticket_url, $hero_attr, $embedded );
}
