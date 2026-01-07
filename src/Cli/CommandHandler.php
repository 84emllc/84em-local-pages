<?php
/**
 * Main WP-CLI Command Handler
 *
 * @package EightyFourEM\LocalPages\Cli
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Cli;

use EightyFourEM\LocalPages\Cli\Commands\TestCommand;
use EightyFourEM\LocalPages\Cli\Commands\GenerateCommand;
use EightyFourEM\LocalPages\Cli\TestimonialIdFinder;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use WP_CLI;

/**
 * Main CLI command handler that routes to appropriate command classes
 */
class CommandHandler {

    /**
     * API key manager instance
     *
     * @var ApiKeyManager
     */
    private ApiKeyManager $apiKeyManager;

    /**
     * Test command handler
     *
     * @var TestCommand
     */
    private TestCommand $testCommand;

    /**
     * Generate command handler
     *
     * @var GenerateCommand
     */
    private GenerateCommand $generateCommand;

    /**
     * Constructor
     *
     * @param  ApiKeyManager  $apiKeyManager
     * @param  TestCommand  $testCommand
     * @param  GenerateCommand  $generateCommand
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        TestCommand $testCommand,
        GenerateCommand $generateCommand
    ) {
        $this->apiKeyManager    = $apiKeyManager;
        $this->testCommand      = $testCommand;
        $this->generateCommand  = $generateCommand;
    }

    /**
     * Main WP-CLI command handler for all plugin commands
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments (flags)
     *
     * @return void
     */
    public function handle( array $args, array $assoc_args ): void {
        try {
            // Validate arguments before processing
            $this->validateArguments( $args, $assoc_args );
            // Handle API key configuration
            if ( isset( $assoc_args['set-api-key'] ) ) {
                $this->handleApiKeySet();
                return;
            }

            // Handle API key validation
            if ( isset( $assoc_args['validate-api-key'] ) ) {
                $this->handleApiKeyValidation();
                return;
            }

            // Handle API model configuration
            if ( isset( $assoc_args['set-api-model'] ) ) {
                $this->handleApiModelSet();
                return;
            }

            if ( isset( $assoc_args['get-api-model'] ) ) {
                $this->handleApiModelGet();
                return;
            }

            if ( isset( $assoc_args['validate-api-model'] ) ) {
                $this->handleApiModelValidation();
                return;
            }

            if ( isset( $assoc_args['reset-api-model'] ) ) {
                $this->handleApiModelReset();
                return;
            }

            // Handle testimonial ID finder (utility command)
            if ( isset( $assoc_args['find-testimonial-ids'] ) ) {
                $finder = new TestimonialIdFinder();
                $finder();
                return;
            }

            // Handle test command (doesn't require API key)
            if ( isset( $assoc_args['test'] ) ) {
                $this->testCommand->handle( $args, $assoc_args );
                return;
            }

            // Handle commands that don't require API key
            if ( isset( $assoc_args['generate-sitemap'] ) ) {
                $this->generateCommand->handleSitemapGeneration( $args, $assoc_args );
                return;
            }

            if ( isset( $assoc_args['generate-index'] ) ) {
                $this->generateCommand->handleIndexGeneration( $args, $assoc_args );
                return;
            }

            if ( isset( $assoc_args['regenerate-schema'] ) ) {
                $this->generateCommand->handleSchemaRegeneration( $args, $assoc_args );
                return;
            }

            if ( isset( $assoc_args['update-location-links'] ) ) {
                $this->generateCommand->handleUpdateLocationLinks( $args, $assoc_args );
                return;
            }

            if ( isset( $assoc_args['update-page-templates'] ) ) {
                $this->generateCommand->handleUpdatePageTemplates( $args, $assoc_args );
                return;
            }

            if ( isset( $assoc_args['migrate-urls'] ) ) {
                $this->generateCommand->handleUrlMigration( $args, $assoc_args );
                return;
            }

            // Handle delete operations (don't require API key)
            if ( isset( $assoc_args['delete'] ) ) {
                $this->generateCommand->handleDelete( $args, $assoc_args );
                return;
            }

            // If no specific command is provided, show help (doesn't require API key)
            if ( empty( $assoc_args )
                 || ( count( $assoc_args ) === 1 && isset( $assoc_args['help'] ) ) ) {
                $this->showHelp();
                return;
            }

            // Handle generation commands (require API key)
            if ( isset( $assoc_args['generate-all'] )
                 || isset( $assoc_args['update-all'] )
                 || isset( $assoc_args['state'] )
                 || isset( $assoc_args['city'] )
                 || isset( $assoc_args['update'] ) ) {

                // These commands require API key
                if ( ! $this->validateApiKey() ) {
                    WP_CLI::error( 'Claude API key not found or invalid. Please set it first using --set-api-key' );
                    return;
                }

                if ( isset( $assoc_args['generate-all'] ) ) {
                    $this->generateCommand->handleGenerateAll( $args, $assoc_args );
                    return;
                }

                if ( isset( $assoc_args['update-all'] ) ) {
                    $this->generateCommand->handleUpdateAll( $args, $assoc_args );
                    return;
                }

                // If both state and city are provided, handle as city command
                if ( isset( $assoc_args['state'] ) && isset( $assoc_args['city'] ) ) {
                    $this->generateCommand->handleCity( $args, $assoc_args );
                    return;
                }

                // Handle state-only command
                if ( isset( $assoc_args['state'] ) ) {
                    $this->generateCommand->handleState( $args, $assoc_args );
                    return;
                }

                // Handle city-only command (will error if no state provided)
                if ( isset( $assoc_args['city'] ) ) {
                    $this->generateCommand->handleCity( $args, $assoc_args );
                    return;
                }

                if ( isset( $assoc_args['update'] ) ) {
                    $this->generateCommand->handleUpdate( $args, $assoc_args );
                    return;
                }
            }

            // Default: show help for unrecognized commands
            $this->showHelp();

        } catch ( \Exception $e ) {
            WP_CLI::error( 'Command failed: ' . $e->getMessage() );
        }
    }

