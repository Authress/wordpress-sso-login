<?php

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use PHPUnit\Framework\TestCase;

class LoginManagerIdTokenTest extends TestCase {

	private const DOMAIN = 'https://login.company.com';

	protected function setUp(): void {
		Authress_Sso_Login_ErrorLog::$errors = [];
	}

	private function newKeyPair() {
		$key_pair = sodium_crypto_sign_keypair();
		return [ sodium_crypto_sign_secretkey( $key_pair ), sodium_crypto_sign_publickey( $key_pair ) ];
	}

	private function jwk( $kid, $public_key ) {
		return (object) [
			'kid' => $kid,
			'alg' => 'EdDSA',
			'x'   => rtrim( strtr( base64_encode( $public_key ), '+/', '-_' ), '=' ),
		];
	}

	private function token( $kid, $secret_key, $issuer, $expires_in = '+1 hour' ) {
		$config = Configuration::forSymmetricSigner( new Signer\Eddsa(), InMemory::plainText( $secret_key ) );
		$now    = new DateTimeImmutable();
		return $config->builder()
			->issuedBy( $issuer )
			->relatedTo( 'google|user_alice' )
			->issuedAt( $now->modify( '-2 hours' ) )
			->expiresAt( $now->modify( $expires_in ) )
			->withHeader( 'kid', $kid )
			->getToken( $config->signer(), $config->signingKey() )
			->toString();
	}

	private function newManager( $custom_domain, $jwks ) {
		$options = $this->createMock( Authress_Sso_Login_Options::class );
		$options->method( 'get' )->with( 'customDomain' )->willReturn( $custom_domain );
		return new JwksStubLoginManager( $this->createMock( Authress_Sso_Login_UsersRepo::class ), $options, $jwks );
	}

	public function testValidTokenIsAccepted() {
		[ $secret_key, $public_key ] = $this->newKeyPair();
		$manager = $this->newManager( self::DOMAIN, [ $this->jwk( 'kid-valid', $public_key ) ] );

		$claims = $manager->decode_id_token( $this->token( 'kid-valid', $secret_key, self::DOMAIN ) );

		$this->assertSame( 'google|user_alice', $claims->sub );
	}

	public function testExpiredTokenIsRejected() {
		[ $secret_key, $public_key ] = $this->newKeyPair();
		$manager = $this->newManager( self::DOMAIN, [ $this->jwk( 'kid-expired', $public_key ) ] );

		$this->expectException( Authress_Sso_Login_InvalidIdTokenException::class );
		$manager->decode_id_token( $this->token( 'kid-expired', $secret_key, self::DOMAIN, '-1 hour' ) );
	}

	public function testTokenFromAnotherIssuerIsRejected() {
		[ $secret_key, $public_key ] = $this->newKeyPair();
		$manager = $this->newManager( self::DOMAIN, [ $this->jwk( 'kid-issuer', $public_key ) ] );

		$this->expectException( Authress_Sso_Login_InvalidIdTokenException::class );
		$manager->decode_id_token( $this->token( 'kid-issuer', $secret_key, 'https://attacker.example' ) );
	}

	public function testTokenSignedByAnotherKeyIsRejected() {
		[ $secret_key ]    = $this->newKeyPair();
		[ , $public_key ] = $this->newKeyPair();
		$manager = $this->newManager( self::DOMAIN, [ $this->jwk( 'kid-signature', $public_key ) ] );

		$this->expectException( Authress_Sso_Login_InvalidIdTokenException::class );
		$manager->decode_id_token( $this->token( 'kid-signature', $secret_key, self::DOMAIN ) );
	}

	public function testTokenWithAnUnknownKeyIdIsRejected() {
		[ $secret_key, $public_key ] = $this->newKeyPair();
		$manager = $this->newManager( self::DOMAIN, [ $this->jwk( 'kid-other', $public_key ) ] );

		$this->expectException( Authress_Sso_Login_InvalidIdTokenException::class );
		$manager->decode_id_token( $this->token( 'kid-unknown', $secret_key, self::DOMAIN ) );
	}

	public function testConstraintViolationsAreLoggedWithTheirDescription() {
		[ $secret_key, $public_key ] = $this->newKeyPair();
		$manager = $this->newManager( self::DOMAIN, [ $this->jwk( 'kid-log', $public_key ) ] );

		try {
			$manager->decode_id_token( $this->token( 'kid-log', $secret_key, self::DOMAIN, '-1 hour' ) );
			$this->fail( 'The expired token was accepted.' );
		} catch ( Authress_Sso_Login_InvalidIdTokenException $e ) {
			$this->assertStringContainsString( 'The token is expired', Authress_Sso_Login_ErrorLog::$errors[0] );
		}
	}

	public function testConfiguredDomainTrailingSlashesAreIgnoredForTheIssuerAndTheKeyFetch() {
		[ $secret_key, $public_key ] = $this->newKeyPair();
		$manager = $this->newManager( self::DOMAIN . '//', [ $this->jwk( 'kid-slash', $public_key ) ] );

		$claims = $manager->decode_id_token( $this->token( 'kid-slash', $secret_key, self::DOMAIN ) );

		$this->assertSame( 'google|user_alice', $claims->sub );
		$this->assertSame( [ self::DOMAIN ], $manager->fetched_issuers );
	}

	public function testAuthressAccountTokensAreAcceptedFromTheConfiguredLoginAndApiHosts() {
		[ $secret_key, $public_key ] = $this->newKeyPair();
		$domain  = 'https://acc.api-eu-west.authress.io';
		$manager = $this->newManager( $domain, [ $this->jwk( 'kid-account', $public_key ) ] );

		foreach ( [ $domain, 'https://acc.login.authress.io', 'https://acc.api.authress.io' ] as $issuer ) {
			$claims = $manager->decode_id_token( $this->token( 'kid-account', $secret_key, $issuer ) );
			$this->assertSame( 'google|user_alice', $claims->sub, $issuer );
		}
	}

	public function testAuthressAccountTokensFromAnotherAccountAreRejected() {
		[ $secret_key, $public_key ] = $this->newKeyPair();
		$manager = $this->newManager( 'https://acc.login.authress.io', [ $this->jwk( 'kid-other-account', $public_key ) ] );

		$this->expectException( Authress_Sso_Login_InvalidIdTokenException::class );
		$manager->decode_id_token( $this->token( 'kid-other-account', $secret_key, 'https://other.login.authress.io' ) );
	}

	public function testAllowedIssuersForACustomDomainIsOnlyTheDomain() {
		$manager = $this->newManager( self::DOMAIN, [] );

		$this->assertSame( [ self::DOMAIN ], $manager->get_allowed_issuers( self::DOMAIN ) );
	}

	public function testAllowedIssuersForAnAuthressDomainIncludeTheLoginAndApiHosts() {
		$manager = $this->newManager( 'https://acc.login.authress.io', [] );

		$this->assertSame(
			[ 'https://acc.login.authress.io', 'https://acc.api.authress.io' ],
			$manager->get_allowed_issuers( 'https://acc.login.authress.io' )
		);
	}
}
