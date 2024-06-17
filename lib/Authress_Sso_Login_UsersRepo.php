<?php
/**
 * Contains class Authress_Sso_Login_UsersRepo.
 *
 * @package WP-Authress
 *
 * @since 1.2.0
 */

/**
 * Class Authress_Sso_Login_UsersRepo.
 */
class Authress_Sso_Login_UsersRepo {

	/**
	 * Options instance used in this class.
	 *
	 * @var Authress_Sso_Login_Options
	 */
	protected $a0_options;

	/**
	 * Authress_Sso_Login_UsersRepo constructor.
	 *
	 * @param Authress_Sso_Login_Options $a0_options - Options instance used in this class.
	 */
	public function __construct( Authress_Sso_Login_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	/**
	 * Update WP user with an incoming Authress one or reject with an exception.
	 *
	 * @param object $ID - WordPress user Id
	 * @param object $userinfo - Profile object from Authress.
	 *
	 */
	public function update( $ID, $userinfo ) {

		Authress_Sso_Login_Users::update_user($ID, $userinfo);
	}

	/**
	 * Create or join a WP user with an incoming Authress one or reject with an exception.
	 *
	 * @param object $userinfo - Profile object from Authress.
	 *
	 * @return int|null|WP_Error
	 *
	 * @throws Authress_Sso_Login_CouldNotCreateUserException - When the user could not be created.
	 */
	public function create( $userinfo ) {

		$authress_sub      = $userinfo->sub;
		list($strategy) = explode( '|', $authress_sub );
		$wp_user        = null;
		$user_id        = null;

		// WP user to join with incoming Authress user.
		if ( ! empty( $userinfo->email ) ) {
			$wp_user = get_user_by( 'email', $userinfo->email );
		}

		if ( is_object( $wp_user ) && $wp_user instanceof WP_User ) {
			// WP user exists, check if we can join.
			$user_id = $wp_user->ID;

			// If the user has a different Authress ID, we cannot join it.
			$current_authress_id = self::get_meta( $user_id, 'authress_id' );
			if ( ! empty( $current_authress_id ) && $authress_sub !== $current_authress_id ) {
				throw new Authress_Sso_Login_CouldNotCreateUserException( __( 'There is a user with the same email.', 'wp-authress' ) );
			}
		} else {
			// } elseif ( ( is_multisite() ? users_can_register_signup_filter() : get_site_option( 'users_can_register' ) ) || $this->a0_options->get( 'auto_provisioning' ) ) {

			// WP user does not exist and registration is allowed.
			$user_id = Authress_Sso_Login_Users::create_user( $userinfo );

			// Check if user was created.
			if ( is_wp_error( $user_id ) ) {
				throw new Authress_Sso_Login_CouldNotCreateUserException( $user_id->get_error_message() );
			} elseif ( $user_id < 0 ) {
				// Registration failed for another reason.
				throw new Authress_Sso_Login_CouldNotCreateUserException();
			}
		}

		$this->update_authress_object( $user_id, $userinfo );
		return $user_id;
	}

	/**
	 * Look for and return a user with an Authress ID
	 *
	 * @param string $id - An Authress user ID, like "provider|id".
	 *
	 * @return null|WP_User
	 */
	public function find_authress_user( $id ) {
		global $wpdb;

		if ( empty( $id ) ) {
			Authress_Sso_Login_ErrorLog::insert_error( __METHOD__, __( 'Empty user id', 'wp-authress' ) );

			return null;
		}

		$query = [
			// Limiting the returned number and this happens on login so some delay is acceptable.
			// phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_key'   => $wpdb->prefix . 'authress_id',
			// phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value' => $id,
			'number'     => 1,
			'blog_id'    => 0,
		];

		$users = get_users( $query );

		if ( $users === [] ) {
			Authress_Sso_Login_ErrorLog::insert_error( __METHOD__ . ' => get_users() ', __( 'User not found', 'wp-authress' ) );
			return null;
		}

		return ! empty( $users[0] ) ? $users[0] : null;
	}

	/**
	 * Get the Authress profile from the database, if one exists.
	 *
	 * @param string $authress_user_id - Authress user ID to find.
	 *
	 * @return mixed
	 */
	//phpcs:ignore
	public static function get_authress_profile( $authress_user_id ) {
		$profile = self::get_meta( $authress_user_id, 'authress_obj' );
		return $profile ? Authress_Sso_Login_Serializer::unserialize( $profile ) : false;
	}

	/**
	 * Update all Authress meta fields for a WordPress user.
	 *
	 * @param int      $user_id - WordPress user ID.
	 * @param stdClass $userinfo - User profile object from Authress.
	 */
	public function update_authress_object( $user_id, $userinfo ) {
		$authress_user_id = isset( $userinfo->user_id ) ? $userinfo->user_id : $userinfo->sub;
		self::update_meta( $user_id, 'authress_id', $authress_user_id );

		$userinfo_encoded = Authress_Sso_Login_Serializer::serialize( $userinfo );
		$userinfo_encoded = wp_slash( $userinfo_encoded );
		self::update_meta( $user_id, 'authress_obj', $userinfo_encoded );

		self::update_meta( $user_id, 'last_update', gmdate( 'c' ) );
	}

	/**
	 * Get a user's Authress meta data.
	 *
	 * @param integer $user_id - WordPress user ID.
	 * @param string  $key - Usermeta key to get.
	 *
	 * @return mixed
	 *
	 * @since 3.8.0
	 */
	public static function get_meta( $user_id, $key ) {
		global $wpdb;
		return get_user_meta( $user_id, $wpdb->prefix . $key, true );
	}

	/**
	 * Update a user's Authress meta data.
	 *
	 * @param integer $user_id - WordPress user ID.
	 * @param string  $key - Usermeta key to update.
	 * @param mixed   $value - Usermeta value to use.
	 *
	 * @return int|bool
	 *
	 * @since 3.11.0
	 */
	public static function update_meta( $user_id, $key, $value ) {
		global $wpdb;
		return update_user_meta( $user_id, $wpdb->prefix . $key, $value );
	}

	/**
	 * Delete a user's Authress meta data.
	 *
	 * @param integer $user_id - WordPress user ID.
	 * @param string  $key - Usermeta key to delete.
	 *
	 * @return bool
	 *
	 * @since 3.11.0
	 */
	public static function delete_meta( $user_id, $key ) {
		global $wpdb;
		return delete_user_meta( $user_id, $wpdb->prefix . $key );
	}
}
