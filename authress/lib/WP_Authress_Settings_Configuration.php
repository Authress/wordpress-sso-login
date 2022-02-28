<?php
/**
 * Contains WP_Authress_Settings_Configuration.
 *
 * @package WP-Authress
 *
 * @since 2.0.0
 */

/**
 * Class WP_Authress_Settings_Configuration.
 * Fields and validations for the Basic settings tab.
 */
class WP_Authress_Settings_Configuration extends WP_Authress_Admin_Generic {

	/**
	 * All settings in the Basic tab
	 *
	 * @see \WP_Authress_Admin::init_admin
	 * @see \WP_Authress_Admin_Generic::init_option_section
	 */
	public function init() {

		$options = [
			[
				'name'     => __( 'Custom Domain', 'wp-authress' ),
				'opt'      => 'customDomain',
				'id'       => 'wp_authress_custom_domain',
				'function' => 'render_custom_domain',
			],
			[
				'name'     => __( 'API Access Key', 'wp-authress' ),
				'opt'      => 'accessKey',
				'id'       => 'wp_authress_access_key',
				'function' => 'render_access_key',
			],
			[
				'name'     => __( 'Application ID', 'wp-authress' ),
				'opt'      => 'applicationId',
				'id'       => 'wp_authress_application_id',
				'function' => 'render_application_id',
			]
			// [
			// 	'name'     => __( 'Original Login Form on wp-login.php', 'wp-authress' ),
			// 	'opt'      => 'wordpress_login_enabled',
			// 	'id'       => 'wp_authress_login_enabled',
			// 	'function' => 'render_allow_wordpress_login',
			// ],
		];
		$this->init_option_section( '', 'basic', $options );
	}

	/**
	 * Render form field and description for the `domain` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_domain( $args = [] ) {

		$style = $this->options->get( $args['opt_name'] ) ? '' : self::ERROR_FIELD_STYLE;
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', '', $style );
		$this->render_field_description(
			__( 'Authress Domain, found in your Application settings in the ', 'wp-authress' ) .
			$this->get_dashboard_link( 'applications' )
		);
	}

	/**
	 * Render form field and description for the `custom_domain` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 *
	 * @since 3.7.0
	 */
	public function render_custom_domain( $args = [] ) {
		$style = $this->options->get( $args['opt_name'] ) ? '' : self::ERROR_FIELD_STYLE;
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', '', $style );
		$this->render_field_description(__( 'Your custom domain host url, found in the domain settings in the ', 'wp-authress' ) . $this->get_dashboard_link( 'domains' ));
	}

	/**
	 * Render form field and description for the `access_key` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_access_key( $args = [] ) {
		$style = $this->options->get( $args['opt_name'] ) ? '' : self::ERROR_FIELD_STYLE;
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'password', '', $style );
		$this->render_field_description(__( 'Authress Service Client Access Key, found in the service client settings in the ', 'wp-authress' ) . $this->get_dashboard_link( 'clients' ));
	}

	/**
	 * Render form field and description for the `organization` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_application_id( $args = [] ) {
		$style = $this->options->get( $args['opt_name'] ) ? '' : self::ERROR_FIELD_STYLE;
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', '', $style );
		$this->render_field_description(__( 'Identifier for this wordpress deployment, found in your Application settings in the ', 'wp-authress' ) . $this->get_dashboard_link( 'applications' ));
	}

	/**
	 * Render form field and description for the `cache_expiration` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_cache_expiration( $args = [] ) {

		$this->render_text_field( $args['label_for'], $args['opt_name'], 'number' );
		printf(
			' <button id="authress_delete_cache_transient" class="button button-secondary">%s</button>',
			__( 'Delete Cache', 'wp-authress' )
		);
		$this->render_field_description( __( 'JWKS cache expiration in minutes (use 0 for no caching)', 'wp-authress' ) );
		$domain = $this->options->get( 'domain' );
		if ( $domain ) {
			$this->render_field_description(
				sprintf(
					'<a href="https://%s/.well-known/jwks.json" target="_blank">%s</a>',
					$domain,
					__( 'View your JWKS here', 'wp-authress' )
				)
			);
		}
	}

	/**
	 * Render form field and description for the `wordpress_login_enabled` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_allow_wordpress_login( $args = [] ) {

		$isset_desc = sprintf(
			'<code class="code-block"><a href="%s?wle" target="_blank">%s?wle</a></code>',
			wp_login_url(),
			wp_login_url()
		);
		$code_desc  = '<code class="code-block">' . __( 'Save settings to generate URL.', 'wp-authress' ) . '</code>';
		$wle_code   = $this->options->get( 'wle_code' );
		if ( $wle_code ) {
			$code_desc = str_replace( '?wle', '?wle=' . $wle_code, $isset_desc );
		}
		$buttons = [
			[
				'label' => __( 'Never', 'wp-authress' ),
				'value' => 'no',
			],
			[
				'label' => __( 'Via a link under the Authress form', 'wp-authress' ),
				'value' => 'link',
				'desc'  => __( 'URL is the same as below', 'wp-authress' ),
			],
			[
				'label' => __( 'When "wle" query parameter is present', 'wp-authress' ),
				'value' => 'isset',
				'desc'  => $isset_desc,
			],
			[
				'label' => __( 'When "wle" query parameter contains specific code', 'wp-authress' ),
				'value' => 'code',
				'desc'  => $code_desc,
			],
		];
		printf(
			'<div class="subelement"><span class="description">%s.</span></div><br>',
			__( 'Logins and signups using the original form will NOT be pushed to Authress', 'wp-authress' )
		);
		$this->render_radio_buttons(
			$buttons,
			$args['label_for'],
			$args['opt_name'],
			$this->options->get( $args['opt_name'] ),
			true
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
		$input['customDomain'] = $this->sanitize_text_val( $input['customDomain'] ?? null );
		if ( empty( $input['customDomain'] ) ) {
			$this->add_validation_error( __( 'You need to specify your Custom Domain', 'wp-authress' ) );
		}

		$input['accessKey'] = $this->sanitize_text_val( $input['accessKey'] ?? null );
		if ( __( '[REDACTED]', 'wp-authress' ) === $input['accessKey'] ) {
			// The field is loaded with "[REDACTED]" so if that value is saved, we keep the existing secret.
			$input['accessKey'] = $this->options->get( 'accessKey' );
		}
		if ( empty( $input['accessKey'] ) ) {
			$this->add_validation_error( __( 'You need to specify a API Access Key', 'wp-authress' ) );
		}

		$input['applicationId'] = $this->sanitize_text_val( $input['applicationId'] ?? null );
		if ( empty( $input['applicationId'] ) ) {
			$this->add_validation_error( __( 'You need to specify your Application Identifier', 'wp-authress' ) );
		}
		$input['cache_expiration'] = absint( $input['cache_expiration'] ?? 0 );

		// $wle = $input['wordpress_login_enabled'] ?? null;
		// if ( ! in_array( $wle, [ 'link', 'isset', 'code', 'no' ] ) ) {
		// 	$input['wordpress_login_enabled'] = $this->options->get_default( 'wordpress_login_enabled' );
		// }

		return $input;
	}
}
