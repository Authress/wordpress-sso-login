<?php
/**
 * Contains WP_Authress_Api_Change_Password.
 *
 * @package WP-Authress
 *
 * @since 3.8.0
 */

/**
 * Class WP_Authress_Api_Change_Password to update a user's password at Authress.
 */
class WP_Authress_Api_Change_Password extends WP_Authress_Api_Abstract {

	/**
	 * Default value to return on failure.
	 */
	const RETURN_ON_FAILURE = false;

	/**
	 * Required scope for Management API token.
	 */
	const API_SCOPE = 'update:users';

	/**
	 * Decoded token received for the Management API.
	 *
	 * @var null|object
	 */
	protected $token_decoded = null;

	/**
	 * WP_Authress_Api_Change_Password constructor.
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
	 * Set the user_id and password, make the API call, and handle the response.
	 *
	 * @param string|null $user_id - Authress user ID to change the password for.
	 * @param string|null $password - New password.
	 *
	 * @return bool|string|null
	 */
	public function call( $user_id = null, $password = null ) {

		if ( empty( $user_id ) || empty( $password ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( ! $this->set_bearer( self::API_SCOPE ) ) {
			return self::RETURN_ON_FAILURE;
		}

		return $this
			->set_path( 'api/v2/users/' . rawurlencode( $user_id ) )
			->add_body( 'password', $password )
			->patch()
			->handle_response( __METHOD__ );
	}

	/**
	 * Handle API response.
	 *
	 * @param string $method - Method that called the API.
	 *
	 * @return bool|string
	 */
	protected function handle_response( $method ) {

		if ( $this->handle_wp_error( $method ) ) {
			return self::RETURN_ON_FAILURE;
		}

		if ( $this->handle_failed_response( $method ) ) {
			$response_body = json_decode( $this->response_body );
			if ( isset( $response_body->message ) && false !== strpos( $response_body->message, 'PasswordStrengthError' ) ) {
				return __( 'Password is too weak, please choose a different one.', 'wp-authress' );
			}
			return self::RETURN_ON_FAILURE;
		}

		return true;
	}
}