<?php
/**
 * Metadata Generator
 *
 * Generates AI-powered SEO metadata (page title, SEO title, meta description)
 * for state and city local pages using Claude API.
 *
 * @package EightyFourEM\LocalPages\Content
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Content;

use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Data\StatesProvider;
use Exception;

/**
 * Generates AI-crafted SEO metadata for location pages
 */
class MetadataGenerator {

    /**
     * API key manager
     *
     * @var ApiKeyManager
     */
    private ApiKeyManager $apiKeyManager;

    /**
     * Claude API client
     *
     * @var ClaudeApiClient
     */
    private ClaudeApiClient $apiClient;

    /**
     * States data provider
     *
     * @var StatesProvider
     */
    private StatesProvider $statesProvider;

    /**
     * Constructor
     *
     * @param  ApiKeyManager  $apiKeyManager  API key manager
     * @param  ClaudeApiClient  $apiClient  Claude API client
     * @param  StatesProvider  $statesProvider  States data provider
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        ClaudeApiClient $apiClient,
        StatesProvider $statesProvider
    ) {
        $this->apiKeyManager  = $apiKeyManager;
        $this->apiClient      = $apiClient;
        $this->statesProvider = $statesProvider;
    }

    /**
     * Generate metadata for a state page
     *
     * @param  string  $state  State name
     *
     * @return array{page_title: string, seo_title: string, meta_description: string}
     * @throws Exception If generation fails
     */
    public function generateStateMetadata( string $state ): array {
        if ( ! $this->statesProvider->has( $state ) ) {
            throw new Exception( "Invalid state: {$state}" );
        }

        if ( ! $this->apiKeyManager->hasKey() ) {
            throw new Exception( 'API key not available' );
        }

        // Get cities for context
        $state_data = $this->statesProvider->get( $state );
        $cities     = $state_data['cities'] ?? [];
        $city_list  = implode( ', ', array_slice( $cities, 0, 5 ) );

        $prompt = $this->buildStateMetadataPrompt( $state, $city_list );
        $response = $this->apiClient->sendRequest( $prompt );

        if ( ! $response ) {
            throw new Exception( 'Failed to generate metadata from API' );
        }

        return $this->parseMetadataResponse( $response );
    }

    /**
     * Generate metadata for a city page
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return array{page_title: string, seo_title: string, meta_description: string}
     * @throws Exception If generation fails
     */
    public function generateCityMetadata( string $state, string $city ): array {
        if ( ! $this->statesProvider->has( $state ) ) {
            throw new Exception( "Invalid state: {$state}" );
        }

        $state_data = $this->statesProvider->get( $state );
        $cities     = $state_data['cities'] ?? [];

        if ( ! in_array( $city, $cities, true ) ) {
            throw new Exception( "Invalid city: {$city} not in {$state}" );
        }

        if ( ! $this->apiKeyManager->hasKey() ) {
            throw new Exception( 'API key not available' );
        }

        $prompt = $this->buildCityMetadataPrompt( $state, $city );
        $response = $this->apiClient->sendRequest( $prompt );

        if ( ! $response ) {
            throw new Exception( 'Failed to generate metadata from API' );
        }

        return $this->parseMetadataResponse( $response );
    }

