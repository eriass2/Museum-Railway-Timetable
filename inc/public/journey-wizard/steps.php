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
 * Step 1: route and trip type
 *
 * @param array<int>          $stations Station IDs
 * @param string              $title_id Heading id (aria-labelledby target)
 * @param string              $panel_id Panel wrapper id
 * @param array<string,mixed> $hero Optional keys: image (url), subtitle (string)
 * @param int               $timetable_id Optional timetable overview post ID
 * @return void
 */
function MRT_render_journey_wizard_step_route( array $stations, $title_id, $panel_id, array $hero = array(), int $timetable_id = 0 ) {
	$hero_subtitle = isset( $hero['subtitle'] ) && is_string( $hero['subtitle'] ) ? trim( $hero['subtitle'] ) : '';
	$panel_class   = 'mrt-journey-wizard__panel mrt-journey-wizard__panel--active mrt-journey-wizard__search-panel';
	if ( $timetable_id > 0 ) {
		$panel_class .= ' mrt-journey-wizard__search-panel--with-timetable';
	}
	?>
	<div
		class="<?php echo esc_attr( $panel_class ); ?>"
		id="<?php echo esc_attr( $panel_id ); ?>"
		data-wizard-step="route"
		role="region"
		aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
	>
		<header class="mrt-journey-wizard__hero-head">
			<h2 class="mrt-journey-wizard__hero-title" id="<?php echo esc_attr( $title_id ); ?>">
				<?php esc_html_e( 'Sök din resa med Lennakatten', 'museum-railway-timetable' ); ?>
			</h2>
			<?php if ( $hero_subtitle !== '' ) : ?>
				<p class="mrt-journey-wizard__hero-lede"><?php echo esc_html( $hero_subtitle ); ?></p>
			<?php endif; ?>
		</header>
		<div class="mrt-form-fields mrt-journey-wizard__route">
			<?php MRT_render_journey_wizard_route_form_fields( $stations ); ?>
		</div>
		<?php MRT_render_journey_wizard_timetable_drawer( $timetable_id ); ?>
	</div>
	<?php
}

/**
 * Shared mock-style step header.
 *
 * @param string $back_step Step key for back button
 * @return void
 */
function MRT_render_journey_wizard_step_context_header( string $back_step ): void {
	?>
	<header class="mrt-journey-wizard__step-head">
		<button type="button" class="mrt-journey-wizard__back" data-wizard-back="<?php echo esc_attr( $back_step ); ?>">
			<?php esc_html_e( '← Tillbaka', 'museum-railway-timetable' ); ?>
		</button>
		<p class="mrt-journey-wizard__context" data-wizard-context></p>
	</header>
	<?php
}

/**
 * Step 2: calendar placeholder + legend
 *
 * @param string $title_id Heading id
 * @param string $panel_id Panel id
 * @return void
 */
function MRT_render_journey_wizard_step_date( $title_id, $panel_id ) {
	?>
	<div
		class="mrt-journey-wizard__panel"
		id="<?php echo esc_attr( $panel_id ); ?>"
		data-wizard-step="date"
		role="region"
		aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
		hidden
	>
		<?php MRT_render_journey_wizard_step_context_header( 'date' ); ?>
		<h3 class="mrt-journey-wizard__step-title" id="<?php echo esc_attr( $title_id ); ?>">
			<?php esc_html_e( 'Välj datum', 'museum-railway-timetable' ); ?>
		</h3>
		<div class="mrt-journey-wizard__calendar-nav mrt-mb-sm" aria-label="<?php esc_attr_e( 'Calendar month navigation', 'museum-railway-timetable' ); ?>">
			<button type="button" class="mrt-journey-wizard__cal-prev" aria-label="<?php esc_attr_e( 'Previous month', 'museum-railway-timetable' ); ?>">‹</button>
			<span class="mrt-journey-wizard__cal-title" aria-live="polite"></span>
			<button type="button" class="mrt-journey-wizard__cal-next" aria-label="<?php esc_attr_e( 'Next month', 'museum-railway-timetable' ); ?>">›</button>
			<button type="button" class="mrt-journey-wizard__cal-today" data-wizard-current-month>
				<?php esc_html_e( 'Denna månad', 'museum-railway-timetable' ); ?>
			</button>
		</div>
		<div
			class="mrt-journey-wizard__calendar mrt-mb-sm"
			data-wizard-calendar
			role="region"
			aria-label="<?php esc_attr_e( 'Travel dates calendar', 'museum-railway-timetable' ); ?>"
		></div>
		<ul class="mrt-journey-wizard__legend mrt-text-secondary mrt-mb-sm" aria-label="<?php esc_attr_e( 'Calendar legend', 'museum-railway-timetable' ); ?>">
			<li><span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--ok" aria-hidden="true"></span> <?php esc_html_e( 'Lennakatten trafikerar den valda resan', 'museum-railway-timetable' ); ?></li>
			<li><span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--traffic" aria-hidden="true"></span> <?php esc_html_e( 'Lennakatten trafikerar, men ej den valda resan', 'museum-railway-timetable' ); ?></li>
			<li><span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--none" aria-hidden="true"></span> <?php esc_html_e( 'Ingen trafik', 'museum-railway-timetable' ); ?></li>
		</ul>
	</div>
	<?php
}

