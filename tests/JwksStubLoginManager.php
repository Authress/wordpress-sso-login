<?php

class JwksStubLoginManager extends Authress_Sso_Login_LoginManager {
	public $fetched_issuers = [];

	public function __construct( Authress_Sso_Login_UsersRepo $users_repo, Authress_Sso_Login_Options $a0_options, private array $jwks ) {
		parent::__construct( $users_repo, $a0_options );
	}

	protected function fetch_jwks( $issuer ) {
		$this->fetched_issuers[] = $issuer;
		return $this->jwks;
	}
}
