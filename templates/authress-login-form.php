<?php
	$authress_options = Authress_Sso_Login_Options::Instance();
	$wle = 'link';
	$loginFlowIsPassword = isset($_REQUEST['login']);
	$showWordPressLogin = (isset($_REQUEST['wle']) || $loginFlowIsPassword) && !isset($_REQUEST['action']);
?>

<script type="text/javascript">
	function redirectToAuthressManagedLogin(navigateToAuthressLoginFromWordpress = false) {
		const currentUrl = new URL(window.location.href);

		// Use WordPress login
		if (currentUrl.searchParams.get('action') || currentUrl.searchParams.get('login') && !navigateToAuthressLoginFromWordpress) {
			console.log('Found action or login, skipping automatic login.')
			return false;
		}


		const forceLogin = currentUrl.searchParams.get('force');

		const authressLoginHostUrl = "<?php echo esc_attr($authress_options->get('customDomain')); ?>";
		const applicationId = "<?php echo esc_attr($authress_options->get('applicationId')); ?>";
		const loginClient = new authress.LoginClient({ authressLoginHostUrl, applicationId });
		
		// const redirectUrl = 'http://localhost:8081';
		const redirectUrl = currentUrl.searchParams.get('redirect_to') ? decodeURIComponent(currentUrl.searchParams.get('redirect_to')) : window.location.href;
		loginClient.authenticate({ redirectUrl, force: !!forceLogin })
		.then(result => {
			window.location.assign(redirectUrl);
		}).catch(async error => {
			console.error('Failed to redirect user to managed login:', error.code);
		});
		return false;
	}

	function wordpressLogin() {
		const currentUrl = new URL(window.location.href);

		const userEmailAddress = (document.getElementById('userLogin').value || '');
		if (currentUrl.searchParams.get('login')) {
			return true;
		}

		window.location.assign(`<?php echo esc_url(wp_login_url()); ?>?login=${userEmailAddress}`);
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
				window.location.assign(redirectUrl);
			}
			if (!currentUrl.searchParams.get('wle') || currentUrl.searchParams.get('nonce')) {
				redirectToAuthressManagedLogin();
				return;
			}
		}).catch(error => {
			console.error('Failed to check if user is logged in:', error);
		});
	};
	var checkHandler = setInterval(checkIfLoaded, 10);
</script>

<div>
	<?php if ($showWordPressLogin) : ?>
		<div style="display: flex; flex-wrap: wrap; justify-content: center;">
			<form name="loginform-custom" id="loginform-custom" action="<?php echo esc_url(wp_login_url()); ?>?<?php echo esc_attr($loginFlowIsPassword ? 'login=' : ''); ?>" method="post" onsubmit="return wordpressLogin()">
				<p class="login-username">
					<label for="userLogin">Enter your username</label>
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
			</form>
			<form class="sign-in-button-panel-wrapper">
				<div class="sign-in-button-panel">
					<button class="sign-in-button google" onclick="return redirectToAuthressManagedLogin(true)">
					<div style="display: flex; align-items: center" id="googleButton">
						<svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M8 16C8 18.8284 8 20.2426 8.87868 21.1213C9.51998 21.7626 10.4466 21.9359 12 21.9827M8 8C8 5.17157 8 3.75736 8.87868 2.87868C9.75736 2 11.1716 2 14 2H15C17.8284 2 19.2426 2 20.1213 2.87868C21 3.75736 21 5.17157 21 8V10V14V16C21 18.8284 21 20.2426 20.1213 21.1213C19.3529 21.8897 18.175 21.9862 16 21.9983" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"/>
							<path d="M3 9.5V14.5C3 16.857 3 18.0355 3.73223 18.7678C4.46447 19.5 5.64298 19.5 8 19.5M3.73223 5.23223C4.46447 4.5 5.64298 4.5 8 4.5" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"/>
							<path d="M6 12L15 12M15 12L12.5 14.5M15 12L12.5 9.5" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<span style="padding-left: 0.5rem">Log in with SSO</span>
						</div>
					</button>
				</div>
			</form>
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
