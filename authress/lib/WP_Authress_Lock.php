<?php

class WP_Authress_Lock {

	const LOCK_GLOBAL_JS_VAR_NAME = 'wpAuthressLockGlobal';

	protected $wp_options;
	protected $extended_settings;
	protected $signup_mode = false;

	/**
	 * WP_Authress_Lock_Options constructor.
	 *
	 * @param array                 $extended_settings Argument in renderAuthressForm(), used by shortcode and widget.
	 * @param null|WP_Authress_Options $opts WP_Authress_Options instance.
	 */
	public function __construct( $extended_settings = [], $opts = null ) {
		$this->wp_options        = ! empty( $opts ) ? $opts : WP_Authress_Options::Instance();
		$this->extended_settings = $extended_settings;
	}

	public function get_state_obj( $redirect_to = null ) {
		// Nonce is not needed here as this is not processing form data.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		$stateObj = [
			'interim' => ( isset( $_GET['interim-login'] ) && $_GET['interim-login'] === 1 ),
			'nonce'   => WP_Authress_State_Handler::get_instance()->get_unique(),
		];

		if ( ! empty( $redirect_to ) ) {
			$stateObj['redirect_to'] = addslashes( $redirect_to );
		} elseif ( isset( $_GET['redirect_to'] ) ) {
			$stateObj['redirect_to'] = addslashes( sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) );
		}

		return base64_encode( json_encode( $stateObj ) );

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	protected function _is_valid( $array, $key ) {
		return isset( $array[ $key ] ) && trim( $array[ $key ] ) !== '';
	}

	/**
	 * Render a link at the bottom of a WordPress core login form back to Lock.
	 */
	public static function render_back_to_lock() {
		$title = wp_authress_get_option( 'form_title' );
		if ( empty( $title ) ) {
			$title = 'SSO Login';
		}

		printf('<div id="extra-options"><a href="?">%s</a></div>', sanitize_text_field( sprintf( __( '← Back to %s', 'wp-authress' ), $title)));
	}

	/**
	 * Render the Lock form with saved and passed options.
	 *
	 * @param bool  $canShowLegacyLogin - Is the legacy login form allowed? Only on wp-login.php.
	 * @param array $specialSettings - Additional settings from widget or shortcode.
	 */
	public static function render( $canShowLegacyLogin = true, $specialSettings = [] ) {
		if ( is_user_logged_in() ) {
			return;
		}

		if ( $canShowLegacyLogin && wp_authress_can_show_wp_login_form() ) {
			add_action( 'login_footer', [ 'WP_Authress_Lock', 'render_back_to_lock' ] );
			return;
		}

		$login_tpl = WP_AUTHRESS_PLUGIN_DIR . 'templates/authress-login-form.php';
		$login_tpl = apply_filters( 'authress_login_form_tpl', $login_tpl);
		require $login_tpl;
	}
}
