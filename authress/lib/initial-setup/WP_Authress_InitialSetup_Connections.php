<?php

class WP_Authress_InitialSetup_Connections {

	protected $a0_options;

	public function __construct( WP_Authress_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render( $step ) {
		include WP_AUTHRESS_PLUGIN_DIR . 'templates/initial-setup/connections.php';
	}

	public function callback() {
		wp_safe_redirect( admin_url( 'admin.php?page=authress_introduction&step=5' ) );
	}

	public function add_validation_error( $error ) {
		wp_safe_redirect(
			admin_url(
				'admin.php?page=authress_introduction&step=5&error=' .
				urlencode( 'There was an error setting up your connections.' )
			)
		);
		exit;
	}
}
