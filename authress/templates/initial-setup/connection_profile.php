<div class="a0-wrap settings wrap">
  	<div class="container-fluid">
	  <h1><?php _e( 'Authress Setup Wizard', 'wp-authress' ); ?></h1>
	  	<p><?php _e( "SSO Login enables Users to log in with their employee credentials through their identity provider. Using SSO Login will increase your WordPress site's security and consolidate identity data.", 'wp-authress' ); ?></p>
		
		<p><?php _e( 'Once configured, this plugin replaces the WordPress login screen, by many additional features to make login easier and more secure your users.', 'wp-authress' ); ?></p>

		<p><?php _e( 'For more information on installation and configuration, please see the', 'wp-authress' );
		printf(' <strong><a href="https://authress.io/knowledge-base" target="_blank">%s</a></strong>', __( 'Authress Knowledge Base', 'wp-authress' )); ?>.</p>

		<!-- <?php if ( wp_authress_is_ready() ) : ?>
		  <div class="a0-step-text a0-message a0-warning">
			  <p>
				  <?php _e( 'SSO Login is set up and ready for use.', 'wp-authress' ); ?>
				  <?php _e( 'To start over and re-run the Setup Wizard:', 'wp-authress' ); ?>
			  </p>
			  <ol>
				<li>
					<a href="<?php echo admin_url( 'admin.php?page=authress#basic' ); ?>"><?php _e( 'Go to Authress > Settings > Basic.', 'wp-authress' ); ?></a>
				</li>
				<li><?php _e( 'Delete the Domain, Client ID, and Client Secret and save changes.', 'wp-authress' ); ?></li>
				<li><?php _e( 'Delete the created Application ', 'wp-authress' ); ?>
					<a target="_blank" href="https://manage.authress.com/#/applications/<?php echo esc_attr( wp_authress_get_option( 'access_key' ) ); ?>/settings" >
						<?php _e( 'here', 'wp-authress' ); ?>
					</a>
				</li>
				<li>
					<?php _e( 'Delete the created Database Connection ', 'wp-authress' ); ?>
						<a href="https://manage.authress.com/#/connections/database" target="_blank"><?php _e( 'here', 'wp-authress' ); ?></a>.
					<?php _e( 'Please note that this will delete all Authress users for this connection.', 'wp-authress' ); ?>
				</li>
			  </ol>
		  </div>

		<?php else : ?> -->
		<!-- <div class="notice notice-info settings-error"><p>
			<b>Important:</b><?php _e( 'To continue you need an Authress account. If you do not already have one: ', 'wp-authress' ); ?>

			<a class="button button-secondary" target="_blank" href="https://authress.io/app/#/signup">Create an Account</a>
		</p></div> -->

		<form action="options.php" method="POST">
			<input type="hidden" name="action" value="wp_authress_callback_step1" />
			<h3><?php _e( 'Automated Setup', 'wp-authress' ); ?></h3>

			<p>
				<?php _e( 'This is the automated setup wizard. Clicking continue below will direct you to Authress to create an account and generate the necessary integration resources. Just follow the instructions.', 'wp-authress' ); ?>
			</p>

			<p><input type="submit" class="button button-primary" value="<?php _e( 'Continue Setup', 'wp-authress' ); ?>"/></p>
		</form>

		<hr>

		<p><?php _e( 'For additional support at any time, reach out to', 'wp-authress' ); ?>
			<a href="https://authress.io/app/#/support" target="_blank"><?php _e( 'Authress support', 'wp-authress' ); ?></a>.</p>

		<!-- <?php endif; ?> -->
  	</div>
</div>
