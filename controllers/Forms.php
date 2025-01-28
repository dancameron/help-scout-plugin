<?php


/**
 * Help Scout API Controller
 *
 * @package Help_Scout_Desk
 * @subpackage Help
 */
class HSD_Forms extends HSD_Controller {
	const SUBMISSION_SUCCESS_QV = 'thread_success';
	const SUBMISSION_ERROR_QV = 'submission_error';
	const FORM_SHORTCODE = 'hsd_form';
	const FORM_SHORTCODE_DEP = 'hds_form'; // deprecated typo

	public static function init() {
		do_action( 'hsd_shortcode', self::FORM_SHORTCODE_DEP, array( __CLASS__, 'submission_form' ) );
		do_action( 'hsd_shortcode', self::FORM_SHORTCODE, array( __CLASS__, 'submission_form' ) );
		// process conversation form
		add_action( 'parse_request', array( __CLASS__, 'maybe_process_form' ) );

		// refresh data after submission
		add_filter( 'hsd_scripts_localization', array( __CLASS__, 'add_refresh_qv' ) );

		// add submission query vars
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
	}

	/**
	 * Add the submission query vars
	 * @param  array $vars
	 * @return array
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = self::SUBMISSION_SUCCESS_QV;
		$vars[] = self::SUBMISSION_ERROR_QV;
		$vars[] = 'conversation_id';

		return $vars;
	}

	/**
	 * Show the reply/creation form
	 * @param  array $atts
	 * @param  string $content used to show a message after a message is received.
	 * @return
	 */
	public static function submission_form( $atts, $content = '' ) {

		if ( ! HelpScout_API::is_customer() ) {
			do_action( 'helpscout_desk_sc_form_not_customer' );
			return;
		}

		if ( '' === $content ) {
			$content = sprintf(
				// translators: 1: Url to send another message.
				__( 'Thank you, message received. <a href="%1$s">Send another message</a>.', 'help-scout' ),
				remove_query_arg( self::SUBMISSION_SUCCESS_QV )
			);
		}

		// Don't show the form if not on the conversation view
		if ( ! empty( get_query_var( self::SUBMISSION_SUCCESS_QV ) ) && get_query_var( self::SUBMISSION_SUCCESS_QV ) ) {
			return self::load_view_to_string(
				'shortcodes/success_message',
				array(
					'message' => $content,
				),
				true
			);
		}
		$error = false;
		if ( ! empty( get_query_var( self::SUBMISSION_ERROR_QV ) ) && get_query_var( self::SUBMISSION_ERROR_QV ) ) {
			$error = urldecode( get_query_var( self::SUBMISSION_ERROR_QV ) );
		}

		$mailbox_id = ( isset( $atts['mid'] ) ) ? $atts['mid'] : HSD_Settings::get_mailbox();

		// Show the form
		wp_enqueue_script( 'hsd' );
		wp_enqueue_style( 'hsd' );
		return self::load_view_to_string(
			'shortcodes/conversation_form',
			array(
				'nonce'             => wp_create_nonce( HSD_Controller::NONCE ),
				'mid'               => $mailbox_id,
				'error'             => $error,
				'conversation_view' => ( ! empty( get_query_var( 'conversation_id' ) ) ),
			),
			true
		);
	}


