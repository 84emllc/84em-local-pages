<?php
/**
 * State Content Generator
 *
 * @package EightyFourEM\LocalPages\Content
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Content;

use EightyFourEM\LocalPages\Contracts\ContentGeneratorInterface;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Config\BlockIds;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use WP_CLI;
use Exception;

/**
 * Generates content for state pages using Claude API
 */
class StateContentGenerator implements ContentGeneratorInterface {

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
     * Generate content based on provided data
     *
     * @param  array  $data  Data for content generation
     *
     * @return string Generated content
     * @throws Exception
     */
    public function generate( array $data ): string {
        if ( ! $this->validate( $data ) ) {
            throw new Exception( 'Invalid data provided for state content generation' );
        }

        $state  = $data['state'];
        $prompt = $this->buildPrompt( $state );

        // Verify API key exists
        if ( ! $this->apiKeyManager->hasKey() ) {
            throw new Exception( 'API key not available' );
        }

        $apiClient   = $this->apiClient;
        $raw_content = $apiClient->sendRequest( $prompt );

        if ( ! $raw_content ) {
            throw new Exception( 'Failed to generate content from API' );
        }

        // Get cities for this state to enable city interlinking
        $state_data = $this->statesProvider->get( $state );
        $cities = $state_data['cities'] ?? [];

        // Process the raw content with city context for interlinking
        $processed_content = $this->contentProcessor->processContent( $raw_content, [
            'state' => $state,
            'cities' => $cities
        ] );

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
        if ( ! isset( $data['state'] ) ) {
            return false;
        }

        $state = $data['state'];
        return $this->statesProvider->has( $state );
    }

