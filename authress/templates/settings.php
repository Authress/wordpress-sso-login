<div class="a0-wrap settings wrap">

	<div class="container-fluid">

			<h1><?php _e( 'SSO Login Settings', 'wp-authress' ); ?></h1>

			<?php settings_errors(); ?>

			<p class="nav nav-tabs" role="tablist">
					<a id="tab-basic" href="#basic" class="js-a0-settings-tabs">
						<?php _e( 'Basic', 'wp-authress' ); ?>
					</a>
					<a id="tab-features" href="#features" class="js-a0-settings-tabs">
						<?php _e( 'Features', 'wp-authress' ); ?>
					</a>
					<a id="tab-appearance" href="#appearance" class="js-a0-settings-tabs">
						<?php _e( 'Embedded', 'wp-authress' ); ?>
					</a>
					<a id="tab-advanced" href="#advanced" class="js-a0-settings-tabs">
						<?php _e( 'Advanced', 'wp-authress' ); ?>
					</a>
					<a id="tab-help" href="#help" class="js-a0-settings-tabs">
						<?php _e( 'Help', 'wp-authress' ); ?>
					</a>
			</p>

		<form action="options.php" method="post" id="js-a0-settings-form" class="a0-settings-form">
			<?php settings_fields( WP_Authress_Options::Instance()->get_options_name() . '_basic' ); ?>

			<div class="tab-content">
				<?php foreach ( WP_Authress_Admin::OPT_SECTIONS as $tab ) : ?>
					<div class="tab-pane" id="panel-<?php echo $tab; ?>">
						<?php do_settings_sections( WP_Authress_Options::Instance()->get_options_name() . '_' . $tab ); ?>
					</div>
				<?php endforeach; ?>

				<div class="tab-pane" id="panel-help">

					<p>
						<?php
						_e( 'Thank you for installing SSO Login! Authress is a powerful identity solution that secures billions of logins every month. In addition to the options here, there are many more features available in the', 'wp-authress' );
						?>
						<a href="https://manage.authress.com" target="_blank"><?php _e( 'Authress dashboard', 'wp-authress' ); ?></a>
						<?php _e( 'including:', 'wp-authress' ); ?>
					</p>

					<ul class="list">
						<li><a href="https://authress.com/docs/identityproviders" target="_blank">
								<?php
								_e( 'Many social and enterprise login connections', 'wp-authress' )
								?>
							</a></li>
						<li><a href="https://authress.com/docs/connections/passwordless" target="_blank">
								<?php
								_e( 'Passwordless login connections', 'wp-authress' );
								?>
							</a></li>
						<li><a href="https://authress.com/docs/anomaly-detection" target="_blank">
								<?php
								_e( 'Anomaly detection', 'wp-authress' );
								?>
							</a></li>
						<li><a href="https://authress.com/docs/rules/current" target="_blank">
								<?php
								_e( 'Profile enrichment, integrations, and other custom management tasks using Rules', 'wp-authress' );
								?>
							</a></li>
					</ul>

					<p><?php _e( 'If you have issues or questions, we provide a variety of channels to assist:', 'wp-authress' ); ?><p>

					<ul class="list">
						<li><a href="https://authress.com/docs/cms/wordpress/configuration" target="_blank">
								<?php
								_e( 'Configuration documentation', 'wp-authress' )
								?>
							</a> -
							<?php
							_e( 'If you are setting up the plugin for the first time or having issues after an upgrade, please review the settings to make sure your Application is setup correctly.', 'wp-authress' )
							?>
						</li>
						<li><a href="https://community.authress.com/tags/wordpress" target="_blank">
								<?php
								_e( 'Authress Community', 'wp-authress' )
								?>
							</a> -
							<?php
							_e( 'If you have questions about how to use Authress or the plugin, please create a post (tagged "WordPress") if you do not find what you are looking for.', 'wp-authress' )
							?>
						</li>
						<li><a href="https://github.com/authress/wp-authress/issues" target="_blank"><?php _e( 'GitHub Issues', 'wp-authress' ); ?></a> -
							<?php _e( 'If you find a bug in the plugin code, the best place to report that is on GitHub under the Issues tab.', 'wp-authress' ); ?>
						</li>
						<li><a href="https://support.authress.com/" target="_blank"><?php _e( 'Support', 'wp-authress' ); ?></a> -
							<?php _e( 'Customers on a paid Authress plan can submit trouble tickets for a quick response.', 'wp-authress' ); ?>
						</li>
					</ul>
				</div>
			</div>

				<div class="a0-buttons">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'wp-authress' ); ?>" />
				</div>
		</form>
	</div>
</div>
