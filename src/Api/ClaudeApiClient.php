<?php
/**
 * Claude API Client
 *
 * @package EightyFourEM\LocalPages\Api
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Api;

use EightyFourEM\LocalPages\Contracts\ApiClientInterface;

/**
 * Client for interacting with Claude API
 */
class ClaudeApiClient implements ApiClientInterface {
    /**
     * API endpoint
     */
    private const API_ENDPOINT = 'https://api.anthropic.com/v1/messages';

    /**
     * API version
     */
    private const API_VERSION = '2023-06-01';

    /**
     * Models API endpoint
     */
    private const MODELS_ENDPOINT = 'https://api.anthropic.com/v1/models';

    /**
     * Max tokens for response
     */
    private const MAX_TOKENS = 4000;

    /**
     * Request timeout in seconds
     */
    private const TIMEOUT = 600; // 10 minutes

    /**
     * Maximum retry attempts
     */
    private const MAX_RETRIES = 5;

    /**
     * Initial retry delay in seconds
     */
    private const INITIAL_RETRY_DELAY = 1;

    /**
     * Maximum retry delay in seconds
     */
    private const MAX_RETRY_DELAY = 60;

    /**
     * API key manager
     *
     * @var ApiKeyManager
     */
    private ApiKeyManager $keyManager;

    /**
     * Constructor
     *
     * @param  ApiKeyManager  $keyManager  API key manager
     */
    public function __construct( ApiKeyManager $keyManager ) {
        $this->keyManager = $keyManager;
    }

    /**
     * Send a request to Claude API
     *
     * @param  string  $prompt  The prompt to send
     *
     * @return string|false Response content or false on failure
     */
    public function sendRequest( string $prompt ): string|false {
        if ( ! $this->isConfigured() ) {
            $this->logError( 'API client is not properly configured' );
            return false;
        }

        $api_key = $this->keyManager->getKey();
        if ( false === $api_key ) {
            $this->logError( 'Failed to retrieve API key' );
            return false;
        }

        // Get the model from ApiKeyManager
        $model = $this->keyManager->getModel();
        if ( false === $model ) {
            $this->logError( 'No model configured. Please set a model first using --set-api-model' );
            return false;
        }

        $body = [
            'model'      => $model,
            'max_tokens' => self::MAX_TOKENS,
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        $args = [
            'method'  => 'POST',
            'timeout' => self::TIMEOUT,
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $api_key,
                'anthropic-version' => self::API_VERSION,
            ],
            'body'    => wp_json_encode( $body ),
        ];

        // Retry logic with exponential backoff
        $attempt = 0;
        $retry_delay = self::INITIAL_RETRY_DELAY;
        $last_error = '';

        while ( $attempt < self::MAX_RETRIES ) {
            $attempt++;

            if ( $attempt > 1 ) {
                $this->logInfo( "Retrying API request (attempt {$attempt}/" . self::MAX_RETRIES . ") after {$retry_delay} seconds delay" );
                sleep( $retry_delay );
            }

            $response = wp_remote_post( self::API_ENDPOINT, $args );

            // Handle WordPress errors (network issues, timeouts, etc.)
            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                $last_error = "Network error: {$error_message}";

                // Check if error is retryable
                if ( $this->isRetryableError( $error_message ) ) {
                    $this->logWarning( "Retryable error on attempt {$attempt}: {$error_message}" );
                    $retry_delay = min( $retry_delay * 2, self::MAX_RETRY_DELAY );
                    continue;
                }

                // Non-retryable error
                $this->logError( "Non-retryable error: {$error_message}" );
                return false;
            }

            $response_code = wp_remote_retrieve_response_code( $response );
            $response_body = wp_remote_retrieve_body( $response );

            // Handle successful response
            if ( 200 === $response_code ) {
                $data = json_decode( $response_body, true );

                if ( ! isset( $data['content'][0]['text'] ) ) {
                    $this->logError( 'Unexpected API response format: ' . wp_json_encode( $data ) );
                    return false;
                }

                if ( $attempt > 1 ) {
                    $this->logInfo( "API request succeeded on attempt {$attempt}" );
                }

                return $data['content'][0]['text'];
            }

            // Handle HTTP errors
            $last_error = "HTTP {$response_code}: {$response_body}";

            // Check if HTTP status code is retryable
            if ( $this->isRetryableHttpStatus( $response_code ) ) {
                $this->logWarning( "Retryable HTTP error on attempt {$attempt}: {$last_error}" );
                $retry_delay = min( $retry_delay * 2, self::MAX_RETRY_DELAY );
                continue;
            }

            // Handle rate limiting (429) with Retry-After header
            if ( 429 === $response_code ) {
                $headers = wp_remote_retrieve_headers( $response );
                $retry_after = isset( $headers['retry-after'] ) ? (int) $headers['retry-after'] : $retry_delay * 2;
                $retry_delay = min( $retry_after, self::MAX_RETRY_DELAY );
                $this->logWarning( "Rate limited on attempt {$attempt}. Retry after {$retry_delay} seconds" );
                continue;
            }

            // Non-retryable HTTP error
            $this->logError( "Non-retryable HTTP error: {$last_error}" );
            $this->logApiErrorDetails( $response_code, $response_body );
            return false;
        }