    /**
     * Handle API key setting
     *
     * @return void
     */
    private function handleApiKeySet(): void {
        WP_CLI::line( 'Setting Claude API Key' );
        WP_CLI::line( '=====================' );
        WP_CLI::line( '' );
        WP_CLI::line( 'For security reasons, please paste your Claude API key when prompted.' );
        WP_CLI::line( 'The key will not be visible as you type and will not appear in your shell history.' );
        WP_CLI::line( '' );

        // Flush all output buffers before reading input to ensure proper display order
        while ( ob_get_level() > 0 ) {
            ob_end_flush();
        }
        flush();

        // Disable echo for secure input
        if ( function_exists( 'system' ) ) {
            system( 'stty -echo' );
        }

        // Now show the prompt and read input
        fwrite( STDOUT, 'Paste your Claude API key: ' );
        fflush( STDOUT );

        $handle  = fopen( 'php://stdin', 'r' );
        $api_key = trim( fgets( $handle ) );
        fclose( $handle );

        // Re-enable echo
        if ( function_exists( 'system' ) ) {
            system( 'stty echo' );
        }

        WP_CLI::line( '' ); // New line after hidden input

        // Debug: Show length of captured input
        WP_CLI::debug( 'Captured input length: ' . strlen( $api_key ) );

        if ( empty( $api_key ) ) {
            WP_CLI::error( 'No API key provided. Operation cancelled.' );
            return;
        }

        // Validate API key format (Claude keys start with 'sk-ant-')
        if ( ! str_starts_with( $api_key, 'sk-ant-' ) ) {
            WP_CLI::warning( 'API key format may be invalid. Claude API keys typically start with "sk-ant-".' );
            if ( ! WP_CLI::confirm( 'Continue anyway?' ) ) {
                WP_CLI::line( 'Operation cancelled.' );
                return;
            }
        }

        try {
            // Store the key first
            $result = $this->apiKeyManager->setKey( $api_key );
            if ( ! $result ) {
                WP_CLI::error( 'Failed to store the API key.' );
                return;
            }

            WP_CLI::success( 'Claude API key securely encrypted and stored.' );

            // Verify it was actually stored
            $verify = $this->apiKeyManager->getKey();
            if ( ! $verify ) {
                WP_CLI::error( 'Error: Key was stored but could not be retrieved for verification.' );
                return;
            }

            // Now try to validate with Claude API
            WP_CLI::line( '' );
            WP_CLI::line( 'Testing API key with Claude...' );

            $apiClient = new ClaudeApiClient( $this->apiKeyManager );
            // Skip model check since we're just validating the API key
            if ( $apiClient->validateCredentials( skip_model_check: true ) ) {
                WP_CLI::success( '✅ API key is valid and working!' );
            }
            else {
                WP_CLI::warning( '❌ Could not validate API key with Claude. The key has been stored but may not be valid.' );
                WP_CLI::line( 'You can test it again later with: wp 84em local-pages --validate-api-key' );
            }

        } catch ( \Exception $e ) {
            WP_CLI::error( 'Failed to set API key: ' . $e->getMessage() );
        }
    }

