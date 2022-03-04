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

function authress_get_configuration_data_from_key( $key, $default = null ) {
	return Authress_Sso_Login_Options::Instance()->get( $key, $default );
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
function authress_user_is_currently_on_login_action( array $actions ) {
	// Not processing form data, just using a redirect parameter if present.

	// Not on wp-login.php.
	if (
		( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' !== $GLOBALS['pagenow'] ) &&
		! function_exists( 'login_header' )
	) {
		return false;
	}

	// Null coalescing validates input variable.
	return in_array( wp_unslash( $_REQUEST['action'] ?? '' ), $actions, true);
}

/**
 * Can the core WP login form be shown?
 *
 * @return bool
 */
function authress_show_user_wordpress_login_form() {
	if ( ! authress_plugin_has_been_fully_configured() ) {
		return true;
	}

	if ( authress_user_is_currently_on_login_action( [ 'resetpass', 'rp', 'validate_2fa', 'postpass' ] ) ) {
		return true;
	}

	if ( ! isset( $_REQUEST['wle'] ) ) {
		return false;
	}

	return true;
}

/**
 * Is the Authress plugin ready to process logins?
 *
 * @return bool
 */
function authress_plugin_has_been_fully_configured() {
	if ( authress_get_configuration_data_from_key( 'accessKey' ) && authress_get_configuration_data_from_key( 'applicationId' ) && authress_get_configuration_data_from_key( 'customDomain' ) ) {
		return true;
	}
	authress_debug_log('!!!!Authress Plugin and DB is not loaded');
	return false;
}

function authress_debug_log($message) {
	if (getenv('DEVELOPMENT_DEBUG')) {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log("****************************************************************           " . wp_json_encode($message) . "           ****************************************************************");
		// phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
