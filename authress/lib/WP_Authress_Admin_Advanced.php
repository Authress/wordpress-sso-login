<?php
/**
 * Contains class WP_Authress_Admin_Advanced.
 *
 * @package WP-Authress
 *
 * @since 2.0.0
 */

/**
 * Class WP_Authress_Admin_Advanced.
 * All setting fields and validations for wp-admin > Authress > Settings > Advanced tab.
 */
class WP_Authress_Admin_Advanced extends WP_Authress_Admin_Generic {

	/**
	 * AJAX nonce action for the rotate token endpoint.
	 *
	 * @see wp_authress_ajax_rotate_migration_token()
	 */
	const ROTATE_TOKEN_NONCE_ACTION = 'authress_rotate_migration_token';

	/**
	 * WP_Authress_Routes instance.
	 *
	 * @var WP_Authress_Routes
	 */
	protected $router;

	/**
	 * WP_Authress_Admin_Advanced constructor.
	 *
	 * @param WP_Authress_Options $options - WP_Authress_Options instance.
	 * @param WP_Authress_Routes  $router - WP_Authress_Routes instance.
	 */
	public function __construct( WP_Authress_Options $options, WP_Authress_Routes $router ) {
		parent::__construct( $options );
		$this->router                = $router;
	}

	/**
	 * All settings in the Advanced tab
	 *
	 * @see \WP_Authress_Admin::init_admin
	 * @see \WP_Authress_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = [
			[
				'name'     => __( 'Require Verified Email', 'wp-authress' ),
				'opt'      => 'requires_verified_email',
				'id'       => 'wp_authress_verified_email',
				'function' => 'render_verified_email',
			],
			[
				'name'     => __( 'Skip Strategies', 'wp-authress' ),
				'opt'      => 'skip_strategies',
				'id'       => 'wp_authress_skip_strategies',
				'function' => 'render_skip_strategies',
			],
			[
				'name'     => __( 'Remember User Session', 'wp-authress' ),
				'opt'      => 'remember_users_session',
				'id'       => 'wp_authress_remember_users_session',
				'function' => 'render_remember_users_session',
			],
			[
				'name'     => __( 'Login Redirection URL', 'wp-authress' ),
				'opt'      => 'default_login_redirection',
				'id'       => 'wp_authress_default_login_redirection',
				'function' => 'render_default_login_redirection',
			],
			[
				'name'     => __( 'Force HTTPS Callback', 'wp-authress' ),
				'opt'      => 'force_https_callback',
				'id'       => 'wp_authress_force_https_callback',
				'function' => 'render_force_https_callback',
			],
			[
				'name'     => __( 'Auto Provisioning', 'wp-authress' ),
				'opt'      => 'auto_provisioning',
				'id'       => 'wp_authress_auto_provisioning',
				'function' => 'render_auto_provisioning',
			],
			[
				'name'     => __( 'User Migration Endpoints', 'wp-authress' ),
				'opt'      => 'migration_ws',
				'id'       => 'wp_authress_migration_ws',
				'function' => 'render_migration_ws',
			],
			[
				'name'     => __( 'Migration IPs Whitelist', 'wp-authress' ),
				'opt'      => 'migration_ips_filter',
				'id'       => 'wp_authress_migration_ws_ips_filter',
				'function' => 'render_migration_ws_ips_filter',
			],
			[
				'name'     => '',
				'opt'      => 'migration_ips',
				'id'       => 'wp_authress_migration_ws_ips',
				'function' => 'render_migration_ws_ips',
			],
			[
				'name'     => __( 'Valid Proxy IP', 'wp-authress' ),
				'opt'      => 'valid_proxy_ip',
				'id'       => 'wp_authress_valid_proxy_ip',
				'function' => 'render_valid_proxy_ip',
			],
			[
				'name'     => __( 'Authress Server Domain', 'wp-authress' ),
				'opt'      => 'authress_server_domain',
				'id'       => 'wp_authress_authress_server_domain',
				'function' => 'render_authress_server_domain',
			],
		];

		$this->init_option_section( '', 'advanced', $options );
	}

	/**
	 * Render form field and description for the `requires_verified_email` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_verified_email( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wp_authress_skip_strategies' );
		$this->render_field_description(
			__( 'Require new users to both provide and verify their email before logging in. ', 'wp-authress' ) .
			__( 'An email address is verified manually by an email from Authress or automatically by the provider. ', 'wp-authress' ) .
			__( 'This will disallow logins from social connections that do not provide email (like Twitter)', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `skip_strategies` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 *
	 * @since 3.8.0
	 */
	public function render_skip_strategies( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'], 'text', 'e.g. "twitter,ldap"' );
		$this->render_field_description(
			__( 'Enter one or more strategies, separated by commas, to skip email verification. ', 'wp-authress' ) .
			__( 'You can find the strategy under the "Connection Name" field in the Authress dashboard. ', 'wp-authress' ) .
			__( 'Leave this field blank to require email for all strategies. ', 'wp-authress' ) .
			__( 'This could introduce a security risk and should be used sparingly, if at all', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `remember_users_session` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_remember_users_session( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'A user session by default is kept for two days. ', 'wp-authress' ) .
			__( 'Enabling this setting will extend that and make the session be kept for 14 days', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `default_login_redirection` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_default_login_redirection( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'URL where successfully logged-in users are redirected when using the wp-login.php page. ', 'wp-authress' ) .
			__( 'This can be overridden with the <code>redirect_to</code> URL parameter', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `force_https_callback` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_force_https_callback( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Forces the plugin to use HTTPS for the callback URL when a site supports both; ', 'wp-authress' ) .
			__( 'if disabled, the protocol from the WordPress home URL will be used', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `auto_provisioning` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_auto_provisioning( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Create new users in the WordPress database when signups are off. ', 'wp-authress' ) .
			__( 'Signups will not be allowed but successful Authress logins will add the user in WordPress', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `migration_ws` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_migration_ws( $args = [] ) {
		$value = $this->options->get( $args['opt_name'] );
		$this->render_switch( $args['label_for'], $args['opt_name'] );

		if ( $value ) {
			$this->render_field_description(
				__( 'User migration endpoints activated. ', 'wp-authress' ) .
				__( 'See below for the token to use. ', 'wp-authress' ) .
				__( 'The custom database scripts need to be configured manually as described ', 'wp-authress' ) .
				$this->get_docs_link( 'cms/wordpress/user-migration' )
			);
			$this->render_field_description( 'Migration token:' );
			if ( $this->options->has_constant_val( 'migration_token' ) ) {
				$this->render_const_notice( 'migration_token' );
			}

			$migration_token = $this->options->get( 'migration_token' );
			printf(
				'<code class="code-block" id="authress_migration_token" disabled>%s</code><br>',
				$migration_token ? sanitize_text_field( $migration_token ) : __( 'No migration token', 'wp-authress' )
			);

			if ( ! $this->options->has_constant_val( 'migration_token' ) ) {
				printf(
					'<button id="%s" class="button button-secondary" data-confirm-msg="%s">%s</button>',
					esc_attr( self::ROTATE_TOKEN_NONCE_ACTION ),
					esc_attr(
						__( 'This will change your migration token immediately. ', 'wp-authress' ) .
						__( 'The new token must be changed in the custom scripts for your database Connection. ', 'wp-authress' ) .
						__( 'Continue?', 'wp-authress' )
					),
					__( 'Generate New Migration Token', 'wp-authress' )
				);
			}
		} else {
			$this->render_field_description(
				__( 'User migration endpoints deactivated. ', 'wp-authress' ) .
				__( 'Custom database connections can be deactivated in the ', 'wp-authress' ) .
				$this->get_dashboard_link( 'connections/database' )
			);
		}
	}

	/**
	 * Render form field and description for the `migration_ips_filter` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_migration_ws_ips_filter( $args = [] ) {
		$this->render_switch( $args['label_for'], $args['opt_name'], 'wp_authress_migration_ws_ips' );
	}

	/**
	 * Render form field and description for the `migration_ips` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_migration_ws_ips( $args = [] ) {
	}

	/**
	 * Render form field and description for the `valid_proxy_ip` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_valid_proxy_ip( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'Whitelist for proxy and load balancer IPs to enable logins and migration webservices', 'wp-authress' )
		);
	}

	/**
	 * Render form field and description for the `authress_server_domain` option.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @param array $args - callback args passed in from add_settings_field().
	 *
	 * @see WP_Authress_Admin_Generic::init_option_section()
	 * @see add_settings_field()
	 */
	public function render_authress_server_domain( $args = [] ) {
		$this->render_text_field( $args['label_for'], $args['opt_name'] );
		$this->render_field_description(
			__( 'The Authress domain used by the setup wizard to fetch your account information', 'wp-authress' )
		);
	}

	/**
	 * Validate all settings without a specific validation method.
	 *
	 * @param array $input - New option values to validate.
	 *
	 * @return array
	 */
	public function basic_validation( array $input ) {
		$input['requires_verified_email']   = $this->sanitize_switch_val( $input['requires_verified_email'] ?? null );
		$input['skip_strategies']           = $this->sanitize_text_val( $input['skip_strategies'] ?? null );
		$input['remember_users_session']    = $this->sanitize_switch_val( $input['remember_users_session'] ?? null );
		$input['default_login_redirection'] = $this->validate_login_redirect( $input['default_login_redirection'] ?? null );
		$input['force_https_callback']      = $this->sanitize_switch_val( $input['force_https_callback'] ?? null );
		$input['auto_provisioning']         = $this->sanitize_switch_val( $input['auto_provisioning'] ?? null );
		$input['migration_ips_filter'] = $this->sanitize_switch_val( $input['migration_ips_filter'] ?? null );
		$input['valid_proxy_ip']      = ( isset( $input['valid_proxy_ip'] ) ? $input['valid_proxy_ip'] : null );
		$input['authress_server_domain'] = $this->sanitize_text_val( $input['authress_server_domain'] ?? null );
		return $input;
	}

	/**
	 * Validate the URL used to redirect users after a successful login.
	 *
	 * @param string      $new_url - Options to save.
	 * @param string|null $existing_url - Value to fall back on if new value does not validate.
	 *
	 * @return string
	 */
	public function validate_login_redirect( $new_url, $existing_url = null ) {
		$new_redirect_url = esc_url_raw( strtolower( $new_url ) );
		$old_redirect_url = $existing_url ?? $this->options->get( 'default_login_redirection' );

		// No change so no validation needed.
		if ( $new_redirect_url === strtolower( $old_redirect_url ) ) {
			return $new_url;
		}

		$home_url = home_url();

		// Set the default redirection URL to be the homepage.
		if ( empty( $new_redirect_url ) ) {
			return $home_url;
		}

		// Allow subdomains within the same domain.
		$home_domain     = $this->get_domain( $home_url );
		$redirect_domain = $this->get_domain( $new_redirect_url );
		if ( $home_domain === $redirect_domain ) {
			return $new_url;
		}

		// If we get here, the redirect URL is a page outside of the WordPress install.
		$error = __( 'Advanced > "Login Redirection URL" cannot point to another site.', 'wp-authress' );
		$this->add_validation_error( $error );

		// Either revert to the previous (validated) value or set as the homepage.
		return ! empty( $old_redirect_url ) ? $old_redirect_url : $home_url;
	}

	/**
	 * Get the top-level domain for a URL.
	 *
	 * @param string $url - Valid URL to parse.
	 *
	 * @return mixed|string
	 */
	private function get_domain( $url ) {
		$host_pieces = explode( '.', wp_parse_url( $url, PHP_URL_HOST ) );
		$domain      = array_pop( $host_pieces );
		if ( count( $host_pieces ) ) {
			$domain = array_pop( $host_pieces ) . '.' . $domain;
		}
		return $domain;
	}
}
