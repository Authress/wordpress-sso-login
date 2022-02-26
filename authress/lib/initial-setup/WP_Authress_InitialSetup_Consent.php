<?php

class WP_Authress_InitialSetup_Consent {

	protected $domain;
	protected $access_token;
	protected $a0_options;
	protected $state;
	protected $hasInternetConnection = true;

	public function __construct( WP_Authress_Options $a0_options ) {
		$this->a0_options = $a0_options;
		$this->domain     = $this->a0_options->get( 'authress_server_domain' );
	}

	public function render( $step ) {
	}

	/**
	 * Used by both Setup Wizard installation flows.
	 * Called in self::callback() when returning from consent URL install.
	 *
	 * @param string $domain - Authress domain for the Application.
	 * @param string $access_token - Management API access token.
	 * @param bool   $hasInternetConnection - True if the installing site be reached by Authress, false if not.
	 */
	public function callback_with_token( $domain, $access_token, $hasInternetConnection = true ) {

		$this->a0_options->set( 'domain', $domain );
		$this->access_token          = $access_token;
		$this->hasInternetConnection = $hasInternetConnection;

		$name = get_authress_curatedBlogName();
		$this->consent_callback( $name );

	}

	public function callback() {
		$access_token = $this->exchange_code();

		if ( $access_token === null ) {
			wp_safe_redirect( admin_url( 'admin.php?page=authress_introduction&error=cant_exchange_token' ) );
			exit;
		}

		$app_domain = $this->parse_token_domain( $access_token );

		$this->callback_with_token( $app_domain, $access_token );
	}

	protected function parse_token_domain( $token ) {
		$parts   = explode( '.', $token );
		$payload = json_decode( wp_authress_url_base64_decode( $parts[1] ) );
		return trim( str_replace( [ '/api/v2', 'https://' ], '', $payload->aud ), ' /' );
	}

	public function exchange_code() {
		// Not processing form data, using a redirect from Authress.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		if ( ! isset( $_REQUEST['code'] ) ) {
			return null;
		}

		$exchange_api = new WP_Authress_Api_Exchange_Code( $this->a0_options, $this->domain );

		$exchange_resp_body = $exchange_api->call(
			// Validated above and only sent to the change signup API endpoint.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			wp_unslash( $_REQUEST['code'] ),
			WP_Authress_InitialSetup::get_setup_access_key(),
			WP_Authress_InitialSetup::get_setup_redirect_uri()
		);

		if ( ! $exchange_resp_body ) {
			return null;
		}

		$tokens = json_decode( $exchange_resp_body );
		return isset( $tokens->access_token ) ? $tokens->access_token : null;

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	/**
	 * Used by both Setup Wizard installation flows.
	 * Called by self::callback_with_token() to create a Client, Connection, and Client Grant.
	 *
	 * @param string $name - Client name to use.
	 */
	public function consent_callback( $name ) {

		$domain    = $this->a0_options->get( 'domain' );
		$access_key = trim( $this->a0_options->get( 'access_key' ) );

		/*
		 * Create Client
		 */

		$should_create_connection = false;

		if ( empty( $access_key ) ) {
			$should_create_connection = true;

			$client_response = WP_Authress_Api_Client::create_client( $domain, $this->access_token, $name );

			if ( $client_response === false ) {
				wp_safe_redirect( admin_url( 'admin.php?page=authress_introduction&error=cant_create_client' ) );
				exit;
			}

			$this->a0_options->set( 'access_key', $client_response->access_key );
			$this->a0_options->set( 'client_secret', $client_response->client_secret );

			$access_key = $client_response->access_key;
		}

		/*
		 * Create Connection
		 */

		$db_connection_name = 'DB-' . get_authress_curatedBlogName();
		if ( $should_create_connection ) {
			$connections = WP_Authress_Api_Client::search_connection( $domain, $this->access_token, null, $db_connection_name );
			if ( $connections && is_array( $connections ) && in_array( $access_key, $connections[0]->enabled_clients ) ) {
				$this->a0_options->set( 'db_connection_name', $db_connection_name );
				$should_create_connection = false;
			}
		}

		if ( $should_create_connection ) {
			$migration_token = $this->a0_options->get( 'migration_token' );
			if ( empty( $migration_token ) ) {
				$migration_token = wp_authress_generate_token();
			}
			$operations = new WP_Authress_Api_Operations( $this->a0_options );
			$operations->create_wordpress_connection(
				$this->access_token,
				$this->hasInternetConnection,
				'fair',
				$migration_token
			);

			$this->a0_options->set( 'migration_ws', $this->hasInternetConnection );
			$this->a0_options->set( 'migration_token', $migration_token );
			$this->a0_options->set( 'db_connection_name', $db_connection_name );
		}

		/*
		 * Create Client Grant
		 */

		$grant_response = WP_Authress_Api_Client::create_client_grant( $this->access_token, $access_key );

		if ( false === $grant_response ) {
			wp_safe_redirect( admin_url( 'admin.php?page=authress_introduction&error=cant_create_client_grant' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=authress_introduction&step=2' ) );
		exit;
	}
}
