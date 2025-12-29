<?php
/**
 * City Content Generator
 *
 * @package EightyFourEM\LocalPages\Content
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Content;

use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Config\BlockIds;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use EightyFourEM\LocalPages\Contracts\ContentGeneratorInterface;
use WP_CLI;
use Exception;

/**
 * Generates content for city-specific local pages
 */
class CityContentGenerator implements ContentGeneratorInterface {

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
     * Schema generator
     *
     * @var SchemaGenerator
     */
    private SchemaGenerator $schemaGenerator;

    /**
     * Content processor
     *
     * @var ContentProcessor
     */
    private ContentProcessor $contentProcessor;

    /**
     * Metadata generator
     *
     * @var MetadataGenerator
     */
    private MetadataGenerator $metadataGenerator;

    /**
     * Constructor
     *
     * @param  ApiKeyManager  $apiKeyManager
     * @param  ClaudeApiClient  $apiClient
     * @param  StatesProvider  $statesProvider
     * @param  SchemaGenerator  $schemaGenerator
     * @param  ContentProcessor  $contentProcessor
     * @param  MetadataGenerator  $metadataGenerator
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        ClaudeApiClient $apiClient,
        StatesProvider $statesProvider,
        SchemaGenerator $schemaGenerator,
        ContentProcessor $contentProcessor,
        MetadataGenerator $metadataGenerator
    ) {
        $this->apiKeyManager     = $apiKeyManager;
        $this->apiClient         = $apiClient;
        $this->statesProvider    = $statesProvider;
        $this->schemaGenerator   = $schemaGenerator;
        $this->contentProcessor  = $contentProcessor;
        $this->metadataGenerator = $metadataGenerator;
    }

    /**
     * Generate content for a city page
     *
     * @param  array  $data  Data for content generation
     *
     * @return string Generated content
     * @throws Exception If generation fails
     */
    public function generate( array $data ): string {
        if ( ! $this->validate( $data ) ) {
            throw new Exception( 'Invalid data provided for city content generation' );
        }

        $state  = $data['state'];
        $city   = $data['city'];
        $prompt = $this->buildPrompt( $state, $city );

        // Verify API key exists
        if ( ! $this->apiKeyManager->hasKey() ) {
            throw new Exception( 'API key not available' );
        }

        $apiClient   = $this->apiClient;
        $raw_content = $apiClient->sendRequest( $prompt );

        if ( ! $raw_content ) {
            throw new Exception( 'Failed to generate content from API' );
        }

        // Process the raw content
        $processed_content = $this->contentProcessor->processContent(
            $raw_content,
            [ 'state' => $state, 'city' => $city ]
        );

        return $processed_content;
    }

    /**
     * Validate that required data is present
     *
     * @param  array  $data  Data to validate
     *
     * @return bool
     */
    public function validate( array $data ): bool {
        if ( ! isset( $data['state'] ) || ! isset( $data['city'] ) ) {
            return false;
        }

        $state = $data['state'];
        $city  = $data['city'];

        // Validate state exists
        if ( ! $this->statesProvider->has( $state ) ) {
            return false;
        }

        // Validate city exists in state
        $state_data = $this->statesProvider->get( $state );
        $cities     = $state_data['cities'] ?? [];

        return in_array( $city, $cities );
    }

