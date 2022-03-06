<?php
	$authress_options = Authress_Sso_Login_Options::Instance();
	$wle = 'link';
	$loginFlowIsPassword = isset($_REQUEST['login']);
?>

	<script type="text/javascript">
		function loginWithSsoDomain() {
			if (window.location.search.includes('login')) {
				return true;
			}

			const authressLoginHostUrl = "<?php echo esc_attr($authress_options->get('customDomain')); ?>";
			const applicationId = "<?php echo esc_attr($authress_options->get('applicationId')); ?>";
			const loginClient = new authress.LoginClient({ authressLoginHostUrl, applicationId });
			const currentUrl = new URL(window.location.href);
			const redirectUrl = currentUrl.searchParams.get('redirect_to') ? decodeURIComponent(currentUrl.searchParams.get('redirect_to')) : window.location.origin;
			const userEmailAddress = (document.getElementById('userLogin').value || '');
			const ssoDomain = userEmailAddress.replace(/[^@]+@(.*)$/, '$1');
			loginClient.authenticate({ tenantLookupIdentifier: ssoDomain, redirectUrl })
			.then(result => {
				window.location.replace(redirectUrl);
			}).catch(error => {
				console.log('Failed to redirect user to SSO login:', error.code);
				if (error.code === 'InvalidConnection') {
					window.location.assign(`<?php echo esc_url(wp_login_url()); ?>?login=${userEmailAddress}`);
					return false;
				}
				
			});
			return false;
		}

		function checkIfLoaded() {
			var script = document.querySelector('#authress_sso_login_login_sdk-js');
			if (!script || !authress) {
				return;
			}
			clearInterval(checkHandler);
			const authressLoginHostUrl = "<?php echo esc_attr($authress_options->get('customDomain')); ?>";
			const applicationId = "<?php echo esc_attr($authress_options->get('applicationId')); ?>";
			const loginClient = new authress.LoginClient({ authressLoginHostUrl, applicationId });
			const currentUrl = new URL(window.location.href);
			const redirectUrl = currentUrl.searchParams.get('redirect_to') ? decodeURIComponent(currentUrl.searchParams.get('redirect_to')) : window.location.origin;

			loginClient.userSessionExists().then(userIsLoggedIn => {
				if (userIsLoggedIn) {
					console.log('User is logged in.', redirectUrl);
					window.location.replace(redirectUrl);
				}
			}).catch(error => {
				console.error('Failed to check if user is logged in:', error);
			});
		};
		var checkHandler = setInterval(checkIfLoaded, 100);
	</script>

	<div>
		<?php if (!isset($_REQUEST['action'])): ?>
			<form name="loginform-custom" id="loginform-custom" action="<?php echo esc_url(wp_login_url()) ?>" method="post" onsubmit="return loginWithSsoDomain()">
				<p class="login-username">
					<label for="userLogin">Enter your email</label>
					<input type="text" autocomplete="username" name="log" id="userLogin" class="input" value="<?php echo esc_attr($_GET['login']) ?>" size="20" />
				</p>
				
				<?php if ($loginFlowIsPassword) :?>
					<p class="login-password">
						<label for="userPassword">Password</label>
						<input autofocus autocomplete="current-password" type="password" name="pwd" id="userPassword" class="input" value="" size="20" />
					</p>
				<?php endif ?>
				<p class="login-remember">
					<input name="rememberme" type="hidden" id="rememberMeValue" value="forever" />
				</p>
				<p class="login-submit">
					<?php if ($loginFlowIsPassword) :?>
						<input type="submit" name="wp-submit" id="loginButton" class="button button-primary" value="Login" />
					<?php else : ?>
						<input type="submit" name="wp-submit" id="nextButton" class="button button-primary" value="Next" />
					<?php endif ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_url(wp_login_url()) ?>" />
				</p>

				<br>
				<?php if ($loginFlowIsPassword) :?>
					<p id="nav" style="display: block">
						<a href="?">← Login with identity provider</a>
					</p>
				<?php endif ?>
			</form>
		<?php else : ?>
			<div></div>
		<?php endif; ?>
	</div>

	<style type="text/css">
		#customer_sso_domain {
			font-size: 18px;
		}
		.enable-on-password {
			display: block;
		}
		.hide-on-password {
			display: none;
		}
		#loginform {
			display: none;
		}
		.login #nav {
			padding-left: 0;
		}
	</style>
	<style type="text/css">
		<?php echo esc_attr(apply_filters( 'authress::user_login_template::css::formatter', '' )); ?>
	</style>
