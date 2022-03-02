<?php
/**
 * Contains class WP_Authress_Routes.
 *
 * @package WP-Authress
 *
 * @since 2.0.0
 */

/**
 * Class WP_Authress_Routes.
 * Handles all custom routes used by Authress except login callback.
 */
class WP_Authress_Routes {

	/**
	 * WP_Authress_Options instance for this class.
	 *
	 * @var WP_Authress_Options
	 */
	protected $a0_options;

	/**
	 * WP_Authress_Routes constructor.
	 *
	 * @param WP_Authress_Options       $a0_options - WP_Authress_Options instance.
	 */
	public function __construct( WP_Authress_Options $a0_options) {
		$this->a0_options = $a0_options;
	}

	/**
	 * Add rewrite tags and rules.
	 */
	public function setup_rewrites() {
		add_rewrite_tag( '%authress%', '([^&]+)' );
		add_rewrite_tag( '%authressfallback%', '([^&]+)' );
		add_rewrite_tag( '%code%', '([^&]+)' );
		add_rewrite_tag( '%state%', '([^&]+)' );
		add_rewrite_tag( '%authress_error%', '([^&]+)' );
		add_rewrite_tag( '%a0_action%', '([^&]+)' );

		add_rewrite_rule( '^\.well-known/oauth2-client-configuration', 'index.php?a0_action=oauth2-config', 'top' );
	}

	/**
	 * Route incoming Authress actions.
	 *
	 * @param WP   $wp - WP object for current request.
	 * @param bool $return - True to return the data, false to echo and exit.
	 *
	 * @return bool|string
	 */
	public function custom_requests( $wp, $return = false ) {
		$page = null;

		if ( isset( $wp->query_vars['authressfallback'] ) ) {
			$page = 'coo-fallback';
		}

		if ( isset( $wp->query_vars['a0_action'] ) ) {
			$page = $wp->query_vars['a0_action'];
		}

		if ( null === $page && isset( $wp->query_vars['pagename'] ) ) {
			$page = $wp->query_vars['pagename'];
		}

		if ( empty( $page ) ) {
			return false;
		}

		$json_header = true;
		switch ( $page ) {
			case 'oauth2-config':
				$output = wp_json_encode( $this->oauth2_config() );
				break;
			default:
				return false;
		}

		if ( $return ) {
			return $output;
		}

		if ( $json_header ) {
			add_filter( 'wp_headers', [ $this, 'add_json_header' ] );
			$wp->send_headers();
		}

		echo $output;
		exit;
	}

	/**
	 * Use with the wp_headers filter to add a Content-Type header for JSON output.
	 *
	 * @param array $headers - Existing headers to modify.
	 *
	 * @return mixed
	 */
	public function add_json_header( array $headers ) {
		$headers['Content-Type'] = 'application/json; charset=' . get_bloginfo( 'charset' );
		return $headers;
	}

	protected function getAuthorizationHeader() {
		// Nonce is not needed here as this is an API endpoint.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		$authorization = false;

		if ( isset( $_POST['access_token'] ) ) {
			// No need to sanitize, value is returned and checked.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$authorization = wp_unslash( $_POST['access_token'] );
		} elseif ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			if ( isset( $headers['Authorization'] ) ) {
				$authorization = $headers['Authorization'];
			} elseif ( isset( $headers['authorization'] ) ) {
				$authorization = $headers['authorization'];
			}
		} elseif ( isset( $_SERVER['Authorization'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$authorization = wp_unslash( $_SERVER['Authorization'] );
		} elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$authorization = wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] );
		}

		return $authorization;

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	protected function oauth2_config() {

		return [
			'client_name'   => get_bloginfo( 'name' ),
			'redirect_uris' => [ WP_Authress_InitialSetup::get_setup_redirect_uri() ],
		];
	}

	/**
	 * Check the migration endpoint status and IP filter for incoming requests.
	 *
	 * @return int
	 */
	private function check_endpoint_access_error() {

		// Migration web service is not turned on.
		if ( ! $this->a0_options->get( 'migration_ws' ) ) {
			return 403;
		}

		return 0;
	}

	/**
	 * Check the incoming request for token and required data.
	 *
	 * @param bool $require_password - True to check for a POSTed password, false to ignore.
	 *
	 * @throws Exception
	 */
	private function check_endpoint_request( $require_password = false ) {
		// Nonce is not needed here as this is an API endpoint.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		$authorization = $this->getAuthorizationHeader();
		$authorization = trim( str_replace( 'Bearer ', '', $authorization ) );

		if ( empty( $authorization ) ) {
			throw new Exception( __( 'Unauthorized: missing authorization header', 'wp-authress' ), 401 );
		}

		if ( $authorization !== $this->a0_options->get( 'migration_token' ) ) {
			throw new Exception( __( 'Invalid token', 'wp-authress' ), 401 );
		}

		if ( empty( $_POST['username'] ) ) {
			throw new Exception( __( 'Username is required', 'wp-authress' ) );
		}

		if ( $require_password && empty( $_POST['password'] ) ) {
			throw new Exception( __( 'Password is required', 'wp-authress' ) );
		}

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	/**
	 * Default error arrays.
	 *
	 * @param integer $code - Error code.
	 *
	 * @return array
	 */
	private function error_return_array( $code ) {

		switch ( $code ) {
			case 401:
				return [
					'status' => 401,
					'error'  => __( 'Unauthorized', 'wp-authress' ),
				];

			default:
				return [
					'status' => 403,
					'error'  => __( 'Forbidden', 'wp-authress' ),
				];
		}
	}
}
