<?php
/**
 * Slack Notifier
 *
 * @package EightyFourEM\LocalPages\Notifications
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Notifications;

/**
 * Handles Slack message delivery for notifications
 */
class SlackNotifier {
	/**
	 * HTTP request timeout in seconds
	 */
	private const TIMEOUT = 10;

	/**
	 * Slack webhook manager
	 *
	 * @var SlackWebhookManager
	 */
	private SlackWebhookManager $webhookManager;

	/**
	 * Constructor
	 *
	 * @param  SlackWebhookManager  $webhookManager  Webhook manager instance
	 */
	public function __construct( SlackWebhookManager $webhookManager ) {
		$this->webhookManager = $webhookManager;
	}

	/**
	 * Check if Slack notifications are enabled
	 *
	 * @return bool
	 */
	public function isEnabled(): bool {
		return $this->webhookManager->hasWebhookUrl();
	}

	/**
	 * Send notification when generate-all command completes
	 *
	 * @param  array  $stats  Generation statistics
	 *
	 * @return bool True on success, false on failure
	 */
	public function notifyGenerateAllComplete( array $stats ): bool {
		if ( ! $this->isEnabled() ) {
			return false;
		}

		$include_cities  = $stats['include_cities'] ?? false;
		$states_created  = $stats['states_created'] ?? 0;
		$states_updated  = $stats['states_updated'] ?? 0;
		$cities_created  = $stats['cities_created'] ?? 0;
		$cities_updated  = $stats['cities_updated'] ?? 0;
		$total_pages     = $stats['total_pages'] ?? 0;
		$duration        = $stats['duration'] ?? 'Unknown';

		$operation = $include_cities ? 'States + Cities' : 'States Only';

		$fields = [
			[
				'type' => 'mrkdwn',
				'text' => "*Operation:*\n" . $operation,
			],
			[
				'type' => 'mrkdwn',
				'text' => "*Duration:*\n" . $duration,
			],
			[
				'type' => 'mrkdwn',
				'text' => "*States Created:*\n" . $states_created,
			],
			[
				'type' => 'mrkdwn',
				'text' => "*States Updated:*\n" . $states_updated,
			],
		];

		if ( $include_cities ) {
			$fields[] = [
				'type' => 'mrkdwn',
				'text' => "*Cities Created:*\n" . $cities_created,
			];
			$fields[] = [
				'type' => 'mrkdwn',
				'text' => "*Cities Updated:*\n" . $cities_updated,
			];
		}

		$payload = [
			'blocks' => [
				[
					'type' => 'header',
					'text' => [
						'type'  => 'plain_text',
						'text'  => '84EM Local Pages Generation Complete',
						'emoji' => true,
					],
				],
				[
					'type'   => 'section',
					'fields' => $fields,
				],
				[
					'type' => 'section',
					'text' => [
						'type' => 'mrkdwn',
						'text' => '*Total Pages Processed:* ' . $total_pages,
					],
				],
				[
					'type'     => 'context',
					'elements' => [
						[
							'type' => 'mrkdwn',
							'text' => 'Sent from ' . get_site_url() . ' via WP-CLI',
						],
					],
				],
			],
		];

		return $this->send( $payload );
	}

	/**
	 * Send notification when update-all command completes
	 *
	 * @param  array  $stats  Update statistics
	 *
	 * @return bool True on success, false on failure
	 */
	public function notifyUpdateAllComplete( array $stats ): bool {
		if ( ! $this->isEnabled() ) {
			return false;
		}

		$states_only = $stats['states_only'] ?? false;
		$updated     = $stats['updated'] ?? 0;
		$failed      = $stats['failed'] ?? 0;
		$total       = $stats['total'] ?? 0;
		$duration    = $stats['duration'] ?? 'Unknown';

		$operation = $states_only ? 'States Only' : 'States + Cities';

		$payload = [
			'blocks' => [
				[
					'type' => 'header',
					'text' => [
						'type'  => 'plain_text',
						'text'  => '84EM Local Pages Update Complete',
						'emoji' => true,
					],
				],
				[
					'type'   => 'section',
					'fields' => [
						[
							'type' => 'mrkdwn',
							'text' => "*Operation:*\n" . $operation,
						],
						[
							'type' => 'mrkdwn',
							'text' => "*Duration:*\n" . $duration,
						],
						[
							'type' => 'mrkdwn',
							'text' => "*Updated:*\n" . $updated,
						],
						[
							'type' => 'mrkdwn',
							'text' => "*Failed:*\n" . $failed,
						],
					],
				],
				[
					'type' => 'section',
					'text' => [
						'type' => 'mrkdwn',
						'text' => '*Total Pages:* ' . $total,
					],
				],
				[
					'type'     => 'context',
					'elements' => [
						[
							'type' => 'mrkdwn',
							'text' => 'Sent from ' . get_site_url() . ' via WP-CLI',
						],
					],
				],
			],
		];

		return $this->send( $payload );
	}

