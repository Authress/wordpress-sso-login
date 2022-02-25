<?php

class WP_Authress_InitialSetup_End {

	protected $a0_options;

	public function __construct( WP_Authress_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render( $step ) {
		include WP_AUTHRESS_PLUGIN_DIR . 'templates/initial-setup/end.php';
	}

	public function callback() {
	}

}
