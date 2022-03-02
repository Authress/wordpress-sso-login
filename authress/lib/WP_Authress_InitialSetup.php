<?php

class WP_Authress_InitialSetup {

	protected $a0_options;

	public function __construct( WP_Authress_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function notify_error( $error ) {
		printf( '<div class="notice notice-error"><p><strong>%s</strong></p></div>', sanitize_text_field( $error ) );
	}

	public function render_setup_page() {
		if (isset($_GET['accessKey'])) {
			$this->a0_options->set( 'accessKey', sanitize_text_field($_GET['accessKey']));
			$this->a0_options->set( 'customDomain', sanitize_text_field($_GET['customDomain'] ));
			$this->a0_options->set( 'applicationId', sanitize_text_field($_GET['applicationId'] ));
		}
		include WP_AUTHRESS_PLUGIN_DIR . 'templates/initial-setup/setup_wizard.php';
	}

	public function cant_create_client_message() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php _e( 'There was an error creating the Authress App. Check the errors page', 'wp-authress' ); ?>
					  <!-- <a target="_blank" href="<php echo esc_attr(admin_url( 'admin.php?page=authress_errors' )); ?>"><php _e( 'error log', 'wp-authress' ); ?></a> -->
					<?php _e( ' for more information. If the problem persists, please follow the ', 'wp-authress' ); ?>
					  <a target="_blank" href="https://authress.io/knowledge-base"><?php _e( 'manual setup instructions', 'wp-authress' ); ?></a>.
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
