<?php
class WP_Authress_Users {

	/**
	 * Create a WordPress user with Authress data.
	 *
	 * @param object $userinfo - User profile data from Authress.
	 *
	 * @return int|WP_Error
	 */
	public static function create_user( $userinfo ) {
		$email = null;
		if ( isset( $userinfo->email ) ) {
			$email = $userinfo->email;
		}
		if ( empty( $email ) ) {
			$email = 'change_this_email@' . uniqid() . '.com';
		}

		$valid_user = apply_filters( 'wp_authress_should_create_user', true, $userinfo );
		if ( ! $valid_user ) {
			return -2;
		}

		// Generate a random password
		$password = wp_generate_password(1024);

		$firstname = '';
		$lastname  = '';

		if ( isset( $userinfo->name ) ) {
			// Split the name into first- and lastname
			$names = explode( ' ', $userinfo->name );

			if ( count( $names ) == 1 ) {
				$firstname = $userinfo->name;
			} elseif ( count( $names ) == 2 ) {
				$firstname = $names[0];
				$lastname  = $names[1];
			} else {
				$lastname  = array_pop( $names );
				$firstname = implode( ' ', $names );
			}
		}

		$username = '';
		if ( isset( $userinfo->username ) ) {
			$username = $userinfo->username;
		} elseif ( isset( $userinfo->nickname ) ) {
			$username = $userinfo->nickname;
		}
		if ( empty( $username ) ) {
			$username = $email;
		}
		while ( username_exists( $username ) ) {
			$username = $username . rand( 0, 9 );
		}

		$description = '';

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
		// Create the user data array for updating first- and lastname
		$user_data = [
			'user_email'   => $email,
			'user_login'   => $username,
			'user_pass'    => $password,
			'first_name'   => $firstname,
			'last_name'    => $lastname,
			'display_name' => $username,
			'description'  => $description,
		];

		$user_data = apply_filters( 'authress_create_user_data', $user_data, $userinfo );

		// Update the user
		$user_id = wp_insert_user( $user_data );

		if ( ! is_numeric( $user_id ) ) {
			return $user_id;
		}

		do_action( 'wp_authress_user_created', $user_id, $email, $password, $firstname, $lastname );

		// Return the user ID
		return $user_id;
	}

	/**
	 * Get the strategy from an Authress user ID.
	 *
	 * @param string $authress_id - Authress user ID.
	 *
	 * @return string
	 */
	public static function get_strategy( $authress_id ) {
		return 'authress';
	}
}