    /**
     * Handle API key validation
     *
     * @return void
     */
    private function handleApiKeyValidation(): void {
        WP_CLI::line( 'Validating Stored API Key' );
        WP_CLI::line( '========================' );
        WP_CLI::line( '' );

        try {
            if ( ! $this->apiKeyManager->hasKey() ) {
                WP_CLI::error( 'No API key found. Please set one first using --set-api-key' );
                return;
            }

            WP_CLI::log( 'Found stored API key. Testing...' );

            $apiClient = new ClaudeApiClient( $this->apiKeyManager );

            // Check if model is configured
            $has_model = $this->apiKeyManager->hasCustomModel();
            if ( ! $has_model ) {
                WP_CLI::line( 'Note: No model configured yet. Validating API key only.' );
            }

            // Validate with or without model check depending on configuration
            if ( $apiClient->validateCredentials( skip_model_check: ! $has_model ) ) {
                WP_CLI::success( '✅ Stored API key is valid and working!' );

                if ( ! $has_model ) {
                    WP_CLI::line( 'Next step: Set a model using --set-api-model' );
                }
            }
            else {
                WP_CLI::error( '❌ Stored API key is invalid or not working.' );
            }

        } catch ( \Exception $e ) {
            WP_CLI::error( 'Failed to validate API key: ' . $e->getMessage() );
        }
    }

