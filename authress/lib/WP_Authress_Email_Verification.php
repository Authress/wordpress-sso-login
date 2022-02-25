<?php
/**
 * Contains class WP_Authress_Email_Verification
 *
 * @package WP-Authress
 *
 * @since 3.5.0
 */

/**
 * Class WP_Authress_Email_Verification.
 */
class WP_Authress_Email_Verification {

	const RESEND_NONCE_ACTION = 'authress_resend_verification_email';

	/**
	 * Stop the login process and show email verification prompt.
	 *
	 * @param object $userinfo - User profile object returned from Authress.
	 */
	public static function render_die( $userinfo ) {
		$user_id = isset( $userinfo->user_id ) ? $userinfo->user_id : $userinfo->sub;

		$html = sprintf( '<p>%s</p>', __( 'This site requires a verified email address.', 'wp-authress' ) );

		// Only provide resend verification link for DB connection users.
		if ( 0 === strpos( $user_id, 'authress|' ) ) {
			$html .= sprintf(
				'<p><a id="js-a0-resend-verification" href="#">%s</a></p>
				<p><a href="%s?%d">%s</a></p>
				<script>var WPAuthressEmailVerification={ajaxUrl:"%s",sub:"%s",nonce:"%s",e_msg:"%s",s_msg:"%s"}</script>
				<script src="%s"></script>
				<script src="%s"></script>',
				__( 'Resend verification email.', 'wp-authress' ),
				wp_login_url(),
				time(),
				__( '← Login', 'wp-authress' ),
				esc_url( admin_url( 'admin-ajax.php' ) ),
				esc_js( $user_id ),
				esc_js( wp_create_nonce( self::RESEND_NONCE_ACTION ) ),
				esc_js( __( 'Something went wrong; please login and try again.', 'wp-authress' ) ),
				esc_js( __( 'Email successfully re-sent to ', 'wp-authress' ) . $userinfo->email ),
				'//code.jquery.com/jquery-1.12.4.js',
				WP_AUTHRESS_PLUGIN_URL . 'assets/js/die-with-verify-email.js?ver=' . WP_AUTHRESS_VERSION
			);
		}

		$html = apply_filters( 'authress_verify_email_page', $html, $userinfo, '' );
		wp_die( $html );
	}
}
