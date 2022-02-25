<?php
/**
 * Contains Trait WP_Authress_AsymmetricVerifier.
 *
 * @package WP-Authress
 *
 * @since 4.0.0
 */

use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsSigner;
use Lcobucci\JWT\Token;

/**
 * Class WP_Authress_AsymmetricVerifier
 *
 * @codeCoverageIgnore - Classes are adapted from the PHP SDK and tested there.
 */
final class WP_Authress_AsymmetricVerifier extends WP_Authress_SignatureVerifier {

	/**
	 * JWKS array with kid as keys, PEM cert as values.
	 *
	 * @var WP_Authress_JwksFetcher
	 */
	private $jwks;

	/**
	 * JwksVerifier constructor.
	 *
	 * @param WP_Authress_JwksFetcher $jwks WP_Authress_JwksFetcher to use.
	 */
	public function __construct( WP_Authress_JwksFetcher $jwks ) {
		$this->jwks = $jwks;
		parent::__construct( 'RS256' );
	}

	/**
	 * Check the token kid and signature.
	 *
	 * @param Token $token Parsed token to check.
	 *
	 * @return boolean
	 *
	 * @throws WP_Authress_InvalidIdTokenException If ID token kid was not found in the JWKS.
	 */
	protected function checkSignature( Token $token ) : bool {
		$token_kid   = $token->getHeader( 'kid', false );
		$signing_key = $this->jwks->getKey( $token_kid );
		if ( ! $signing_key ) {
			throw new WP_Authress_InvalidIdTokenException(
				'Could not find a public key for Key ID (kid) "' . $token_kid . '"'
			);
		}

		return $token->verify( new RsSigner(), new Key( $signing_key ) );
	}
}
