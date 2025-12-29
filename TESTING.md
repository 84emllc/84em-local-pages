# 84EM Local Pages Generator - WP-CLI Integration Testing

## Overview

This plugin uses WP-CLI as its exclusive testing framework. All tests are executed through custom WP-CLI commands, providing a streamlined testing experience that integrates directly with WordPress.

**Testing Philosophy**: All tests are **integration tests** that use real WordPress functions (get_option, update_option, delete_option), real class instances, and **real API calls**. **No mocks, no anonymous classes**, just real integration testing with complete test isolation from production data.

## Requirements

- WordPress installation with WP-CLI installed
- PHP 8.2 or higher
- The 84EM Local Pages plugin activated
- **Claude API key configured** (production key or test key)

## Test Configuration

### Test Data Isolation

**All tests use isolated test data with `test_` prefixed WordPress options.** This ensures complete separation between test and production data:

- Tests read the production API key for authentication (from `84em_local_pages_claude_api_key_encrypted`)
- Tests write all data to `test_` prefixed options (like `test_84em_local_pages_claude_api_key_encrypted`, `test_84em_local_pages_claude_api_model`)
- Production options are **never modified** during test execution
- Test cleanup automatically removes all `test_` prefixed options

**API Key Configuration**: Tests require a valid Claude API key to be configured in production options. Set this once using:

```bash
wp 84em local-pages --set-api-key
wp 84em local-pages --set-api-model
```

The test suite will read this production API key but store all test data separately in `test_` prefixed options, ensuring production data remains untouched.

## Running Tests

### Install Dependencies

```bash
composer install
```

### Execute Tests

All tests are run through the `wp 84em local-pages --test` command:

```bash
# Run all tests
wp 84em local-pages --test --all

# Run specific test suite
wp 84em local-pages --test --suite=encryption
wp 84em local-pages --test --suite=data-structures
wp 84em local-pages --test --suite=url-generation
wp 84em local-pages --test --suite=ld-json
wp 84em local-pages --test --suite=cli-args
wp 84em local-pages --test --suite=content-processing
wp 84em local-pages --test --suite=simple
```

### Available Test Suites (v3.2.5)

1. **encryption** - Tests for API key encryption and decryption (4 tests)
2. **data-structures** - Tests for US states data structure (1 test)
3. **content-processing** - Tests for ContentProcessor class methods (12 tests)
4. **cli-args** - Tests for WP-CLI argument parsing (6 tests)
5. **ld-json** - Tests for LD-JSON schema generation (14 tests)
6. **api-client** - Tests for Claude API client (8 tests) - **No mocks, uses real instances**
7. **content-generators** - Tests for state and city content generators (10 tests) - **No mocks, uses real instances**
8. **error-handling** - Tests for error handling and logging (5 tests)
9. **security** - Tests for security features (5 tests)
10. **model-management** - Tests for model configuration and validation (13 tests)

**Total: 78 tests across 10 test suites**

**All tests use real WordPress functions and real class instances - no mocks!**

## Test Files

All test files are located in the `tests/integration` directory:

- `test-encryption.php` - API key encryption/decryption integration tests
- `test-data-structures.php` - Data structure validation integration tests
- `test-content-processing.php` - ContentProcessor class integration tests
- `test-wp-cli-args.php` - CLI argument parsing integration tests
- `test-ld-json-schema.php` - Schema generation integration tests
- `test-api-client.php` - Claude API client integration tests (real API calls, no mocks)
- `test-content-generators.php` - Content generator integration tests (real API calls, no mocks)
- `test-error-handling.php` - Error handling and recovery integration tests
- `test-security.php` - Security feature integration tests
- `test-model-management.php` - Model configuration and validation integration tests

### Version 3.2.5 Test Improvements (October 2025)

Complete removal of all mocks in favor of real WordPress and API integration with test data isolation:

- **Removed All Mocks**: No more anonymous classes or mock creation methods
- **ApiKeyManager Methods**: All tests use `ApiKeyManager` methods exclusively
  - Tests use `getKey()`, `setKey()`, `deleteKey()`, `getModel()`, `setModel()`, `deleteModel()`
  - No direct calls to `get_option()`, `update_option()`, `delete_option()` in tests
  - Properly respects `getOptionName()` which handles `test_` prefix logic
- **Real WordPress Functions**: Underlying implementation uses real WordPress functions
- **Real Class Instances**: All tests use real ApiKeyManager, ClaudeApiClient, etc.
- **Real API Calls**: Tests make actual calls to Claude API (not mocked)
- **Test Data Isolation**: All test data stored in `test_` prefixed options via ApiKeyManager
- **Production Safety**: Production options never modified during tests
- **RUNNING_TESTS Constant**: Automatic test mode detection in TestCase::setUp()
- **Automatic Cleanup**: All `test_` prefixed options removed in tearDown() via ApiKeyManager methods
- **All 82 Tests Pass**: 100% success rate with valid API key configured

This follows the WordPress best practice and global AGENTS.md guideline: "don't use mocks, always use real wordpress functions, api calls, etc." while maintaining proper encapsulation through the ApiKeyManager interface.

## Test Framework

The plugin includes a custom `TestCase` class (`tests/TestCase.php`) that provides assertion methods similar to PHPUnit but designed specifically for WP-CLI execution. This allows tests to run without requiring PHPUnit or other external testing frameworks.

### Available Assertions

- `assertEquals($expected, $actual, $message = '')`
- `assertTrue($value, $message = '')`
- `assertFalse($value, $message = '')`
- `assertNull($value, $message = '')`
- `assertNotNull($value, $message = '')`
- `assertArrayHasKey($key, $array, $message = '')`
- `assertStringContainsString($needle, $haystack, $message = '')`
- `assertIsArray($value, $message = '')`
- `assertIsBool($value, $message = '')`
- `assertCount($expected, $array, $message = '')`
- And many more...


