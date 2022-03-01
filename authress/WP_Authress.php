<?php
/*
	Plugin Name:  Authress
	Plugin URI:   https://wordpress.org/plugins/authress
	Description:  Upgrades the WordPress login to support SSO Login.
	Version:      {{VERSION}}
	Author:       Authress 
	Author URI:   https://authress.io
	License:      Apache-2.0
	License URI:  https://www.apache.org/licenses/LICENSE-2.0
*/

define( 'WP_AUTHRESS_VERSION', '4.3.1' );

define( 'WP_AUTHRESS_PLUGIN_FILE', __FILE__ );
define( 'WP_AUTHRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // Includes trailing slash
define( 'WP_AUTHRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_AUTHRESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WP_AUTHRESS_PLUGIN_JS_URL', WP_AUTHRESS_PLUGIN_URL . 'assets/js/' );
define( 'WP_AUTHRESS_PLUGIN_CSS_URL', WP_AUTHRESS_PLUGIN_URL . 'assets/css/' );
define( 'WP_AUTHRESS_PLUGIN_IMG_URL', WP_AUTHRESS_PLUGIN_URL . 'assets/img/' );
define( 'WP_AUTHRESS_PLUGIN_LIB_URL', WP_AUTHRESS_PLUGIN_URL . 'assets/lib/' );
define( 'WP_AUTHRESS_PLUGIN_BS_URL', WP_AUTHRESS_PLUGIN_URL . 'assets/bootstrap/' );

define( 'WP_AUTHRESS_AUTHRESS_LOGIN_FORM_ID', 'authress-login-form' );
define( 'WP_AUTHRESS_CACHE_GROUP', 'wp_authress' );
define( 'WP_AUTHRESS_JWKS_CACHE_TRANSIENT_NAME', 'WP_Authress_JWKS_cache' );

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/vendor/autoload.php';

/*
 * Startup
 */

function wp_authress_plugins_loaded() {
	load_plugin_textdomain( 'wp-authress', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wp_authress_plugins_loaded' );

// function wp_authress_shortcode( $atts ) {
// 	if ( empty( $atts ) ) {
// 		$atts = [];
// 	}

// 	if ( empty( $atts['redirect_to'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
// 		// $atts['redirect_to'] = home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
// 	}

// 	ob_start();
// 	\WP_Authress_Lock::render( false, $atts );
// 	return ob_get_clean();
// }
// add_shortcode( 'authress', 'wp_authress_shortcode' );

/*
 * Plugin install/uninstall/update actions
 */

function wp_authress_activation_hook() {
	$options    = WP_Authress_Options::Instance();
	$router     = new WP_Authress_Routes( $options );

	$router->setup_rewrites();
	$options->save();

	flush_rewrite_rules();
}
register_activation_hook( WP_AUTHRESS_PLUGIN_FILE, 'wp_authress_activation_hook' );

function wp_authress_deactivation_hook() {
	flush_rewrite_rules();
}
register_deactivation_hook( WP_AUTHRESS_PLUGIN_FILE, 'wp_authress_deactivation_hook' );

function wp_authress_uninstall_hook() {
	$a0_options = WP_Authress_Options::Instance();
	$a0_options->delete();

	$error_log = new WP_Authress_ErrorLog();
	$error_log->delete();

	delete_option( 'widget_wp_authress_popup_widget' );
	delete_option( 'widget_wp_authress_widget' );
	delete_option( 'widget_wp_authress_social_amplification_widget' );

	delete_transient( WP_AUTHRESS_JWKS_CACHE_TRANSIENT_NAME );
}
register_uninstall_hook( WP_AUTHRESS_PLUGIN_FILE, 'wp_authress_uninstall_hook' );

function wp_authress_activated_plugin_redirect( $plugin ) {

	if ( defined( 'WP_CLI' ) || $plugin !== WP_AUTHRESS_PLUGIN_BASENAME ) {
		return;
	}

	wp_safe_redirect( admin_url( 'admin.php?page=authress') );
	exit;
}
add_action( 'activated_plugin', 'wp_authress_activated_plugin_redirect' );

/*
 * Core WP hooks
 */

function wp_authress_add_allowed_redirect_hosts( $hosts ) {
	$hosts[] = 'authress.io';
	$hosts[] = wp_authress_get_option( 'domain' );
	$hosts[] = wp_authress_get_option( 'customDomain' );
	$hosts[] = wp_authress_get_option( 'authress_server_domain' );
	return $hosts;
}

add_filter( 'allowed_redirect_hosts', 'wp_authress_add_allowed_redirect_hosts' );

/**
 * Enqueue login page CSS if plugin is configured.
 */
function wp_authress_login_enqueue_scripts() {
	if ( wp_authress_is_ready() ) {
		wp_enqueue_style( 'authress', WP_AUTHRESS_PLUGIN_CSS_URL . 'login.css', false, WP_AUTHRESS_VERSION );
	}
}
add_action( 'login_enqueue_scripts', 'wp_authress_login_enqueue_scripts' );

/**
 * Enqueue login widget CSS if plugin is configured.
 */
function wp_authress_enqueue_scripts() {
	if ( wp_authress_is_ready() ) {
		wp_enqueue_style( 'authress-widget', WP_AUTHRESS_PLUGIN_CSS_URL . 'main.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'wp_authress_enqueue_scripts' );

function wp_authress_register_widget() {
	register_widget( 'WP_Authress_Embed_Widget' );
	register_widget( 'WP_Authress_Popup_Widget' );
}
add_action( 'widgets_init', 'wp_authress_register_widget' );

function wp_authress_register_query_vars( $qvars ) {
	return array_merge( $qvars, [ 'error', 'applicationId', 'accessKey', 'customDomain'] );
}
add_filter( 'query_vars', 'wp_authress_register_query_vars' );

/**
 * Output the Authress form on wp-login.php
 *
 * @hook filter:login_message
 *
 * @param string $html
 *
 * @return string
 */
function wp_authress_render_lock_form( $html ) {
	debug('wp_authress_render_lock_form');
	ob_start();
	\WP_Authress_Lock::render();
	$authress_form = ob_get_clean();
	return $authress_form ? $authress_form : $html;
}
add_filter( 'login_message', 'wp_authress_render_lock_form', 5 );

/**
 * Add settings link on plugin page.
 */
function wp_authress_plugin_action_links( $links ) {
	array_unshift($links, sprintf('<a href="%s">%s</a>', admin_url( 'admin.php?page=authress' ), __( 'Settings', 'wp-authress' )));

	if ( ! wp_authress_is_ready() ) {
		array_unshift($links, sprintf('<a href="%s">%s</a>', admin_url( 'admin.php?page=authress' ), __( 'Setup Wizard', 'wp-authress' )));
	}

	return $links;
}
add_filter( 'plugin_action_links_' . WP_AUTHRESS_PLUGIN_BASENAME, 'wp_authress_plugin_action_links' );

/**
 * Filter the avatar to use the Authress profile image
 *
 * @param string                                $avatar - avatar HTML
 * @param int|string|WP_User|WP_Comment|WP_Post $id_or_email - user identifier
 * @param int                                   $size - width and height of avatar
 * @param string                                $default - what to do if nothing
 * @param string                                $alt - alt text for the <img> tag
 *
 * @return string
 */
function wp_authress_filter_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
	if ( ! wp_authress_get_option( 'override_wp_avatars' ) ) {
		return $avatar;
	}

	$user_id = null;

	if ( $id_or_email instanceof WP_User ) {
		$user_id = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$user_id = $id_or_email->user_id;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user_id = $id_or_email->post_author;
	} elseif ( is_email( $id_or_email ) ) {
		$maybe_user = get_user_by( 'email', $id_or_email );

		if ( $maybe_user instanceof WP_User ) {
			$user_id = $maybe_user->ID;
		}
	} elseif ( is_numeric( $id_or_email ) ) {
		$user_id = absint( $id_or_email );
	}

	if ( ! $user_id ) {
		return $avatar;
	}

	$authressProfile = get_authressuserinfo( $user_id );

	if ( ! $authressProfile || empty( $authressProfile->picture ) ) {
		return $avatar;
	}

	return sprintf(
		'<img alt="%s" src="%s" class="avatar avatar-%d photo avatar-authress" width="%d" height="%d"/>',
		esc_attr( $alt ),
		esc_url( $authressProfile->picture ),
		absint( $size ),
		absint( $size ),
		absint( $size )
	);
}
add_filter( 'get_avatar', 'wp_authress_filter_get_avatar', 1, 5 );

function wp_authress_error_admin_notices() {
	// Not processing form data, just using a redirect parameter if present.
	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

	if ( empty( $_GET['error'] ) ) {
		return false;
	}

	$initial_setup = new WP_Authress_InitialSetup( WP_Authress_Options::Instance() );

	switch ( $_GET['error'] ) {

		case 'cant_create_client':
			$initial_setup->cant_create_client_message();
			break;

		case 'cant_create_client_grant':
			$initial_setup->cant_create_client_grant_message();
			break;

		case 'cant_exchange_token':
			$initial_setup->cant_exchange_token_message();
			break;

		case 'rejected':
			$initial_setup->rejected_message();
			break;

		case 'access_denied':
			$initial_setup->access_denied_message();
			break;

		default:
			// Output is sanitized in the notify_error method.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$initial_setup->notify_error( wp_unslash( $_GET['error'] ) );
	}

	return true;

	// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
}
add_action( 'admin_notices', 'wp_authress_error_admin_notices' );

function wp_authress_callback_step1() {
	$consent_url = sprintf('https://authress.io/app/#/wordpress?hostedUrl=%s', urlencode(admin_url( 'admin.php?page=authress')));
	wp_safe_redirect( $consent_url );
	exit();
}
add_action( 'admin_action_wp_authress_callback_step1', 'wp_authress_callback_step1' );

/**
 * Function to call the method that clears out the error log.
 *
 * @hook admin_action_wp_authress_clear_error_log
 */
function wp_authress_errorlog_clear_error_log() {

	// Null coalescing validates input variable.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	if ( ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ?? '' ), WP_Authress_ErrorLog::CLEAR_LOG_NONCE ) ) {
		wp_die( __( 'Not allowed.', 'wp-authress' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Not authorized.', 'wp-authress' ) );
	}

	$error_log = new WP_Authress_ErrorLog();
	$error_log->clear();

	wp_safe_redirect( admin_url( 'admin.php?page=authress_errors&cleared=1' ) );
	exit;
}
add_action( 'admin_action_wp_authress_clear_error_log', 'wp_authress_errorlog_clear_error_log' );

function wp_authress_initial_setup_init() {
	debug('wp_authress_initial_setup_init');

	// Not processing form data, just using a redirect parameter if present.
	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

	// Null coalescing validates input variable.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	if ( 'authress' !== ( $_REQUEST['page'] ?? null ) || ! isset( $_REQUEST['callback'] ) ) {
		return false;
	}

	// Null coalescing validates input variable.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	if ( 'rejected' === ( $_REQUEST['error'] ?? null ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authress&error=rejected' ) );
		exit;
	}

	// Null coalescing validates input variable.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	if ( 'access_denied' === ( $_REQUEST['error'] ?? null ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authress&error=access_denied' ) );
		exit;
	}

	(new WP_Authress_InitialSetup_Consent( WP_Authress_Options::Instance() ))->callback();

	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification
}
add_action( 'init', 'wp_authress_initial_setup_init', 1 );

function wp_authress_init() {
	debug('wp_authress_init()');
	$router = new WP_Authress_Routes( WP_Authress_Options::Instance() );
	$router->setup_rewrites();
}
add_action( 'init', 'wp_authress_init');

function check_for_user_logged_in() {
	debug('check_for_user_logged_in');
	debug($_REQUEST['nonce']);
	
	if (!is_user_logged_in() && $_REQUEST['nonce']) {
		$users_repo    = new WP_Authress_UsersRepo( WP_Authress_Options::Instance() );
		$login_manager = new WP_Authress_LoginManager( $users_repo, WP_Authress_Options::Instance() );
		$login_manager->init_authress();
		wp_safe_redirect(home_url());
		exit();

		// if (is_user_logged_in()) {
		// 	wp_safe_redirect(home_url());
		// 	debug('User successfully now logged in during handler');
		// } else {
		// 	debug('User NOT logged in during handler');
		// }
		// wp_safe_redirect();
		// debug($_REQUEST['redirect_to']);
		// if ($_REQUEST['redirect_to']) {
		// 	// wp_redirect(urldecode($_REQUEST['redirect_to']));
		// 	wp_safe_redirect(urldecode($_REQUEST['redirect_to']));
		// 	exit;
		// }
	}
}
add_action('init', 'check_for_user_logged_in');
// add_action('login_init', 'check_for_user_logged_in');

function wp_authress_profile_change_email( $wp_user_id, $old_user_data ) {
	$options              = WP_Authress_Options::Instance();
	$api_client_creds     = new WP_Authress_Api_Client_Credentials( $options );
	$api_change_email     = new WP_Authress_Api_Change_Email( $options, $api_client_creds );
	$profile_change_email = new WP_Authress_Profile_Change_Email( $api_change_email );
	return $profile_change_email->update_email( $wp_user_id, $old_user_data );
}
add_action( 'profile_update', 'wp_authress_profile_change_email', 100, 2 );

function wp_authress_validate_new_password( $errors, $user ) {
	$options             = WP_Authress_Options::Instance();
	$api_client_creds    = new WP_Authress_Api_Client_Credentials( $options );
	$api_change_password = new WP_Authress_Api_Change_Password( $options, $api_client_creds );
	$profile_change_pwd  = new WP_Authress_Profile_Change_Password( $api_change_password );
	return $profile_change_pwd->validate_new_password( $errors, $user );
}

// Used during profile update in wp-admin.
add_action( 'user_profile_update_errors', 'wp_authress_validate_new_password', 10, 2 );

// Used during password reset on wp-login.php.
add_action( 'validate_password_reset', 'wp_authress_validate_new_password', 10, 2 );

function wp_authress_show_delete_identity() {
	$profile_delete_data = new WP_Authress_Profile_Delete_Data();
	$profile_delete_data->show_delete_identity();
}
add_action( 'edit_user_profile', 'wp_authress_show_delete_identity' );
add_action( 'show_user_profile', 'wp_authress_show_delete_identity' );

function wp_authress_delete_user_data() {
	$profile_delete_data = new WP_Authress_Profile_Delete_Data();
	$profile_delete_data->delete_user_data();
}
add_action( 'wp_ajax_authress_delete_data', 'wp_authress_delete_user_data' );

function wp_authress_init_admin_menu() {
	debug('wp_authress_init_admin_menu');
	if (is_admin() && !empty($_REQUEST['page']) && 'authress_help' === $_REQUEST['page']) {
		wp_safe_redirect( admin_url( 'admin.php?page=authress_configuration#help' ), 301 );
		exit;
	}

	$options       = WP_Authress_Options::Instance();
	$initial_setup = new WP_Authress_InitialSetup( $options );
	$routes        = new WP_Authress_Routes( $options );
	$admin         = new WP_Authress_Admin( $options, $routes );

	$setup_slug  = 'authress';
	$setup_title = __( 'Setup Wizard', 'wp-authress' );
	$setup_func  = [ $initial_setup, 'render_setup_page' ];

	$settings_slug  = 'authress_configuration';
	$settings_title = __( 'Settings', 'wp-authress' );
	$settings_func  = [ $admin, 'render_settings_page' ];

	$menu_parent = $setup_slug;
	$cap         = 'manage_options';

	add_menu_page('Authress', 'Authress', $cap, $menu_parent, $setup_func, WP_AUTHRESS_PLUGIN_IMG_URL . 'logo_16x16.png', 86);

	add_submenu_page($menu_parent, $setup_title, $setup_title, $cap, $setup_slug, $setup_func );
	add_submenu_page($menu_parent, $settings_title, $settings_title, $cap, $settings_slug, $settings_func );
	add_submenu_page($menu_parent, __( 'Error Log', 'wp-authress' ), __( 'Error Log', 'wp-authress' ), $cap, 'authress_errors', [ new WP_Authress_ErrorLog(), 'render_settings_page' ]);
	add_submenu_page($menu_parent, __( 'Help', 'wp-authress' ), __( 'Help', 'wp-authress' ), $cap, 'authress_help', '__return_false');
}
add_action( 'admin_menu', 'wp_authress_init_admin_menu', 96, 0 );

function wp_authress_create_account_message() {
	// Not processing form data, just using a redirect parameter if present.
	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

	// Null coalescing validates input variable.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	$current_page     = $_GET['page'] ?? null;
	$is_correct_admin = in_array( $current_page, ['authress_configuration', 'authress_errors' ] );
	if ( wp_authress_is_ready() || ! $is_correct_admin ) {
		return false;
	}

	printf('<div class="update-nag">%s<strong><a href="%s">%s</a></strong>.</div>',
		__( 'SSO Login is not yet configured. Please use the ', 'wp-authress' ),
		admin_url( 'admin.php?page=authress' ),
		__( 'Setup Wizard', 'wp-authress' )
	);
	return true;

	// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
}
add_action( 'admin_notices', 'wp_authress_create_account_message' );

function wp_authress_init_admin() {
	debug('wp_authress_init_admin');
	$options = WP_Authress_Options::Instance();
	$routes  = new WP_Authress_Routes( $options );
	$admin   = new WP_Authress_Admin( $options, $routes );
	$admin->init_admin();
}
add_action( 'admin_init', 'wp_authress_init_admin' );

function wp_authress_admin_enqueue_scripts() {
	debug('wp_authress_admin_enqueue_scripts');
	$options = WP_Authress_Options::Instance();
	$routes  = new WP_Authress_Routes( $options );
	$admin   = new WP_Authress_Admin( $options, $routes );
	return $admin->admin_enqueue();
}
add_action( 'admin_enqueue_scripts', 'wp_authress_admin_enqueue_scripts', 1 );

function wp_authress_custom_requests( $wp, $return = false ) {
	$routes = new WP_Authress_Routes( WP_Authress_Options::Instance() );
	return $routes->custom_requests( $wp, $return );
}
add_action( 'parse_request', 'wp_authress_custom_requests' );

function wp_authress_profile_enqueue_scripts() {
	debug('wp_authress_profile_enqueue_scripts');
	global $pagenow;

	if ( ! in_array( $pagenow, [ 'profile.php', 'user-edit.php' ] ) ) {
		return false;
	}

	wp_enqueue_script(
		'wp_authress_user_profile',
		WP_AUTHRESS_PLUGIN_JS_URL . 'edit-user-profile.js',
		[ 'jquery' ],
		WP_AUTHRESS_VERSION
	);

	$profile  = get_authressuserinfo( $GLOBALS['user_id'] );
	$strategy = isset( $profile->sub ) ? WP_Authress_Users::get_strategy( $profile->sub ) : '';

	wp_localize_script(
		'wp_authress_user_profile',
		'wp_authressUserProfile',
		[
			'userId'        => intval( $GLOBALS['user_id'] ),
			'userStrategy'  => sanitize_text_field( $strategy ),
			'deleteIdNonce' => wp_create_nonce( 'delete_authress_identity' ),
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'i18n'          => [
				'confirmDeleteId'   => __( 'Are you sure you want to delete the Authress user data for this user?', 'wp-authress' ),
				'actionComplete'    => __( 'Deleted', 'wp-authress' ),
				'actionFailed'      => __( 'Action failed, please see the Authress error log for details.', 'wp-authress' ),
				'cannotChangeEmail' => __( 'Email cannot be changed for non-database connections.', 'wp-authress' ),
			],
		]
	);

	return true;
}
add_action( 'admin_enqueue_scripts', 'wp_authress_profile_enqueue_scripts' );

function page_loaded() {
	$users_repo    = new WP_Authress_UsersRepo( WP_Authress_Options::Instance() );
	$login_manager = new WP_Authress_LoginManager( $users_repo, WP_Authress_Options::Instance() );
	debug('page_loaded');
	return $login_manager->init_authress();
}

function redirect_handled($location) {
	return $location;
}

add_action( 'wp_redirect', 'redirect_handled', 1 );
add_action( 'template_redirect', 'page_loaded', 1 );

function login_widget_loaded() {
	$users_repo    = new WP_Authress_UsersRepo( WP_Authress_Options::Instance() );
	$login_manager = new WP_Authress_LoginManager( $users_repo, WP_Authress_Options::Instance() );
	debug('login_widget_loaded');
	return $login_manager->login_auto();
}
add_action( 'login_init', 'login_widget_loaded' );

function wp_authress_process_logout() {
	$users_repo    = new WP_Authress_UsersRepo( WP_Authress_Options::Instance() );
	$login_manager = new WP_Authress_LoginManager( $users_repo, WP_Authress_Options::Instance() );
	$login_manager->logout();
}
add_action( 'wp_logout', 'wp_authress_process_logout' );


function wp_authress_ajax_delete_cache_transient() {
	check_ajax_referer( 'authress_delete_cache_transient' );
	delete_transient( WP_AUTHRESS_JWKS_CACHE_TRANSIENT_NAME );
	wp_send_json_success();
}
add_action( 'wp_ajax_authress_delete_cache_transient', 'wp_authress_ajax_delete_cache_transient' );

/**
 * AJAX handler to re-send verification email.
 * Hooked to: wp_ajax_nopriv_resend_verification_email
 *
 * @codeCoverageIgnore - Tested in TestEmailVerification::testResendVerificationEmail()
 */
function wp_authress_ajax_resend_verification_email() {
	check_ajax_referer( WP_Authress_Email_Verification::RESEND_NONCE_ACTION );

	$options               = WP_Authress_Options::Instance();
	$api_client_creds      = new WP_Authress_Api_Client_Credentials( $options );
	$api_jobs_verification = new WP_Authress_Api_Jobs_Verification( $options, $api_client_creds );

	if ( empty( $_POST['sub'] ) ) {
		wp_send_json_error( [ 'error' => __( 'No Authress user ID provided.', 'wp-authress' ) ] );
	}

	// Validated above and only sent to the change signup API endpoint.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( ! $api_jobs_verification->call( wp_unslash( $_POST['sub'] ) ) ) {
		wp_send_json_error( [ 'error' => __( 'API call failed.', 'wp-authress' ) ] );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_nopriv_resend_verification_email', 'wp_authress_ajax_resend_verification_email' );

/**
 * Redirect a successful lost password submission to a login override page.
 *
 * @param string $location - Redirect in process.
 *
 * @return string
 */
function wp_authress_filter_wp_redirect_lostpassword( $location ) {
	// Make sure we're going to the check email action on the wp-login page.
	if ( 'wp-login.php?checkemail=confirm' !== $location ) {
		return $location;
	}

	// Make sure we're on the lost password action on the wp-login page.
	if ( ! wp_authress_is_current_login_action( [ 'lostpassword' ] ) ) {
		return $location;
	}

	// Make sure plugin settings allow core WP login form overrides
	if ( 'never' === wp_authress_get_option( 'wordpress_login_enabled' ) ) {
		return $location;
	}

	// Make sure we're coming from an override page.
	$required_referrer = remove_query_arg( 'wle', wp_login_url() );
	$required_referrer = add_query_arg( 'action', 'lostpassword', $required_referrer );
	$required_referrer = wp_authress_login_override_url( $required_referrer );
	if ( ! isset( $_SERVER['HTTP_REFERER'] ) || $required_referrer !== $_SERVER['HTTP_REFERER'] ) {
		return $location;
	}

	return wp_authress_login_override_url( $location );
}

add_filter( 'wp_redirect', 'wp_authress_filter_wp_redirect_lostpassword', 100 );

/**
 * Add an override code to the lost password URL if authorized.
 *
 * @param string $wp_login_url - Existing lost password URL.
 *
 * @return string
 */
function wp_authress_filter_login_override_url( $wp_login_url ) {
	// Not processing form data, just using a redirect parameter if present.
	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

	if ( wp_authress_can_show_wp_login_form() && isset( $_REQUEST['wle'] ) ) {
		// We are on an override page.
		$wp_login_url = add_query_arg( 'wle', sanitize_text_field( wp_unslash( $_REQUEST['wle'] ) ), $wp_login_url );
	} elseif ( wp_authress_is_current_login_action( [ 'resetpass' ] ) ) {
		// We are on the reset password page with a link to login.
		// This page will not be shown unless we get here via a valid reset password request.
		$wp_login_url = wp_authress_login_override_url( $wp_login_url );
	}
	return $wp_login_url;

	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification
}

add_filter( 'lostpassword_url', 'wp_authress_filter_login_override_url', 100 );
add_filter( 'login_url', 'wp_authress_filter_login_override_url', 100 );

/**
 * Add the core WP form override to the lost password and login forms.
 */
function wp_authress_filter_login_override_form() {
	// Not processing form data, just using a redirect parameter if present.
	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

	if ( wp_authress_can_show_wp_login_form() && isset( $_REQUEST['wle'] ) ) {
		// Input is being output, not stored.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		printf( '<input type="hidden" name="wle" value="%s" />', esc_attr( wp_unslash( $_REQUEST['wle'] ) ) );
	}

	// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification
}

add_action( 'login_form', 'wp_authress_filter_login_override_form', 100 );
add_action( 'lostpassword_form', 'wp_authress_filter_login_override_form', 100 );

/**
 * Add new classes to the body element on all front-end and login pages.
 *
 * @param array $classes - Array of existing classes.
 *
 * @return array
 */
function wp_authress_filter_body_class( array $classes ) {
	if ( wp_authress_can_show_wp_login_form() ) {
		$classes[] = 'a0-show-core-login';
	}
	return $classes;
}
add_filter( 'body_class', 'wp_authress_filter_body_class' );
add_filter( 'login_body_class', 'wp_authress_filter_body_class' );

/*
 * Beta plugin deactivation
 */

// Passwordless beta testing - https://github.com/authress/wp-authress/issues/400
remove_filter( 'login_message', 'wp_authress_pwl_plugin_login_message_before', 5 );
remove_filter( 'login_message', 'wp_authress_pwl_plugin_login_message_after', 6 );
