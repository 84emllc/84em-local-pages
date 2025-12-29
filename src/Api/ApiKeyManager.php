<?php
/**
 * API Key Manager
 *
 * @package EightyFourEM\LocalPages\Api
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Api;

/**
 * Manages API keys for external services
 */
class ApiKeyManager {
    /**
     * Option name for storing the encrypted API key
     */
    private const OPTION_NAME = '84em_local_pages_claude_api_key_encrypted';

    /**
     * Option name for storing the API model
     */
    private const MODEL_OPTION_NAME = '84em_local_pages_claude_api_model';

    /**
     * Encryption service
     *
     * @var Encryption
     */
    private Encryption $encryption;

    /**
     * Constructor
     *
     * @param  Encryption  $encryption  Encryption service
     */
    public function __construct( Encryption $encryption ) {
        $this->encryption = $encryption;
    }

    /**
     * Get option name with test prefix if in test mode
     *
     * This ensures complete isolation between test and production data.
     * When RUNNING_TESTS constant is defined, all option names are prefixed
     * with 'test_' so tests never touch production options.
     *
     * @param  string  $base_name  Base option name
     *
     * @return string Option name (with test_ prefix if testing)
     */
    private function getOptionName( string $base_name ): string {
        if ( defined( 'RUNNING_TESTS' ) && RUNNING_TESTS ) {
            return 'test_' . $base_name;
        }
        return $base_name;
    }

    /**
     * Get the API key
     *
     * @return string|false Decrypted API key or false if not set
     */
    public function getKey(): string|false {
        $encrypted = get_option( $this->getOptionName( self::OPTION_NAME ) );

        if ( empty( $encrypted ) ) {
            return false;
        }

        return $this->encryption->decrypt( $encrypted );
    }

    /**
     * Set the API key
     *
     * @param  string  $key  API key to store
     *
     * @return bool True on success, false on failure
     */
    public function setKey( string $key ): bool {
        if ( empty( $key ) ) {
            return false;
        }

        $encrypted = $this->encryption->encrypt( $key );

        if ( false === $encrypted ) {
            return false;
        }

        $option_name = $this->getOptionName( self::OPTION_NAME );
        update_option( $option_name, $encrypted );

        // Store a dummy IV for legacy compatibility (encryption includes IV in the data)
        update_option( $this->getOptionName( '84em_local_pages_claude_api_key_iv' ), base64_encode( random_bytes( 16 ) ) );

        // Verify the option now contains the expected value
        // (update_option returns false both on failure AND when value is unchanged)
        return get_option( $option_name ) === $encrypted;
    }

    /**
     * Delete the API key
     *
     * @return bool True on success, false on failure
     */
    public function deleteKey(): bool {
        $result = delete_option( $this->getOptionName( self::OPTION_NAME ) );
        delete_option( $this->getOptionName( '84em_local_pages_claude_api_key_iv' ) );
        return $result;
    }

    /**
     * Check if an API key is stored
     *
     * @return bool
     */
    public function hasKey(): bool {
        return false !== $this->getKey();
    }

    /**
     * Validate the stored API key format
     *
     * @return bool
     */
    public function validateStoredKey(): bool {
        $key = $this->getKey();

        if ( false === $key ) {
            return false;
        }

        return $this->validateKeyFormat( $key );
    }

    /**
     * Validate API key format
     *
     * @param  string  $key  API key to validate
     *
     * @return bool
     */
    public function validateKeyFormat( string $key ): bool {
        // Claude API keys start with 'sk-ant-api03-' followed by 93 characters
        return (bool) preg_match( '/^sk-ant-api03-[\w\-]{93}$/', $key );
    }

    /**
     * Get the API model
     *
     * @return string|false Model name or false if not set
     */
    public function getModel(): string|false {
        $model = get_option( $this->getOptionName( self::MODEL_OPTION_NAME ) );

        if ( empty( $model ) ) {
            return false;
        }

        return $model;
    }

    /**
     * Set the API model
     *
     * @param  string  $model  Model name to store
     *
     * @return bool True on success, false on failure
     */
    public function setModel( string $model ): bool {
        if ( empty( $model ) ) {
            return false;
        }

        $option_name = $this->getOptionName( self::MODEL_OPTION_NAME );
        update_option( $option_name, $model );

        // Verify the option now contains the expected value
        // (update_option returns false both on failure AND when value is unchanged)
        return get_option( $option_name ) === $model;
    }

    /**
     * Delete the API model (revert to default)
     *
     * @return bool True on success, false on failure
     */
    public function deleteModel(): bool {
        return delete_option( $this->getOptionName( self::MODEL_OPTION_NAME ) );
    }

    /**
     * Check if a custom API model is stored
     *
     * @return bool
     */
    public function hasCustomModel(): bool {
        $model = get_option( $this->getOptionName( self::MODEL_OPTION_NAME ) );
        return ! empty( $model );
    }
}
