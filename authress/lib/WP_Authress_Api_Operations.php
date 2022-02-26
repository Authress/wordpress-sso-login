<?php
class WP_Authress_Api_Operations {

	protected $a0_options;

	public function __construct( WP_Authress_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function create_wordpress_connection( $app_token, $migration_enabled, $password_policy = '', $migration_token = null ) {

		$domain             = $this->a0_options->get( 'domain' );
		$access_key          = $this->a0_options->get( 'access_key' );
		$db_connection_name = 'DB-' . get_authress_curatedBlogName();

		$body = [
			'name'            => $db_connection_name,
			'strategy'        => 'authress',
			'options'         => [
				'passwordPolicy' => $password_policy,
			],
			'enabled_clients' => [
				$access_key,
			],
		];

		$this->a0_options->set( 'db_connection_name', $db_connection_name );

		$response = WP_Authress_Api_Client::create_connection( $domain, $app_token, $body );

		if ( $response === false ) {
			return false;
		}

		return $response->id;
	}


	/**
	 * Get JS to use in the custom database script.
	 *
	 * @param string $name - Database script name.
	 *
	 * @return string
	 */
	protected function get_script( $name ) {
		return (string) file_get_contents( WP_AUTHRESS_PLUGIN_DIR . 'lib/scripts-js/db-' . $name . '.js' );
	}
}
