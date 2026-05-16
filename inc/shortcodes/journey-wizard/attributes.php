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
 * Optional hero background: CSS custom property for cover image (see journey-wizard.css).
 *
 * @param string $hero_image Attachment URL or empty
 * @return string Safe HTML attributes for the opening tag (leading space when non-empty; escaped).
 */
function MRT_journey_wizard_hero_bg_attr( $hero_image ) {
	$hero_image = is_string( $hero_image ) ? trim( $hero_image ) : '';
	if ( $hero_image === '' ) {
		return '';
	}
	$u = esc_url( $hero_image );
	return ' data-has-hero-bg="1" style="' . esc_attr( '--mrt-hero-image: url("' . $u . '")' ) . '"';
}
