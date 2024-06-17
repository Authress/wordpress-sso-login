<div class="a0-wrap settings wrap">
  	<div class="container-fluid">
	  <h1><?php esc_attr_e( 'Authress Setup Wizard', 'wp-authress' ); ?></h1>
		<?php if ( authress_plugin_has_been_fully_configured() ) : ?>
			<h3>Your integration has successfully completed!

			<h3>Important: How to return to this page later</h3>
			<p>Navigate to <a href="/wp-login.php?wle=true">/wp-login.php?wle=true</a> at any time to use the WordPress login.</p>
			
			<hr>

			<p>
				<h3>Next steps:</h3>
				<ol>
				<li>Your users can now sign in with their configured SSO provider.</li>
				<li>For your customer's admin they should be directed to configure their SSO.</li>
				</ol>
			</p>

			<p>Or try out the SSO Login default configuration:</p>

			<a href="/wp-login.php?force=true"><button class="button button-primary">Try the SSO login</button></a>

			<h3><?php esc_attr_e( 'Configure your login page', 'wp-authress' ); ?></h3>

			<p>The Authress managed login box is option to use. This page can be configured using the Authress Log in branding configuration screen. Or you can set up and configure your own by interacting with the Authress login SDK.</p>
			<a href="https://authress.io/app/#/setup?focus=branding" target="_blank"><button class="button button-primary">Style the login box</button></a>

			<hr>

			<form action="options.php" method="POST">
				<input type="hidden" name="action" value="authress_sso_login_callback_step1" />
				<h3><?php esc_attr_e( 'Rerun Automated Setup', 'wp-authress' ); ?></h3>

				<p>
					<?php esc_attr_e( 'If you are running into issues, you can always rerun the automated setup.', 'wp-authress' ); ?>
				</p>

				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Rerun Setup', 'wp-authress' ); ?>"/></p>
			</form>
		<?php else : ?>
			<p><?php esc_attr_e( "SSO Login enables Users to log in with their social, OIDC, or SAML credentials through their existing identity provider. Using this Authress SSO Login plugin will increase your WordPress site's security and consolidate identity data.", 'wp-authress' ); ?></p>
		
			<p><?php esc_attr_e( 'Once configured, this plugin replaces the WordPress login screen, by many additional features to make login easier and more secure your users.', 'wp-authress' ); ?></p>

			<form action="options.php" method="POST">
				<input type="hidden" name="action" value="authress_sso_login_callback_step1" />
				<h3><?php esc_attr_e( 'Automated Setup', 'wp-authress' ); ?></h3>

				<p>
					<?php esc_attr_e( 'This is the automated setup wizard. Clicking continue below will direct you to Authress to create an account and generate the necessary integration resources. Just follow the instructions.', 'wp-authress' ); ?>
				</p>

				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Continue Setup', 'wp-authress' ); ?>"/></p>
			</form>
		<?php endif; ?>


		<hr>

		<p><?php esc_attr_e( 'For more information on installation and configuration, please see the ', 'wp-authress' ); ?>
			<a href="https://authress.io/knowledge-base" target="_blank"><?php esc_attr_e( 'Authress Knowledge Base', 'wp-authress' ); ?></a>.</p>

		<p><?php esc_attr_e( 'For additional support at any time, reach out to', 'wp-authress' ); ?>
			<a href="https://authress.io/app/#/support" target="_blank"><?php esc_attr_e( 'Authress support', 'wp-authress' ); ?></a>.</p>

  	</div>
</div>