        // All retries exhausted
        $this->logError( "API request failed after " . self::MAX_RETRIES . " attempts. Last error: {$last_error}" );
        return false;
    }

    /**
     * Check if the API client is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool {
        // Must have both API key and a model selected
        return $this->keyManager->hasKey() && $this->keyManager->hasCustomModel();
    }

    /**
     * Validate API credentials
     *
     * @param bool $skip_model_check Skip checking for model (useful when setting API key before model)
     * @return bool
     */
    public function validateCredentials( bool $skip_model_check = false ): bool {
        // At minimum, we need an API key
        if ( ! $this->keyManager->hasKey() ) {
            return false;
        }

        // If not skipping model check, verify we have a model configured
        if ( ! $skip_model_check && ! $this->keyManager->hasCustomModel() ) {
            return false;
        }

        // If we're skipping model check, we need to use getAvailableModels() instead of sendRequest()
        // because sendRequest() requires a model
        if ( $skip_model_check ) {
            // Try to fetch available models - this validates the API key without needing a model
            $result = $this->getAvailableModels();
            return $result['success'];
        }

        // Send a simple test request (requires both API key and model)
        $response = $this->sendRequest( 'Reply with just the word "OK" if you receive this message.' );

        return false !== $response && str_contains( strtoupper( $response ), 'OK' );
    }

    /**
     * Get available models from Claude API
     *
     * @return array{success: bool, models: array, message: string} Models list or error
     */
    public function getAvailableModels(): array {
        // Only need API key to fetch models, not a configured model
        if ( ! $this->keyManager->hasKey() ) {
            return [
                'success' => false,
                'models'  => [],
                'message' => 'API client is not properly configured. Please set API key first.',
            ];
        }

        $api_key = $this->keyManager->getKey();
        if ( false === $api_key ) {
            return [
                'success' => false,
                'models'  => [],
                'message' => 'Failed to retrieve API key.',
            ];
        }

        $args = [
            'method'  => 'GET',
            'timeout' => 30,
            'headers' => [
                'x-api-key'         => $api_key,
                'anthropic-version' => self::API_VERSION,
            ],
        ];

        $response = wp_remote_get( self::MODELS_ENDPOINT, $args );

        // Handle WordPress errors
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return [
                'success' => false,
                'models'  => [],
                'message' => "Network error: {$error_message}",
            ];
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        // Success
        if ( 200 === $response_code ) {
            $data = json_decode( $response_body, true );

            if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
                return [
                    'success' => false,
                    'models'  => [],
                    'message' => 'Unexpected API response format.',
                ];
            }

            // Extract and format model information
            $models = [];
            foreach ( $data['data'] as $model_data ) {
                if ( isset( $model_data['id'] ) ) {
                    $models[] = [
                        'id'           => $model_data['id'],
                        'display_name' => $model_data['display_name'] ?? $model_data['id'],
                        'created_at'   => $model_data['created_at'] ?? null,
                        'type'         => $model_data['type'] ?? 'model',
                    ];
                }
            }

            return [
                'success' => true,
                'models'  => $models,
                'message' => 'Successfully retrieved models.',
            ];
        }

        // Handle errors
        $data = json_decode( $response_body, true );
        $error_message = 'Unknown error';

        if ( isset( $data['error'] ) ) {
            $error = $data['error'];
            if ( is_array( $error ) ) {
                $error_message = $error['message'] ?? 'No error message';
            } else {
                $error_message = $error;
            }
        }

        return [
            'success' => false,
            'models'  => [],
            'message' => "HTTP {$response_code}: {$error_message}",
        ];
    }

    /**
     * Validate a specific API model
     *
     * @param  string  $model  Model name to validate
     *
     * @return array{success: bool, message: string} Validation result with success status and message
     */
    public function validateModel( string $model ): array {
        // Only require API key for model validation (model is being validated, so it won't exist yet)
        if ( ! $this->keyManager->hasKey() ) {
            return [
                'success' => false,
                'message' => 'API key is not configured. Please set API key first.',
            ];
        }

        if ( empty( $model ) ) {
            return [
                'success' => false,
                'message' => 'Model name cannot be empty.',
            ];
        }

        $api_key = $this->keyManager->getKey();
        if ( false === $api_key ) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve API key.',
            ];
        }

        // Prepare test request with the specified model
        $body = [
            'model'      => $model,
            'max_tokens' => 50, // Minimal tokens for test
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => 'Reply with just the word "OK" if you receive this message.',
                ],
            ],
        ];

        $args = [
            'method'  => 'POST',
            'timeout' => 30, // Shorter timeout for validation
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $api_key,
                'anthropic-version' => self::API_VERSION,
            ],
            'body'    => wp_json_encode( $body ),
        ];

        $response = wp_remote_post( self::API_ENDPOINT, $args );

        // Handle WordPress errors
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return [
                'success' => false,
                'message' => "Network error: {$error_message}",
            ];
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        // Success
        if ( 200 === $response_code ) {
            $data = json_decode( $response_body, true );

            if ( ! isset( $data['content'][0]['text'] ) ) {
                return [
                    'success' => false,
                    'message' => 'Unexpected API response format.',
                ];
            }

            return [
                'success' => true,
                'message' => "Model '{$model}' is valid and working!",
            ];
        }

        // Parse error details
        $data = json_decode( $response_body, true );
        $error_message = 'Unknown error';

        if ( isset( $data['error'] ) ) {
            $error = $data['error'];

            if ( is_array( $error ) ) {
                $error_type = $error['type'] ?? 'unknown';
                $error_msg = $error['message'] ?? 'No error message';

                // Provide helpful messages based on error type
                if ( 'invalid_request_error' === $error_type && str_contains( $error_msg, 'model' ) ) {
                    $error_message = "Invalid model '{$model}'. The model may not exist or your account may not have access to it.";
                } else {
                    $error_message = "{$error_type}: {$error_msg}";
                }
            } else {
                $error_message = $error;
            }
        }

        // Provide specific guidance based on status code
        switch ( $response_code ) {
            case 400:
                return [
                    'success' => false,
                    'message' => "Bad Request: {$error_message}",
                ];
            case 401:
                return [
                    'success' => false,
                    'message' => 'Unauthorized: Check your API key.',
                ];
            case 403:
                return [
                    'success' => false,
                    'message' => "Forbidden: Your account may not have access to model '{$model}'.",
                ];
            case 404:
                return [
                    'success' => false,
                    'message' => "Model '{$model}' not found.",
                ];
            default:
                return [
                    'success' => false,
                    'message' => "HTTP {$response_code}: {$error_message}",
                ];
        }
    }

    /**
     * Check if an error is retryable
     *
     * @param  string  $error_message  Error message from WP_Error
     *
     * @return bool
     */
    private function isRetryableError( string $error_message ): bool {
        $retryable_patterns = [
            'timeout',
            'timed out',
            'connection reset',
            'connection refused',
            'could not resolve host',
            'name or service not known',
            'temporary failure',
            'network is unreachable',
            'empty reply from server',
        ];

        $error_lower = strtolower( $error_message );
        foreach ( $retryable_patterns as $pattern ) {
            if ( str_contains( $error_lower, $pattern ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an HTTP status code is retryable
     *
     * @param  int  $status_code  HTTP status code
     *
     * @return bool
     */
    private function isRetryableHttpStatus( int $status_code ): bool {
        // Retry on server errors and rate limiting
        $retryable_codes = [
            429, // Too Many Requests
            500, // Internal Server Error
            502, // Bad Gateway
            503, // Service Unavailable
            504, // Gateway Timeout
            507, // Insufficient Storage
            509, // Bandwidth Limit Exceeded
            529, // Overloaded
        ];

        return in_array( $status_code, $retryable_codes, true );
    }

    /**
     * Log API error details
     *
     * @param  int     $status_code    HTTP status code
     * @param  string  $response_body  Response body
     */
    private function logApiErrorDetails( int $status_code, string $response_body ): void {
        // Try to parse error details from response
        $data = json_decode( $response_body, true );

        if ( isset( $data['error'] ) ) {
            $error = $data['error'];

            if ( is_array( $error ) ) {
                $error_type = $error['type'] ?? 'unknown';
                $error_message = $error['message'] ?? 'No error message';
                $this->logError( "API Error Type: {$error_type}, Message: {$error_message}" );
            } else {
                $this->logError( "API Error: {$error}" );
            }
        }

        // Log specific guidance based on status code
        switch ( $status_code ) {
            case 400:
                $this->logError( 'Bad Request: Check the request format and parameters' );
                break;
            case 401:
                $this->logError( 'Unauthorized: Check your API key' );
                break;
            case 403:
                $this->logError( 'Forbidden: API key may lack necessary permissions' );
                break;
            case 404:
                $this->logError( 'Not Found: Check the API endpoint URL' );
                break;
            case 413:
                $this->logError( 'Payload Too Large: Request body exceeds size limit' );
                break;
            case 422:
                $this->logError( 'Unprocessable Entity: Request validation failed' );
                break;
        }
    }

    /**
     * Log an error message
     *
     * @param  string  $message  Error message
     */
    private function logError( string $message ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[84EM Local Pages] Claude API Error: ' . $message );
        }

        if ( defined( 'WP_CLI' ) && WP_CLI && ! $this->isTestContext() ) {
            \WP_CLI::warning( $message );
        }
    }

    /**
     * Log a warning message
     *
     * @param  string  $message  Warning message
     */
    private function logWarning( string $message ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[84EM Local Pages] Claude API Warning: ' . $message );
        }

        if ( defined( 'WP_CLI' ) && WP_CLI && ! $this->isTestContext() ) {
            \WP_CLI::debug( $message );
        }
    }

    /**
     * Log an info message
     *
     * @param  string  $message  Info message
     */
    private function logInfo( string $message ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( '[84EM Local Pages] Claude API Info: ' . $message );
        }

        if ( defined( 'WP_CLI' ) && WP_CLI && ! $this->isTestContext() ) {
            \WP_CLI::debug( $message );
        }
    }

    /**
     * Check if we're in a test context
     *
     * @return bool
     */
    private function isTestContext(): bool {
        // Check if we're running tests
        if ( defined( 'EIGHTYFOUREM_TESTING' ) && EIGHTYFOUREM_TESTING ) {
            return true;
        }

        // Check if the current WP-CLI command is a test command
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $args = $_SERVER['argv'] ?? [];
            foreach ( $args as $arg ) {
                if ( strpos( $arg, '--test' ) !== false ) {
                    return true;
                }
            }
        }

        return false;
    }

}
