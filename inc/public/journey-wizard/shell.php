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
 * Sanitize debug preset key (development only).
 */
function MRT_journey_wizard_sanitize_debug_attr( string $debug ): string {
	if ( ! MRT_is_development_mode() || ! function_exists( 'MRT_journey_wizard_debug_presets' ) ) {
		return '';
	}
	$debug   = sanitize_key( $debug );
	$allowed = array_keys( MRT_journey_wizard_debug_presets() );
	return in_array( $debug, $allowed, true ) ? $debug : '';
}

/**
 * @return array{ticket_url: string, hero_subtitle: string, timetable_id: int, embedded: bool, debug: string}
 */
function MRT_journey_wizard_parse_shortcode_atts( $atts ): array {
	$atts = shortcode_atts(
		array(
			'ticket_url'    => '',
			'hero_subtitle' => '',
			'timetable_id'  => '',
			'timetable'     => '',
			'embedded'      => '',
			'debug'         => '',
		),
		(array) $atts,
		'museum_journey_wizard'
	);

	$timetable_id = MRT_journey_wizard_resolve_timetable_id( $atts );

	return array(
		'ticket_url'    => esc_url( $atts['ticket_url'] ),
		'hero_subtitle' => is_string( $atts['hero_subtitle'] ) ? $atts['hero_subtitle'] : '',
		'timetable_id'  => $timetable_id,
		'embedded'      => MRT_journey_wizard_shortcode_bool( $atts['embedded'] ),
		'debug'         => MRT_journey_wizard_sanitize_debug_attr( is_string( $atts['debug'] ) ? $atts['debug'] : '' ),
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
 * @param string                $hero_subtitle Optional subtitle on step 1
 * @param int                   $timetable_id Optional timetable ID
 * @param string                $ticket_url Ticket purchase URL
 * @param bool                  $embedded Compact layout inside page content (no full-bleed hero).
 * @param string                $debug Development preset key (date|outbound|return|summary).
 * @return string HTML
 */
function MRT_render_journey_wizard_shell( $uid, array $ids, array $stations, $hero_subtitle, $timetable_id, $ticket_url, $embedded = false, $debug = '' ) {
	$root_class = 'mrt-journey-wizard mrt-my-lg';
	if ( $embedded ) {
		$root_class .= ' mrt-journey-wizard--embedded';
	}
	if ( $debug !== '' ) {
		$root_class .= ' mrt-journey-wizard--debug';
	}
	ob_start();
	?>
	<div
		class="<?php echo esc_attr( $root_class ); ?>"
		data-ticket-url="<?php echo $ticket_url ? esc_attr( $ticket_url ) : ''; ?>"
		data-start-of-week="<?php echo esc_attr( (string) (int) get_option( 'start_of_week', '1' ) ); ?>"
		<?php echo $debug !== '' ? ' data-wizard-debug="' . esc_attr( $debug ) . '"' : ''; ?>
	>
		<section class="mrt-journey-wizard__hero">
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
						$hero_subtitle,
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
	$parsed        = MRT_journey_wizard_parse_shortcode_atts( $atts );
	$ticket_url    = $parsed['ticket_url'];
	$hero_subtitle = isset( $parsed['hero_subtitle'] ) && is_string( $parsed['hero_subtitle'] ) ? trim( $parsed['hero_subtitle'] ) : '';
	$timetable_id  = isset( $parsed['timetable_id'] ) ? intval( $parsed['timetable_id'] ) : 0;
	$embedded      = ! empty( $parsed['embedded'] );
	$debug         = isset( $parsed['debug'] ) ? (string) $parsed['debug'] : '';

	$stations = MRT_get_all_stations();
	if ( empty( $stations ) ) {
		return '<p class="mrt-alert mrt-alert-info">' . esc_html__( 'No stations are available.', 'museum-railway-timetable' ) . '</p>';
	}

	if ( MRT_use_vue_frontend() ) {
		return MRT_render_vue_mount(
			'wizard',
			MRT_vue_wizard_config(
				$stations,
				array(
					'ticket_url'    => $ticket_url,
					'hero_subtitle' => $hero_subtitle,
					'timetable_id'  => $timetable_id,
					'embedded'      => $embedded,
					'debug'         => $debug,
				)
			)
		);
	}

	$uid = wp_unique_id( 'mrtjw' );
	$ids = MRT_journey_wizard_step_element_ids( $uid );

	return MRT_render_journey_wizard_shell( $uid, $ids, $stations, $hero_subtitle, $timetable_id, $ticket_url, $embedded, $debug );
}
