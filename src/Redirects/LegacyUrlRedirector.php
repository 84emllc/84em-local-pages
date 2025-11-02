<?php
/**
 * Legacy URL Redirector
 *
 * Handles 301 redirects from old URL structure to new simplified URLs
 *
 * @package EightyFourEM\LocalPages\Redirects
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Redirects;

use EightyFourEM\LocalPages\Data\StatesProvider;

/**
 * Redirects legacy URL patterns to new simplified structure
 *
 * Old format: /wordpress-development-services-usa/wordpress-development-services-alabama/
 * New format: /wordpress-development-services-usa/alabama/
 */
class LegacyUrlRedirector {

	/**
	 * States data provider
	 *
	 * @var StatesProvider
	 */
	private StatesProvider $statesProvider;

	/**
	 * Constructor
	 *
	 * @param StatesProvider $statesProvider States data provider for validation.
	 */
	public function __construct( StatesProvider $statesProvider ) {
		$this->statesProvider = $statesProvider;
	}

	/**
	 * Initialize the redirector by registering WordPress hooks
	 *
	 * @return void
	 */
	public function initialize(): void {
		add_action( 'template_redirect', [ $this, 'handleRedirect' ] );
	}

	/**
	 * Handle redirect logic on template_redirect hook
	 *
	 * @return void
	 */
	public function handleRedirect(): void {
		$request_uri = $_SERVER['REQUEST_URI'] ?? '';

		// Check for legacy state URL pattern
		if ( $this->isLegacyStateUrl( $request_uri ) ) {
			$new_url = $this->convertStateUrl( $request_uri );
			wp_redirect( $new_url, 301 );
			exit;
		}

		// Check for legacy city URL pattern
		if ( $this->isLegacyCityUrl( $request_uri ) ) {
			$new_url = $this->convertCityUrl( $request_uri );
			wp_redirect( $new_url, 301 );
			exit;
		}
	}

	/**
	 * Check if URL matches legacy state pattern
	 *
	 * Pattern: /wordpress-development-services-usa/wordpress-development-services-{state}/
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool True if matches legacy state pattern.
	 */
	private function isLegacyStateUrl( string $url ): bool {
		return (bool) preg_match(
			'#^/wordpress-development-services-usa/wordpress-development-services-([^/]+)/?$#',
			$url
		);
	}

	/**
	 * Check if URL matches legacy city pattern
	 *
	 * Pattern: /wordpress-development-services-usa/wordpress-development-services-{state}/{city}/
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool True if matches legacy city pattern.
	 */
	private function isLegacyCityUrl( string $url ): bool {
		return (bool) preg_match(
			'#^/wordpress-development-services-usa/wordpress-development-services-([^/]+)/([^/]+)/?$#',
			$url
		);
	}

	/**
	 * Convert legacy state URL to new format
	 *
	 * @param string $url Legacy state URL.
	 *
	 * @return string New format URL.
	 */
	private function convertStateUrl( string $url ): string {
		preg_match(
			'#^/wordpress-development-services-usa/wordpress-development-services-([^/]+)/?$#',
			$url,
			$matches
		);
		$state_slug = $matches[1] ?? '';
		return home_url( "/wordpress-development-services-usa/{$state_slug}/" );
	}

	/**
	 * Convert legacy city URL to new format
	 *
	 * @param string $url Legacy city URL.
	 *
	 * @return string New format URL.
	 */
	private function convertCityUrl( string $url ): string {
		preg_match(
			'#^/wordpress-development-services-usa/wordpress-development-services-([^/]+)/([^/]+)/?$#',
			$url,
			$matches
		);
		$state_slug = $matches[1] ?? '';
		$city_slug  = $matches[2] ?? '';
		return home_url( "/wordpress-development-services-usa/{$state_slug}/{$city_slug}/" );
	}
}
