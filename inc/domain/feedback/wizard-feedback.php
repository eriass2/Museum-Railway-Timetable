<?php
/**
 * Journey wizard feedback storage.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MRT_FEEDBACK_META_TYPE = 'mrt_feedback_type';
const MRT_FEEDBACK_META_EMAIL = 'mrt_feedback_email';
const MRT_FEEDBACK_META_PAGE_URL = 'mrt_feedback_page_url';
const MRT_FEEDBACK_META_WIZARD_STEP = 'mrt_feedback_wizard_step';
const MRT_FEEDBACK_META_CONTEXT = 'mrt_feedback_context';
const MRT_FEEDBACK_META_STATUS = 'mrt_feedback_status';
const MRT_FEEDBACK_STATUS_NEW = 'new';

/**
 * @return array<int, string>
 */
function MRT_feedback_allowed_statuses(): array {
	return array( 'new', 'read', 'resolved', 'dismissed' );
}

/**
 * @return array<int, string>
 */
function MRT_feedback_allowed_types(): array {
	return array( 'bug', 'suggestion' );
}

/**
 * @param array<string, mixed> $input Raw feedback input.
 * @return array<string, mixed>|WP_Error
 */
function MRT_feedback_sanitize_input( array $input ) {
	$type    = MRT_feedback_sanitize_choice( $input['type'] ?? '', MRT_feedback_allowed_types(), 'bug' );
	$message = sanitize_textarea_field( (string) ( $input['message'] ?? '' ) );
	if ( strlen( $message ) < 10 ) {
		return new WP_Error( 'mrt_feedback_message_short', __( 'Beskrivningen är för kort.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	if ( strlen( $message ) > 2000 ) {
		return new WP_Error( 'mrt_feedback_message_long', __( 'Beskrivningen är för lång.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}

	return array(
		'type'        => $type,
		'message'     => $message,
		'email'       => MRT_feedback_sanitize_email( $input['email'] ?? '' ),
		'page_url'    => esc_url_raw( (string) ( $input['pageUrl'] ?? $input['page_url'] ?? '' ) ),
		'wizard_step' => sanitize_key( (string) ( $input['wizardStep'] ?? $input['wizard_step'] ?? '' ) ),
		'context'     => MRT_feedback_sanitize_context( $input['context'] ?? array() ),
	);
}

/**
 * @param mixed $value Raw e-mail.
 */
function MRT_feedback_sanitize_email( $value ): string {
	$email = function_exists( 'sanitize_email' ) ? sanitize_email( (string) $value ) : sanitize_text_field( (string) $value );
	if ( $email === '' ) {
		return '';
	}
	return function_exists( 'is_email' ) && ! is_email( $email ) ? '' : $email;
}

/**
 * @param mixed              $value   Raw value.
 * @param array<int, string> $allowed Allowed values.
 */
function MRT_feedback_sanitize_choice( $value, array $allowed, string $fallback ): string {
	$choice = sanitize_key( (string) $value );
	return in_array( $choice, $allowed, true ) ? $choice : $fallback;
}

/**
 * @param mixed $context Raw context.
 * @return array<string, mixed>
 */
function MRT_feedback_sanitize_context( $context ): array {
	if ( ! is_array( $context ) ) {
		return array();
	}
	$allowed = array( 'fromStationId', 'toStationId', 'date', 'tripType' );
	$out     = array();
	foreach ( $allowed as $key ) {
		if ( isset( $context[ $key ] ) ) {
			$out[ $key ] = sanitize_text_field( (string) $context[ $key ] );
		}
	}
	return $out;
}

/**
 * @param array<string, mixed> $input Raw feedback input.
 * @return array{id:int}|WP_Error
 */
function MRT_feedback_create( array $input ) {
	$clean = MRT_feedback_sanitize_input( $input );
	if ( is_wp_error( $clean ) ) {
		return $clean;
	}
	$post_id = wp_insert_post( MRT_feedback_post_data( $clean ), true );
	if ( is_wp_error( $post_id ) || (int) $post_id <= 0 ) {
		return new WP_Error( 'mrt_feedback_save_failed', __( 'Kunde inte spara feedback.', 'museum-railway-timetable' ), array( 'status' => 500 ) );
	}
	MRT_feedback_save_meta( (int) $post_id, $clean );
	return array( 'id' => (int) $post_id );
}

/**
 * @param array<string, mixed> $clean Sanitized feedback.
 * @return array<string, mixed>
 */
function MRT_feedback_post_data( array $clean ): array {
	$title = sprintf( '%s — %s', $clean['type'] === 'bug' ? 'Fel' : 'Förslag', current_time( 'mysql' ) );
	return array(
		'post_type'    => MRT_POST_TYPE_FEEDBACK,
		'post_status'  => 'private',
		'post_title'   => $title,
		'post_content' => (string) $clean['message'],
	);
}

/**
 * @param array<string, mixed> $clean Sanitized feedback.
 */
function MRT_feedback_save_meta( int $post_id, array $clean ): void {
	update_post_meta( $post_id, MRT_FEEDBACK_META_TYPE, (string) $clean['type'] );
	update_post_meta( $post_id, MRT_FEEDBACK_META_EMAIL, (string) $clean['email'] );
	update_post_meta( $post_id, MRT_FEEDBACK_META_PAGE_URL, (string) $clean['page_url'] );
	update_post_meta( $post_id, MRT_FEEDBACK_META_WIZARD_STEP, (string) $clean['wizard_step'] );
	update_post_meta( $post_id, MRT_FEEDBACK_META_STATUS, MRT_FEEDBACK_STATUS_NEW );
	update_post_meta( $post_id, MRT_FEEDBACK_META_CONTEXT, wp_json_encode( $clean['context'] ) );
}

/**
 * @return array<int, array<string, mixed>>
 */
function MRT_feedback_list( int $limit = 50 ): array {
	$ids = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_FEEDBACK,
			'post_status'    => 'private',
			'posts_per_page' => max( 1, min( 100, $limit ) ),
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	return array_map( 'MRT_feedback_format_item', array_map( 'intval', $ids ) );
}

/**
 * @return array<string, mixed>
 */
function MRT_feedback_format_item( int $post_id ): array {
	$post = get_post( $post_id );
	return array(
		'id'          => $post_id,
		'title'       => $post ? (string) $post->post_title : '',
		'message'     => $post ? (string) ( $post->post_content ?? '' ) : '',
		'type'        => (string) get_post_meta( $post_id, MRT_FEEDBACK_META_TYPE, true ),
		'email'       => (string) get_post_meta( $post_id, MRT_FEEDBACK_META_EMAIL, true ),
		'page_url'    => (string) get_post_meta( $post_id, MRT_FEEDBACK_META_PAGE_URL, true ),
		'wizard_step' => (string) get_post_meta( $post_id, MRT_FEEDBACK_META_WIZARD_STEP, true ),
		'status'      => (string) get_post_meta( $post_id, MRT_FEEDBACK_META_STATUS, true ),
		'context'     => MRT_feedback_decode_context( (string) get_post_meta( $post_id, MRT_FEEDBACK_META_CONTEXT, true ) ),
	);
}

/**
 * @return array<string, mixed>
 */
function MRT_feedback_decode_context( string $json ): array {
	$decoded = $json !== '' ? json_decode( $json, true ) : array();
	return is_array( $decoded ) ? $decoded : array();
}

/**
 * @return array<string, mixed>|WP_Error
 */
function MRT_feedback_update_status( int $post_id, string $status ) {
	if ( $post_id <= 0 || get_post( $post_id ) === null ) {
		return new WP_Error( 'mrt_feedback_not_found', __( 'Feedback hittades inte.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	$clean = MRT_feedback_sanitize_choice( $status, MRT_feedback_allowed_statuses(), '' );
	if ( $clean === '' ) {
		return new WP_Error( 'mrt_feedback_status', __( 'Ogiltig status.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	update_post_meta( $post_id, MRT_FEEDBACK_META_STATUS, $clean );
	return MRT_feedback_format_item( $post_id );
}
