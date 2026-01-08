<?php
/**
 * City Content Generator (Updated)
 *
 * Enhanced version with:
 * - Location context for industry-aware content
 * - Randomized testimonial blocks
 * - Improved prompts for better content variation
 *
 * @package EightyFourEM\LocalPages\Content
 * @license MIT License
 */

namespace EightyFourEM\LocalPages\Content;

use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Config\BlockIds;
use EightyFourEM\LocalPages\Config\TestimonialBlockIds;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\LocationContextProvider;
use EightyFourEM\LocalPages\Data\TestimonialProvider;
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
     * Location context provider
     *
     * @var LocationContextProvider
     */
    private LocationContextProvider $locationContext;

    /**
     * Testimonial provider
     *
     * @var TestimonialProvider
     */
    private TestimonialProvider $testimonialProvider;

    /**
     * Banned phrases to avoid repetitive content
     *
     * @var array<string>
     */
    private const BANNED_PHRASES = [
        'Your WordPress site needs to work',
        "can't afford downtime",
        'straightforward support',
        'actually fixes things',
        'handle WordPress',
        'stop worrying about',
        'doing the heavy lifting',
        'game-changing',
        'cutting-edge',
        'best-in-class',
        'second to none',
        'unparalleled',
        'world-class',
    ];

    /**
     * Constructor
     *
     * @param ApiKeyManager           $apiKeyManager
     * @param ClaudeApiClient         $apiClient
     * @param StatesProvider          $statesProvider
     * @param SchemaGenerator         $schemaGenerator
     * @param ContentProcessor        $contentProcessor
     * @param MetadataGenerator       $metadataGenerator
     * @param LocationContextProvider $locationContext
     * @param TestimonialProvider     $testimonialProvider
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        ClaudeApiClient $apiClient,
        StatesProvider $statesProvider,
        SchemaGenerator $schemaGenerator,
        ContentProcessor $contentProcessor,
        MetadataGenerator $metadataGenerator,
        ?LocationContextProvider $locationContext = null,
        ?TestimonialProvider $testimonialProvider = null
    ) {
        $this->apiKeyManager       = $apiKeyManager;
        $this->apiClient           = $apiClient;
        $this->statesProvider      = $statesProvider;
        $this->schemaGenerator     = $schemaGenerator;
        $this->contentProcessor    = $contentProcessor;
        $this->metadataGenerator   = $metadataGenerator;
        $this->locationContext     = $locationContext ?? new LocationContextProvider();
        $this->testimonialProvider = $testimonialProvider ?? new TestimonialProvider( TestimonialBlockIds::getAll() );
    }

    /**
     * Generate content for a city page
     *
     * @param array $data Data for content generation
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

        // Validate for banned phrases
        $this->validateBannedPhrases( $processed_content, $city, $state );

        return $processed_content;
    }

    /**
     * Validate that required data is present
     *
     * @param array $data Data to validate
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
     * @param string $state State name
     * @param string $city  City name
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
            }
            catch ( Exception $e ) {
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
                        'compare' => '=',
                    ],
                    [
                        'key'     => '_local_page_city',
                        'compare' => 'NOT EXISTS',
                    ],
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

        }
        catch ( Exception $e ) {
            WP_CLI::error( "Failed to generate city page for {$city}, {$state}: " . $e->getMessage() );

            return false;
        }
    }

    /**
     * Update an existing city page
     *
     * @param int    $post_id Post ID to update
     * @param string $state   State name
     * @param string $city    City name
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
            }
            catch ( Exception $e ) {
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

        }
        catch ( Exception $e ) {
            WP_CLI::error( "Failed to update city page for {$city}, {$state}  (ID: {$post_id}): " . $e->getMessage() );

            return false;
        }
    }


    /**
     * Generate the post title based on the provided data.
     *
     * @param mixed $data Input data used to construct the post title.
     *
     * @return string Generated post title.
     */
    public function getPostTitle( $data ): string {
        return "WordPress Development, Plugins, Consulting, Agency Services in {$data} | 84EM";
    }

    /**
     * Generate the meta description based on the provided data.
     *
     * @param string      $data
     * @param string|null $cities
     *
     * @return string
     */
    public function getMetaDescription( string $data, string $cities = null ): string {
        return "WordPress Development, Plugins, Consulting, Agency Services in {$data}, {$data}";
    }

    /**
     * Build the prompt for Claude API
     *
     * @param string $state State name
     * @param string $city  City name
     *
     * @return string
     */
    private function buildPrompt( string $state, string $city ): string {
        $services_block = BlockIds::SERVICES;
        $cta_block      = BlockIds::CTA;

        // Get location context
        $city_context  = $this->locationContext->getCityContext( $city, $state );
        $state_context = $this->locationContext->getStateContext( $state );

        $industries     = $city_context ? implode( ', ', $city_context['industries'] ) : '';
        $city_desc      = $city_context['context'] ?? '';
        $is_home_state  = $state_context['is_home_state'] ?? false;
        $has_city_data  = $this->locationContext->hasCityContext( $city, $state );

        // Get testimonial block reference (deterministic per city)
        $testimonial_block = $this->testimonialProvider->getCityBlockReference( $state, $city );

        // Build banned phrases string
        $banned_list = implode( "\n- ", self::BANNED_PHRASES );

        // Special notes for home state cities
        $home_note = '';
        if ( $is_home_state ) {
            if ( $city === 'Cedar Rapids' ) {
                $home_note = "\nNOTE: 84EM is headquartered in Cedar Rapids. This is our home city—mention this local presence prominently.";
            }
            else {
                $home_note = "\nNOTE: 84EM is based in Cedar Rapids, Iowa—right here in the state. Mention this nearby presence.";
            }
        }

        // City context instruction
        $city_context_note = $has_city_data
            ? "Use this context about {$city}: {$city_desc}. Key industries: {$industries}."
            : "Research or infer what {$city} might be known for (industry, character, size) and reference it naturally.";

        $prompt = "Write a landing page for 84EM's WordPress services in {$city}, {$state}.

ABOUT 84EM:
- WordPress development agency, fully remote, based in Cedar Rapids, Iowa
- Programming since 1995, WordPress specialist since 2012. IMPORTANT: When mentioning experience duration, you MUST use these exact shortcodes with 'years' after them: '[dev_years] years' or '[wp_years] years'. Correct: 'over [wp_years] years of WordPress' or '[dev_years] years of programming'. Wrong: 'since [wp_years]' or just '[wp_years]'
- Partners with digital agencies (white-label or client-facing) and works directly with businesses
- Works with businesses in fintech, healthcare, education, non-profits{$home_note}

CITY CONTEXT:
{$city_context_note}

VOICE:
- Direct, matter-of-fact, no marketing fluff
- Short sentences, one idea per paragraph
- Contractions are fine
- Helpful tone—explain how we can help, not why others fail

STRUCTURE:
1. Opening hook (2-3 sentences, each its own paragraph): Something specific about doing business in {$city} and WordPress needs. Don't be generic. Reference what makes {$city} distinctive.

2. Why remote works (1-2 sentences): Briefly explain why location doesn't limit service quality. Mention experience since 1995.

3. What we bring (1-2 sentences): Reference specific expertise relevant to likely industries in {$city}.

4. H2: \"WordPress Services in {$city}\" followed by exactly:
<!-- wp:block {\"ref\":{$services_block}} /-->

5. Testimonial: Insert exactly:
{$testimonial_block}

6. End with exactly:
<!-- wp:block {\"ref\":{$cta_block}} /-->

FORMATTING:
- Paragraphs: <!-- wp:paragraph {\"fontSize\":\"large\"} --><p class=\"has-large-font-size\">Text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {\"level\":2,\"fontSize\":\"large\"} --><h2 class=\"has-large-font-size\"><strong>Heading</strong></h2><!-- /wp:heading -->

REQUIREMENTS:
- Total: 100-150 words of unique content (excluding blocks)
- DO NOT include any links to the state page (breadcrumbs already provide this navigation)
- DO NOT start with 'Your WordPress site needs to work'
- DO NOT use these phrases:
- {$banned_list}
- Each city page should feel distinct—vary the angle based on what makes {$city} unique

OUTPUT: Return only the WordPress block content, no preamble.";

        return $prompt;
    }

    /**
     * Validate content doesn't contain banned phrases
     *
     * @param string $content Generated content
     * @param string $city    City name
     * @param string $state   State name
     *
     * @return void
     */
    private function validateBannedPhrases( string $content, string $city, string $state ): void {
        $lower_content = strtolower( $content );

        foreach ( self::BANNED_PHRASES as $phrase ) {
            if ( str_contains( $lower_content, strtolower( $phrase ) ) ) {
                WP_CLI::warning( "Content for {$city}, {$state} contains banned phrase: \"{$phrase}\"" );
            }
        }
    }
}
