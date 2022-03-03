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

define( 'AUTHRESS_SSO_LOGIN_VERSION', '{{VERSION}}' );

define( 'AUTHRESS_SSO_LOGIN_PLUGIN_FILE', __FILE__ );
define( 'AUTHRESS_SSO_LOGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // Includes trailing slash
define( 'AUTHRESS_SSO_LOGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AUTHRESS_SSO_LOGIN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'AUTHRESS_SSO_LOGIN_PLUGIN_JS_URL', AUTHRESS_SSO_LOGIN_PLUGIN_URL . 'assets/js/' );
define( 'AUTHRESS_SSO_LOGIN_PLUGIN_CSS_URL', AUTHRESS_SSO_LOGIN_PLUGIN_URL . 'assets/css/' );
define( 'AUTHRESS_SSO_LOGIN_PLUGIN_IMG_URL', AUTHRESS_SSO_LOGIN_PLUGIN_URL . 'assets/img/' );
define( 'AUTHRESS_SSO_LOGIN_PLUGIN_LIB_URL', AUTHRESS_SSO_LOGIN_PLUGIN_URL . 'assets/lib/' );
define( 'AUTHRESS_SSO_LOGIN_PLUGIN_BS_URL', AUTHRESS_SSO_LOGIN_PLUGIN_URL . 'assets/bootstrap/' );

define( 'AUTHRESS_SSO_LOGIN_AUTHRESS_LOGIN_FORM_ID', 'authress-login-form' );
define( 'AUTHRESS_SSO_LOGIN_CACHE_GROUP', 'wp_authress' );
define( 'AUTHRESS_SSO_LOGIN_JWKS_CACHE_TRANSIENT_NAME', 'Authress_Sso_Login_JWKS_cache' );

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/vendor/autoload.php';

/**
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
// 	\Authress_Sso_Login_Lock::render( false, $atts );
// 	return ob_get_clean();
// }
// add_shortcode( 'authress', 'wp_authress_shortcode' );

/**
 * Plugin install/uninstall/update actions
 */

function wp_authress_activation_hook() {
	$options    = Authress_Sso_Login_Options::Instance();
	$router     = new Authress_Sso_Login_Routes( $options );

	$router->setup_rewrites();
	$options->save();

	flush_rewrite_rules();
}
register_activation_hook( AUTHRESS_SSO_LOGIN_PLUGIN_FILE, 'wp_authress_activation_hook' );

function wp_authress_deactivation_hook() {
	flush_rewrite_rules();
}
register_deactivation_hook( AUTHRESS_SSO_LOGIN_PLUGIN_FILE, 'wp_authress_deactivation_hook' );

function wp_authress_uninstall_hook() {
	$a0_options = Authress_Sso_Login_Options::Instance();
	$a0_options->delete();

	$error_log = new Authress_Sso_Login_ErrorLog();
	$error_log->delete();

	delete_transient( AUTHRESS_SSO_LOGIN_JWKS_CACHE_TRANSIENT_NAME );
}
register_uninstall_hook( AUTHRESS_SSO_LOGIN_PLUGIN_FILE, 'wp_authress_uninstall_hook' );

function wp_authress_activated_plugin_redirect( $plugin ) {

	if ( defined( 'WP_CLI' ) || $plugin !== AUTHRESS_SSO_LOGIN_PLUGIN_BASENAME ) {
		return;
	}

	wp_safe_redirect( admin_url( 'admin.php?page=authress') );
	exit;
}
add_action( 'activated_plugin', 'wp_authress_activated_plugin_redirect' );

/**
 * Core WP hooks
 * 
 * @param string $hosts
 */

function wp_authress_add_allowed_redirect_hosts( $hosts ) {
	$hosts[] = 'authress.io';
	$hosts[] = authress_get_configuration_data_from_key( 'domain' );
	$hosts[] = authress_get_configuration_data_from_key( 'customDomain' );
	$hosts[] = authress_get_configuration_data_from_key( 'authress_server_domain' );
	return $hosts;
}

add_filter( 'allowed_redirect_hosts', 'wp_authress_add_allowed_redirect_hosts' );

/**
 * Enqueue login page CSS if plugin is configured.
 */
function wp_authress_login_enqueue_scripts() {
	if ( authress_plugin_has_been_fully_configured() ) {
		wp_enqueue_style( 'authress', AUTHRESS_SSO_LOGIN_PLUGIN_CSS_URL . 'login.css', false, AUTHRESS_SSO_LOGIN_VERSION );
	}
}
add_action( 'login_enqueue_scripts', 'wp_authress_login_enqueue_scripts' );

/**
 * Enqueue login widget CSS if plugin is configured.
 */
function wp_authress_enqueue_scripts() {
	if ( authress_plugin_has_been_fully_configured() ) {
		wp_enqueue_style( 'authress-widget', AUTHRESS_SSO_LOGIN_PLUGIN_CSS_URL . 'main.css', false, AUTHRESS_SSO_LOGIN_VERSION);
	}
}
add_action( 'wp_enqueue_scripts', 'wp_authress_enqueue_scripts' );

function wp_authress_register_query_vars( $qvars ) {
	return array_merge( $qvars, [ 'error', 'applicationId', 'accessKey', 'customDomain' ]);
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
	authress_debug_log('wp_authress_render_lock_form');
	ob_start();
	\Authress_Sso_Login_Lock::render();
	$authress_form = ob_get_clean();
	return $authress_form ? $authress_form : $html;
}
add_filter( 'login_message', 'wp_authress_render_lock_form', 5 );

/**
 * Add settings link on plugin page.
 * 
 * @param string $links
 */
function wp_authress_plugin_action_links( $links ) {
	array_unshift($links, sprintf('<a href="%s">%s</a>', admin_url( 'admin.php?page=authress' ), __( 'Settings', 'wp-authress' )));

	if ( ! authress_plugin_has_been_fully_configured() ) {
		array_unshift($links, sprintf('<a href="%s">%s</a>', admin_url( 'admin.php?page=authress' ), __( 'Setup Wizard', 'wp-authress' )));
	}

	return $links;
}
add_filter( 'plugin_action_links_' . AUTHRESS_SSO_LOGIN_PLUGIN_BASENAME, 'wp_authress_plugin_action_links' );

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
	if ( ! authress_get_configuration_data_from_key( 'override_wp_avatars' ) ) {
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

	$authressProfile = Authress_Sso_Login_UsersRepo::get_authress_profile( $user_id );

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

function wp_authress_callback_step1() {
	$consent_url = sprintf('https://authress.io/app/#/wordpress?hostedUrl=%s', rawurlencode(admin_url( 'admin.php?page=authress')));
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
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(esc_attr_e( 'Not authorized.', 'wp-authress' ) );
	}

	$error_log = new Authress_Sso_Login_ErrorLog();
	$error_log->clear();

	wp_safe_redirect( admin_url( 'admin.php?page=authress_errors&cleared=1' ) );
	exit;
}
add_action( 'admin_action_wp_authress_clear_error_log', 'wp_authress_errorlog_clear_error_log' );

function wp_authress_initial_setup_init() {
	authress_debug_log('wp_authress_initial_setup_init');
	return false;
}
add_action( 'init', 'wp_authress_initial_setup_init', 1 );

function wp_authress_init() {
	authress_debug_log('wp_authress_init()');
	$router = new Authress_Sso_Login_Routes( Authress_Sso_Login_Options::Instance() );
	$router->setup_rewrites();
}
add_action( 'init', 'wp_authress_init');

function check_for_user_logged_in() {
	authress_debug_log('check_for_user_logged_in');
	
	if (!is_user_logged_in() && isset($_REQUEST['nonce'])) {
		$users_repo    = new Authress_Sso_Login_UsersRepo( Authress_Sso_Login_Options::Instance() );
		$login_manager = new Authress_Sso_Login_LoginManager( $users_repo, Authress_Sso_Login_Options::Instance() );
		$login_manager->init_authress();
		wp_safe_redirect(home_url());
		exit();

		// if (is_user_logged_in()) {
		// 	wp_safe_redirect(home_url());
		// 	authress_debug_log('User successfully now logged in during handler');
		// } else {
		// 	authress_debug_log('User NOT logged in during handler');
		// }
		// wp_safe_redirect();
		// authress_debug_log($_REQUEST['redirect_to']);
		// if ($_REQUEST['redirect_to']) {
		// 	// wp_redirect(urldecode($_REQUEST['redirect_to']));
		// 	wp_safe_redirect(urldecode($_REQUEST['redirect_to']));
		// 	exit;
		// }
	}
}
add_action('init', 'check_for_user_logged_in');

function wp_authress_show_delete_identity() {
	$profile_delete_data = new Authress_Sso_Login_Profile_Delete_Data();
	$profile_delete_data->show_delete_identity();
}
add_action( 'edit_user_profile', 'wp_authress_show_delete_identity' );
add_action( 'show_user_profile', 'wp_authress_show_delete_identity' );

function wp_authress_delete_user_data() {
	$profile_delete_data = new Authress_Sso_Login_Profile_Delete_Data();
	$profile_delete_data->delete_user_data();
}
add_action( 'wp_ajax_authress_delete_data', 'wp_authress_delete_user_data' );

function wp_authress_init_admin_menu() {
	authress_debug_log('wp_authress_init_admin_menu');
	if (is_admin() && !empty($_REQUEST['page']) && 'authress_help' === $_REQUEST['page']) {
		wp_safe_redirect( admin_url( 'admin.php?page=authress_configuration#help' ), 301 );
		exit;
	}

	$options       = Authress_Sso_Login_Options::Instance();
	$initial_setup = new Authress_Sso_Login_InitialSetup( $options );
	$routes        = new Authress_Sso_Login_Routes( $options );
	$admin         = new Authress_Sso_Login_Admin( $options, $routes );

	$setup_slug  = 'authress';
	$setup_title = __( 'Setup Wizard', 'wp-authress' );
	$setup_func  = [ $initial_setup, 'render_setup_page' ];

	$settings_slug  = 'authress_configuration';
	$settings_title = __( 'Settings', 'wp-authress' );
	$settings_func  = [ $admin, 'render_settings_page' ];

	$menu_parent = $setup_slug;
	$cap         = 'manage_options';

	add_menu_page('Authress', 'Authress', $cap, $menu_parent, $setup_func, AUTHRESS_SSO_LOGIN_PLUGIN_IMG_URL . 'logo_16x16.png', 86);

	add_submenu_page($menu_parent, $setup_title, $setup_title, $cap, $setup_slug, $setup_func );
	add_submenu_page($menu_parent, $settings_title, $settings_title, $cap, $settings_slug, $settings_func );
	add_submenu_page($menu_parent, __( 'Error Log', 'wp-authress' ), __( 'Error Log', 'wp-authress' ), $cap, 'authress_errors', [ new Authress_Sso_Login_ErrorLog(), 'render_settings_page' ]);
	add_submenu_page($menu_parent, __( 'Help', 'wp-authress' ), __( 'Help', 'wp-authress' ), $cap, 'authress_help', '__return_false');
}
add_action( 'admin_menu', 'wp_authress_init_admin_menu', 96, 0 );

function wp_authress_create_account_message() {
	// Null coalescing validates input variable.
	$current_page     = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : null;
	$is_correct_admin = in_array( $current_page, [ 'authress_configuration', 'authress_errors' ], true);
	if ( authress_plugin_has_been_fully_configured() || ! $is_correct_admin ) {
		return false;
	}

	printf('<div class="update-nag">%s<strong><a href="%s">%s</a></strong>.</div>',
		esc_attr_e( 'SSO Login is not yet configured. Please use the ', 'wp-authress' ),
		esc_url(admin_url( 'admin.php?page=authress' )),
		esc_attr_e( 'Setup Wizard', 'wp-authress' )
	);
	return true;
}
add_action( 'admin_notices', 'wp_authress_create_account_message' );

function wp_authress_init_admin() {
	authress_debug_log('wp_authress_init_admin');
	$options = Authress_Sso_Login_Options::Instance();
	$routes  = new Authress_Sso_Login_Routes( $options );
	$admin   = new Authress_Sso_Login_Admin( $options, $routes );
	$admin->init_admin();
}
add_action( 'admin_init', 'wp_authress_init_admin' );

function wp_authress_admin_enqueue_scripts() {
	authress_debug_log('wp_authress_admin_enqueue_scripts');
	$options = Authress_Sso_Login_Options::Instance();
	$routes  = new Authress_Sso_Login_Routes( $options );
	$admin   = new Authress_Sso_Login_Admin( $options, $routes );
	return $admin->admin_enqueue();
}
add_action( 'admin_enqueue_scripts', 'wp_authress_admin_enqueue_scripts', 1 );

function wp_authress_custom_requests( $wp, $return = false ) {
	$routes = new Authress_Sso_Login_Routes( Authress_Sso_Login_Options::Instance() );
	return $routes->custom_requests( $wp, $return );
}
add_action( 'parse_request', 'wp_authress_custom_requests' );

function wp_authress_profile_enqueue_scripts() {
	authress_debug_log('wp_authress_profile_enqueue_scripts');
	global $pagenow;

	if ( ! in_array( $pagenow, [ 'profile.php', 'user-edit.php' ], true) ) {
		return false;
	}

	wp_enqueue_script(
		'wp_authress_user_profile',
		AUTHRESS_SSO_LOGIN_PLUGIN_JS_URL . 'edit-user-profile.js',
		[ 'jquery' ],
		AUTHRESS_SSO_LOGIN_VERSION,
		false
	);

	$profile  = Authress_Sso_Login_UsersRepo::get_authress_profile( $GLOBALS['user_id'] );
	$strategy = isset( $profile->sub ) ? Authress_Sso_Login_Users::get_strategy( $profile->sub ) : '';

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
				'cannotChangeEmail' => __( 'This email is attached to SSO Login provider, and must be changed there.', 'wp-authress' ),
			],
		]
	);

	return true;
}
add_action( 'admin_enqueue_scripts', 'wp_authress_profile_enqueue_scripts' );

