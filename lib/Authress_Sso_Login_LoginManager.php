<?php
/**
 * Authress_Sso_Login_LoginManager class
 *
 * @package WordPress
 * @subpackage WP-Authress
 * @since 2.0.0
 */

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Signer\Key\InMemory;

 /**
 * Handles login callbacks and auto-login redirecting
 *
 * @since 2.0.0
 */
class Authress_Sso_Login_LoginManager {

	/**
	 * Instance of Authress_Sso_Login_Options.
	 *
	 * @var null|Authress_Sso_Login_Options
	 */
	protected $a0_options;

	/**
	 * User strategy to use.
	 *
	 * @var Authress_Sso_Login_UsersRepo
	 */
	protected $users_repo;

	/**
	 * Authress_Sso_Login_LoginManager constructor.
	 *
	 * @param Authress_Sso_Login_UsersRepo $users_repo - see member variable doc comment.
	 * @param Authress_Sso_Login_Options   $a0_options - see member variable doc comment.
	 */
	public function __construct( Authress_Sso_Login_UsersRepo $users_repo, Authress_Sso_Login_Options $a0_options ) {
		$this->users_repo = $users_repo;
		$this->a0_options = $a0_options;
	}

	/**
	 * Redirect logged-in users from wp-login.php.
	 * Redirect to Universal Login Page under certain conditions and if the option is turned on.
	 *
	 * @return bool
	 */
	public function login_auto() {
		authress_debug_log('login_auto');
		// Not processing form data, just using a redirect parameter if present.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		// Do not redirect anywhere if this is a logout action.
		if ( authress_user_is_currently_on_login_action( [ 'logout' ] ) ) {
			return false;
		}

		// Do not redirect login page override.
		if ( authress_show_user_wordpress_login_form() ) {
			return false;
		}

		// If the user has a WP session, determine where they should end up and redirect.
		if ( is_user_logged_in() ) {
			$login_redirect = empty( $_REQUEST['redirect_to'] ) ?
				$this->a0_options->get( 'default_login_redirection' ) :
				filter_var( wp_unslash( $_REQUEST['redirect_to'] ), FILTER_SANITIZE_URL );

			// Add a cache buster to avoid an infinite redirect loop on pages that check for auth.
			$login_redirect = add_query_arg( time(), '', $login_redirect );
			wp_safe_redirect( $login_redirect );
			exit;
		}

		return $this->init_authress();

		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	/**
	 * Process an incoming successful login from Authress, aka login callback.
	 * Authress must be configured and 'authress' URL parameter not empty.
	 * Handles errors and state validation
	 */
	public function init_authress() {
		authress_debug_log('init_authress');

		// Not an Authress login process or settings are not configured to allow logins.
		if ( ! authress_plugin_has_been_fully_configured() ) {
			return false;
		}

		// Catch any incoming errors and stop the login process.
		if ( ! empty( $_REQUEST['error'] ) || ! empty( $_REQUEST['error_description'] ) ) {
			// Input variable is sanitized.
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$error_msg  = sanitize_text_field( rawurldecode( wp_unslash( $_REQUEST['error_description'] ) ) );
			$error_code = sanitize_text_field( rawurldecode( wp_unslash( $_REQUEST['error'] ) ) );
			// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$this->die_on_login( $error_msg, $error_code );
			return false;
		}

		// No need to process a login if the user is already logged in and there is no error.
		if ( is_user_logged_in() ) {
			// wp_safe_redirect( $this->a0_options->get( 'default_login_redirection' ) );
			authress_debug_log('user_logged_in: returning without further setup');
			return true;
		}

		try {
			return $this->handle_login_redirect();
		} catch ( Authress_Sso_Login_LoginFlowValidationException $e ) {

			// Errors encountered during the OAuth login flow.
			$this->die_on_login( $e->getMessage(), $e->getCode() );
		} catch ( Authress_Sso_Login_BeforeLoginException $e ) {

			// Errors encountered during the WordPress login flow.
			$this->die_on_login( $e->getMessage(), $e->getCode() );
		} catch ( Authress_Sso_Login_InvalidIdTokenException $e ) {
			$code            = 'invalid_id_token';
			$display_message = __( 'Invalid ID token', 'wp-authress' );
			Authress_Sso_Login_ErrorLog::insert_error(__METHOD__ . ' L:' . __LINE__, new WP_Error( $code, $display_message . ': ' . $e->getMessage() ));
			$this->die_on_login( $display_message, $code );
		}

		return is_user_logged_in();
	}

	/**
	 * Main login flow using the Authorization Code Grant.
	 *
	 * @throws Authress_Sso_Login_LoginFlowValidationException - OAuth login flow errors.
	 * @throws Authress_Sso_Login_BeforeLoginException - Errors encountered during the authress_before_login action.
	 * @throws Authress_Sso_Login_InvalidIdTokenException If the ID token does not validate.
	 *
	 */
	public function handle_login_redirect() {
		authress_debug_log('handle_login_redirect');
		$access_token = sanitize_text_field(isset($_COOKIE['authorization']) ? wp_unslash($_COOKIE['authorization']) : '');
		if (!isset($_COOKIE['authorization']) && isset($_REQUEST['access_token'])) {
			$access_token = sanitize_text_field(wp_unslash($_REQUEST['access_token']));
			setcookie('authorization', $access_token);
		}

		$id_token = sanitize_text_field(wp_unslash(isset($_COOKIE['user']) ? $_COOKIE['user'] : ''));
		if (!isset($_COOKIE['user']) && isset($_REQUEST['id_token'])) {
			$id_token = sanitize_text_field(wp_unslash($_REQUEST['id_token']));
			setcookie('user', $id_token);
		}

		authress_debug_log('access_token: ' . $access_token);
		authress_debug_log('id_token:' . $id_token);

		if (empty($id_token) || empty($access_token)) {
			authress_debug_log('No tokens set, user is not logged in');
			return false;
		}

		// Decode the incoming ID token for the Authress user.
		$decoded_token = $this->decode_id_token( $id_token );
		$userinfo = $this->clean_id_token( $decoded_token );

		if ( $this->login_user($userinfo) ) {
			authress_debug_log('Tokens set, user is logged in');
			return true;
		}
	}

	/**
	 * Attempts to log the user in and create a new user, if possible/needed.
	 *
	 * @param object      $userinfo - Authress profile of the user.
	 *
	 * @return bool
	 *
	 * @throws Authress_Sso_Login_LoginFlowValidationException - OAuth login flow errors.
	 * @throws Authress_Sso_Login_BeforeLoginException - Errors encountered during the authress_before_login action.
	 */
	public function login_user( $userinfo) {
		$authress_sub        = $userinfo->sub;
		list( $strategy ) = explode( '|', $authress_sub );
		$user = $this->users_repo->find_authress_user( $authress_sub );

		if ( ! is_null( $user ) ) {
			authress_debug_log('Existing user: updating');
			// User exists so log them in.
			if ( isset( $userinfo->email ) && $user->data->user_email !== $userinfo->email ) {
				$description = $user->data->description;
				if ( empty( $description ) ) {
					if ( isset( $userinfo->headline ) ) {
						$description = $userinfo->headline;
					}
					if ( isset( $userinfo->description ) ) {
						$description = $userinfo->description;
					}
					if ( isset( $userinfo->bio ) ) {
						$description = $userinfo->bio;
					}
					if ( isset( $userinfo->about ) ) {
						$description = $userinfo->about;
					}
				}

				wp_update_user(
					(object) [
						'ID'          => $user->data->ID,
						'user_email'  => $userinfo->email,
						'description' => $description,
					]
				);
			}

			$this->users_repo->update_authress_object( $user->data->ID, $userinfo );
			$this->do_login( $user);
			return is_user_logged_in();
		}
		try {
			authress_debug_log('New user: creating.');
			$creator = new Authress_Sso_Login_UsersRepo( $this->a0_options );
			$user_id = $creator->create( $userinfo);
			$user    = get_user_by( 'id', $user_id );
			$this->do_login( $user);
			return is_user_logged_in();
		} catch ( Authress_Sso_Login_CouldNotCreateUserException $e ) {

			throw new Authress_Sso_Login_LoginFlowValidationException( $e->getMessage() );
		} catch ( Authress_Sso_Login_RegistrationNotEnabledException $e ) {

			$msg = __( 'Could not create user. The registration process is not available. Please contact your site’s administrator.', 'wp-authress' );
			throw new Authress_Sso_Login_LoginFlowValidationException( $msg );
		}
		return is_user_logged_in();
	}

	/**
	 * Does all actions required to log the user in to WordPress, invoking hooks as necessary
	 *
	 * @param object      $user - the WP user object, such as returned by get_user_by().
	 *
	 * @throws Authress_Sso_Login_BeforeLoginException - Errors encountered during the authress_before_login action.
	 */
	private function do_login( $user) {
		authress_debug_log('LoginManager.do_login');
		$remember_users_session = $this->a0_options->get( 'remember_users_session', true);

		$secure_cookie = is_ssl();
		$secure_cookie = apply_filters('secure_signon_cookie',
			$secure_cookie,
			[
				'user_login' => $user->user_login,
				'user_password' => null,
				'remember' => $remember_users_session
			]
		);

		authress_debug_log($user->user_login);

		wp_set_auth_cookie( $user->ID, $remember_users_session, $secure_cookie );
		do_action( 'wp_login', $user->user_login, $user );
	}

	/**
	 * Complete the logout process based on settings.
	 * Hooked to `wp_logout` action.
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 *
	 * @see Authress_Sso_Login_LoginManager::init()
	 *
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_logout
	 */
	public function logout() {
		setcookie('user', '');
		setcookie('authorization', '');
		if ( ! authress_plugin_has_been_fully_configured() ) {
			return;
		}

		wp_safe_redirect( home_url() );
	}

	/**
	 * Get a value from query_vars or request global.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/query_vars/
	 *
	 * @param string $key - query var key to return.
	 *
	 * @return string|null
	 */
	protected function query_vars( $key ) {
		global $wp_query;

		if ( isset( $wp_query->query_vars[ $key ] ) ) {
			return $wp_query->query_vars[ $key ];
		}

		if ( isset( $_REQUEST[ $key ] ) ) {
			return sanitize_text_field(wp_unslash( $_REQUEST[ $key ] ));
		}

		return null;
	}

	/**
	 * Die during login process with a message
	 *
	 * @param string     $msg - translated error message to display.
	 * @param string|int $code - error code, if given.
	 */
	protected function die_on_login( $msg = '', $code = 0 ) {
		authress_debug_log('Ending User Session.');

		// Log the user out completely.
		wp_destroy_current_session();
		wp_logout();
		wp_clear_auth_cookie();
		wp_set_current_user( 0 );
		wp_safe_redirect(wp_login_url());
		exit;

		// $html = sprintf(
		// 	'%s: %s [%s: %s]<br><br><a href="%s">%s</a>',
		// 	__( 'There was a problem with your log in', 'wp-authress' ),
		// 	! empty( $msg )
		// 		? sanitize_text_field( $msg )
		// 		: __( 'Please see the site administrator', 'wp-authress' ),
		// 	__( 'error code', 'wp-authress' ),
		// 	$code ? sanitize_text_field( $code ) : __( 'unknown', 'wp-authress' ),
		// 	$this->authress_logout_url( wp_login_url() ),
		// 	__( '← Login', 'wp-authress' )
		// );

		// wp_die($html);
	}

	/**
	 * @param string $id_token
	 * @return object
	 * @throws Authress_Sso_Login_InvalidIdTokenException - Token was not valid.
	 */
	private function decode_id_token( $id_token ) {
		$expectedIss = $this->a0_options->get_auth_domain();

		$config = Configuration::forUnsecuredSigner();
		$token = $config->parser()->parse($id_token);
		$keyId = $token->headers()->get('kid');

		$client = new GuzzleHttp\Client([
			'base_uri' => $expectedIss,
			'decode_content' => false
		]);

		$response = $client->request('GET', '/.well-known/openid-configuration/jwks');
		$keys = json_decode($response->getBody()->getContents())->keys;

		$jwk = null;
		$signer = null;
		foreach ( $keys as $element ) {
			if ( $keyId !== $element->kid ) {
				continue;
			}

			if ($element->alg === 'RS512') {
				$signer = new Signer\Rsa\Sha512();
				$jwkConverter = new CoderCat\JWKToPEM\JWKConverter();
				$jwk = InMemory::plainText($jwkConverter->toPEM(json_decode(wp_json_encode($element), true)));
			} else {
				$signer = new Signer\Eddsa();
				$jwk = InMemory::plainText(base64_decode(strtr($element->x, '-_', '+/')), true);
			}
		}

		if (empty($jwk) || $jwk === null) {
			authress_debug_log('   No $JWK found: ' . wp_json_encode($keys));
			throw new Authress_Sso_Login_InvalidIdTokenException();
		}

		$config->setValidationConstraints(new Constraint\LooseValidAt(SystemClock::fromUTC()));
		$config->setValidationConstraints(new Constraint\IssuedBy($expectedIss));
		$config->setValidationConstraints(new Constraint\SignedWith($signer, $jwk));
		$constraints = $config->validationConstraints();

		try {
			$config->validator()->assert($token, ...$constraints);
			$userObject = (object) $token->claims()->all();
			return $userObject;
		} catch (RequiredConstraintsViolated $e) {
			authress_debug_log('   Invalid user authentication token. Error:' . $e->violations());
			Authress_Sso_Login_ErrorLog::insert_error( __METHOD__, __( 'Invalid user authentication token:', 'wp-authress' ) . $e->violations());
			throw new Authress_Sso_Login_InvalidIdTokenException($e);
		} catch (Exception $e) {
			authress_debug_log('   Invalid user authentication token. Error:' . $e->getMessage());
			Authress_Sso_Login_ErrorLog::insert_error( __METHOD__, __( 'Failed to verify authentication token:', 'wp-authress' ) . $e->getMessage());
			throw new Authress_Sso_Login_InvalidIdTokenException($e);
		}
	}

	/**
	 * Remove unnecessary ID token properties.
	 *
	 * @param stdClass $id_token_obj - ID token object to clean.
	 *
	 * @return stdClass
	 *
	 * @codeCoverageIgnore - Private method
	 */
	private function clean_id_token( $id_token_obj ) {
		foreach ( [ 'iss', 'aud', 'iat', 'exp', 'nonce' ] as $attr ) {
			unset( $id_token_obj->$attr );
		}
		return $id_token_obj;
	}

	/**
	 * Generate the Authress logout URL.
	 *
	 * @param string|null $return_to - Site URL to return to after logging out.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore - Private method
	 */
	private function authress_logout_url( $return_to = null ) {
		return sprintf('/wp-login.php?action=logout');
	}
}
