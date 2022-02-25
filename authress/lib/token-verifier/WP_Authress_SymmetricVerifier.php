<?php
/**
 * Contains Trait WP_Authress_SymmetricVerifier.
 *
 * @package WP-Authress
 *
 * @since 4.0.0
 */

use Lcobucci\JWT\Signer\Hmac\Sha256 as HsSigner;
use Lcobucci\JWT\Token;

/**
 * Class WP_Authress_SymmetricVerifier
 *
 * @codeCoverageIgnore - Classes are adapted from the PHP SDK and tested there.
 */
final class WP_Authress_SymmetricVerifier extends WP_Authress_SignatureVerifier {


	/**
	 * Client secret for the application.
	 *
	 * @var string
	 */
	private $clientSecret;

	/**
	 * SymmetricVerifier constructor.
	 *
	 * @param string $clientSecret Client secret for the application.
	 */
	public function __construct( string $clientSecret ) {
		$this->clientSecret = $clientSecret;
		parent::__construct( 'HS256' );
	}

	/**
	 * Check the token signature.
	 *
	 * @param Token $token Parsed token to check.
	 *
	 * @return boolean
	 */
	protected function checkSignature( Token $token ) : bool {
		return $token->verify( new HsSigner(), $this->clientSecret );
	}

	/**
	 * Algorithm for signature check.
	 *
	 * @return string
	 */
	protected function getAlgorithm() : string {
		return 'HS256';
	}
}
