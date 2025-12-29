<?php
/**
 * Test Command Handler
 *
 * @package EightyFourEM\LocalPages\Cli\Commands
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Cli\Commands;

use WP_CLI;
use Exception;

/**
 * Handles test-related CLI commands
 */
class TestCommand {

    /**
     * Available test suites
     *
     * @var array
     */
    private array $testSuites
        = [
            'encryption'           => 'test-encryption.php',
            'data-structures'      => 'test-data-structures.php',
            'content-processing'   => 'test-content-processing.php',
            'cli-args'             => 'test-wp-cli-args.php',
            'ld-json'              => 'test-ld-json-schema.php',
            'api-client'           => 'test-api-client.php',
            'content-generators'   => 'test-content-generators.php',
            'error-handling'       => 'test-error-handling.php',
            'security'             => 'test-security.php',
            'model-management'     => 'test-model-management.php',
        ];

    /**
     * Handle test commands
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handle( array $args, array $assoc_args ): void {
        $suite = $assoc_args['suite'] ?? null;
        $all   = isset( $assoc_args['all'] );

        if ( ! $suite && ! $all ) {
            WP_CLI::error( 'Please specify --suite=<name> or --all' );
            return;
        }

        // Check if API key and model are configured
        $this->ensureApiConfiguration();

        $test_dir   = $this->getTestDirectory();
        $mocks_file = $this->getMocksFile();

        if ( ! is_dir( $test_dir ) ) {
            WP_CLI::error( 'Test directory not found: ' . $test_dir );
            return;
        }

        // Load WordPress mocks if not already loaded
        $this->loadWordPressMocks( $mocks_file );

        if ( $all ) {
            $this->runAllTests( $test_dir );
        }
        else {
            $this->runSpecificTest( $test_dir, $suite );
        }
    }

    /**
     * Ensure API key and model are configured before running tests
     *
     * @return void
     */
    private function ensureApiConfiguration(): void {
        $has_key   = get_option( '84em_local_pages_claude_api_key_encrypted' );
        $has_model = get_option( '84em_local_pages_claude_api_model' );

        if ( ! $has_key || ! $has_model ) {
            WP_CLI::line( '' );
            WP_CLI::warning( '⚠️  API Configuration Required' );
            WP_CLI::line( '=============================' );
            WP_CLI::line( '' );

            $missing = [];
            if ( ! $has_key ) {
                WP_CLI::line( '❌ Claude API key is not configured.' );
                $missing[] = 'API key';
            }

            if ( ! $has_model ) {
                WP_CLI::line( '❌ Claude API model is not configured.' );
                $missing[] = 'API model';
            }

            WP_CLI::line( '' );
            WP_CLI::line( 'Tests require both a valid Claude API key and model to run.' );
            WP_CLI::line( '' );
            WP_CLI::line( 'Please configure the missing ' . implode( ' and ', $missing ) . ':' );
            WP_CLI::line( '' );

            if ( ! $has_key ) {
                WP_CLI::line( '  1. Set API key:' );
                WP_CLI::line( '     wp 84em local-pages --set-api-key' );
                WP_CLI::line( '' );
            }

            if ( ! $has_model ) {
                WP_CLI::line( '  ' . ( ! $has_key ? '2' : '1' ) . '. Set API model:' );
                WP_CLI::line( '     wp 84em local-pages --set-api-model' );
                WP_CLI::line( '' );
            }

            WP_CLI::line( 'After configuration, run your tests again.' );
            WP_CLI::line( '' );

            WP_CLI::error( 'API configuration incomplete. Cannot proceed with tests.' );
        }
    }

    /**
     * Run all available test suites
     *
     * @param  string  $test_dir  Test directory path
     *
     * @return void
     */
    private function runAllTests( string $test_dir ): void {
        WP_CLI::line( '' );
        WP_CLI::line( '🧪 Running All Test Suites' );
        WP_CLI::line( '==========================' );
        WP_CLI::line( '' );

        $total_tests   = 0;
        $passed_tests  = 0;
        $failed_tests  = 0;
        $failed_suites = [];

        foreach ( $this->testSuites as $suite_name => $test_file ) {
            WP_CLI::line( "📋 Running {$suite_name} tests..." );

            $result = $this->runTestFile( $test_dir . $test_file );

            if ( $result['success'] ) {
                $total_tests  += $result['total'];
                $passed_tests += $result['passed'];
                $failed_tests += $result['failed'];

                if ( $result['failed'] > 0 ) {
                    $failed_suites[] = $suite_name;
                    WP_CLI::warning( "  ❌ {$result['passed']}/{$result['total']} tests passed" );
                }
                else {
                    WP_CLI::success( "  ✅ {$result['passed']}/{$result['total']} tests passed" );
                }
            }
            else {
                $failed_suites[] = $suite_name;
                WP_CLI::error( "  💥 Test suite failed to run: {$result['error']}" );
            }

            WP_CLI::line( '' );
        }

        // Summary
        WP_CLI::line( '📊 Test Summary' );
        WP_CLI::line( '===============' );
        WP_CLI::line( "Total test suites: " . count( $this->testSuites ) );
        WP_CLI::line( "Total tests: {$total_tests}" );
        WP_CLI::line( "Passed: {$passed_tests}" );
        WP_CLI::line( "Failed: {$failed_tests}" );

        if ( empty( $failed_suites ) ) {
            WP_CLI::success( '🎉 All tests passed!' );
        }
        else {
            WP_CLI::warning( '⚠️  Failed suites: ' . implode( ', ', $failed_suites ) );
        }
    }