    /**
     * Build prompt for state page metadata generation
     *
     * @param  string  $state  State name
     * @param  string  $city_list  Comma-separated list of major cities
     *
     * @return string
     */
    private function buildStateMetadataPrompt( string $state, string $city_list ): string {
        return "Generate SEO metadata for a WordPress development services page targeting businesses in {$state}.

CONTEXT:
- Company: 84EM (WordPress development agency)
- Location focus: {$state} (major cities: {$city_list})
- Services: WordPress development, custom plugins, consulting, agency services
- Company is 100% remote, headquartered in Cedar Rapids, Iowa

REQUIREMENTS:
Generate exactly 3 pieces of metadata in the following JSON format:

{
    \"page_title\": \"...\",
    \"seo_title\": \"...\",
    \"meta_description\": \"...\"
}

SPECIFICATIONS:

1. PAGE TITLE (visible H1 on the page):
   - Must be unique and specific to {$state}
   - 40-60 characters
   - Include \"WordPress\" and \"{$state}\"
   - Professional, direct tone
   - NO pipe character, NO \"84EM\"
   - Example format: \"WordPress Development Services in {$state}\"

2. SEO TITLE (appears in browser tab and search results):
   - Must end with \" | 84EM\"
   - Total length: 50-60 characters (including \" | 84EM\")
   - Include primary keyword \"WordPress\" and location \"{$state}\"
   - Compelling but not clickbait
   - Example format: \"Custom WordPress Development in {$state} | 84EM\"

3. META DESCRIPTION (appears in search results):
   - 150-160 characters exactly
   - Include \"WordPress\", \"{$state}\", and one or two cities
   - Include a call-to-action hint (e.g., \"contact us\", \"get started\")
   - Describe value proposition concisely
   - NO superlatives or hyperbole

VOICE AND TONE:
- Direct and matter-of-fact
- Professional, not salesy
- Focus on services, not promises
- No marketing fluff or buzzwords

OUTPUT: Return ONLY the JSON object, no other text or markdown formatting.";
    }

    /**
     * Build prompt for city page metadata generation
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return string
     */
    private function buildCityMetadataPrompt( string $state, string $city ): string {
        return "Generate SEO metadata for a WordPress development services page targeting businesses in {$city}, {$state}.

CONTEXT:
- Company: 84EM (WordPress development agency)
- Location focus: {$city}, {$state}
- Services: WordPress development, custom plugins, consulting, agency services
- Company is 100% remote, headquartered in Cedar Rapids, Iowa

REQUIREMENTS:
Generate exactly 3 pieces of metadata in the following JSON format:

{
    \"page_title\": \"...\",
    \"seo_title\": \"...\",
    \"meta_description\": \"...\"
}

SPECIFICATIONS:

1. PAGE TITLE (visible H1 on the page):
   - Must be unique and specific to {$city}
   - 40-70 characters
   - Include \"WordPress\" and \"{$city}\"
   - May optionally include \"{$state}\" if space allows
   - Professional, direct tone
   - NO pipe character, NO \"84EM\"
   - Example format: \"WordPress Development Services in {$city}\"

2. SEO TITLE (appears in browser tab and search results):
   - Must end with \" | 84EM\"
   - Total length: 50-60 characters (including \" | 84EM\")
   - Include \"WordPress\" and \"{$city}\"
   - Include \"{$state}\" if space allows, otherwise abbreviate or omit
   - Compelling but not clickbait
   - Example format: \"WordPress Development in {$city}, {$state} | 84EM\"

3. META DESCRIPTION (appears in search results):
   - 150-160 characters exactly
   - Include \"WordPress\", \"{$city}\", and \"{$state}\"
   - Include a call-to-action hint (e.g., \"contact us\", \"get started\")
   - Describe value proposition concisely
   - NO superlatives or hyperbole

VOICE AND TONE:
- Direct and matter-of-fact
- Professional, not salesy
- Focus on services, not promises
- No marketing fluff or buzzwords

OUTPUT: Return ONLY the JSON object, no other text or markdown formatting.";
    }

    /**
     * Parse the API response into metadata array
     *
     * @param  string  $response  Raw API response
     *
     * @return array{page_title: string, seo_title: string, meta_description: string}
     * @throws Exception If parsing fails
     */
    private function parseMetadataResponse( string $response ): array {
        // Clean up response - remove any markdown code blocks
        $cleaned = preg_replace( '/^```json\s*/i', '', trim( $response ) );
        $cleaned = preg_replace( '/\s*```$/i', '', $cleaned );
        $cleaned = trim( $cleaned );

        $data = json_decode( $cleaned, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new Exception( 'Failed to parse metadata JSON: ' . json_last_error_msg() );
        }

        // Validate required fields
        $required_fields = [ 'page_title', 'seo_title', 'meta_description' ];
        foreach ( $required_fields as $field ) {
            if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
                throw new Exception( "Missing required metadata field: {$field}" );
            }
        }

        return [
            'page_title'       => sanitize_text_field( $data['page_title'] ),
            'seo_title'        => sanitize_text_field( $data['seo_title'] ),
            'meta_description' => sanitize_text_field( $data['meta_description'] ),
        ];
    }

    /**
     * Get fallback metadata for a state (used when API fails)
     *
     * @param  string  $state  State name
     * @param  string  $city_list  Comma-separated list of major cities
     *
     * @return array{page_title: string, seo_title: string, meta_description: string}
     */
    public function getFallbackStateMetadata( string $state, string $city_list = '' ): array {
        return [
            'page_title'       => "WordPress Development Services in {$state}",
            'seo_title'        => "WordPress Development, Plugins, Consulting, Agency Services in {$state} | 84EM",
            'meta_description' => "WordPress Development, Plugins, Consulting, Agency Services in {$state}" .
                                  ( $city_list ? ", including {$city_list}" : '' ),
        ];
    }

    /**
     * Get fallback metadata for a city (used when API fails)
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return array{page_title: string, seo_title: string, meta_description: string}
     */
    public function getFallbackCityMetadata( string $state, string $city ): array {
        return [
            'page_title'       => "WordPress Development Services in {$city}, {$state}",
            'seo_title'        => "WordPress Development, Plugins, Consulting, Agency Services in {$city}, {$state} | 84EM",
            'meta_description' => "WordPress Development, Plugins, Consulting, Agency Services in {$city}, {$state}",
        ];
    }
}