	/**
	 * Send notification when a single state or city operation completes
	 *
	 * @param  array  $stats  Operation statistics
	 *
	 * @return bool True on success, false on failure
	 */
	public function notifyOperationComplete( array $stats ): bool {
		if ( ! $this->isEnabled() ) {
			return false;
		}

		$operation = $stats['operation'] ?? 'Unknown';
		$location  = $stats['location'] ?? 'Unknown';
		$created   = $stats['created'] ?? 0;
		$updated   = $stats['updated'] ?? 0;
		$duration  = $stats['duration'] ?? 'Unknown';

		$fields = [
			[
				'type' => 'mrkdwn',
				'text' => "*Operation:*\n" . $operation,
			],
			[
				'type' => 'mrkdwn',
				'text' => "*Location:*\n" . $location,
			],
			[
				'type' => 'mrkdwn',
				'text' => "*Created:*\n" . $created,
			],
			[
				'type' => 'mrkdwn',
				'text' => "*Updated:*\n" . $updated,
			],
		];

		// Add duration if provided
		if ( $duration !== 'Unknown' ) {
			$fields[] = [
				'type' => 'mrkdwn',
				'text' => "*Duration:*\n" . $duration,
			];
		}

		$payload = [
			'blocks' => [
				[
					'type' => 'header',
					'text' => [
						'type'  => 'plain_text',
						'text'  => '84EM Local Pages Operation Complete',
						'emoji' => true,
					],
				],
				[
					'type'   => 'section',
					'fields' => $fields,
				],
				[
					'type' => 'section',
					'text' => [
						'type' => 'mrkdwn',
						'text' => '*Total Pages:* ' . ( $created + $updated ),
					],
				],
				[
					'type'     => 'context',
					'elements' => [
						[
							'type' => 'mrkdwn',
							'text' => 'Sent from ' . get_site_url() . ' via WP-CLI',
						],
					],
				],
			],
		];

		return $this->send( $payload );
	}

	/**
	 * Send a test notification to verify webhook works
	 *
	 * @return bool True on success, false on failure
	 */
	public function sendTestNotification(): bool {
		$fields = [
			[
				'type' => 'mrkdwn',
				'text' => "*Operation:*\nStates + Cities",
			],
			[
				'type' => 'mrkdwn',
				'text' => "*Duration:*\n2 hr 47 min",
			],
			[
				'type' => 'mrkdwn',
				'text' => "*States Created:*\n50",
			],
			[
				'type' => 'mrkdwn',
				'text' => "*States Updated:*\n0",
			],
			[
				'type' => 'mrkdwn',
				'text' => "*Cities Created:*\n500",
			],
			[
				'type' => 'mrkdwn',
				'text' => "*Cities Updated:*\n0",
			],
		];

		$payload = [
			'blocks' => [
				[
					'type' => 'header',
					'text' => [
						'type'  => 'plain_text',
						'text'  => '84EM Local Pages - Test Notification',
						'emoji' => true,
					],
				],
				[
					'type'   => 'section',
					'fields' => $fields,
				],
				[
					'type' => 'section',
					'text' => [
						'type' => 'mrkdwn',
						'text' => '*Total Pages Processed:* 550',
					],
				],
				[
					'type'     => 'context',
					'elements' => [
						[
							'type' => 'mrkdwn',
							'text' => 'Sent from ' . get_site_url() . ' via WP-CLI',
						],
					],
				],
			],
		];

		return $this->send( $payload );
	}

	/**
	 * Send a payload to Slack
	 *
	 * @param  array  $payload  Slack Block Kit payload
	 *
	 * @return bool True on success, false on failure
	 */
	private function send( array $payload ): bool {
		$webhook_url = $this->webhookManager->getWebhookUrl();

		if ( false === $webhook_url ) {
			return false;
		}

		$response = wp_remote_post(
			$webhook_url,
			[
				'timeout' => self::TIMEOUT,
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode( $payload ),
			]
		);

		if ( is_wp_error( $response ) ) {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				\WP_CLI::warning( 'Slack notification failed: ' . $response->get_error_message() );
			}
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				\WP_CLI::warning( 'Slack notification failed with status code: ' . $response_code );
			}
			return false;
		}

		return true;
	}
}
