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
					<a target="_blank" href="https://manage.authress.com/#/applications/<?php echo esc_attr( wp_authress_get_option( 'client_id' ) ); ?>/settings" >
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
		<div class="notice notice-info settings-error"><p>
			<b>Important:</b><?php _e( 'To continue you need an Authress account. If you do not already have one: ', 'wp-authress' ); ?>

			<a class="button button-secondary" target="_blank" href="https://authress.io/app/#/signup">Create an Account</a>
		</p></div>

		<form action="options.php" method="POST">
			<?php wp_nonce_field( WP_Authress_InitialSetup_ConnectionProfile::SETUP_NONCE_ACTION ); ?>
			<input type="hidden" name="action" value="wp_authress_callback_step1" />
			<h3><?php _e( 'Standard Setup', 'wp-authress' ); ?></h3>

			<p>
				<?php _e( 'Authress Account ID', 'wp-authress' ); ?>
				(<a href="https://authress.io/app/#/setup?focus=general" target="_blank"><?php _e( 'Create an account', 'wp-authress' ); ?></a>):
			</p>
			<input type="text" name="accountId" class="js-a0-setup-input" placeholder="AuthressAccountId" required>

			<p>
				<?php _e( 'Create an Authress application and copy the ID here:', 'wp-authress' ); ?>
				(<a href="https://authress.io/app/#/setup?focus=applications" target="_blank"><?php _e( 'Create an application', 'wp-authress' ); ?></a>):
			</p>
			<input type="text" name="applicationId" class="js-a0-setup-input" placeholder="ApplicationId" required>
			
			<!-- <p>
				<a href="https://authress.com/docs/api/management/v2/get-access-tokens-for-test#get-access-tokens-manually"
					target="_blank">
					<?php _e( 'Create a Management API token using these steps', 'wp-authress' ); ?>
				</a>
				<?php _e( ' and paste it below:', 'wp-authress' ); ?>
			</p>
			<input type="text" name="apitoken" class="js-a0-setup-input" autocomplete="off" required>

			<p>
				<?php _e( 'Scopes required', 'wp-authress' ); ?>:
				<code><?php echo implode( '</code> <code>', WP_Authress_Api_Client::ConsentRequiredScopes() ); ?></code>
			</p> -->

			<p><input type="submit" class="button button-primary" value="<?php _e( 'Continue', 'wp-authress' ); ?>"/></p>
		</form>

		<hr>

		<p><?php _e( 'For additional support at any time, reach out to', 'wp-authress' ); ?>
			<a href="https://authress.io/app/#/support" target="_blank"><?php _e( 'Authress support', 'wp-authress' ); ?></a>.</p>

		<!-- <?php endif; ?> -->
  	</div>
</div>
