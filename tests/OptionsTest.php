<?php

use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase {

	protected function setUp(): void {
		wp_stub_reset();
	}

	public function trailingSlashCases() {
		return [
			'no trailing slash'       => [ 'https://authress.company.com', 'https://authress.company.com' ],
			'one trailing slash'      => [ 'https://authress.company.com/', 'https://authress.company.com' ],
			'several trailing slashes' => [ 'https://authress.company.com///', 'https://authress.company.com' ],
		];
	}

	/**
	 * @dataProvider trailingSlashCases
	 */
	public function testCustomDomainReadFromTheDatabaseHasNoTrailingSlashes( $stored, $expected ) {
		$GLOBALS['wp_stub_options']['authress_settings'] = [ 'customDomain' => $stored ];

		$this->assertSame( $expected, ( new Authress_Sso_Login_Options() )->get( 'customDomain' ) );
	}

	/**
	 * @dataProvider trailingSlashCases
	 */
	public function testCustomDomainIsSavedWithoutTrailingSlashes( $given, $expected ) {
		$options = new Authress_Sso_Login_Options();

		$options->set( 'customDomain', $given );

		$this->assertSame( $expected, $GLOBALS['wp_stub_options']['authress_settings']['customDomain'] );
	}

	public function testOtherSettingsKeepTheirTrailingSlashes() {
		$GLOBALS['wp_stub_options']['authress_settings'] = [ 'accessKey' => 'key/', 'applicationId' => 'app/' ];
		$options = new Authress_Sso_Login_Options();

		$this->assertSame( 'key/', $options->get( 'accessKey' ) );
		$this->assertSame( 'app/', $options->get( 'applicationId' ) );
	}

	public function testMissingCustomDomainFallsBackToTheDefault() {
		$GLOBALS['wp_stub_options']['authress_settings'] = [ 'accessKey' => 'key' ];

		$this->assertNull( ( new Authress_Sso_Login_Options() )->get( 'customDomain' ) );
		$this->assertSame( 'fallback', ( new Authress_Sso_Login_Options() )->get( 'customDomain', 'fallback' ) );
	}

	public function testOnlySettingsThePluginReadsHaveDefaults() {
		$this->assertSame(
			[ 'applicationId', 'customDomain', 'accessKey', 'default_login_redirection', 'authress_server_domain', 'override_wp_avatars', 'remember_users_session' ],
			( new Authress_Sso_Login_Options() )->get_defaults( true )
		);
	}
}
