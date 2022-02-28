<?php
/**
 * Contains WP_Authress_Api_Get_User.
 *
 * @package WP-Authress
 *
 * @since 3.11.0
 */

/**
 * Class WP_Authress_Api_Get_User
 * Get user information for an Authress user ID.
 */
class WP_Authress_Api_Get_User extends WP_Authress_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = null;

	/**
	 * Required scope for Management API token.
	 */
	const API_SCOPE = 'read:users';

	/**
	 * WP_Authress_Api_Get_User constructor.
	 *
	 * @param WP_Authress_Options                $options - WP_Authress_Options instance.
	 * @param WP_Authress_Api_Client_Credentials $api_client_creds - WP_Authress_Api_Client_Credentials instance.
	 */
	public function __construct(
		WP_Authress_Options $options,
		WP_Authress_Api_Client_Credentials $api_client_creds
	) {
		parent::__construct( $options );
		$this->api_client_creds = $api_client_creds;
	}

	/**
	 * Check the user_id, make the API call, and handle the response.
	 *
	 * @param string|null $user_id - Authress user ID to get.
	 *
	 * @return null|string
	 */
	public function call( $user_id = null ) {

		if ( empty( $user_id ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( ! $this->set_bearer( self::API_SCOPE ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->set_path( 'api/v2/users/' . rawurlencode( $user_id ) )
			->get()
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

		if ( $this->handle_wp_error( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_failed_response( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this->response_body;
	}
}
