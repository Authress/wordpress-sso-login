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

		echo esc_attr($output);
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
		$authorization = false;

		if ( isset( $_POST['access_token'] ) ) {
			// No need to sanitize, value is returned and checked.
			$authorization = sanitize_text_field(wp_unslash( $_POST['access_token'] ));
		} elseif ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			if ( isset( $headers['Authorization'] ) ) {
				$authorization = $headers['Authorization'];
			} elseif ( isset( $headers['authorization'] ) ) {
				$authorization = $headers['authorization'];
			}
		} elseif ( isset( $_SERVER['Authorization'] ) ) {
			$authorization = sanitize_text_field(wp_unslash( $_SERVER['Authorization'] ));
		} elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$authorization = sanitize_text_field(wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ));
		}

		return $authorization;
	}

	protected function oauth2_config() {

		return [
			'client_name'   => get_bloginfo( 'name' ),
			'redirect_uris' => [ WP_Authress_InitialSetup::get_setup_redirect_uri() ],
		];
	}
}
