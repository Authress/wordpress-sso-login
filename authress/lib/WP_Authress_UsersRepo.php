<?php
/**
 * Contains class WP_Authress_UsersRepo.
 *
 * @package WP-Authress
 *
 * @since 1.2.0
 */

/**
 * Class WP_Authress_UsersRepo.
 */
class WP_Authress_UsersRepo {

	/**
	 * Options instance used in this class.
	 *
	 * @var WP_Authress_Options
	 */
	protected $a0_options;

	/**
	 * WP_Authress_UsersRepo constructor.
	 *
	 * @param WP_Authress_Options $a0_options - Options instance used in this class.
	 */
	public function __construct( WP_Authress_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	/**
	 * Create or join a WP user with an incoming Authress one or reject with an exception.
	 *
	 * @param object $userinfo - Profile object from Authress.
	 * @param string $token - ID token from Authress.
	 *
	 * @return int|null|WP_Error
	 *
	 * @throws WP_Authress_CouldNotCreateUserException - When the user could not be created.
	 * @throws WP_Authress_EmailNotVerifiedException - When a users's email is not verified but the site requires it.
	 * @throws WP_Authress_RegistrationNotEnabledException - When registration is not turned on for this site.
	 */
	public function create( $userinfo, $token ) {

		$authress_sub      = $userinfo->sub;
		list($strategy) = explode( '|', $authress_sub );
		$wp_user        = null;
		$user_id        = null;

		// Check legacy identities profile object for a DB connection.
		$is_db_connection = 'authress' === $strategy;
		if ( ! $is_db_connection && ! empty( $userinfo->identities ) ) {
			foreach ( $userinfo->identities as $identity ) {
				if ( 'authress' === $identity->provider ) {
					$is_db_connection = true;
					break;
				}
			}
		}

		// Email is considered verified if flagged as such, if we ignore the requirement, or if the strategy is skipped.
		$email_verified = ! empty( $userinfo->email_verified )
			|| $this->a0_options->strategy_skips_verified_email( $strategy );

		// WP user to join with incoming Authress user.
		if ( ! empty( $userinfo->email ) ) {
			$wp_user = get_user_by( 'email', $userinfo->email );
		}

		if ( is_object( $wp_user ) && $wp_user instanceof WP_User ) {
			// WP user exists, check if we can join.
			$user_id = $wp_user->ID;

			// Cannot join a DB connection user without a verified email.
			if ( $is_db_connection && ! $email_verified ) {
				throw new WP_Authress_EmailNotVerifiedException( $userinfo, $token );
			}

			// If the user has a different Authress ID, we cannot join it.
			$current_authress_id = self::get_meta( $user_id, 'authress_id' );
			if ( ! empty( $current_authress_id ) && $authress_sub !== $current_authress_id ) {
				throw new WP_Authress_CouldNotCreateUserException( __( 'There is a user with the same email.', 'wp-authress' ) );
			}
		} elseif ( $this->a0_options->is_wp_registration_enabled() || $this->a0_options->get( 'auto_provisioning' ) ) {
			// WP user does not exist and registration is allowed.
			$user_id = WP_Authress_Users::create_user( $userinfo );

			// Check if user was created.
			if ( is_wp_error( $user_id ) ) {
				throw new WP_Authress_CouldNotCreateUserException( $user_id->get_error_message() );
			} elseif ( -2 === $user_id ) {
				// Registration rejected by wp_authress_should_create_user filter in WP_Authress_Users::create_user().
				throw new WP_Authress_CouldNotCreateUserException( __( 'Registration rejected.', 'wp-authress' ) );
			} elseif ( $user_id < 0 ) {
				// Registration failed for another reason.
				throw new WP_Authress_CouldNotCreateUserException();
			}
		} else {
			// Signup is not allowed.
			throw new WP_Authress_RegistrationNotEnabledException();
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
			WP_Authress_ErrorLog::insert_error( __METHOD__, __( 'Empty user id', 'wp-authress' ) );

			return null;
		}

		/**
		 * Short-circuits the user query below.
		 *
		 * Returning a WP_User object will stop the method here and use the returned user.
		 *
		 * @param string $id The Authress ID.
		 */
		$check = apply_filters( 'find_authress_user', null, $id );
		if ( $check instanceof WP_User ) {
			return $check;
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
			WP_Authress_ErrorLog::insert_error( __METHOD__ . ' => get_users() ', __( 'User not found', 'wp-authress' ) );
			return null;
		}

		return ! empty( $users[0] ) ? $users[0] : null;
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

		$userinfo_encoded = WP_Authress_Serializer::serialize( $userinfo );
		$userinfo_encoded = wp_slash( $userinfo_encoded );
		self::update_meta( $user_id, 'authress_obj', $userinfo_encoded );

		self::update_meta( $user_id, 'last_update', date( 'c' ) );
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

		/**
		 * Short circuits the return value of the Authress user meta field.
		 *
		 * Returning a non null value will stop the method here and use the returned value.
		 *
		 * @param integer $user_id The user ID.
		 * @param string  $key     The meta key.
		 */
		$check = apply_filters( 'authress_get_meta', null, $user_id, $key );
		if ( $check !== null ) {
			return $check;
		}

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

		/**
		 * Short circuits updating a user's Authress meta values.
		 *
		 * Returning a non null value will stop the method here.
		 * The returned value is a boolean indicating whether or not the update was successful.
		 *
		 * @param integer $user_id The user ID.
		 * @param string  $key     The meta key.
		 */
		$check = apply_filters( 'authress_update_meta', null, $user_id, $key );
		if ( $check !== null ) {
			return (bool) $check;
		}

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

		/**
		 * Short circuits deleting a user's Authress meta values.
		 *
		 * Returning a non null value will stop the method here.
		 * The returned value is a boolean indicating whether or not the deletion was successful.
		 *
		 * @param integer $user_id The user ID.
		 * @param string  $key     The meta key.
		 */
		$check = apply_filters( 'authress_delete_meta', null, $user_id, $key );
		if ( $check !== null ) {
			return (bool) $check;
		}
		global $wpdb;
		return delete_user_meta( $user_id, $wpdb->prefix . $key );
	}
}
