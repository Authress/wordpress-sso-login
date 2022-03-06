<?php

require __DIR__ . '/Authress_Sso_Login_Settings_Configuration.php';

class Authress_Sso_Login_Admin {

	const OPT_SECTIONS = [ 'basic' ];

	protected $a0_options;

	protected $router;

	protected $sections = [];

	public function __construct( Authress_Sso_Login_Options $a0_options, Authress_Sso_Login_Routes $router ) {
		$this->a0_options = $a0_options;
		$this->router     = $router;

		$this->sections = [
			'basic'      => new Authress_Sso_Login_Settings_Configuration( $this->a0_options )
		];
	}

	/**
	 * Enqueue scripts for all Authress wp-admin pages
	 */
	public function admin_enqueue() {
		// Nonce is not needed here as this is not processing form data.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		// Register admin styles
		wp_register_style( 'authress_sso_login_admin_initial_setup', AUTHRESS_SSO_LOGIN_PLUGIN_CSS_URL . 'initial-setup.css', false, AUTHRESS_SSO_LOGIN_VERSION );

		// Register admin scripts
		wp_register_script( 'authress_sso_login_async', AUTHRESS_SSO_LOGIN_PLUGIN_LIB_URL . 'async.min.js', false, AUTHRESS_SSO_LOGIN_VERSION, false);
		wp_register_script( 'authress_sso_login_admin', AUTHRESS_SSO_LOGIN_PLUGIN_JS_URL . 'admin.js', [ 'jquery' ], AUTHRESS_SSO_LOGIN_VERSION, false);
		wp_localize_script(
			'authress_sso_login_admin',
			'authress_sso_login',
			[
				'media_title'             => __( 'Choose your icon', 'wp-authress' ),
				'media_button'            => __( 'Choose icon', 'wp-authress' ),
				'ajax_working'            => __( 'Working ...', 'wp-authress' ),
				'ajax_done'               => __( 'Done!', 'wp-authress' ),
				'refresh_prompt'          => __( 'Save or refresh this page to see changes.', 'wp-authress' ),
				'clear_cache_nonce'       => wp_create_nonce( 'authress_delete_cache_transient' ),
				'rotate_token_nonce'      => wp_create_nonce( 'authress_rotate_migration_token' ),
				'form_confirm_submit_msg' => __( 'Are you sure?', 'wp-authress' ),
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
			]
		);

		// Only checking the value, not processing.
		$authress_sso_login_curr_page = sanitize_text_field(!empty( $_REQUEST['page'] ) ? wp_unslash( $_REQUEST['page'] ) : '');
		$authress_sso_login_pages     = [ 'authress', 'authress_configuration', 'authress_errors' ];
		if ( ! in_array( $authress_sso_login_curr_page, $authress_sso_login_pages, true) ) {
			return false;
		}

		wp_enqueue_script( 'authress_sso_login_admin' );
		wp_enqueue_script( 'authress_sso_login_async' );

		if ( 'authress_sso_login' === $authress_sso_login_curr_page ) {
			wp_enqueue_media();
			wp_enqueue_style( 'media' );
		}

		wp_enqueue_style( 'authress_sso_login_admin_initial_setup' );
		return true;
	}

	public function init_admin() {

		foreach ( $this->sections as $section ) {
			$section->init();
		}

		register_setting(
			$this->a0_options->getConfigurationDatabaseName() . '_basic',
			$this->a0_options->getConfigurationDatabaseName(),
			[
				'sanitize_callback' => [ $this, 'input_validator' ],
			]
		);
	}

	/**
	 * Main validator for settings page inputs.
	 * Delegates validation to settings sections in self::init_admin().
	 *
	 * @param array $input - Incoming array of settings fields to validate.
	 *
	 * @return mixed
	 */
	public function input_validator( array $input ) {
		$constant_keys = $this->a0_options->get_all_constant_keys();

		// Look for and set constant overrides so validation is still possible.
		foreach ( $constant_keys as $key ) {
			$input[ $key ] = $this->a0_options->get_constant_val( $key );
		}

		$option_keys = $this->a0_options->get_defaults( true );

		// Remove unknown keys.
		foreach ( $input as $key => $val ) {
			if ( ! in_array( $key, $option_keys, true) ) {
				unset( $input[ $key ] );
			}
		}

		foreach ( $this->sections as $name => $section ) {
			$input = $section->input_validator( $input );
		}

		// Remove constant overrides so they are not saved to the database.
		foreach ( $constant_keys as $key ) {
			unset( $input[ $key ] );
		}

		return $input;
	}

	public function render_settings_page() {
		include AUTHRESS_SSO_LOGIN_PLUGIN_DIR . 'templates/settings.php';
	}
}
