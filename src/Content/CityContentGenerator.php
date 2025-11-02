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
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
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
     * Keywords provider
     *
     * @var KeywordsProvider
     */
    private KeywordsProvider $keywordsProvider;

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
     * Constructor
     *
     * @param  ApiKeyManager  $apiKeyManager  API key manager
     * @param  ClaudeApiClient  $apiClient  Claude API client
     * @param  StatesProvider  $statesProvider  States data provider
     * @param  KeywordsProvider  $keywordsProvider  Keywords provider
     * @param  SchemaGenerator  $schemaGenerator  Schema generator
     * @param  ContentProcessor  $contentProcessor  Content processor
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        ClaudeApiClient $apiClient,
        StatesProvider $statesProvider,
        KeywordsProvider $keywordsProvider,
        SchemaGenerator $schemaGenerator,
        ContentProcessor $contentProcessor
    ) {
        $this->apiKeyManager    = $apiKeyManager;
        $this->apiClient        = $apiClient;
        $this->statesProvider   = $statesProvider;
        $this->keywordsProvider = $keywordsProvider;
        $this->schemaGenerator  = $schemaGenerator;
        $this->contentProcessor = $contentProcessor;
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
            WP_CLI::log( "🏙️ Generating content for {$city}, {$state}..." );

            $content = $this->generate( [ 'state' => $state, 'city' => $city ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

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
                'post_title'   => $this->getPostTitle( "$city, $state" ),
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
                    '_genesis_description'  => $this->getMetaDescription( "{$city}, {$state}" ),
                    '_genesis_title'        => $this->getPostTitle( "{$city}, {$state}" ),
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
            WP_CLI::log( "🔄 Updating content for {$city}, {$state}..." );

            $content = $this->generate( [ 'state' => $state, 'city' => $city ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Update post
            $post_data = [
                'ID'            => $post_id,
                'post_title'    => $this->getPostTitle( "$city, $state" ),
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
            update_post_meta( $post_id, '_genesis_description', $this->getMetaDescription( "{$city}, {$state}" ) );
            update_post_meta( $post_id, '_genesis_title', $this->getPostTitle( "$city, $state" ) );

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

        return "AI-Enhanced WordPress Development, White-Label Services, Plugins, Consulting in {$data} | 84EM";
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

        return "AI-Enhanced WordPress Development, White-Label Services, Plugins, Consulting in {$data}, {$data}";
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

        $prompt = "Write a concise, SEO-optimized landing page for 84EM's WordPress services specifically for businesses in {$city}, {$state}.

IMPORTANT: Create unique, original content that is different from other city pages. Focus on local relevance through city-specific benefits and geographic context.

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely. DO mention that 84EM is headquartered in Cedar Rapids, Iowa.

VOICE AND TONE (CRITICAL):
- Direct and matter-of-fact, no marketing fluff or superlatives
- Skip phrases like \"game-changing,\" \"cutting-edge,\" \"industry-leading,\" \"best-in-class,\" \"unlock,\" \"leverage,\" \"take your business to the next level\"
- Avoid soft benefit language like \"feel confident\" or \"peace of mind\" - focus on concrete deliverables
- No excessive enthusiasm or hype
- Short sentences, one idea per line in opening/closing sections
- Describe what actually happens, not aspirational outcomes
- Professional but straightforward, like explaining technical work to another developer
- Use contractions naturally: \"won\'t,\" \"you\'re,\" \"we\'ll\"

CONTENT STRUCTURE (REQUIRED):

**Opening Section (2-3 short sentences, each in their own paragraph using start tag of \"<!-- wp:paragraph --><p> and end tag of </p><!-- /wp:paragraph -->)\" -- NEVER USE <br> or <br/> tags.****Opening Section (2-3 short sentences, each in their own paragraph using start tag of \"<!-- wp:paragraph --><p> and end tag of </p><!-- /wp:paragraph -->)\" -- NEVER USE <br> or <br/> tags.**
- Brief introduction mentioning {$city}, {$state} and local business context
- Brief overview of 84EM's WordPress expertise
- Include ONE contextual call-to-action link in the opening

CRITICAL: Every paragraph must 1 sentence and formatted this exact way replacing {CONTENT} with the actual content:
<!-- wp:paragraph --><p>{CONTENT}</p><!-- /wp:paragraph -->)

CRITICAL: never use <br> or <br/> tags.

**Core Services Section (H2: \"WordPress Services in {$city}\")**
***IMPORTANT: Present services using THIS EXACT HTML. DO NOT MODIFY:
<!-- wp:block {\"ref\":5031} /-->

**Why Choose 84EM Section (H2: \"Why {$city} Businesses Choose 84EM\")**
Present 4-5 key benefits as a list using this exact HTML structure but vary the content of each list item:
<!-- wp:list {\"className\":\"is-style-checkmark-list\",\"fontSize\":\"large\"} -->
<ul class=\"wp-block-list is-style-checkmark-list has-large-font-size\">
<!-- wp:list-item --><li><strong>Understands WordPress architecture</strong> deeply enough to solve complex problems fast.</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>30 years building for the web. 12 years of WordPress expertise</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Fully remote team serving clients nationwide with proven processes</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Proven track record across diverse industries</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Reliable delivery with consistent communication</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Scalable solutions designed to grow with your business or agency</li><!-- /wp:list-item -->
<!-- wp:list -->

**Closing Paragraph**
- 2 sentences, 1 sentence per paragraph
- Emphasize that 84EM is headquartered in Cedar Rapids, Iowa and serves businesses across {$state}
- Strong call-to-action with contact link
- Keep it direct: describe the work, not the dream

IMPORTANT GRAMMAR RULES:
- Use proper prepositions (in, for, near) when mentioning locations
- Never use city/state names as adjectives directly before service terms
- Correct: \"businesses in {$city}\", \"services for {$city} companies\", \"development in {$city}\"
- Incorrect: \"{$city} businesses seeking {$city} solutions\"
- NEVER USE EMDASHES or HYPHENS

TARGET METRICS:
- Total word count: 200-300 words
- Opening: 2 short sentences, 1 sentence per paragraph
- Services: Use the PRECISE HTML as specified, replacing the content (not the html tags)
- Benefits: 3-4 list items
- Closing: 2 sentences, 1 sentence per paragrph
- Call-to-action links: 2-3 total (contextual, not in lists)

TONE EXAMPLES:
- Not: \"Transform your digital presence\" → \"Build custom WordPress solutions\"
- Not: \"Empower your team\" → \"Your developers can maintain it\"
- Not: \"Scalable solutions designed to grow\" → \"WordPress sites that handle growth\"

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:block {\"ref\":5034} /--><!-- wp:heading {\"level\":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Lists: <!-- wp:list --><ul><li>Item text here</li><li>Item text here</li></ul><!-- /wp:list -->
- Call-to-action links: <a href=\"/contact/\">contact us</a> or <a href=\"/contact/\">get started</a>

IMPORTANT:
- All headings (h2, h3) must be wrapped in <strong> tags
- Include 2-3 call-to-action links throughout the content linking to /contact/ using phrases like \"contact us\", \"get started\", \"discuss your project\"
- Make call-to-action links natural and contextual within PARAGRAPH content only (never in list items)
- Insert this exact CTA block at the very end:

<!-- wp:block {\"ref\":1219} /-->


Do NOT use markdown syntax or plain HTML. Use proper WordPress block markup for all content.

CRITICAL: do not add a link to a substring within a word.  for example AI is a service we offer but retail should not have the AI linked.

CRITICAL: our main focus is on the following services, write text relevant to them and do not link any service related phrase or keyword to anything BUT our 84em.com/services page
AI-Enhanced Development
White-Label Agency Services
Custom Plugin Development
Code Cleanup and Refactoring
Consulting & Strategy
Maintenance & Support
";

        return $prompt;
    }
}
