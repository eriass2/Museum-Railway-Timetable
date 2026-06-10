<?php
/**
 * Wizard feedback REST/domain tests.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}
if ( ! defined( 'MRT_POST_TYPE_FEEDBACK' ) ) {
	define( 'MRT_POST_TYPE_FEEDBACK', 'mrt_feedback' );
}

require_once ABSPATH . 'inc/infrastructure/rest/shared/permissions.php';
require_once ABSPATH . 'inc/domain/feedback/wizard-feedback.php';
require_once ABSPATH . 'inc/infrastructure/rest/public/wizard-feedback.php';

final class WizardFeedbackTest extends TestCase {
	protected function tearDown(): void {
		unset(
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_next_post_id'],
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_transients']
		);
		parent::tearDown();
	}

	public function test_create_feedback_saves_private_post_and_meta(): void {
		$request = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/wizard/feedback' );
		$request->set_json_params(
			array(
				'type'       => 'bug',
				'message'    => 'Priset verkar inte stämma i sammanfattningen.',
				'email'      => 'test@example.com',
				'pageUrl'    => 'https://example.test/wizard',
				'wizardStep' => 'summary',
				'context'    => array( 'tripType' => 'return' ),
			)
		);

		$response = MRT_rest_wizard_feedback_create_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;
		$post_id  = (int) $data['id'];

		self::assertTrue( $data['saved'] );
		self::assertSame( MRT_POST_TYPE_FEEDBACK, $GLOBALS['mrt_test_posts'][ $post_id ]->post_type );
		self::assertSame( 'private', $GLOBALS['mrt_test_posts'][ $post_id ]->post_status );
		self::assertSame( 'bug', get_post_meta( $post_id, MRT_FEEDBACK_META_TYPE, true ) );
		self::assertSame( 'summary', get_post_meta( $post_id, MRT_FEEDBACK_META_WIZARD_STEP, true ) );
		self::assertSame( MRT_FEEDBACK_STATUS_NEW, get_post_meta( $post_id, MRT_FEEDBACK_META_STATUS, true ) );
	}

	public function test_create_feedback_rejects_short_message(): void {
		$result = MRT_feedback_create( array( 'message' => 'kort' ) );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_feedback_message_short', $result->get_error_code() );
	}

	public function test_honeypot_returns_saved_without_post(): void {
		$request = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/wizard/feedback' );
		$request->set_json_params(
			array(
				'message' => 'Denna rapport ska fastna i honeypot.',
				'website' => 'bot.example',
			)
		);

		$response = MRT_rest_wizard_feedback_create_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertTrue( $data['saved'] );
		self::assertArrayNotHasKey( 'mrt_test_posts', $GLOBALS );
	}

	public function test_list_and_update_feedback_status(): void {
		$created = MRT_feedback_create(
			array(
				'type'    => 'suggestion',
				'message' => 'Visa gärna tydligare hjälptext i datumsteget.',
			)
		);
		$post_id = (int) $created['id'];
		$GLOBALS['mrt_test_get_posts'] = static fn (): array => array( $post_id );

		$list = MRT_feedback_list();
		self::assertCount( 1, $list );
		self::assertSame( 'suggestion', $list[0]['type'] );

		$updated = MRT_feedback_update_status( $post_id, 'resolved' );
		self::assertSame( 'resolved', $updated['status'] );
	}

	public function test_rate_limit_blocks_after_five_reports(): void {
		$request = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/wizard/feedback' );
		for ( $i = 0; $i < 5; $i++ ) {
			self::assertTrue( MRT_rest_wizard_feedback_rate_limited( $request ) );
		}

		$result = MRT_rest_wizard_feedback_rate_limited( $request );
		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 429, $result->get_error_data()['status'] ?? 0 );
	}
}
