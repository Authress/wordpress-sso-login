<?php
	$authress_options = Authress_Sso_Login_Options::Instance();
	$wle = 'link';
	$loginFlowIsPassword = isset($_REQUEST['login']);
?>

<script type="text/javascript">
	function loginWithSsoDomain(connectionId, elementId) {
		if (window.location.search.includes('login')) {
			return true;
		}

		var loginClickNextButton = document.getElementById(elementId);
		loginClickNextButton.classList.toggle('loader');
		const authressLoginHostUrl = "<?php echo esc_attr($authress_options->get('customDomain')); ?>";
		const applicationId = "<?php echo esc_attr($authress_options->get('applicationId')); ?>";
		const loginClient = new authress.LoginClient({ authressLoginHostUrl, applicationId });
		const currentUrl = new URL(window.location.href);
		const redirectUrl = currentUrl.searchParams.get('redirect_to') ? decodeURIComponent(currentUrl.searchParams.get('redirect_to')) : window.location.origin;
		const userEmailAddress = (document.getElementById('userLogin').value || '');
		const ssoDomain = userEmailAddress.replace(/[^@]+@(.*)$/, '$1');
		loginClient.authenticate({ tenantLookupIdentifier: !connectionId && ssoDomain, connectionId, redirectUrl })
		.then(result => {
			window.location.replace(redirectUrl);
		}).catch(async error => {
			loginClickNextButton.classList.toggle('loader');
			console.log('Failed to redirect user to SSO login:', error.code);
			if (error.code !== 'InvalidConnection' && error.code !== 'InvalidTenantIdentifier') {
				return;
			}
			if (!connectionId) {
				window.location.assign(`<?php echo esc_url(wp_login_url()); ?>?login=${userEmailAddress}`);
				return;
			}

			var connectionConfigurationWarning = document.getElementById('configurationConfigurationWarning');
			if (connectionConfigurationWarning && connectionConfigurationWarning.classList.contains('hidden')) {
				connectionConfigurationWarning.classList.toggle('hidden');
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
		<div style="display: flex; flex-wrap: wrap; justify-content: center;">
			<form name="loginform-custom" id="loginform-custom" action="<?php echo esc_url(wp_login_url()); ?>?<?php echo esc_attr($loginFlowIsPassword ? 'login=' : ''); ?>" method="post" onsubmit="return loginWithSsoDomain(null, 'loginClickNextButtonLoader')">
				<p class="login-username">
					<label for="userLogin">Enter your username or email</label>
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
			<form class="sign-in-button-panel-wrapper">
				<div class="sign-in-button-panel">
					<button class="sign-in-button google" onclick="return loginWithSsoDomain('google', 'googleButton')">
					<div style="display: flex; align-items: center" id="googleButton">
						<svg width="21" height="21" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"/><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"/><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"/><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"/></svg>
						<span style="padding-left: 0.5rem">Continue with Google</span>
						</div>
					</button>

					<button class="sign-in-button github" onclick="return loginWithSsoDomain('github', 'githubButton')">
						<div style="display: flex; align-items: center" id="githubButton">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
							<span style="padding-left: 0.5rem">Continue with GitHub</span>
						</div>
					</button>
				</div>
			</form>
		</div>
		<div id="configurationConfigurationWarning" class="message hidden">
			This connection is not yet configured. Navigate to <a href="https://authress.io/app/#/setup?focus=connections" target="_blank">Authress management portal</a> to enable it.
		</div>
	<?php else : ?>
		<div></div>
	<?php endif; ?>
</div>

<style type="text/css">
	.hidden {
		display: none;
	}
	#login {
		width: unset;
	}
	#loginform-custom {
		width: 320px;
	}
	#registerform, #lostpasswordform {
		width: 320px;
		margin-left: auto;
		margin-right: auto;
	}
	#login .message, #login #login_error {
		max-width: 342px;
		margin-left: auto;
		margin-right: auto;
	}

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
	.login #nav, .login #backtoblog {
		display: flex;
		justify-content: center;
	}
	.login #nav a, .login #backtoblog a {
		padding: 0 0.25rem
	}
	.login form {
		margin-top: 0px;
		padding: 26px 24px;
	}

	.sign-in-button, #loginClickNextButton {
		display: flex;
		align-items: center;
		justify-content: center;
	}
	#loginClickNextButton {
		display: flex;
		height: 30px;
		min-width: 50px;
	}

	.sign-in-button-panel-wrapper {
		width: 320px;
		display: flex;
		justify-content: center
	}
	.sign-in-button-panel {
		display: flex;
		justify-content: center;
		flex-direction: column;
	}
	.sign-in-button {
		cursor: pointer;
		height: 42px;
		width: 200px;
		background-color: white;
		color: #1d2f3b;
		border-radius: 5px;
		border-color: #dfe8eb;
		padding: 10px 14px;
		line-height: normal;
	}
	.sign-in-button:not(:last-of-type) {
		margin-bottom: 1rem;
	}
	.sign-in-button:focus, .sign-in-button:active:focus {
		box-shadow: none;
	}
	.sign-in-button:hover:not(:disabled) {
		background-color: #3e6077;
		color: white
	}

	.sign-in-button:active:not(:disabled) {
		background-color: #43535d;
		color: #f1f4f5;
		border-color: #dfe8eb;
	}
	.sign-in-button:active:disabled {
		color: gray;
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
	.sign-in-button .loader, .sign-in-button .loader:before, .sign-in-button .loader:after {
		background: #43535d;
		color: #43535d;
	}
	.sign-in-button:hover .loader, .sign-in-button:hover .loader:before, .sign-in-button:hover .loader:after {
		background: lightgray;
		color: lightgray;
	}
	.loader {
		color: #ffffff;
		text-indent: -9999em;
		margin-top: 5px;
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
