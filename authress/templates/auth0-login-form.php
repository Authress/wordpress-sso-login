<?php
$authress_options = WP_Authress_Options::Instance();
$wle           = $authress_options->get( 'wordpress_login_enabled' );
?>
	<div id="form-signin-wrapper" class="authress-login">
		<div class="form-signin">
			<div id="<?php echo esc_attr( WP_AUTHRESS_AUTHRESS_LOGIN_FORM_ID ); ?>"></div>
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
