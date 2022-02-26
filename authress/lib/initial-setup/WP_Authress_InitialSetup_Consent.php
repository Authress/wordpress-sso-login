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
}
