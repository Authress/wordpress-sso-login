<?php

use PHPUnit\Framework\TestCase;

class InitialSetupTest extends TestCase {

	private const RETURN_QUERY = [
		'accessKey'     => 'sc_key.secret',
		'customDomain'  => 'https://authress.company.com',
		'applicationId' => 'app_123',
	];

	protected function setUp(): void {
		wp_stub_reset();
	}

	private function newSetup( $options ) {
		return new Authress_Sso_Login_InitialSetup( $options );
	}

	public function testReadingTheReturnedValuesNeverSavesThem() {
		$options = $this->createMock( Authress_Sso_Login_Options::class );
		$options->expects( $this->never() )->method( 'set' );

		$setup = $this->newSetup( $options )->read_setup_values( self::RETURN_QUERY );

		$this->assertSame( self::RETURN_QUERY, $setup );
	}

	public function testReturnedValuesAreSanitized() {
		$options = $this->createMock( Authress_Sso_Login_Options::class );

		$setup = $this->newSetup( $options )->read_setup_values( array_merge( self::RETURN_QUERY, [ 'customDomain' => ' <b>authress.company.com</b> ' ] ) );

		$this->assertSame( 'authress.company.com', $setup['customDomain'] );
	}

	public function testReturnedCustomDomainTrailingSlashesAreRemoved() {
		$options = $this->createMock( Authress_Sso_Login_Options::class );

		$setup = $this->newSetup( $options )->read_setup_values( array_merge( self::RETURN_QUERY, [ 'customDomain' => 'https://authress.company.com//' ] ) );

		$this->assertSame( 'https://authress.company.com', $setup['customDomain'] );
	}

	public function testReturnWithAMissingValueIsIncomplete() {
		$options = $this->createMock( Authress_Sso_Login_Options::class );

		foreach ( array_keys( self::RETURN_QUERY ) as $missing ) {
			$query = self::RETURN_QUERY;
			unset( $query[ $missing ] );
			$this->assertNull( $this->newSetup( $options )->read_setup_values( $query ), $missing );
		}
	}

	public function testReturnWithAnEmptyValueIsIncomplete() {
		$options = $this->createMock( Authress_Sso_Login_Options::class );

		$this->assertNull( $this->newSetup( $options )->read_setup_values( array_merge( self::RETURN_QUERY, [ 'applicationId' => ' <b></b> ' ] ) ) );
	}

	public function testConfirmingSavesAllThreeValuesInOneWrite() {
		$options = $this->createMock( Authress_Sso_Login_Options::class );
		$saved   = [];
		$options->method( 'set' )->willReturnCallback(
			function ( $key, $value, $should_update = true ) use ( &$saved ) {
				$saved[] = [ $key, $value, $should_update ];
				return true;
			}
		);

		$this->assertTrue( $this->newSetup( $options )->save_confirmed_setup( self::RETURN_QUERY ) );

		$this->assertSame(
			[
				[ 'accessKey', 'sc_key.secret', false ],
				[ 'customDomain', 'https://authress.company.com', false ],
				[ 'applicationId', 'app_123', true ],
			],
			$saved
		);
	}

	public function testConfirmingAnIncompleteFormSavesNothing() {
		$options = $this->createMock( Authress_Sso_Login_Options::class );
		$options->expects( $this->never() )->method( 'set' );
		$query = self::RETURN_QUERY;
		unset( $query['accessKey'] );

		$this->assertFalse( $this->newSetup( $options )->save_confirmed_setup( $query ) );
	}

	public function testConfirmingWithoutManageOptionsSavesNothing() {
		wp_stub_reset( [ 'edit_posts' ] );
		$options = $this->createMock( Authress_Sso_Login_Options::class );
		$options->expects( $this->never() )->method( 'set' );

		$this->assertFalse( $this->newSetup( $options )->save_confirmed_setup( self::RETURN_QUERY ) );
	}
}