function authress_wp_page_loaded() {
	$users_repo    = new Authress_Sso_Login_UsersRepo( Authress_Sso_Login_Options::Instance() );
	$login_manager = new Authress_Sso_Login_LoginManager( $users_repo, Authress_Sso_Login_Options::Instance() );
	authress_debug_log('authress_wp_page_loaded');
	return $login_manager->init_authress();
}

function authress_wp_redirect_handled($location) {
	return $location;
}

add_action( 'wp_redirect', 'authress_wp_redirect_handled', 1 );
add_action( 'template_redirect', 'authress_wp_page_loaded', 1 );

function authress_wp_login_widget_loaded() {
	$users_repo    = new Authress_Sso_Login_UsersRepo( Authress_Sso_Login_Options::Instance() );
	$login_manager = new Authress_Sso_Login_LoginManager( $users_repo, Authress_Sso_Login_Options::Instance() );
	authress_debug_log('authress_wp_login_widget_loaded');
	return $login_manager->login_auto();
}
add_action( 'login_init', 'authress_wp_login_widget_loaded' );

function wp_authress_process_logout() {
	$users_repo    = new Authress_Sso_Login_UsersRepo( Authress_Sso_Login_Options::Instance() );
	$login_manager = new Authress_Sso_Login_LoginManager( $users_repo, Authress_Sso_Login_Options::Instance() );
	$login_manager->logout();
}
add_action( 'wp_logout', 'wp_authress_process_logout' );


function wp_authress_ajax_delete_cache_transient() {
	check_ajax_referer( 'authress_delete_cache_transient' );
	delete_transient( AUTHRESS_SSO_LOGIN_JWKS_CACHE_TRANSIENT_NAME );
	wp_send_json_success();
}
add_action( 'wp_ajax_authress_delete_cache_transient', 'wp_authress_ajax_delete_cache_transient' );

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

	if ( authress_show_user_wordpress_login_form() && isset( $_REQUEST['wle'] ) ) {
		// We are on an override page.
		$wp_login_url = add_query_arg( 'wle', sanitize_text_field( wp_unslash( $_REQUEST['wle'] ) ), $wp_login_url );
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

	if ( authress_show_user_wordpress_login_form() && isset( $_REQUEST['wle'] ) ) {
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
	if ( authress_show_user_wordpress_login_form() ) {
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
