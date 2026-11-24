<div class="a0-wrap settings wrap">
	<div class="container-fluid">
		<h1><?php esc_html_e( 'Confirm Authress Setup', 'wp-authress' ); ?></h1>

		<p><?php esc_html_e( 'Authress is asking to connect this site to the account below. Only continue if you just started this setup from this page.', 'wp-authress' ); ?></p>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Authress domain', 'wp-authress' ); ?></th>
				<td><code><?php echo esc_html( $returned_setup['customDomain'] ); ?></code></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Application ID', 'wp-authress' ); ?></th>
				<td><code><?php echo esc_html( $returned_setup['applicationId'] ); ?></code></td>
			</tr>
			<?php if ( authress_plugin_has_been_fully_configured() ) : ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Replaces current domain', 'wp-authress' ); ?></th>
				<td><code><?php echo esc_html( $this->a0_options->get( 'customDomain' ) ); ?></code></td>
			</tr>
			<?php endif; ?>
		</table>

		<form action="<?php echo esc_url( admin_url( 'admin.php?page=authress' ) ); ?>" method="POST">
			<?php wp_nonce_field( Authress_Sso_Login_InitialSetup::CONFIRM_SETUP_NONCE ); ?>
			<?php foreach ( Authress_Sso_Login_InitialSetup::SETUP_SETTINGS as $setting ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $setting ); ?>" value="<?php echo esc_attr( $returned_setup[ $setting ] ); ?>" />
			<?php endforeach; ?>
			<p>
				<button type="submit" name="authress_setup_confirm" value="1" class="button button-primary"><?php esc_html_e( 'Connect this site', 'wp-authress' ); ?></button>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=authress' ) ); ?>"><?php esc_html_e( 'Cancel', 'wp-authress' ); ?></a>
			</p>
		</form>
	</div>
</div>
