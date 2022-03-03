<?php
/**
 * Contains class WP_Authress_Profile_Delete_Data.
 *
 * @package WP-Authress
 *
 * @since 3.8.0
 */

/**
 * Class WP_Authress_Profile_Delete_Data.
 * Provides UI and AJAX handlers to delete a user's Authress data.
 */
class WP_Authress_Profile_Delete_Data {

	/**
	 * Show the delete Authress user data button.
	 * Hooked to: edit_user_profile, show_user_profile
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 */
	public function show_delete_identity() {

		if ( ! isset( $GLOBALS['user_id'] ) || ! current_user_can( 'edit_users', $GLOBALS['user_id'] ) ) {
			return;
		}

		return;

		// $authress_user = get_authressuserinfo( $GLOBALS['user_id'] );
		// if ( ! $authress_user ) {
		// 	return;
		// }

		// ? >
		// <table class="form-table">
		// 	<tr>
		// 		<th>
		// 			<label><?php esc_attr_e( 'Delete Authress Data', 'wp-authress' ); ? ></label>
		// 		</th>
		// 		<td>
		// 			<input type="button" id="authress_delete_data" class="button button-secondary"
		// 				value="<?php esc_attr_e( 'Delete Authress Data', 'wp-authress' ); ? >" />
		// 			<br><br>
		// 			<a href="https://authress.io/app/#/setup?focus=explorer<?php echo esc_attr(rawurlencode( $authress_user->sub )); ? >" target="_blank">
		// 				< ?php esc_attr_e( 'View in Authress', 'wp-authress' ); ? >
		// 			</a>
		// 		</td>
		// 	</tr>
		// </table>
		// <?php
	}

	/**
	 * AJAX function to delete Authress data in the usermeta table.
	 * Hooked to: wp_ajax_authress_delete_data
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 */
	public function delete_user_data() {
		check_ajax_referer( 'delete_authress_identity' );

		if ( empty( $_POST['user_id'] ) ) {
			wp_send_json_error( [ 'error' => __( 'Empty user_id', 'wp-authress' ) ] );
		}

		$user_id = absint( $_POST['user_id'] );

		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( [ 'error' => __( 'Forbidden', 'wp-authress' ) ] );
		}

		WP_Authress_UsersRepo::delete_meta( $user_id, 'authress_id' );
		WP_Authress_UsersRepo::delete_meta( $user_id, 'authress_obj' );
		WP_Authress_UsersRepo::delete_meta( $user_id, 'last_update' );
		WP_Authress_UsersRepo::delete_meta( $user_id, 'authress_transient_email_update' );
		wp_send_json_success();
	}
}
