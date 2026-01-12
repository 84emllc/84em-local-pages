<?php
/**
 * Slack Webhook Manager
 *
 * @package EightyFourEM\LocalPages\Notifications
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Notifications;

use EightyFourEM\LocalPages\Api\Encryption;

/**
 * Manages Slack webhook URL storage with encryption
 */
class SlackWebhookManager {
	/**
	 * Option name for storing the encrypted webhook URL
	 */
	private const OPTION_NAME = '84em_local_pages_slack_webhook_encrypted';

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
	 * Get the webhook URL
	 *
	 * @return string|false Decrypted webhook URL or false if not set
	 */
	public function getWebhookUrl(): string|false {
		$encrypted = get_option( $this->getOptionName( self::OPTION_NAME ) );

		if ( empty( $encrypted ) ) {
			return false;
		}

		return $this->encryption->decrypt( $encrypted );
	}

	/**
	 * Set the webhook URL
	 *
	 * @param  string  $url  Webhook URL to store
	 *
	 * @return bool True on success, false on failure
	 */
	public function setWebhookUrl( string $url ): bool {
		if ( empty( $url ) ) {
			return false;
		}

		$encrypted = $this->encryption->encrypt( $url );

		if ( false === $encrypted ) {
			return false;
		}

		$option_name = $this->getOptionName( self::OPTION_NAME );
		update_option( $option_name, $encrypted );

		// Verify the option now contains the expected value
		// (update_option returns false both on failure AND when value is unchanged)
		return get_option( $option_name ) === $encrypted;
	}

	/**
	 * Delete the webhook URL
	 *
	 * @return bool True on success, false on failure
	 */
	public function deleteWebhookUrl(): bool {
		return delete_option( $this->getOptionName( self::OPTION_NAME ) );
	}

	/**
	 * Check if a webhook URL is stored
	 *
	 * @return bool
	 */
	public function hasWebhookUrl(): bool {
		return false !== $this->getWebhookUrl();
	}

	/**
	 * Validate webhook URL format
	 *
	 * @param  string  $url  Webhook URL to validate
	 *
	 * @return bool
	 */
	public function validateUrlFormat( string $url ): bool {
		// Slack webhook URLs must start with https://hooks.slack.com/services/
		// followed by three path segments: T[workspace]/B[bot]/[token]
		return (bool) preg_match( '/^https:\/\/hooks\.slack\.com\/services\/[A-Z0-9]+\/[A-Z0-9]+\/[a-zA-Z0-9]+$/', $url );
	}
}
