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

function wp_authress_get_option( $key, $default = null ) {
	return WP_Authress_Options::Instance()->get( $key, $default );
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

/**
 * Generate a valid WordPress login override URL, if plugin settings allow.
 *
 * @param string|null $login_url - An existing URL to modify; default is wp-login.php.
 *
 * @return string
 */
function wp_authress_login_override_url( $login_url = null ) {
	$wle = wp_authress_get_option( 'wordpress_login_enabled' );
	if ( 'no' === $wle ) {
		return '';
	}

	$wle_code = '';
	if ( 'code' === $wle ) {
		$wle_code = wp_authress_get_option( 'wle_code' );
	}

	$login_url = $login_url ?: wp_login_url();
	return add_query_arg( 'wle', $wle_code, $login_url );
}

/**
 * Can the core WP login form be shown?
 *
 * @return bool
 */
function wp_authress_can_show_wp_login_form() {
	// Not processing form data, just using a redirect parameter if present.
	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

	if ( ! wp_authress_is_ready() ) {
		return true;
	}

	if ( wp_authress_is_current_login_action( [ 'resetpass', 'rp', 'validate_2fa', 'postpass' ] ) ) {
		return true;
	}

	if ( get_query_var( 'authress_login_successful' ) ) {
		return true;
	}

	if ( ! isset( $_REQUEST['wle'] ) ) {
		return false;
	}

	$wle_setting = wp_authress_get_option( 'wordpress_login_enabled' );
	if ( 'no' === $wle_setting ) {
		return false;
	}

	if ( in_array( $wle_setting, [ 'link', 'isset' ] ) ) {
		return true;
	}

	$wle_code = wp_authress_get_option( 'wle_code' );
	if ( 'code' === $wle_setting && $wle_code === $_REQUEST['wle'] ) {
		return true;
	}

	return false;

	// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
}

/**
 * @param string $input
 *
 * @return string
 *
 * @see https://github.com/firebase/php-jwt/blob/v5.0.0/src/JWT.php#L337
 */
function wp_authress_url_base64_encode( $input ) {
	return str_replace( '=', '', strtr( base64_encode( $input ), '+/', '-_' ) );
}

/**
 * @param string $input
 *
 * @return bool|string
 *
 * @see https://github.com/firebase/php-jwt/blob/v5.0.0/src/JWT.php#L320
 */
function wp_authress_url_base64_decode( $input ) {
	$remainder = strlen( $input ) % 4;
	if ( $remainder ) {
		$padlen = 4 - $remainder;
		$input .= str_repeat( '=', $padlen );
	}
	return base64_decode( strtr( $input, '-_', '+/' ), true );
}

/**
 * Delete all Authress data for a specific user.
 *
 * @param int $user_id - WordPress user ID.
 */
function wp_authress_delete_authress_object( $user_id ) {
	WP_Authress_UsersRepo::delete_meta( $user_id, 'authress_id' );
	WP_Authress_UsersRepo::delete_meta( $user_id, 'authress_obj' );
	WP_Authress_UsersRepo::delete_meta( $user_id, 'last_update' );
	WP_Authress_UsersRepo::delete_meta( $user_id, 'authress_transient_email_update' );
}

/**
 * Determine whether a specific admin page is being loaded or not.
 *
 * @param string $page - Admin page slug to check.
 *
 * @return bool
 */
function wp_authress_is_admin_page( $page ) {
	// Not processing form data, just using a redirect parameter if present.
	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

	if ( empty( $_REQUEST['page'] ) || ! is_admin() ) {
		return false;
	}

	return $page === $_REQUEST['page'];

	// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
}

/**
 * Is the Authress plugin ready to process logins?
 *
 * @return bool
 */
function wp_authress_is_ready() {
	if ( wp_authress_get_option( 'accessKey' ) && wp_authress_get_option( 'applicationId' ) && wp_authress_get_option( 'customDomain' ) ) {
		return true;
	}
	return false;
}

/**
 * Get the tenant region based on a domain.
 *
 * @param string $domain Tenant domain.
 *
 * @return string
 */
function wp_authress_get_tenant_region( $domain ) {
	preg_match( '/^[\w\d\-_0-9]+\.([\w\d\-_0-9]*)[\.]*authress\.com$/', $domain, $matches );
	return ! empty( $matches[1] ) ? $matches[1] : 'us';
}

/**
 * Get the full tenant name with region.
 *
 * @param null|string $domain Tenant domain.
 *
 * @return string
 */
function wp_authress_get_tenant( $domain = null ) {

	if ( empty( $domain ) ) {
		$domain = wp_authress_get_option( 'domain' );
	}

	$parts = explode( '.', $domain );
	return $parts[0] . '@' . wp_authress_get_tenant_region( $domain );
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