    /**
     * Run a specific test suite
     *
     * @param  string  $test_dir  Test directory path
     * @param  string  $suite  Suite name
     *
     * @return void
     */
    private function runSpecificTest( string $test_dir, string $suite ): void {
        if ( ! isset( $this->testSuites[ $suite ] ) ) {
            WP_CLI::error(
                "Invalid test suite: {$suite}. Available suites: " .
                implode( ', ', array_keys( $this->testSuites ) )
            );
            return;
        }

        $test_file = $this->testSuites[ $suite ];

        WP_CLI::line( '' );
        WP_CLI::line( "🧪 Running {$suite} Test Suite" );
        WP_CLI::line( str_repeat( '=', 30 + strlen( $suite ) ) );
        WP_CLI::line( '' );

        $result = $this->runTestFile( $test_dir . $test_file );

        if ( $result['success'] ) {
            if ( $result['failed'] > 0 ) {
                WP_CLI::warning( "Tests completed with failures: {$result['passed']}/{$result['total']} passed" );
            }
            else {
                WP_CLI::success( "All tests passed: {$result['passed']}/{$result['total']}" );
            }
        }
        else {
            WP_CLI::error( "Test suite failed to run: {$result['error']}" );
        }
    }

    /**
     * Run a specific test file
     *
     * @param  string  $test_file  Path to test file
     *
     * @return array Results array with success, total, passed, failed, error keys
     */
    private function runTestFile( string $test_file ): array {
        if ( ! file_exists( $test_file ) ) {
            return [
                'success' => false,
                'total'   => 0,
                'passed'  => 0,
                'failed'  => 0,
                'error'   => 'Test file not found: ' . basename( $test_file ),
            ];
        }

        try {
            // Load the TestCase base class
            require_once $this->getTestCaseFile();

            // Capture output to prevent interference
            ob_start();

            // Include the test file
            require_once $test_file;

            // Get all declared classes and find test classes
            $all_classes  = get_declared_classes();
            $test_classes = array_filter( $all_classes, function ( $class ) {
                return strpos( $class, 'Test_' ) === 0 || strpos( $class, 'Test' ) === 0;
            } );

            $total_tests  = 0;
            $passed_tests = 0;
            $failed_tests = 0;

            foreach ( $test_classes as $test_class ) {
                $reflection = new \ReflectionClass( $test_class );

                // Skip if not in the current test file
                if ( $reflection->getFileName() !== $test_file ) {
                    continue;
                }

                $methods = $reflection->getMethods( \ReflectionMethod::IS_PUBLIC );

                foreach ( $methods as $method ) {
                    if ( strpos( $method->getName(), 'test' ) === 0 ) {
                        $total_tests ++;

                        try {
                            // Create a fresh instance for each test to ensure isolation
                            $instance = new $test_class();

                            // Call setUp if it exists
                            if ( method_exists( $instance, 'setUp' ) ) {
                                $instance->setUp();
                            }

                            // Run the test
                            $instance->{$method->getName()}();

                            $passed_tests ++;
                            WP_CLI::log( "  ✅ {$method->getName()}" );

                            // Call tearDown if it exists
                            if ( method_exists( $instance, 'tearDown' ) ) {
                                $instance->tearDown();
                            }

                        } catch ( Exception $e ) {
                            $failed_tests ++;
                            WP_CLI::warning( "  ❌ {$method->getName()}: {$e->getMessage()}" );
                        }
                    }
                }
            }

            // Clean up output buffer
            ob_end_clean();

            return [
                'success' => true,
                'total'   => $total_tests,
                'passed'  => $passed_tests,
                'failed'  => $failed_tests,
                'error'   => null,
            ];

        } catch ( Exception $e ) {
            // Clean up output buffer on error
            if ( ob_get_level() > 0 ) {
                ob_end_clean();
            }

            return [
                'success' => false,
                'total'   => 0,
                'passed'  => 0,
                'failed'  => 0,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Get test directory path
     *
     * @return string
     */
    private function getTestDirectory(): string {
        return dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/tests/integration/';
    }

    /**
     * Get WordPress mocks file path
     *
     * @return string
     */
    private function getMocksFile(): string {
        return dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/tests/wp-mocks.php';
    }

    /**
     * Get TestCase base class file path
     *
     * @return string
     */
    private function getTestCaseFile(): string {
        return dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/tests/TestCase.php';
    }

    /**
     * Load WordPress mocks if needed
     *
     * @param  string  $mocks_file  Path to mocks file
     *
     * @return void
     */
    private function loadWordPressMocks( string $mocks_file ): void {
        if ( file_exists( $mocks_file ) && ! function_exists( 'sanitize_title' ) ) {
            require_once $mocks_file;
            WP_CLI::debug( 'Loaded WordPress mocks for testing' );
        }
    }
}
