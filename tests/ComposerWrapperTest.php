<?php

use PHPUnit\Framework\TestCase;

class ComposerWrapperTest extends TestCase {

	private string $project_dir;

	protected function setUp(): void {
		$this->project_dir = sys_get_temp_dir() . '/authress-composer-wrapper-' . bin2hex( random_bytes( 6 ) );
		mkdir( $this->project_dir . '/scripts', 0755, true );
		copy( dirname( __DIR__ ) . '/scripts/composer.sh', $this->project_dir . '/scripts/composer.sh' );
		chmod( $this->project_dir . '/scripts/composer.sh', 0755 );
	}

	protected function tearDown(): void {
		array_map( 'unlink', glob( $this->project_dir . '/scripts/*' ) );
		array_map( 'unlink', glob( $this->project_dir . '/*.*' ) );
		rmdir( $this->project_dir . '/scripts' );
		rmdir( $this->project_dir );
	}

	private function installFakeComposer( $stderr_output, $exit_code ) {
		$fake_composer = "#!/bin/sh\necho \"composer called with: \$*\"\n"
			. 'cat >&2 <<\'OUTPUT\'' . "\n" . $stderr_output . "\nOUTPUT\n"
			. "exit {$exit_code}\n";
		file_put_contents( $this->project_dir . '/composer.phar', $fake_composer );
		chmod( $this->project_dir . '/composer.phar', 0755 );
	}

	private function runWrapper( array $arguments ) {
		$process = proc_open(
			array_merge( [ 'bash', $this->project_dir . '/scripts/composer.sh' ], $arguments ),
			[ 1 => [ 'pipe', 'w' ], 2 => [ 'pipe', 'w' ] ],
			$pipes,
			$this->project_dir
		);
		$stdout = stream_get_contents( $pipes[1] );
		$stderr = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );
		return [ 'exit_code' => proc_close( $process ), 'stdout' => $stdout, 'stderr' => $stderr ];
	}

	public function rateLimitOutputs() {
		return [
			'exhausted limit reported with the reset time' => [ 'GitHub API limit (60 calls/hr) is exhausted, could not fetch https://api.github.com/repos/guzzle/guzzle/zipball/abc. Create a GitHub OAuth token to go over the API rate limit. You can also wait until Tue Nov 24 18:00:00 UTC 2026 for the rate limit to reset.' ],
			'non interactive request for a token'          => [ 'Could not fetch https://api.github.com/repos/guzzle/guzzle/zipball/abc, please create a GitHub OAuth token to go over the API rate limit' ],
			'exhausted limit reported by a lowercase message' => [ 'api rate limit exceeded for 203.0.113.7' ],
		];
	}

	public function testSuccessfulRunPassesThroughWithoutAnyExplanation() {
		$this->installFakeComposer( 'Generating autoload files', 0 );

		$result = $this->runWrapper( [ 'install' ] );

		$this->assertSame( 0, $result['exit_code'] );
		$this->assertStringContainsString( 'Generating autoload files', $result['stderr'] );
		$this->assertStringNotContainsString( 'rate limit', strtolower( $result['stderr'] ) );
	}

	public function testArgumentsAreForwardedToComposer() {
		$this->installFakeComposer( '', 0 );

		$result = $this->runWrapper( [ 'install', '--prefer-dist', '--no-dev' ] );

		$this->assertStringContainsString( 'composer called with: install --prefer-dist --no-dev', $result['stdout'] );
	}

	/**
	 * @dataProvider rateLimitOutputs
	 */
	public function testRateLimitFailureExplainsHowToGoOverTheLimitAndKeepsTheExitCode( $composer_output ) {
		$this->installFakeComposer( $composer_output, 2 );

		$result = $this->runWrapper( [ 'install' ] );

		$this->assertSame( 2, $result['exit_code'] );
		$this->assertStringContainsString( $composer_output, $result['stderr'] );
		$this->assertStringContainsString( 'GitHub rate limited the Composer install', $result['stderr'] );
		$this->assertStringContainsString( 'COMPOSER_AUTH', $result['stderr'] );
		$this->assertStringContainsString( '2.9.8', $result['stderr'] );
	}

	public function testOtherFailuresKeepTheirExitCodeWithoutTheRateLimitExplanation() {
		$this->installFakeComposer( 'Your requirements could not be resolved to an installable set of packages.', 2 );

		$result = $this->runWrapper( [ 'install' ] );

		$this->assertSame( 2, $result['exit_code'] );
		$this->assertStringContainsString( 'Your requirements could not be resolved', $result['stderr'] );
		$this->assertStringNotContainsString( 'GitHub rate limited', $result['stderr'] );
	}

	public function testRateLimitTextInASuccessfulRunDoesNotTriggerTheExplanation() {
		$this->installFakeComposer( 'Note: the API rate limit was close but the install completed', 0 );

		$result = $this->runWrapper( [ 'install' ] );

		$this->assertSame( 0, $result['exit_code'] );
		$this->assertStringNotContainsString( 'GitHub rate limited', $result['stderr'] );
	}
}
