<?php

/**
 * Class WP_Authress_EmailNotVerifiedException
 */
class WP_Authress_EmailNotVerifiedException extends Exception {

	public $userinfo;
	public $id_token;

	/**
	 * WP_Authress_EmailNotVerifiedException constructor.
	 *
	 * @param stdClass $userinfo - userinfo object returned from Authress
	 * @param string   $id_token - should not be output in any template
	 */
	public function __construct( $userinfo, $id_token ) {
		$this->userinfo = $userinfo;
		$this->id_token = $id_token;
	}
}
