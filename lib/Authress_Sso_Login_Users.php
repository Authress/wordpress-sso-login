<?php
class Authress_Sso_Login_Users {
	/**
	 * Create a WordPress user with Authress data.
	 *
	 * @param object $userinfo - User profile data from Authress.
	 *
	 * @return int|WP_Error
	 */
	public static function create_user($userinfo) {
		$email = null;
		if ( isset( $userinfo->email ) ) {
			$email = $userinfo->email;
		}
		if ( empty( $email ) ) {
			$email = 'user-' . $userinfo->sub . '-' . uniqid() . '@' . wp_parse_url(get_site_url())['host'];
		}

		// Generate a random password
		$password = wp_generate_password(1024);

		$firstname = '';
		$lastname  = '';

		if ( isset( $userinfo->name ) ) {
			// Split the name into first- and lastname
			$names = explode( ' ', $userinfo->name );

			if ( count( $names ) === 1 ) {
				$firstname = $userinfo->name;
			} elseif ( count( $names ) === 2 ) {
				$firstname = $names[0];
				$lastname  = $names[1];
			} else {
				$lastname  = array_pop( $names );
				$firstname = implode( ' ', $names );
			}
		}

		$displayName = '';
		if ( isset( $userinfo->preferred_username ) ) {
			$displayName = $userinfo->preferred_username;
		} elseif ( isset( $userinfo->nickname ) ) {
			$displayName = $userinfo->nickname;
		} elseif ( isset( $userinfo->name ) ) {
			$displayName = $userinfo->name;
		} elseif ( isset( $userinfo->username ) ) {
			$displayName = $userinfo->username;
		} elseif ( isset( $userinfo->sub ) ) {
			$displayName = $userinfo->sub;
		}

		$description = '';
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

		// Create the user data array for updating first- and lastname
		$user_data = [
			'user_email'   => $email,
			'user_login'   => $userinfo->sub,
			'user_pass'    => $password,
			'first_name'   => $firstname,
			'last_name'    => $lastname,
			'nickname'     => $displayName,
			'display_name' => $displayName,
			'description'  => $description
		];

		// Update the user
		$user_id = wp_insert_user( $user_data );
		return $user_id;
	}

	/**
	 * Create a WordPress user with Authress data.
	 *
	 * @param object $ID - WordPress userId
	 * @param object $userinfo - User profile data from Authress.
	 *
	 */
	public static function update_user($ID, $userinfo) {
		authress_debug_log('=> Authress_Sso_Login_Users.update_user()');
		$email = null;
		if ( isset( $userinfo->email ) ) {
			$email = $userinfo->email;
		}

		$updatedUserObject = (object) [
			'ID' => $ID
		];

		if ( isset( $email ) ) {
			$updatedUserObject->user_email = $email;
		}

		// Should we force updating the user attributes to sync from the source? Or let the user change their name here?

		// Update the user
		authress_debug_log('    wp_update_user');
		wp_update_user($updatedUserObject);
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