	/**
	 * Maybe process the submission
	 * @return
	 */
	public static function maybe_process_form() {
		$nonce = isset( $_REQUEST['hsd_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hsd_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, HSD_Controller::NONCE ) ) {
			return;
		}

		if ( ! current_user_can( 'read' ) ) {
			return;
		}

		do_action( 'hsd_submission_form' );

		$error = false;
		if ( ! isset( $_POST['message'] ) || $_POST['message'] == '' ) {
			$error = __( 'Message Required.', 'help-scout' );
		}
		if ( ! isset( $_GET['conversation_id'] ) ) {
			if ( ! isset( $_POST['subject'] ) || $_POST['subject'] == '' ) {
				$error = __( 'Subject Required.', 'help-scout' );
			}
		}
		if ( ! $error ) {
			$success = self::process_form_submission( $_POST );
			if ( $success === true ) {
				$redirect_url = null;
				do_action( 'hsd_form_submitted_without_error', $success );
				wp_redirect( remove_query_arg( self::SUBMISSION_ERROR_QV, add_query_arg( self::SUBMISSION_SUCCESS_QV, true ), esc_url_raw( apply_filters( 'si_hsd_thread_submitted_error_redirect_url', $redirect_url ) ) ) );
				exit();
			}
		}
		$redirect_url = null;
		//do_action( 'hsd_form_submitted_with_error', $success );
		wp_redirect( remove_query_arg( self::SUBMISSION_SUCCESS_QV, add_query_arg( self::SUBMISSION_ERROR_QV, urlencode( __( 'Failed Submission', 'help-scout' ) ) ), esc_url_raw( apply_filters( 'si_hsd_thread_submitted_redirect_url', $redirect_url ) ) ) );
		exit();

	}

	/**
	 * Process the form submission
	 * @return
	 */
	public static function process_form_submission( $form_data ) {
		if ( ! wp_verify_nonce( $form_data['hsd_nonce'], HSD_Controller::NONCE ) ) {
			return;
		}

		$attachments = array();

		$attachment_data = array();
		if ( ! empty( $_FILES ) && isset( $_FILES['message_attachment'] ) ) {
			$attach_count = count(
				isset( $_FILES['message_attachment']['name'] ) ? $_FILES['message_attachment']['name'] : array()
			);
			for ( $n = 0; $n < $attach_count; $n++ ) {
				if ( ! empty( $_FILES['message_attachment']['tmp_name'][ $n ] ) ) {
					$attachment_data[] = array(
						'fileName' => isset( $_FILES['message_attachment']['name'][ $n ] ) ? sanitize_text_field( wp_unslash( $_FILES['message_attachment']['name'][ $n ] ) ) : '',
						'mimeType' => isset( $_FILES['message_attachment']['type'][ $n ] ) ? sanitize_mime_type( wp_unslash( $_FILES['message_attachment']['type'][ $n ] ) ) : '',
						'data' => base64_encode( wp_remote_get( sanitize_text_field( $_FILES['message_attachment']['tmp_name'][ $n ] ) ) ),
					);
				}
			}
		}

		if ( isset( $form_data['hsd_conversation_id'] ) && '' !== $form_data['hsd_conversation_id'] ) {
			do_action( 'hsd_form_submitted_to_create_thread' );
			$new_status = ( isset( $form_data['close_thread'] ) ) ? 'closed' : 'active' ;
			$new_thread = HelpScout_API::create_thread(
				isset( $_GET['conversation_id'] ) ? sanitize_text_field( wp_unslash( $_GET['conversation_id'] ) ) : '',
				stripslashes( $form_data['message'] ),
				$new_status,
				sanitize_text_field( wp_unslash( $form_data['mid'] ) ),
				$attachment_data
			);
		} else {
			do_action( 'hsd_form_submitted_to_create_conversation' );
			$new_thread = HelpScout_API::create_conversation( stripslashes( $form_data['subject'] ), stripslashes( $form_data['message'] ), esc_attr( $form_data['mid'], 'help-scout' ), $attachment_data );
		}

		return apply_filters( 'hsd_process_form_submission_new_thread', $new_thread );
	}

	/**
	 * Add the conversation id to the js object
	 * @param array $hsd_js_object
	 */
	public static function add_refresh_qv( $hsd_js_object ) {
		if ( ! wp_verify_nonce( $hsd_js_object['sec'], HSD_Controller::NONCE ) ) {
			return;
		}

		$hsd_js_object['refresh_data'] = 0;
		$hsd_js_object['current_page'] = max( 1, absint( get_query_var( 'page', 1 ) ) );
		$hsd_js_object['status'] = ( isset( $_REQUEST['status'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all';

		if ( isset( $_GET[ self::SUBMISSION_SUCCESS_QV ] ) && sanitize_text_field( wp_unslash( $_GET[ self::SUBMISSION_SUCCESS_QV ] ) ) ) {
			$hsd_js_object['refresh_data'] = 1;
		}
		return $hsd_js_object;
	}
}
