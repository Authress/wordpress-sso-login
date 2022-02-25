<?php
/**
 * Contains Class WP_Authress_WooCommerceOverrides class.
 *
 * @package WP-Authress
 *
 * @since 2.0.0
 */

/**
 * Class WP_Authress_WooCommerceOverrides.
 */
class WP_Authress_WooCommerceOverrides {

	/**
	 * Injected WP_Authress_Options instance.
	 *
	 * @var WP_Authress_Options
	 */
	protected $options;

	/**
	 * WP_Authress_WooCommerceOverrides constructor.
	 *
	 * @param WP_Authress_Options $options - WP_Authress_Options instance.
	 */
	public function __construct( WP_Authress_Options $options ) {
		$this->options = $options;
	}

	/**
	 * Render the login form or link to ULP.
	 *
	 * @param string $redirect_page - Page slug to redirect to after logging in.
	 */
	private function render_login_form( $redirect_page ) {
		wp_authress_login_enqueue_scripts();
		if ( $this->options->get( 'auto_login', false ) ) {
			// Redirecting to WordPress login page.
			$redirect_url = get_permalink( wc_get_page_id( $redirect_page ) );
			$login_url    = wp_login_url( $redirect_url );

			printf( "<a class='button' href='%s'>%s</a>", $login_url, __( 'Login', 'wp-authress' ) );
		} else {
			echo wp_authress_render_lock_form( '' );
		}
	}

	/**
	 * Handle Authress login on the checkout form if the plugin is ready.
	 *
	 * @param string $html - Original HTML passed to filter.
	 *
	 * @return mixed
	 */
	public function override_woocommerce_checkout_login_form( $html ) {

		if ( ! wp_authress_is_ready() ) {
			return $html;
		}

		$this->render_login_form( 'checkout' );

		if ( wp_authress_can_show_wp_login_form() ) {
			echo '<style>.woocommerce-checkout .woocommerce-info{display:block;}</style>';
		}
	}

	/**
	 * Handle Authress login on the account form if the plugin is ready.
	 *
	 * @param string $html - Original HTML passed to filter.
	 *
	 * @return mixed
	 */
	public function override_woocommerce_login_form( $html ) {

		if ( ! wp_authress_is_ready() ) {
			return $html;
		}

		$this->render_login_form( 'myaccount' );
	}
}