## Writing New Integration Tests

To add new integration tests following the no-mocks philosophy with test data isolation:

1. Create a new file in `tests/integration` following the naming pattern `test-{feature}.php`
2. Include the required files:
   ```php
   require_once dirname( __DIR__ ) . '/TestCase.php';
   require_once dirname( __DIR__ ) . '/test-config.php';
   require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
   ```
3. Create a test class extending TestCase with proper setUp/tearDown:
   ```php
   class Test_Feature extends TestCase {
       private ApiKeyManager $apiKeyManager;
       private Encryption $encryption;

       public function setUp(): void {
           parent::setUp(); // IMPORTANT: Enables test mode (RUNNING_TESTS constant)

           // Create real instances (no mocks!)
           // These will automatically use test_ prefixed options due to RUNNING_TESTS
           $this->encryption = new Encryption();
           $this->apiKeyManager = new ApiKeyManager($this->encryption);

           // Set test data (will be stored in test_ prefixed options)
           $this->apiKeyManager->setKey(TestConfig::getTestApiKey());
           $this->apiKeyManager->setModel(TestConfig::getTestModel());
       }

       public function tearDown(): void {
           // Clean up test options using ApiKeyManager methods
           // ApiKeyManager will automatically handle test_ prefix
           $this->apiKeyManager->deleteKey();
           $this->apiKeyManager->deleteModel();
       }

       public function test_something() {
           // All WordPress option operations automatically use test_ prefix
           // Production options remain completely untouched
           $this->assertTrue($this->apiKeyManager->hasKey());
       }
   }
   ```
4. Add the test suite to the `$test_suites` array in the `wp_cli_test_handler` method
5. Run your tests with `wp 84em local-pages --test --suite=feature`

**Important**:
- Always call `parent::setUp()` to enable test mode
- Always use real WordPress functions and real class instances
- Never create mocks or anonymous classes
- Test data is automatically isolated using `test_` prefixed options
- Clean up by deleting `test_` prefixed options in tearDown()

## Test Configuration Class

The `TestConfig` class (`tests/test-config.php`) provides methods for retrieving production configuration for use in tests:

- `TestConfig::getTestApiKey()` - Returns production API key (read directly from `84em_local_pages_claude_api_key_encrypted`)
- `TestConfig::getTestModel()` - Returns production model identifier (from `84em_local_pages_claude_api_model` or default)

**Important**: TestConfig reads production options directly (not `test_` prefixed) to get the API key for authentication, but tests store all their data in `test_` prefixed options for complete isolation.

## Test Output

When running tests, you'll see:

- 📋 Test file being run
- ✅ Passed tests
- ❌ Failed tests with error messages
- 📊 Summary with total, passed, and failed counts

## Continuous Integration

**Note**: Due to complexities with WP-CLI command registration in CI environments, GitHub Actions currently only runs basic syntax checks. Full test suite should be run locally.

To run tests locally:
```bash
wp 84em local-pages --test --all
```

The GitHub Actions workflow performs:
- PHP syntax validation for all files
- composer.json validation

Full WP-CLI tests must be run in a proper WordPress environment where the plugin can register its commands correctly.

## Troubleshooting

### Tests Not Found

If you get a "Test directory not found" error, ensure:
1. The plugin is activated
2. You're running the command from your WordPress root directory
3. The tests directory exists at `wp-content/plugins/84em-local-pages/tests/integration/`

### Class Not Found

If you get a "Test class not found" error:
1. Check that the test file follows the naming convention
2. Ensure the class name matches the file name pattern
3. Verify the TestCase.php file exists

### WordPress Functions Not Available

Some tests may require WordPress functions. These are available when running through WP-CLI as the WordPress environment is already loaded.

### Critical Errors

Some tests that instantiate the plugin class directly may cause critical errors due to:
- The plugin already being loaded in WordPress
- Conflicts with singleton patterns or global state
- WordPress hooks being registered multiple times

Currently working integration test suites (v3.2.5):
- All 10 test suites are passing with 78 of 78 tests passing
- Tests focus on actual plugin functionality using real WordPress integration
- No mocks, no anonymous classes, just real instances and real database operations
- Complete test data isolation using `test_` prefixed options

**Total: 78 integration tests, 78 passing** (with valid Claude API key configured)

Note: All integration tests follow these principles:
- Use real WordPress functions (get_option, update_option, delete_option)
- Create real class instances (ApiKeyManager, ClaudeApiClient, etc.)
- Make real API calls to Claude (no mocking)
- Store all test data in `test_` prefixed options
- Production options never modified during tests
- Automatic cleanup of test data in tearDown()

## Testing Schema Regeneration

The plugin includes commands to regenerate LD-JSON schemas without regenerating content. To test:

```bash
# First, create some test pages
wp 84em local-pages --state="California"
wp 84em local-pages --state="California" --city="Los Angeles"

# Then test schema regeneration
wp 84em local-pages --regenerate-schema --state="California"
wp 84em local-pages --regenerate-schema --state="California" --city="Los Angeles"

# Verify the schema was updated
wp post meta get <post_id> schema
```

This is useful for:
- Fixing schema validation errors without API calls
- Updating schema structure after plugin updates
- Testing schema generation independently from content generation

## Benefits of WP-CLI Testing

1. **No External Dependencies** - No need for PHPUnit, Codeception, or other frameworks
2. **WordPress Integration** - Tests run in the actual WordPress environment
3. **Simple Execution** - Single command interface for all testing needs
4. **Lightweight** - Minimal setup and configuration required
5. **Production-Ready** - Can test in the same environment as production