/**
 * Outbound step panel (filled by JS).
 *
 * @param string $title_id Heading id
 * @param string $panel_id Panel id
 */
function MRT_render_journey_wizard_outbound_panel( $title_id, $panel_id ) {
	?>
	<div
		class="mrt-journey-wizard__panel"
		id="<?php echo esc_attr( $panel_id ); ?>"
		data-wizard-step="outbound"
		role="region"
		aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
		hidden
	>
		<?php MRT_render_journey_wizard_step_context_header( 'outbound' ); ?>
		<h3 class="mrt-journey-wizard__step-title" id="<?php echo esc_attr( $title_id ); ?>">
			<?php esc_html_e( 'Välj utresa', 'museum-railway-timetable' ); ?>
		</h3>
		<div data-wizard-outbound></div>
	</div>
	<?php
}

/**
 * Return step panel (filled by JS).
 *
 * @param string $title_id Heading id
 * @param string $panel_id Panel id
 */
function MRT_render_journey_wizard_return_panel( $title_id, $panel_id ) {
	?>
	<div
		class="mrt-journey-wizard__panel"
		id="<?php echo esc_attr( $panel_id ); ?>"
		data-wizard-step="return"
		role="region"
		aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
		hidden
	>
		<?php MRT_render_journey_wizard_step_context_header( 'return' ); ?>
		<div data-wizard-return-summary class="mrt-journey-wizard__selected-trip"></div>
		<h3 class="mrt-journey-wizard__step-title" id="<?php echo esc_attr( $title_id ); ?>">
			<?php esc_html_e( 'Välj återresa', 'museum-railway-timetable' ); ?>
		</h3>
		<div data-wizard-return></div>
	</div>
	<?php
}

/**
 * Summary step panel (filled by JS).
 *
 * @param string $title_id Heading id
 * @param string $panel_id Panel id
 */
function MRT_render_journey_wizard_summary_panel( $title_id, $panel_id ) {
	?>
	<div
		class="mrt-journey-wizard__panel"
		id="<?php echo esc_attr( $panel_id ); ?>"
		data-wizard-step="summary"
		role="region"
		aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
		hidden
	>
		<?php MRT_render_journey_wizard_step_context_header( 'summary' ); ?>
		<h3 class="mrt-journey-wizard__step-title" id="<?php echo esc_attr( $title_id ); ?>">
			<?php esc_html_e( 'Din resa', 'museum-railway-timetable' ); ?>
		</h3>
		<div data-wizard-summary></div>
		<p class="mrt-mt-sm" data-wizard-ticket-wrap hidden>
			<a href="#" class="mrt-btn mrt-btn--primary mrt-journey-wizard__cta" data-wizard-ticket><?php esc_html_e( 'Fortsätt till biljetter', 'museum-railway-timetable' ); ?></a>
		</p>
	</div>
	<?php
}

/**
 * Steps 3–5: outbound, return, summary (filled by JS)
 *
 * @param string $title_out  Outbound heading id
 * @param string $panel_out  Outbound panel id
 * @param string $title_ret  Return heading id
 * @param string $panel_ret  Return panel id
 * @param string $title_sum  Summary heading id
 * @param string $panel_sum  Summary panel id
 * @return void
 */
function MRT_render_journey_wizard_step_placeholders( $title_out, $panel_out, $title_ret, $panel_ret, $title_sum, $panel_sum ) {
	MRT_render_journey_wizard_outbound_panel( $title_out, $panel_out );
	MRT_render_journey_wizard_return_panel( $title_ret, $panel_ret );
	MRT_render_journey_wizard_summary_panel( $title_sum, $panel_sum );
}
