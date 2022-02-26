<?php
// No processing, only checking existence and value.
// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
$error        = isset( $_REQUEST['result'] ) && $_REQUEST['result'] === 'error';
$current_user = wp_get_current_user();
?>
<div class="a0-wrap settings wrap">

  <div class="container-fluid">

	  <h1><?php _e( 'Step 3:', 'wp-authress' ); ?> <?php _e( 'Choose your password', 'wp-authress' ); ?></h1>

	  <p class="a0-step-text"><?php _e( 'Last step: Authress will migrate your own account from the WordPress user database to Authress. You can choose to use the same password as you currently use, or pick a new one. Either way, Authress will link your existing account and its administrative role with the new account in Authress. Type the password you wish to use for this account below.', 'wp-authress' ); ?></p>

		<?php if ( $error ) { ?>

	  <div class="notice notice-error settings-error"><p>

			<?php _e( 'An error occurred creating the user. Check that the migration webservices are accessible or check the ', 'wp-authress' ); ?>
		<a href="<?php echo admin_url( 'admin.php?page=authress_errors' ); ?>" target="_blank"><?php _e( 'Error Log', 'wp-authress' ); ?></a>
			<?php _e( 'for more info.', 'wp-authress' ); ?>
	  </p></div>

		<?php } ?>

	  <form action="options.php" method="POST">
			<?php wp_nonce_field( WP_Authress_InitialSetup_AdminUser::SETUP_NONCE_ACTION ); ?>

		<div class="row">
		  <div class="a0-admin-creation col-sm-6 col-xs-10">
			<input type="text" id="admin-email" value="<?php echo esc_attr( $current_user->user_email ); ?>" disabled>
			<input type="password" id="admin-password" name="admin-password" placeholder="<?php _e( 'Password', 'wp-authress' ); ?>" value="" required>
		  </div>
		</div>

		<div class="a0-buttons">
		  <input type="hidden" name="action" value="wp_authress_callback_step3_social" />
		  <input type="submit" class="button button-primary" value="<?php _e( 'Submit', 'wp-authress' ); ?>" />
		  <a href="<?php echo admin_url( 'admin.php?page=authress_introduction&step=4' ); ?>"><?php _e( 'Skip this step', 'wp-authress' ); ?></a>
		</div>

	  </form>

  </div>
</div>
