<div class="a0-wrap settings wrap">
  	<div class="container-fluid">
	  <h1><?php esc_attr_e( 'Authress Setup Wizard', 'wp-authress' ); ?></h1>
		<?php if ( wp_authress_is_ready() ) : ?>
			<h3>Your integration has successfully completed!
			<p>
				<h3>Next steps:</h3>
				<ul>
				<li>Your users can now sign in with their configured SSO provider. When prompted they can enter their SSO identity and log in.</li>
				<li>For your customer's admin they should be directed to configure their SSO.</li>
				</ul>
			</p>

			<h3><?php esc_attr_e( 'Automated Setup', 'wp-authress' ); ?></h3>

			<p><?php esc_attr_e( 'Want to see the SSO login page?', 'wp-authress' ); ?></p>
			<p><?php esc_attr_e( "(Note: You won't be able to actually log in until you configure a tenant identity provider:", 'wp-authress' );
				printf(' <strong><a href="https://authress.io/knowledge-base/user-oauth-authentication-quick-start" target="_blank">%s</a></strong>', esc_attr_e( 'Configure identity provider', 'wp-authress' ));
			?>)</p>

			<a href="/wp-login.php?action=logout"><button class="button button-primary">Try the SSO login</button></a>

			<hr>

			<form action="options.php" method="POST">
				<input type="hidden" name="action" value="wp_authress_callback_step1" />
				<h3><?php esc_attr_e( 'Automated Setup', 'wp-authress' ); ?></h3>

				<p>
					<?php esc_attr_e( 'If you are running into issues, you can always rerun the automated setup.', 'wp-authress' ); ?>
				</p>

				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Rerun Setup', 'wp-authress' ); ?>"/></p>
			</form>
		<?php else : ?>
			<p><?php esc_attr_e( "SSO Login enables Users to log in with their employee credentials through their identity provider. Using SSO Login will increase your WordPress site's security and consolidate identity data.", 'wp-authress' ); ?></p>
		
			<p><?php esc_attr_e( 'Once configured, this plugin replaces the WordPress login screen, by many additional features to make login easier and more secure your users.', 'wp-authress' ); ?></p>

			<form action="options.php" method="POST">
				<input type="hidden" name="action" value="wp_authress_callback_step1" />
				<h3><?php esc_attr_e( 'Automated Setup', 'wp-authress' ); ?></h3>

				<p>
					<?php esc_attr_e( 'This is the automated setup wizard. Clicking continue below will direct you to Authress to create an account and generate the necessary integration resources. Just follow the instructions.', 'wp-authress' ); ?>
				</p>

				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Continue Setup', 'wp-authress' ); ?>"/></p>
			</form>
		<?php endif; ?>


		<hr>

		<p><?php esc_attr_e( 'For more information on installation and configuration, please see the', 'wp-authress' );
		printf(' <strong><a href="https://authress.io/knowledge-base" target="_blank">%s</a></strong>', esc_attr_e( 'Authress Knowledge Base', 'wp-authress' )); ?>.</p>

		<p><?php esc_attr_e( 'For additional support at any time, reach out to', 'wp-authress' ); ?>
			<a href="https://authress.io/app/#/support" target="_blank"><?php esc_attr_e( 'Authress support', 'wp-authress' ); ?></a>.</p>

  	</div>
</div>
