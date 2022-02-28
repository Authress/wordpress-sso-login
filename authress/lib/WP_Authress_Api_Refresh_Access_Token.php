<?php
/**
 * Contains WP_Authress_Api_Refresh_Access_Token.
 *
 * @package WP-Authress
 *
 * @since 4.0.0
 */

/**
 * Class WP_Authress_Api_Refresh_Access_Token
 * Get a new access token using the refresh token of a user.
 */
class WP_Authress_Api_Refresh_Access_Token extends WP_Authress_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = null;

	/**
	 * Make the API call and handle the response.
	 *
	 * @param string|null $access_key - Client ID to use.
	 * @param string|null $client_secret - Client Secret to use.
	 * @param string|null $refresh_token - Client's refresh token to use.
	 *
	 * @return string|null
	 */
	public function call( $access_key = null, $client_secret = null, $refresh_token = null ) {

		if ( empty( $refresh_token ) ) {
			return self::RETURN_ON_FAILURE;
		}

		$access_key = $access_key ?: $this->options->get( 'accessKey' );
		if ( empty( $access_key ) ) {
			return self::RETURN_ON_FAILURE;
		}

		$client_secret = $client_secret ?: $this->options->get( 'client_secret' );
		if ( empty( $client_secret ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->set_path( 'oauth/token' )
			->add_body( 'grant_type', 'refresh_token' )
			->add_body( 'accessKey', $access_key )
			->add_body( 'client_secret', $client_secret )
			->add_body( 'refresh_token', $refresh_token )
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
				__( 'Please check the Client ID and Client Secret saved in the Authress plugin settings. ', 'wp-authress' )
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
