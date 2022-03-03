<?php

require __DIR__ . '/WP_Authress_Settings_Configuration.php';

class WP_Authress_Admin {

	const OPT_SECTIONS = [ 'basic', 'features', 'appearance', 'advanced' ];

	protected $a0_options;

	protected $router;

	protected $sections = [];

	public function __construct( WP_Authress_Options $a0_options, WP_Authress_Routes $router ) {
		$this->a0_options = $a0_options;
		$this->router     = $router;

		$this->sections = [
			'basic'      => new WP_Authress_Settings_Configuration( $this->a0_options )
		];
	}

	/**
	 * Enqueue scripts for all Authress wp-admin pages
	 */
	public function admin_enqueue() {
		// Nonce is not needed here as this is not processing form data.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		// Register admin styles
		wp_register_style( 'wp_authress_admin_initial_setup', WP_AUTHRESS_PLUGIN_CSS_URL . 'initial-setup.css', false, WP_AUTHRESS_VERSION );

		// Register admin scripts
		wp_register_script( 'wp_authress_async', WP_AUTHRESS_PLUGIN_LIB_URL . 'async.min.js', false, WP_AUTHRESS_VERSION, false);
		wp_register_script( 'wp_authress_admin', WP_AUTHRESS_PLUGIN_JS_URL . 'admin.js', [ 'jquery' ], WP_AUTHRESS_VERSION, false);
		wp_localize_script(
			'wp_authress_admin',
			'wp_authress',
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
		$wp_authress_curr_page = sanitize_text_field(!empty( $_REQUEST['page'] ) ? wp_unslash( $_REQUEST['page'] ) : '');
		$wp_authress_pages     = [ 'authress', 'authress_configuration', 'authress_errors' ];
		if ( ! in_array( $wp_authress_curr_page, $wp_authress_pages, true) ) {
			return false;
		}

		wp_enqueue_script( 'wp_authress_admin' );
		wp_enqueue_script( 'wp_authress_async' );

		if ( 'wp_authress' === $wp_authress_curr_page ) {
			wp_enqueue_media();
			wp_enqueue_style( 'media' );
		}

		wp_enqueue_style( 'wp_authress_admin_initial_setup' );
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

		// Look for custom settings fields.
		$custom_opts = [];
		foreach ( self::OPT_SECTIONS as $section ) {
			$custom_opts = array_merge( $custom_opts, apply_filters( 'authress_settings_fields', [], $section ) );
		}

		// Merge in any custom setting option keys.
		foreach ( $custom_opts as $custom_opt ) {
			if ( $custom_opt && $custom_opt['opt'] ) {
				$option_keys[] = $custom_opt['opt'];
			}
		}

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
		include WP_AUTHRESS_PLUGIN_DIR . 'templates/settings.php';
	}
}