    /**
     * Generate a complete city page
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return int|false Post ID on success, false on failure
     */
    public function generateCityPage( string $state, string $city ): int|false {
        try {
            WP_CLI::log( "Generating content for {$city}, {$state}..." );

            $content = $this->generate( [ 'state' => $state, 'city' => $city ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Generate AI metadata with fallback
            try {
                WP_CLI::log( "Generating AI metadata for {$city}, {$state}..." );
                $metadata = $this->metadataGenerator->generateCityMetadata( $state, $city );
            } catch ( Exception $e ) {
                WP_CLI::warning( "Metadata generation failed: {$e->getMessage()}. Using fallback." );
                $metadata = $this->metadataGenerator->getFallbackCityMetadata( $state, $city );
            }

            // Find or get state parent page
            $state_posts = get_posts( [
                'post_type'   => 'page',
                'meta_query'  => [
                    'relation' => 'AND',
                    [
                        'key'     => '_local_page_state',
                        'value'   => $state,
                        'compare' => '='
                    ],
                    [
                        'key'     => '_local_page_city',
                        'compare' => 'NOT EXISTS'
                    ]
                ],
                'numberposts' => 1,
                'post_status' => 'any',
            ] );

            $parent_id = ! empty( $state_posts ) ? $state_posts[0]->ID : 0;

            // Create city slug
            $city_slug = sanitize_title( $city );

            // Create post
            $post_data = [
                'post_title'   => $metadata['page_title'],
                'post_name'    => $city_slug,
                'post_content' => $content,
                'post_excerpt' => $sections['excerpt'] ?? '',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_parent'  => $parent_id,
                'meta_input'   => [
                    '_local_page_type'      => 'city',
                    '_local_page_state'     => $state,
                    '_local_page_city'      => $city,
                    '_local_page_generated' => current_time( 'mysql' ),
                    '_84em_seo_description' => $metadata['meta_description'],
                    '_84em_seo_title'       => $metadata['seo_title'],
                ],
            ];

            $post_id = wp_insert_post( $post_data );

            if ( is_wp_error( $post_id ) ) {
                throw new Exception( 'Failed to create post: ' . $post_id->get_error_message() );
            }

            // Generate and save schema
            $schema = $this->schemaGenerator->generateCitySchema( $state, $city );
            update_post_meta( $post_id, 'schema', $schema );

            WP_CLI::success( "Created city page: {$city}, {$state} (ID: {$post_id})" );

            return $post_id;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to generate city page for {$city}, {$state}: " . $e->getMessage() );
            return false;
        }
    }

    /**
     * Update an existing city page
     *
     * @param  int  $post_id  Post ID to update
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return bool Success status
     */
    public function updateCityPage( int $post_id, string $state, string $city ): bool {
        try {
            WP_CLI::log( "Updating content for {$city}, {$state}..." );

            $content = $this->generate( [ 'state' => $state, 'city' => $city ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Generate AI metadata with fallback
            try {
                WP_CLI::log( "Generating AI metadata for {$city}, {$state}..." );
                $metadata = $this->metadataGenerator->generateCityMetadata( $state, $city );
            } catch ( Exception $e ) {
                WP_CLI::warning( "Metadata generation failed: {$e->getMessage()}. Using fallback." );
                $metadata = $this->metadataGenerator->getFallbackCityMetadata( $state, $city );
            }

            // Update post
            $post_data = [
                'ID'            => $post_id,
                'post_title'    => $metadata['page_title'],
                'post_content'  => $content,
                'post_excerpt'  => $sections['excerpt'] ?? '',
                'post_modified' => current_time( 'mysql' ),
            ];

            $result = wp_update_post( $post_data );

            if ( is_wp_error( $result ) ) {
                throw new Exception( 'Failed to update post: ' . $result->get_error_message() );
            }

            // Update metadata
            update_post_meta( $post_id, '_local_page_generated', current_time( 'mysql' ) );
            update_post_meta( $post_id, '_84em_seo_description', $metadata['meta_description'] );
            update_post_meta( $post_id, '_84em_seo_title', $metadata['seo_title'] );

            // Regenerate schema
            $schema = $this->schemaGenerator->generateCitySchema( $state, $city );
            update_post_meta( $post_id, 'schema', $schema );

            return true;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to update city page for {$city}, {$state}  (ID: {$post_id}): " . $e->getMessage() );
            return false;
        }
    }


    /**
     * Generate the post title based on the provided data.
     *
     * @param  mixed  $data  Input data used to construct the post title.
     *
     * @return string Generated post title.
     */
    public function getPostTitle( $data ): string {

        return "WordPress Development, Plugins, Consulting, Agency Services in {$data} | 84EM";
    }

    /**
     * Generate the meta description based on the provided data.
     *
     * @param  string  $data
     *
     * @param  string|null  $cities
     *
     * @return string
     */
    public function getMetaDescription( string $data, string $cities = null ): string {

        return "WordPress Development, Plugins, Consulting, Agency Services in {$data}, {$data}";
    }

    /**
     * Build the prompt for Claude API
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return string
     */
    private function buildPrompt( string $state, string $city ): string {
        $services_block  = BlockIds::SERVICES;
        $cta_block       = BlockIds::CTA;
        $separator_block = BlockIds::SEPARATOR;

        $prompt = "Write a 200-300 word landing page for 84EM's WordPress services in {$city}, {$state}.

VOICE:
- Direct and matter-of-fact, no marketing fluff
- Lead with what businesses need, not what we offer
- Short sentences, one idea per paragraph
- Use contractions naturally (you're, we'll, won't)

STRUCTURE:
1. Opening (2-3 sentences, each its own paragraph): Problem-focused intro mentioning {$city}, {$state}
2. <!-- wp:block {{\"ref\":{$separator_block}}} /-->
   H2: \"WordPress Services in {$city}\" followed by exactly: <!-- wp:block {{\"ref\":{$services_block}}} /-->
3. <!-- wp:block {{\"ref\":{$separator_block}}} /-->
   H2: \"Why {$city} Businesses Choose 84EM\" with 4-5 bullet benefits
4. <!-- wp:block {{\"ref\":{$separator_block}}} /-->
   Closing (2 sentences, each its own paragraph): Mention headquartered in Cedar Rapids, Iowa. CTA linking to /contact/
5. End with exactly: <!-- wp:block {{\"ref\":{$cta_block}}} /-->

BENEFITS (vary these, use this voice):
- WordPress experts since 2012, building websites since 1995
- Headquartered in Cedar Rapids, fully remote team serving clients nationwide
- We've shipped plugins for fintech, healthcare, education, and nonprofits
- You'll hear from us before deadlines, not after

FORMATTING:
- Paragraphs: <!-- wp:paragraph --><p>Text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {{\"level\":2}} --><h2><strong>Heading</strong></h2><!-- /wp:heading -->
- Lists: <!-- wp:list {{\"className\":\"is-style-checkmark-list\",\"fontSize\":\"large\"}} --><ul class=\"wp-block-list is-style-checkmark-list has-large-font-size\"><!-- wp:list-item --><li>Item</li><!-- /wp:list-item --></ul><!-- /wp:list -->

AVOID:
- Superlatives (game-changing, cutting-edge, best-in-class, proven track record)
- Emdashes or hyphens
- Location as adjective (not \"{$city} solutions\", use \"solutions for {$city} businesses\")
- <br> or <br/> tags
- Linking inside list items";

        return $prompt;
    }
}
