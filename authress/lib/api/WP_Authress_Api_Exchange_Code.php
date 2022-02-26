<?php
/**
 * Contains WP_Authress_Api_Exchange_Code.
 *
 * @package WP-Authress
 *
 * @since 3.11.0
 */

/**
 * Class WP_Authress_Api_Exchange_Code
 * Exchange an authorization code for tokens.
 *
 * @see https://authress.com/docs/api/authentication#authorization-code-flow
 */
class WP_Authress_Api_Exchange_Code extends WP_Authress_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = null;

	/**
	 * Make the API call and handle the response.
	 *
	 * @param string|null $code - Authorization code to exchange for tokens.
	 * @param string|null $access_key - Client ID to use.
	 * @param string|null $redirect_uri - Redirect URI to use.
	 *
	 * @return string|null
	 */
	public function call( $code = null, $access_key = null, $redirect_uri = null ) {

		if ( empty( $code ) ) {
			return self::RETURN_ON_FAILURE;
		}

		$access_key = $access_key ?: $this->options->get( 'access_key' );
		if ( empty( $access_key ) ) {
			return self::RETURN_ON_FAILURE;
		}

		$client_secret = $this->options->get( 'client_secret' ) ?: '';
		$redirect_uri  = $redirect_uri ?: $this->options->get_wp_authress_url();

		return $this
			->set_path( 'oauth/token' )
			->add_body( 'grant_type', 'authorization_code' )
			->add_body( 'code', $code )
			->add_body( 'redirect_uri', $redirect_uri )
			->add_body( 'access_key', $access_key )
			->add_body( 'client_secret', $client_secret )
			->post()
			->handle_response( __METHOD__ );
	}

	/**
	 * Handle API response.
	 *
	 * @param string $method - Method that called the API.
	 *
	 * @return string|null
	 */
	protected function handle_response( $method ) {

		if ( 401 == $this->response_code ) {
			WP_Authress_ErrorLog::insert_error(
				__METHOD__ . ' L:' . __LINE__,
				__( 'An /oauth/token call triggered a 401 response from Authress. ', 'wp-authress' ) .
				__( 'Please check the Client Secret saved in the Authress plugin settings. ', 'wp-authress' )
			);
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_wp_error( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_failed_response( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this->response_body;
	}
}