    /**
     * Validate API key exists and is configured
     *
     * @return bool
     */
    private function validateApiKey(): bool {
        try {
            $api_key = $this->apiKeyManager->getKey();
            return ! empty( $api_key );
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Handle API model setting
     *
     * @return void
     */
    private function handleApiModelSet(): void {
        WP_CLI::line( 'Setting Claude API Model' );
        WP_CLI::line( '=======================' );
        WP_CLI::line( '' );

        // Check if API key is configured first
        if ( ! $this->validateApiKey() ) {
            WP_CLI::error( 'API key not configured. Please set it first using --set-api-key' );
            return;
        }

        // Show current model
        $current_model = $this->apiKeyManager->getModel();
        $is_custom = $this->apiKeyManager->hasCustomModel();
        if ( empty( $current_model ) ) {
            $current_model = 'NOT DEFINED';
            $model_status = 'NOT CONFIGURED';
        }
        else {
            $model_status = $is_custom ? 'custom' : 'default';
        }

        WP_CLI::line( "Current model: {$current_model} ({$model_status})" );
        WP_CLI::line( '' );

        // Fetch available models from Claude API
        WP_CLI::line( 'Fetching available models from Claude API...' );
        try {
            $apiClient = new ClaudeApiClient( $this->apiKeyManager );
            $models_result = $apiClient->getAvailableModels();

            if ( ! $models_result['success'] ) {
                WP_CLI::error( "❌ Failed to fetch models: {$models_result['message']}" );
                return;
            }

            $models = $models_result['models'];

            if ( empty( $models ) ) {
                WP_CLI::error( '❌ No models available. Please check your API key permissions.' );
                return;
            }

            WP_CLI::line( '' );
            WP_CLI::line( 'Available Claude models:' );
            WP_CLI::line( '' );

            // Display models with numbers for selection
            foreach ( $models as $index => $model_info ) {
                $number = $index + 1;
                $display = $model_info['display_name'];
                $id = $model_info['id'];

                // Highlight current model
                if ( $id === $current_model ) {
                    WP_CLI::line( "  [{$number}] {$display} (current)" );
                } else {
                    WP_CLI::line( "  [{$number}] {$display}" );
                }
            }

            WP_CLI::line( '' );

            // Flush all output buffers before reading input to ensure proper display order
            while ( ob_get_level() > 0 ) {
                ob_end_flush();
            }
            flush();

            // Now show the prompt and read input
            fwrite( STDOUT, 'Enter the number of the model you want to use (or 0 to cancel): ' );
            fflush( STDOUT );

            $handle = fopen( 'php://stdin', 'r' );
            $selection = trim( fgets( $handle ) );
            fclose( $handle );

            // Validate selection
            if ( $selection === '0' || $selection === '' ) {
                WP_CLI::line( 'Operation cancelled.' );
                return;
            }

            $selection_num = (int) $selection;
            if ( $selection_num < 1 || $selection_num > count( $models ) ) {
                WP_CLI::error( '❌ Invalid selection. Please enter a number from the list.' );
                return;
            }

            $selected_model = $models[ $selection_num - 1 ];
            $model_id = $selected_model['id'];

            WP_CLI::line( '' );
            WP_CLI::line( "Selected: {$selected_model['display_name']}" );
            WP_CLI::line( 'Validating model with Claude API...' );

            // Validate the selected model
            $validation = $apiClient->validateModel( $model_id );

            if ( $validation['success'] ) {
                WP_CLI::success( "✅ {$validation['message']}" );

                // Save the model
                $result = $this->apiKeyManager->setModel( $model_id );
                if ( ! $result ) {
                    WP_CLI::error( '❌ Failed to store the model configuration.' );
                    return;
                }

                WP_CLI::success( 'Model updated successfully!' );
            } else {
                WP_CLI::error( "❌ Model validation failed: {$validation['message']}" );
                WP_CLI::line( 'Model was NOT saved. Please try another model.' );
            }
        } catch ( \Exception $e ) {
            WP_CLI::error( '❌ Failed to set model: ' . $e->getMessage() );
        }
    }

    /**
     * Handle API model retrieval
     *
     * @return void
     */
    private function handleApiModelGet(): void {
        WP_CLI::line( 'Current API Model Configuration' );
        WP_CLI::line( '===============================' );
        WP_CLI::line( '' );

        $current_model = $this->apiKeyManager->getModel();

        if ( false === $current_model ) {
            WP_CLI::warning( 'No model configured.' );
            WP_CLI::line( 'Use --set-api-model to select a model from the Claude API.' );
        } else {
            WP_CLI::line( "Current API model: {$current_model}" );
            WP_CLI::line( 'Status: Model configured' );
        }
    }

    /**
     * Handle API model validation
     *
     * @return void
     */
    private function handleApiModelValidation(): void {
        WP_CLI::line( 'Validating API Model' );
        WP_CLI::line( '====================' );
        WP_CLI::line( '' );

        // Check if API key is configured first
        if ( ! $this->validateApiKey() ) {
            WP_CLI::error( 'API key not configured. Please set it first using --set-api-key' );
            return;
        }

        $current_model = $this->apiKeyManager->getModel();

        if ( false === $current_model ) {
            WP_CLI::error( 'No model configured. Please set a model first using --set-api-model' );
            return;
        }

        WP_CLI::log( "Testing current model: {$current_model}" );

        try {
            $apiClient = new ClaudeApiClient( $this->apiKeyManager );
            $validation = $apiClient->validateModel( $current_model );

            if ( $validation['success'] ) {
                WP_CLI::success( "✅ {$validation['message']}" );
            } else {
                WP_CLI::error( "❌ {$validation['message']}" );
            }
        } catch ( \Exception $e ) {
            WP_CLI::error( '❌ Failed to validate model: ' . $e->getMessage() );
        }
    }

    /**
     * Handle API model reset (delete current model)
     *
     * @return void
     */
    private function handleApiModelReset(): void {
        WP_CLI::line( 'Reset API Model Configuration' );
        WP_CLI::line( '=============================' );
        WP_CLI::line( '' );

        $current_model = $this->apiKeyManager->getModel();

        if ( false === $current_model ) {
            WP_CLI::warning( 'No model currently configured.' );
            return;
        }

        WP_CLI::line( "Current model: {$current_model}" );
        WP_CLI::line( '' );

        if ( ! WP_CLI::confirm( 'Are you sure you want to clear the current model? You will need to set a new model before generating content.' ) ) {
            WP_CLI::line( 'Operation cancelled.' );
            return;
        }

        try {
            $result = $this->apiKeyManager->deleteModel();

            if ( $result || ! $this->apiKeyManager->hasCustomModel() ) {
                WP_CLI::success( '✅ Model configuration cleared.' );
                WP_CLI::line( 'Use --set-api-model to select a new model from the Claude API.' );
            } else {
                WP_CLI::error( '❌ Failed to clear model configuration.' );
            }
        } catch ( \Exception $e ) {
            WP_CLI::error( '❌ Failed to reset model: ' . $e->getMessage() );
        }
    }

    /**
     * Validate command arguments and provide helpful error messages
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments (flags)
     *
     * @return void
     * @throws \Exception
     */
    private function validateArguments( array $args, array $assoc_args ): void {
        // Check for positional arguments that look like they should be named arguments
        $this->checkForMissingDashes( $args );

        // Check for unrecognized named arguments
        $this->checkForUnrecognizedArguments( $assoc_args );

        // Check for mutually exclusive arguments
        $this->checkForMutuallyExclusiveArguments( $assoc_args );

        // Check for incomplete argument combinations
        $this->checkForIncompleteArguments( $assoc_args );
    }

    /**
     * Check for positional arguments that look like they should be named arguments
     *
     * @param  array  $args  Positional arguments
     *
     * @return void
     * @throws \Exception
     */
    private function checkForMissingDashes( array $args ): void {
        if ( empty( $args ) ) {
            return;
        }

        $problematic_args = [];
        $suggestions = [];

        foreach ( $args as $arg ) {
            // Check for key=value patterns without --
            if ( preg_match( '/^([a-zA-Z-]+)=(.*)$/', $arg, $matches ) ) {
                $key = $matches[1];
                $value = $matches[2];

                // Check if this looks like a valid argument name
                if ( $this->isValidArgumentName( $key ) ) {
                    $problematic_args[] = $arg;
                    $suggestions[] = "--{$key}=\"$value\"";
                }
            }
            // Check for standalone argument names that should have --
            elseif ( $this->isValidArgumentName( $arg ) ) {
                $problematic_args[] = $arg;
                $suggestions[] = "--{$arg}";
            }
            // Check for common typos
            elseif ( $this->isLikelyTypo( $arg ) ) {
                $problematic_args[] = $arg;
                $suggestion = $this->getSuggestionForTypo( $arg );
                if ( $suggestion ) {
                    $suggestions[] = $suggestion;
                }
            }
        }

        if ( ! empty( $problematic_args ) ) {
            $error_msg = "Invalid positional arguments detected:\n";

            foreach ( $problematic_args as $i => $arg ) {
                $error_msg .= "  \"$arg\"\n";
                if ( isset( $suggestions[$i] ) ) {
                    $error_msg .= "    Did you mean: {$suggestions[$i]}\n";
                }
            }

            $error_msg .= "\nRemember: All options must start with '--' (two dashes).\n";
            $error_msg .= "Examples:\n";
            $error_msg .= "  wp 84em local-pages --state=\"California\"\n";
            $error_msg .= "  wp 84em local-pages --state=\"California\" --city=\"Los Angeles\"\n";
            $error_msg .= "  wp 84em local-pages --generate-all --states-only\n";

            throw new \Exception( $error_msg );
        }
    }

    /**
     * Check if a string looks like a valid argument name
     *
     * @param  string  $name
     *
     * @return bool
     */
    private function isValidArgumentName( string $name ): bool {
        $valid_args = [
            'state', 'city', 'test', 'suite', 'generate-all', 'update-all',
            'states-only', 'complete', 'set-api-key', 'validate-api-key',
            'set-api-model', 'get-api-model', 'validate-api-model', 'reset-api-model',
            'generate-sitemap', 'generate-index', 'regenerate-schema',
            'update-location-links', 'update-page-templates', 'migrate-urls', 'delete', 'update', 'help', 'all',
            'template', 'dry-run', 'resume', 'find-testimonial-ids',
        ];

        return in_array( strtolower( $name ), $valid_args, true );
    }

    /**
     * Check if a string is likely a typo of a valid argument
     *
     * @param  string  $arg
     *
     * @return bool
     */
    private function isLikelyTypo( string $arg ): bool {
        $common_typos = [
            'State', 'STATE', 'City', 'CITY', 'Test', 'TEST',
            'generateall', 'generate_all', 'updateall', 'update_all',
            'statesonly', 'states_only', 'setapikey', 'set_api_key'
        ];

        return in_array( $arg, $common_typos, true );
    }

    /**
     * Get suggestion for a likely typo
     *
     * @param  string  $typo
     *
     * @return string|null
     */
    private function getSuggestionForTypo( string $typo ): ?string {
        $typo_map = [
            'State' => '--state',
            'STATE' => '--state',
            'City' => '--city',
            'CITY' => '--city',
            'Test' => '--test',
            'TEST' => '--test',
            'generateall' => '--generate-all',
            'generate_all' => '--generate-all',
            'updateall' => '--update-all',
            'update_all' => '--update-all',
            'statesonly' => '--states-only',
            'states_only' => '--states-only',
            'setapikey' => '--set-api-key',
            'set_api_key' => '--set-api-key'
        ];

        return $typo_map[$typo] ?? null;
    }

    /**
     * Check for unrecognized named arguments
     *
     * @param  array  $assoc_args
     *
     * @return void
     * @throws \Exception
     */
    private function checkForUnrecognizedArguments( array $assoc_args ): void {
        $valid_args = [
            'state', 'city', 'test', 'suite', 'generate-all', 'update-all',
            'states-only', 'complete', 'set-api-key', 'validate-api-key',
            'set-api-model', 'get-api-model', 'validate-api-model', 'reset-api-model',
            'generate-sitemap', 'generate-index', 'regenerate-schema',
            'update-location-links', 'update-page-templates', 'migrate-urls', 'delete', 'update', 'help', 'all',
            'template', 'dry-run', 'resume', 'find-testimonial-ids',
        ];

        $unrecognized = [];
        $suggestions = [];

        foreach ( array_keys( $assoc_args ) as $arg ) {
            if ( ! in_array( $arg, $valid_args, true ) ) {
                $unrecognized[] = $arg;

                // Try to find a close match
                $suggestion = $this->findClosestArgument( $arg, $valid_args );
                if ( $suggestion ) {
                    $suggestions[$arg] = $suggestion;
                }
            }
        }

        if ( ! empty( $unrecognized ) ) {
            $error_msg = "Unrecognized arguments:\n";

            foreach ( $unrecognized as $arg ) {
                $error_msg .= "  --$arg\n";
                if ( isset( $suggestions[$arg] ) ) {
                    $error_msg .= "    Did you mean: --{$suggestions[$arg]}?\n";
                }
            }

            $error_msg .= "\nUse 'wp 84em local-pages --help' to see all available options.\n";

            throw new \Exception( $error_msg );
        }
    }

    /**
     * Find the closest valid argument using Levenshtein distance
     *
     * @param  string  $input
     * @param  array  $valid_args
     *
     * @return string|null
     */
    private function findClosestArgument( string $input, array $valid_args ): ?string {
        $min_distance = PHP_INT_MAX;
        $closest = null;

        foreach ( $valid_args as $valid_arg ) {
            $distance = levenshtein( strtolower( $input ), strtolower( $valid_arg ) );

            // Only suggest if it's reasonably close (within 3 character changes)
            if ( $distance < $min_distance && $distance <= 3 ) {
                $min_distance = $distance;
                $closest = $valid_arg;
            }
        }

        return $closest;
    }

    /**
     * Check for mutually exclusive arguments
     *
     * @param  array  $assoc_args
     *
     * @return void
     * @throws \Exception
     */
    private function checkForMutuallyExclusiveArguments( array $assoc_args ): void {
        $exclusive_groups = [
            // Generation commands are mutually exclusive
            [
                'generate-all', 'update-all', 'state', 'city', 'update',
                'generate-sitemap', 'generate-index', 'regenerate-schema',
                'update-location-links', 'migrate-urls', 'delete'
            ],
            // API key commands are mutually exclusive with everything
            ['set-api-key', 'validate-api-key'],
            // API model commands are mutually exclusive with everything
            ['set-api-model', 'get-api-model', 'validate-api-model', 'reset-api-model'],
            // Test command is exclusive with generation
            ['test']
        ];

        $conflicts = [];

        foreach ( $exclusive_groups as $group ) {
            $found_in_group = [];

            foreach ( $group as $arg ) {
                if ( isset( $assoc_args[$arg] ) ) {
                    $found_in_group[] = $arg;
                }
            }

            // Check if this group conflicts with other groups
            if ( count( $found_in_group ) > 0 ) {
                // Check against other groups
                foreach ( $exclusive_groups as $other_group ) {
                    if ( $group === $other_group ) {
                        // Within same group, only allow one unless it's the main generation group
                        if ( $group[0] !== 'generate-all' && count( $found_in_group ) > 1 ) {
                            $conflicts[] = $found_in_group;
                        }
                    } else {
                        $found_in_other = [];
                        foreach ( $other_group as $arg ) {
                            if ( isset( $assoc_args[$arg] ) ) {
                                $found_in_other[] = $arg;
                            }
                        }

                        if ( ! empty( $found_in_other ) ) {
                            $conflicts[] = array_merge( $found_in_group, $found_in_other );
                        }
                    }
                }
            }
        }

        if ( ! empty( $conflicts ) ) {
            $unique_conflicts = array_unique( $conflicts, SORT_REGULAR );
            $error_msg = "Conflicting arguments detected:\n";

            foreach ( $unique_conflicts as $conflict_group ) {
                $error_msg .= "  Cannot use together: --" . implode( ', --', $conflict_group ) . "\n";
            }

            $error_msg .= "\nPlease use only one command at a time.\n";

            throw new \Exception( $error_msg );
        }
    }

    /**
     * Check for incomplete argument combinations
     *
     * @param  array  $assoc_args
     *
     * @return void
     * @throws \Exception
     */
    private function checkForIncompleteArguments( array $assoc_args ): void {
        // Check for test command without required parameters
        if ( isset( $assoc_args['test'] ) && ! isset( $assoc_args['all'] ) && ! isset( $assoc_args['suite'] ) ) {
            throw new \Exception(
                "Test command requires either --all or --suite=<name>\n" .
                "Examples:\n" .
                "  wp 84em local-pages --test --all\n" .
                "  wp 84em local-pages --test --suite=encryption\n"
            );
        }

        // Check for city without state
        if ( isset( $assoc_args['city'] ) && ! isset( $assoc_args['state'] ) ) {
            throw new \Exception(
                "City argument requires a state to be specified\n" .
                "Examples:\n" .
                "  wp 84em local-pages --state=\"California\" --city=\"Los Angeles\"\n" .
                "  wp 84em local-pages --state=\"California\" --city=all\n"
            );
        }

        // Check for delete without target
        if ( isset( $assoc_args['delete'] ) && ! isset( $assoc_args['state'] ) ) {
            throw new \Exception(
                "Delete command requires at least a state to be specified\n" .
                "Examples:\n" .
                "  wp 84em local-pages --delete --state=\"California\"\n" .
                "  wp 84em local-pages --delete --state=\"California\" --city=\"Los Angeles\"\n"
            );
        }

        // Check for suite without test
        if ( isset( $assoc_args['suite'] ) && ! isset( $assoc_args['test'] ) ) {
            throw new \Exception(
                "Suite argument can only be used with --test\n" .
                "Example:\n" .
                "  wp 84em local-pages --test --suite=encryption\n"
            );
        }

        // Check for states-only with inappropriate commands
        if ( isset( $assoc_args['states-only'] ) ) {
            $valid_with_states_only = ['generate-all', 'update-all', 'regenerate-schema', 'update-location-links'];
            $has_valid_command = false;

            foreach ( $valid_with_states_only as $valid_cmd ) {
                if ( isset( $assoc_args[$valid_cmd] ) ) {
                    $has_valid_command = true;
                    break;
                }
            }

            if ( ! $has_valid_command ) {
                throw new \Exception(
                    "--states-only can only be used with --generate-all, --update-all, --regenerate-schema, or --update-location-links\n" .
                    "Examples:\n" .
                    "  wp 84em local-pages --generate-all --states-only\n" .
                    "  wp 84em local-pages --update-all --states-only\n" .
                    "  wp 84em local-pages --update-location-links --states-only\n"
                );
            }
        }

        // Check for complete without city=all
        if ( isset( $assoc_args['complete'] ) ) {
            if ( ! isset( $assoc_args['city'] ) || $assoc_args['city'] !== 'all' ) {
                throw new \Exception(
                    "--complete can only be used with --city=all\n" .
                    "Example:\n" .
                    "  wp 84em local-pages --state=\"California\" --city=all --complete\n"
                );
            }
        }
    }

    /**
     * Show help information
     *
     * @return void
     */
    private function showHelp(): void {
        WP_CLI::line( '' );
        WP_CLI::line( '84EM Local Pages Generator' );
        WP_CLI::line( '==========================' );
        WP_CLI::line( '' );
        WP_CLI::line( 'USAGE:' );
        WP_CLI::line( '  wp 84em local-pages [command] [options]' );
        WP_CLI::line( '' );
        WP_CLI::line( 'API KEY MANAGEMENT:' );
        WP_CLI::line( '  --set-api-key              Set/update Claude API key (interactive prompt)' );
        WP_CLI::line( '  --validate-api-key         Validate stored Claude API key' );
        WP_CLI::line( '' );
        WP_CLI::line( 'API MODEL CONFIGURATION:' );
        WP_CLI::line( '  --set-api-model            Set/update Claude API model (fetches list from API)' );
        WP_CLI::line( '  --get-api-model            Display current API model configuration' );
        WP_CLI::line( '  --validate-api-model       Test current model with Claude API' );
        WP_CLI::line( '  --reset-api-model          Clear current model configuration' );
        WP_CLI::line( '' );
        WP_CLI::line( 'TESTING:' );
        WP_CLI::line( '  --test --all               Run all test suites' );
        WP_CLI::line( '  --test --suite=<name>      Run specific test suite' );
        WP_CLI::line( '                             Available: encryption, data-structures, content-processing,' );
        WP_CLI::line( '                             cli-args, ld-json, api-client, content-generators,' );
        WP_CLI::line( '                             error-handling, security, model-management' );
        WP_CLI::line( '' );
        WP_CLI::line( 'CONTENT GENERATION:' );
        WP_CLI::line( '  --generate-all             Generate/update all 550 pages (50 states + 500 cities)' );
        WP_CLI::line( '  --generate-all --states-only  Generate/update 50 state pages only' );
        WP_CLI::line( '  --update-all               Update all existing pages' );
        WP_CLI::line( '  --state="State Name"       Generate/update specific state page' );
        WP_CLI::line( '  --state="State" --city="City"  Generate/update specific city page' );
        WP_CLI::line( '  --state="State" --city=all     Generate/update all cities for a state' );
        WP_CLI::line( '  --state="State" --city=all --complete  Generate all cities AND update state page' );
        WP_CLI::line( '' );
        WP_CLI::line( 'MAINTENANCE:' );
        WP_CLI::line( '  --delete --state="State"   Delete state and all its cities' );
        WP_CLI::line( '  --delete --state="State" --city="City"  Delete specific city' );
        WP_CLI::line( '  --generate-sitemap         Generate XML sitemap for all local pages' );
        WP_CLI::line( '  --generate-index           Generate index page with all locations' );
        WP_CLI::line( '  --update-location-links    Update location links in all existing pages' );
        WP_CLI::line( '  --update-location-links --states-only  Update location links in state pages only' );
        WP_CLI::line( '  --migrate-urls             Migrate all pages from old to new URL structure' );
        WP_CLI::line( '  --regenerate-schema        Regenerate schema markup for all pages' );
        WP_CLI::line( '' );
        WP_CLI::line( 'UTILITIES:' );
        WP_CLI::line( '  --find-testimonial-ids     Find testimonial pattern block IDs for configuration' );
        WP_CLI::line( '' );
        WP_CLI::line( 'EXAMPLES:' );
        WP_CLI::line( '  wp 84em local-pages --set-api-key' );
        WP_CLI::line( '  wp 84em local-pages --set-api-model' );
        WP_CLI::line( '  wp 84em local-pages --get-api-model' );
        WP_CLI::line( '  wp 84em local-pages --test --all' );
        WP_CLI::line( '  wp 84em local-pages --generate-all --states-only' );
        WP_CLI::line( '  wp 84em local-pages --state="California"' );
        WP_CLI::line( '  wp 84em local-pages --state="California" --city="Los Angeles"' );
        WP_CLI::line( '  wp 84em local-pages --state="California" --city=all --complete' );
        WP_CLI::line( '' );
    }
}
