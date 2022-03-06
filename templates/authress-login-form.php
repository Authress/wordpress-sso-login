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

			var loginClickNextButton = document.getElementById('loginClickNextButtonLoader');
			loginClickNextButton.classList.toggle('loader');
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
				loginClickNextButton.classList.toggle('loader');
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
		<?php if (!isset($_REQUEST['action'])) : ?>
			<form name="loginform-custom" id="loginform-custom" action="<?php echo esc_url(wp_login_url()); ?>" method="post" onsubmit="return loginWithSsoDomain()">
				<p class="login-username">
					<label for="userLogin">Enter your email</label>
					<input type="text" autocomplete="username" name="log" id="userLogin" class="input"
						value="<?php echo isset($_GET['login']) ? esc_attr(sanitize_text_field(wp_unslash($_GET['login']))) : ''; ?>" size="20" />
				</p>
				
				<?php if ($loginFlowIsPassword) : ?>
					<p class="login-password">
						<label for="userPassword">Password</label>
						<input autofocus autocomplete="current-password" type="password" name="pwd" id="userPassword" class="input" value="" size="20" />
					</p>
				<?php endif ?>
				<p class="login-remember">
					<input name="rememberme" type="hidden" id="rememberMeValue" value="forever" />
				</p>
				<p class="login-submit">
					<?php if ($loginFlowIsPassword) : ?>
						<input type="submit" name="wp-submit" id="loginButton" class="button button-primary" value="Login" />
					<?php else : ?>
						<button type="submit" id="loginClickNextButton" class="button button-primary"><div id="loginClickNextButtonLoader">Next</div></button>
					<?php endif ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_url(wp_login_url()); ?>" />
				</p>

				<br>
				<?php if ($loginFlowIsPassword) : ?>
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

		#loginClickNextButton {
			display: flex;
			height: 30px !important;
			min-width: 50px;
		}

		/* #loginClickNextButtonLoader {
			display: inline: block;
		} */
		.loader,
		.loader:before,
		.loader:after {
			background: #ffffff;
			-webkit-animation: load1 1s infinite ease-in-out;
			animation: load1 1.1s infinite ease-in-out;
			width: 0.6em;
			height: 0.25em;
		}
		.loader {
			color: #ffffff;
			text-indent: -9999em;
			margin: 14px auto;
			position: relative;
			font-size: 11px;
			-webkit-transform: translateZ(0);
			-ms-transform: translateZ(0);
			transform: translateZ(0);
			-webkit-animation-delay: -0.16s;
			animation-delay: -0.16s;
		}
		.loader:before, .loader:after {
			position: absolute;
			top: 0;
			content: '';
		}
		.loader:before {
			left: -0.8em;
			-webkit-animation-delay: -0.32s;
			animation-delay: -0.32s;
		}
		.loader:after {
			left: 0.8em;
		}
		@-webkit-keyframes load1 {
			0%, 80%, 100% {
				box-shadow: 0 0;
				height: 0.25em;
			}
			40% {
				box-shadow: 0 -0.75em;
				height: 0.9em;
			}
		}
		@keyframes load1 {
			0%, 80%, 100% {
				box-shadow: 0 0;
				height: 0.25em;
			}
			40% {
				box-shadow: 0 -0.75em;
				height: 0.9em;
			}
		}

	</style>
	<style type="text/css">
		<?php echo esc_attr(apply_filters( 'authress::user_login_template::css::formatter', '' )); ?>
	</style>
