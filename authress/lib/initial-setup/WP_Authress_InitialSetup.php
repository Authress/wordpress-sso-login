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
			$this->a0_options->set( 'accessKey', $_GET['accessKey'] );
			$this->a0_options->set( 'customDomain', $_GET['customDomain'] );
			$this->a0_options->set( 'applicationId', $_GET['applicationId'] );
		}
		include WP_AUTHRESS_PLUGIN_DIR . 'templates/initial-setup/setup_wizard.php';
	}

	public function cant_create_client_message() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php _e( 'There was an error creating the Authress App. Check the ', 'wp-authress' ); ?>
					  <a target="_blank" href="<?php echo admin_url( 'admin.php?page=authress_errors' ); ?>"><?php _e( 'error log', 'wp-authress' ); ?></a>
					<?php _e( ' for more information. If the problem persists, please follow the ', 'wp-authress' ); ?>
					  <a target="_blank" href="https://authress.io/knowledge-base"><?php _e( 'manual setup instructions', 'wp-authress' ); ?></a>.
				  </strong>
			  </p>
		  </div>
		<?php
	}

	public function cant_create_client_grant_message() {
		?>
		<div class="notice notice-error">
			<p>
				<strong>
					<?php _e( 'There was an error creating the necessary client grants. ', 'wp-authress' ); ?>
					<?php
					_e( 'Go to your Authress dashboard > APIs > Authress Management API > Machine to Machine Applications tab and authorize this Application. ', 'wp-authress' );
					?>
					<?php _e( 'Make sure to add the following scopes: ', 'wp-authress' ); ?>
					<code><?php echo implode( '</code>, <code>', WP_Authress_Api_Client::get_required_scopes() ); ?></code>
					<?php _e( 'You can also check the ', 'wp-authress' ); ?>
					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=authress_errors' ); ?>"><?php _e( 'Error log', 'wp-authress' ); ?></a>
					<?php _e( ' for more information.', 'wp-authress' ); ?>
				</strong>
			</p>
		</div>
		<?php
	}

	public function cant_exchange_token_message() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php _e( 'There was an error retrieving your Authress credentials. Check the ', 'wp-authress' ); ?>
					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=authress_errors' ); ?>"><?php _e( 'Error log', 'wp-authress' ); ?></a>
					<?php _e( ' for more information.', 'wp-authress' ); ?>
					<?php _e( 'Please check that your server has internet access and can reach ', 'wp-authress' ); ?>
					<code><?php echo esc_url( 'https://' . $this->a0_options->get( 'domain' ) ); ?></code>
				  </strong>
			  </p>
		  </div>
		<?php
	}

	public function rejected_message() {
		?>
	  <div class="notice notice-error">
		<p>
		  <strong>
				<?php _e( 'The required scopes were rejected.', 'wp-authress' ); ?>
		  </strong>
		</p>
	  </div>
		<?php
	}

	public function access_denied_message() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php _e( 'Please create your Authress account first at ', 'wp-authress' ); ?><a href="https://https://authress.io/app/#/setup">https://https://authress.io/app/#/setup</a>
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
