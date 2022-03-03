<?php
/**
 * Global WP-Authress functions.
 *
 * @package WP-Authress
 *
 * @since 3.10.0
 */

/**
 * Return a stored option value.
 *
 * @since 3.10.0
 *
 * @param string $key - Settings key to get.
 * @param mixed  $default - Default value to return if not found.
 *
 * @return mixed
 */

if ( ! function_exists( 'wp_authress_get_option' ) ) {
	function wp_authress_get_option( $key, $default = null ) {
		return WP_Authress_Options::Instance()->get( $key, $default );
	}
}

/**
 * Determine if we're on the wp-login.php page and if the current action matches a given set.
 *
 * @since 3.11.0
 *
 * @param array $actions - An array of actions to check the current action against.
 *
 * @return bool
 */
if ( ! function_exists( 'wp_authress_is_current_login_action' ) ) {
	function wp_authress_is_current_login_action( array $actions ) {
		// Not processing form data, just using a redirect parameter if present.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		// Not on wp-login.php.
		if (
			( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' !== $GLOBALS['pagenow'] ) &&
			! function_exists( 'login_header' )
		) {
			return false;
		}

		// Null coalescing validates input variable.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		return in_array( wp_unslash( $_REQUEST['action'] ?? '' ), $actions );

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}
}

/**
 * Generate a valid WordPress login override URL, if plugin settings allow.
 *
 * @param string|null $login_url - An existing URL to modify; default is wp-login.php.
 *
 * @return string
 */
if ( ! function_exists( 'wp_authress_login_override_url' ) ) {
	function wp_authress_login_override_url( $login_url = null ) {
		$login_url = $login_url ?: wp_login_url();
		return add_query_arg( 'wle', $wle_code, $login_url );
	}
}

/**
 * Can the core WP login form be shown?
 *
 * @return bool
 */
if ( ! function_exists( 'wp_authress_can_show_wp_login_form' ) ) {
	function wp_authress_can_show_wp_login_form() {
		if ( ! wp_authress_is_ready() ) {
			return true;
		}

		if ( wp_authress_is_current_login_action( [ 'resetpass', 'rp', 'validate_2fa', 'postpass' ] ) ) {
			return true;
		}

		if ( ! isset( $_REQUEST['wle'] ) ) {
			return false;
		}

		return true;

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}
}

/**
 * Is the Authress plugin ready to process logins?
 *
 * @return bool
 */
if ( ! function_exists( 'wp_authress_is_ready' ) ) {
	function wp_authress_is_ready() {
		if ( wp_authress_get_option( 'accessKey' ) && wp_authress_get_option( 'applicationId' ) && wp_authress_get_option( 'customDomain' ) ) {
			return true;
		}
		authress_debug_log('!!!!Authress Plugin and DB is not loaded');
		return false;
	}
}

if ( ! function_exists( 'get_authressuserinfo' ) ) {
	/**
	 * Get the Authress profile from the database, if one exists.
	 *
	 * @param string $authress_user_id - Authress user ID to find.
	 *
	 * @return mixed
	 */
	//phpcs:ignore
	function get_authressuserinfo( $authress_user_id ) {
		$profile = WP_Authress_UsersRepo::get_meta( $authress_user_id, 'authress_obj' );
		return $profile ? WP_Authress_Serializer::unserialize( $profile ) : false;
	}
}

function authress_debug_log($message) {
	if (getenv('DEVELOPMENT_DEBUG')) {
		error_log("****************************************************************           " . json_encode($message) . "           ****************************************************************");
	}
}