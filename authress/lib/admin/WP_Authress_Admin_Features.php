<?php
/**
 * Contains WP_Authress_Admin_Features.
 *
 * @package WP-Authress
 *
 * @since 2.0.0
 */

/**
 * Class WP_Authress_Admin_Features.
 * Fields and validations for the Features settings tab.
 */
class WP_Authress_Admin_Features extends WP_Authress_Admin_Generic {

	/**
	 * All settings in the Features tab
	 *
	 * @see \WP_Authress_Admin::init_admin
	 * @see \WP_Authress_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = [
			[
				'name'     => __( 'Universal Login Page', 'wp-authress' ),
				'opt'      => 'auto_login',
				'id'       => 'wp_authress_auto_login',
				'function' => 'render_auto_login',
			],
			[
				'name'     => __( 'Auto Login Method', 'wp-authress' ),
				'opt'      => 'auto_login_method',
				'id'       => 'wp_authress_auto_login_method',
				'function' => 'render_auto_login_method',
			],
			[
				'name'     => __( 'Authress Logout', 'wp-authress' ),
				'opt'      => 'singlelogout',
				'id'       => 'wp_authress_singlelogout',
				'function' => 'render_singlelogout',
			],
			[
				'name'     => __( 'Override WordPress Avatars', 'wp-authress' ),
				'opt'      => 'override_wp_avatars',
				'id'       => 'wp_authress_override_wp_avatars',
				'function' => 'render_override_wp_avatars',
			],
		];

		$this->init_option_section( '', 'features', $options );
	}

	/**
	 * Render form field and description for the `singlelogout` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_singlelogout( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Turning this on will log users out of Authress when they log out of WordPress.', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `auto_login` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_auto_login( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wp_authress_auto_login_method' );
		$this->render_field_description(
			__( 'Use the Universal Login Page (ULP) for authentication and SSO. ', 'wp-authress' ) .
			__( 'When turned on, <code>wp-login.php</code> will redirect to the hosted login page. ', 'wp-authress' ) .
			__( 'When turned off, <code>wp-login.php</code> will show an embedded login form. ', 'wp-authress' ) .
			$this->get_docs_link( 'guides/login/universal-vs-embedded', __( 'More on ULP vs embedded here', 'wp-authress' ) )
		);
	}

	/**
	 * Render form field and description for the `auto_login_method` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_auto_login_method( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Enter a name here to automatically use a single, specific connection to login . ', 'wp-authress' ) .
			sprintf(
				// translators: Placeholder is an HTML link to the Authress dashboard.
				__( 'Find the method name to use under Connections > [Connection Type] in your %s. ', 'wp-authress' ),
				$this->get_dashboard_link()
			) .
			__( 'Click the expand icon and use the value in the "Name" field (like "google-oauth2")', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `override_wp_avatars` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_override_wp_avatars( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Overrides the WordPress avatar with the Authress profile avatar', 'wp-authress' )
		);
	}

	/**
	 * Validation for Basic settings tab.
	 *
	 * @param array $input - New options being saved.
	 *
	 * @return array
	 */
	public function basic_validation( array $input ) {
		$input['auto_login']          = $this->sanitize_switch_val( $input['auto_login'] ?? null );
		$input['auto_login_method']   = $this->sanitize_text_val( $input['auto_login_method'] ?? null );
		$input['singlelogout']        = $this->sanitize_switch_val( $input['singlelogout'] ?? null );
		$input['override_wp_avatars'] = $this->sanitize_switch_val( $input['override_wp_avatars'] ?? null );
		return $input;
	}
}