    /**
     * Generate a complete state page
     *
     * @param  string  $state  State name
     *
     * @return int|false Post ID on success, false on failure
     */
    public function generateStatePage( string $state ): int|false {
        try {
            WP_CLI::log( "Generating content for {$state}..." );

            $content = $this->generate( [ 'state' => $state ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Validate content quality
            $validation = $this->contentProcessor->validateContent( $sections['content'] );
            if ( ! $validation['success'] ) {
                WP_CLI::warning( "Content quality issues for {$state}: " . implode( ', ', $validation['issues'] ) );
            }

            // Get cities for fallback metadata
            $state_data = $this->statesProvider->get( $state );
            $cities     = $state_data['cities'] ?? [];
            $city_list  = implode( ', ', array_slice( $cities, 0, 5 ) );

            // Generate AI metadata with fallback
            try {
                WP_CLI::log( "Generating AI metadata for {$state}..." );
                $metadata = $this->metadataGenerator->generateStateMetadata( $state );
            } catch ( Exception $e ) {
                WP_CLI::warning( "Metadata generation failed: {$e->getMessage()}. Using fallback." );
                $metadata = $this->metadataGenerator->getFallbackStateMetadata( $state, $city_list );
            }

            // Create the WordPress post
            $post_data = [
                'post_title'   => $metadata['page_title'],
                'post_content' => $sections['content'],
                'post_excerpt' => $sections['excerpt'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
                'meta_input'   => [
                    '_local_page_state'     => $state,
                    '_84em_seo_description' => $metadata['meta_description'],
                    '_84em_seo_title'       => $metadata['seo_title'],
                ],
            ];

            $post_id = wp_insert_post( $post_data, true );

            if ( is_wp_error( $post_id ) ) {
                throw new Exception( 'Failed to create post: ' . $post_id->get_error_message() );
            }

            // Set up URL structure
            $this->setupStateUrl( $post_id, $state );

            // Generate and save schema
            $schema = $this->schemaGenerator->generateStateSchema( $state );
            update_post_meta( $post_id, 'schema', $schema );

            WP_CLI::log( "Generated state page for {$state} (ID: {$post_id})" );

            return $post_id;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to generate state page for {$state}: " . $e->getMessage() );
            return false;
        }
    }

    /**
     * Update an existing state page
     *
     * @param  int  $post_id  Post ID to update
     * @param  string  $state  State name
     *
     * @return bool Success status
     */
    public function updateStatePage( int $post_id, string $state ): bool {
        try {
            WP_CLI::log( "Updating content for {$state}..." );

            $content = $this->generate( [ 'state' => $state ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Get cities for fallback metadata
            $state_data = $this->statesProvider->get( $state );
            $cities     = $state_data['cities'] ?? [];
            $city_list  = implode( ', ', array_slice( $cities, 0, 5 ) );

            // Generate AI metadata with fallback
            try {
                WP_CLI::log( "Generating AI metadata for {$state}..." );
                $metadata = $this->metadataGenerator->generateStateMetadata( $state );
            } catch ( Exception $e ) {
                WP_CLI::warning( "Metadata generation failed: {$e->getMessage()}. Using fallback." );
                $metadata = $this->metadataGenerator->getFallbackStateMetadata( $state, $city_list );
            }

            // Update the post
            $post_data = [
                'ID'            => $post_id,
                'post_title'    => $metadata['page_title'],
                'post_content'  => $sections['content'],
                'post_excerpt'  => $sections['excerpt'],
                'post_modified' => current_time( 'mysql' ),
            ];

            $result = wp_update_post( $post_data, true );

            if ( is_wp_error( $result ) ) {
                throw new Exception( 'Failed to update post: ' . $result->get_error_message() );
            }

            // Update meta fields
            update_post_meta( $post_id, '_84em_seo_description', $metadata['meta_description'] );
            update_post_meta( $post_id, '_84em_seo_title', $metadata['seo_title'] );

            // Regenerate and save schema
            $schema = $this->schemaGenerator->generateStateSchema( $state );
            update_post_meta( $post_id, 'schema', $schema );

            WP_CLI::log( "Updated state page for {$state} (ID: {$post_id})" );

            return true;

        } catch ( Exception $e ) {
            WP_CLI::error( "Failed to update state page for {$state}  (ID: {$post_id}): " . $e->getMessage() );
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
     * @param string $data
     * @param string|null $cities
     *
     * @return string
     */
    public function getMetaDescription( string $data, string $cities = null ): string {

        return "WordPress Development, Plugins, Consulting, Agency Services in {$data}, including {$cities}";
    }

    /**
     * Build the prompt for Claude API
     *
     * @param  string  $state  State name
     *
     * @return string The prompt for API
     */
    private function buildPrompt( string $state ): string {
        // Get state data and cities
        $state_data = $this->statesProvider->get( $state );
        $cities     = $state_data['cities'] ?? [];
        $city_list  = implode( ', ', array_slice( $cities, 0, 10 ) );

        $services_block = BlockIds::SERVICES;
        $cta_block      = BlockIds::CTA;

        $prompt = "Write a short landing page for 84EM's WordPress services in {$state}.

VOICE:
- Direct and matter-of-fact, no marketing fluff
- Short sentences, one idea per paragraph
- Use contractions naturally

STRUCTURE:
1. Intro (2-3 sentences, each its own paragraph): Why businesses in {$state} need reliable WordPress help. Mention these cities: {$city_list}
2. H2: \"WordPress Services in {$state}\" followed by exactly: <!-- wp:block {\"ref\":{$services_block}} /-->
3. End with exactly: <!-- wp:block {\"ref\":{$cta_block}} /-->

FORMATTING:
- Paragraphs: <!-- wp:paragraph {\"fontSize\":\"large\"} --><p class=\"has-large-font-size\">Text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {\"level\":2,\"fontSize\":\"large\"} --><h2 class=\"has-large-font-size\"><strong>Heading</strong></h2><!-- /wp:heading -->

AVOID:
- Superlatives (game-changing, cutting-edge, best-in-class)
- Emdashes
- Bullet lists
- Links in the intro";

        return $prompt;
    }

    /**
     * Set up URL structure for state page
     *
     * @param  int  $post_id  Post ID
     * @param  string  $state  State name
     *
     * @return void
     */
    private function setupStateUrl( int $post_id, string $state ): void {
        $slug         = sanitize_title( $state );
        $desired_slug = $slug;  // Just the state slug

        // Get index page to set as parent
        $index_page = get_page_by_path( 'wordpress-development-services-usa' );
        $parent_id  = $index_page ? $index_page->ID : 0;

        // Update post slug and parent if needed
        $current_post = get_post( $post_id );
        if ( $current_post && ( $current_post->post_name !== $desired_slug || $current_post->post_parent !== $parent_id ) ) {
            wp_update_post( [
                'ID'          => $post_id,
                'post_name'   => $desired_slug,
                'post_parent' => $parent_id,
            ] );
        }
    }
}
