<?php
	$authress_options = WP_Authress_Options::Instance();
	$wle = $authress_options->get('wordpress_login_enabled');
?>

	<script src="https://unpkg.com/authress-login@1.2.93/dist/authress.min.js"></script>
	<script type="text/javascript">
		const authressLoginHostUrl = "<?php echo $authress_options->get('customDomain') ?>";
		const applicationId = "<?php echo $authress_options->get('applicationId') ?>";
		const loginClient = new authress.LoginClient({ authressLoginHostUrl, applicationId });
		const currentUrl = new URL(window.location.href);
		const redirectUrl = currentUrl.searchParams.get('redirect_to') ? decodeURIComponent(currentUrl.searchParams.get('redirect_to')) : window.location.origin;

		loginClient.userSessionExists().then(userIsLoggedIn => {
			console.log('User is logged in?', userIsLoggedIn, redirectUrl);
			if (userIsLoggedIn) {
				console.log('User is logged in?', userIsLoggedIn, redirectUrl);
				window.location.replace(redirectUrl);
			}
		}).catch(error => {
			console.error('Failed to check if user is logged in:', error);
		});

		function loginWithSsoDomain() {
			const ssoDomain = document.getElementById('customer_sso_domain').value;
			loginClient.authenticate({ tenantLookupIdentifier: ssoDomain, redirectUrl })
			.then(result => {
				window.location.replace(redirectUrl);
			}).catch(error => {
				console.error('Failed to redirect user to SSO login:', error);
			});
			return false;
		}
	</script>
	<div id="form-signin-wrapper" class="authress-login">
		<div class="form-signin">
			<div id="<?php echo esc_attr( WP_AUTHRESS_AUTHRESS_LOGIN_FORM_ID ); ?>">
				<form onsubmit="return loginWithSsoDomain()">
					<p>
						<label for="customer_sso_domain">Enter SSO Domain</label>
						<input type="text" name="sso_domain" autocomplete="on" id="customer_sso_domain" class="input" value="" size="20" autocapitalize="off" autocomplete="off" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%;" required>
					</p>

					<p class="submit">
						<input type="submit" name="wp-submit" class="button button-primary button-large" value="Continue to SSO Provider">
					</p>
				</form>
			</div>
			<?php if ( 'link' === $wle && function_exists( 'login_header' ) ) : ?>
			  <div id="extra-options">
				  <a href="<?php echo wp_login_url(); ?>?wle">
					<?php _e( 'Login with WordPress username', 'wp-authress' ); ?>
				  </a>
			  </div>
			<?php endif ?>
		</div>
	</div>

	<style type="text/css">
		<?php echo apply_filters( 'authress_login_css', '' ); ?>
	</style>
