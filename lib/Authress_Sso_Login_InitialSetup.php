<?php

class Authress_Sso_Login_InitialSetup {

	const CONFIRM_SETUP_NONCE = 'authress_sso_login_confirm_setup';

	const SETUP_SETTINGS = [ 'accessKey', 'customDomain', 'applicationId' ];

	protected $a0_options;

	public function __construct( Authress_Sso_Login_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function notify_error( $error ) {
		printf( '<div class="notice notice-error"><p><strong>%s</strong></p></div>', esc_attr( $error ) );
	}

	public function render_setup_page() {
		if ( isset( $_POST['authress_setup_confirm'] ) ) {
			check_admin_referer( self::CONFIRM_SETUP_NONCE );
			if ( ! $this->save_confirmed_setup( $_POST ) ) {
				$this->notify_error( __( 'The setup was not saved. Start the setup again from this page.', 'wp-authress' ) );
			}
		} elseif ( isset( $_GET['accessKey'] ) ) {
			$returned_setup = $this->read_setup_values( $_GET );
			if ( null !== $returned_setup ) {
				include AUTHRESS_SSO_LOGIN_PLUGIN_DIR . 'templates/initial-setup/confirm-setup.php';
				return;
			}
		}
		include AUTHRESS_SSO_LOGIN_PLUGIN_DIR . 'templates/initial-setup/setup-wizard.php';
	}

	/**
	 * Reads the values the hosted setup returns. Reading never saves anything, saving requires the administrator to confirm.
	 *
	 * @param array $source - The query parameters of the return request, or the posted confirmation form.
	 *
	 * @return array|null The sanitized values, or null when any is missing.
	 */
	public function read_setup_values( array $source ) {
		$setup = [];
		foreach ( self::SETUP_SETTINGS as $setting ) {
			$setup[ $setting ] = sanitize_text_field( wp_unslash( isset( $source[ $setting ] ) ? $source[ $setting ] : '' ) );
			if ( '' === $setup[ $setting ] ) {
				return null;
			}
		}
		return $setup;
	}

	/**
	 * Saves the values from the confirmation form.
	 * The caller must have verified the confirmation nonce.
	 *
	 * @param array $posted - The posted confirmation form.
	 *
	 * @return bool
	 */
	public function save_confirmed_setup( array $posted ) {
		$setup = $this->read_setup_values( $posted );
		if ( null === $setup || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$this->a0_options->set( 'accessKey', $setup['accessKey'], false );
		$this->a0_options->set( 'customDomain', $setup['customDomain'], false );
		$this->a0_options->set( 'applicationId', $setup['applicationId'] );
		return true;
	}

	public function cant_create_client_message() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php esc_attr_e( 'There was an error creating the Authress App. Check the errors page', 'wp-authress' ); ?>
					  <!-- <a target="_blank" href="<php echo esc_attr(admin_url( 'admin.php?page=authress_errors' )); ?>"><php esc_attr_e( 'error log', 'wp-authress' ); ?></a> -->
					<?php esc_attr_e( ' for more information. If the problem persists, please follow the ', 'wp-authress' ); ?>
					  <a target="_blank" href="https://authress.io/knowledge-base"><?php esc_attr_e( 'manual setup instructions', 'wp-authress' ); ?></a>.
				  </strong>
			  </p>
		  </div>
		<?php
	}

	public static function get_setup_access_key() {
		return site_url();
	}

	public static function get_setup_redirect_uri() {
		return admin_url( 'admin.php?page=authress&callback=1' );
	}
}
