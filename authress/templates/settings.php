<div class="a0-wrap settings wrap">

	<div class="container-fluid">

			<h1><?php esc_attr_e( 'SSO Login Settings', 'authress_configuration' ); ?></h1>

			<?php settings_errors(); ?>

			<p class="nav nav-tabs" role="tablist">
					<a id="tab-basic" href="#basic" class="js-a0-settings-tabs">
						<?php esc_attr_e( 'Configuration', 'wp-authress' ); ?>
					</a>
					<!-- <a id="tab-features" href="#features" class="js-a0-settings-tabs">
						< ?php esc_attr_e( 'Features', 'wp-authress' ); ?>
					</a>
					<a id="tab-appearance" href="#appearance" class="js-a0-settings-tabs">
						< ?php esc_attr_e( 'Embedded', 'wp-authress' ); ?>
					</a>
					<a id="tab-advanced" href="#advanced" class="js-a0-settings-tabs">
						< ?php esc_attr_e( 'Advanced', 'wp-authress' ); ?>
					</a> -->
					<a id="tab-help" href="#help" class="js-a0-settings-tabs">
						<?php esc_attr_e( 'Help', 'wp-authress' ); ?>
					</a>
			</p>

		<form action="options.php" method="post" id="js-a0-settings-form" class="a0-settings-form">
			<?php settings_fields( WP_Authress_Options::Instance()->getConfigurationDatabaseName() . '_basic' ); ?>

			<div class="tab-content">
				<?php foreach ( WP_Authress_Admin::OPT_SECTIONS as $admin_tab ) : ?>
					<div class="tab-pane" id="panel-<?php echo esc_attr($admin_tab); ?>">
						<?php do_settings_sections( WP_Authress_Options::Instance()->getConfigurationDatabaseName() . '_' . $admin_tab ); ?>
					</div>
				<?php endforeach; ?>

				<div class="tab-pane" id="panel-help">

					<p>
						<?php
						esc_attr_e( 'Thank you for installing SSO Login! Authress is a powerful identity solution that secures billions of logins every month. In addition to the options here, there are many more features available in the', 'wp-authress' );
						?>
						<a href="https://authress.io/app" target="_blank"><?php esc_attr_e( 'Authress management portal', 'wp-authress' ); ?></a>
						<?php esc_attr_e( 'including:', 'wp-authress' ); ?>
					</p>

					<ul class="list">
						<li><a href="https://authress.io/knowledge-base" target="_blank"><?php esc_attr_e( 'Many social and enterprise login connections', 'wp-authress' ); ?></a></li>
						<li><a href="https://authress.io/knowledge-base" target="_blank"><?php esc_attr_e( 'Anomaly detection', 'wp-authress' ); ?></a></li>
						<li><a href="https://authress.io/knowledge-base" target="_blank"><?php esc_attr_e( 'User access control and granular permissions', 'wp-authress' ); ?></a></li>
					</ul>

					<p><?php esc_attr_e( 'If you have issues or questions, we provide a variety of channels to assist:', 'wp-authress' ); ?><p>

					<ul class="list">
						<li>
							<a href="https://authress.io/knowledge-base" target="_blank"><?php esc_attr_e( 'Knowledge base', 'wp-authress' ); ?></a> -
							<?php esc_attr_e( 'If you are setting up the plugin for the first time or having issues after an upgrade, please review the settings to make sure your Application is setup correctly.', 'wp-authress' ); ?>
						</li>
						<li>
							<a href="https://authress.io/community" target="_blank"><?php esc_attr_e( 'Authress Community', 'wp-authress' ); ?></a> -
							<?php esc_attr_e( 'If you have questions about how to use Authress or the plugin, join the Authress community, and ask any questions you may have.', 'wp-authress' ); ?>
						</li>
						<li><a href="https://github.com/Authress/wordpress-sso-login/issues" target="_blank"><?php esc_attr_e( 'GitHub Issues', 'wp-authress' ); ?></a> -
							<?php esc_attr_e( 'If you find a bug in the plugin code, the best place to report that is on GitHub under the Issues tab.', 'wp-authress' ); ?>
						</li>
						<li><a href="https://authress.io/app/#/support" target="_blank"><?php esc_attr_e( 'Support', 'wp-authress' ); ?></a> -
							<?php esc_attr_e( 'Customers can submit support tickets or reach out directly for a quick response.', 'wp-authress' ); ?>
						</li>
					</ul>
				</div>
			</div>

				<div class="a0-buttons">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-authress' ); ?>" />
				</div>
		</form>
	</div>
</div>
