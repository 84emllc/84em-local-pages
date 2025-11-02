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
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
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
     * Keywords data provider
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
     * @param  ApiKeyManager  $apiKeyManager
     * @param  ClaudeApiClient  $apiClient
     * @param  StatesProvider  $statesProvider
     * @param  KeywordsProvider  $keywordsProvider
     * @param  SchemaGenerator  $schemaGenerator
     * @param  ContentProcessor  $contentProcessor
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
            WP_CLI::log( "🏛️ Generating content for {$state}..." );

            $content = $this->generate( [ 'state' => $state ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Validate content quality
            $validation = $this->contentProcessor->validateContent( $sections['content'] );
            if ( ! $validation['success'] ) {
                WP_CLI::warning( "Content quality issues for {$state}: " . implode( ', ', $validation['issues'] ) );
            }

            // Get cities for meta description
            $state_data = $this->statesProvider->get( $state );
            $cities     = $state_data['cities'] ?? [];

            // Create the WordPress post
            $post_data = [
                'post_title'   => $this->getPostTitle( $state ),
                'post_content' => $sections['content'],
                'post_excerpt' => $sections['excerpt'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
                'meta_input'   => [
                    '_local_page_state'    => $state,
                    '_genesis_description' => $this->getMetaDescription( $state, implode( ', ', $cities ) ),
                    '_genesis_title'       => $this->getPostTitle( $state ),
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

            WP_CLI::log( "✅ Generated state page for {$state} (ID: {$post_id})" );

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
            WP_CLI::log( "🔄 Updating content for {$state}..." );

            $content = $this->generate( [ 'state' => $state ] );

            // Extract content sections
            $sections = $this->contentProcessor->extractContentSections( $content );

            // Update the post
            $post_data = [
                'ID'            => $post_id,
                'post_title'    => $this->getPostTitle( $state ),
                'post_content'  => $sections['content'],
                'post_excerpt'  => $sections['excerpt'],
                'post_modified' => current_time( 'mysql' ),
            ];

            $result = wp_update_post( $post_data, true );

            if ( is_wp_error( $result ) ) {
                throw new Exception( 'Failed to update post: ' . $result->get_error_message() );
            }

            // Get cities for meta description
            $state_data = $this->statesProvider->get( $state );
            $cities     = $state_data['cities'] ?? [];

            // Update meta fields
            update_post_meta( $post_id, '_genesis_description', $this->getMetaDescription( $state, implode( ', ', $cities ) ) );
            update_post_meta( $post_id, '_genesis_title', $this->getPostTitle( $state) );

            // Regenerate and save schema
            $schema = $this->schemaGenerator->generateStateSchema( $state );
            update_post_meta( $post_id, 'schema', $schema );

            WP_CLI::log( "✅ Updated state page for {$state} (ID: {$post_id})" );

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

        return "AI-Enhanced WordPress Development, White-Label Services, Plugins, Consulting in {$data} | 84EM";
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

        return "AI-Enhanced WordPress Development, White-Label Services, Plugins, Consulting in {$data}, including {$cities}";
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
        $city_list  = implode( ', ', array_slice( $cities, 0, 6 ) );

        $prompt = "Write a concise, SEO-optimized landing page for 84EM's WordPress  services specifically for businesses in {$state}.

IMPORTANT: Create unique, original content that is different from other state pages. Focus on local relevance through city mentions and state-specific benefits.

84EM is a 100% FULLY REMOTE WordPress development company. Do NOT mention on-site visits, in-person consultations, local offices, or physical presence. All work is done remotely. But DO mention that 84EM is headquartered in Cedar Rapids, Iowa. No need to specifically use the phrase \"remote-first\".

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

**Opening Section (2-3 short sentences, each in their own paragraph using start tag of \"<!-- wp:paragraph --><p> and end tag of </p><!-- /wp:paragraph -->)\" -- NEVER USE <br> or <br/> tags.**
- Brief introduction mentioning {$state} and ALL of these cities: {$city_list} (you MUST mention all 6 cities naturally)
- Brief overview of 84EM's WordPress expertise
- Include ONE contextual call-to-action link in the opening

CRITICAL: Every paragraph must 1 sentence and formatted this exact way replacing {CONTENT} with the actual content:
<!-- wp:paragraph --><p>{CONTENT}</p><!-- /wp:paragraph -->)

CRITICAL: never use <br> or <br/> tags.

**Core Services Section (H2: \"WordPress Services in {$state}\")**
***IMPORTANT: Present services using THIS EXACT HTML.  DO NOT MODIFY:
<!-- wp:block {\"ref\":5031} /-->

**Why Choose 84EM Section (H2: \"Why {$state} Businesses Choose 84EM\")**
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
- Strong call-to-action with contact link

IMPORTANT GRAMMAR RULES:
- Use proper prepositions (in, for, near) when mentioning locations
- Never use city/state names as adjectives directly before service terms (avoid \"{$state} solutions\")
- Correct: \"businesses in {$state}\", \"services for {$state} companies\", \"development in {$state}\"
- Incorrect: \"{$state} businesses seeking {$state} solutions\"
- NEVER USE EMDASHES or HYPHENS

TARGET METRICS:
- Total word count: 200-300 words
- Opening: 2 short sentences, each on their own line
- Services: Use the PRECISE HTML as specified.
- Benefits: 4-5 list items
- Closing: 2 sentences, each on their own line
- Call-to-action links: 2-3 total (contextual, not in lists)
- City mentions: All 6 cities mentioned at least once

TONE: Professional and factual. Avoid hyperbole and superlatives. Focus on concrete services, technical expertise, and actual capabilities. Make it locally relevant through geographic references.

CRITICAL: Format the content using WordPress block editor syntax (Gutenberg blocks). Use the following format:
- Paragraphs: <!-- wp:paragraph --><p>Your paragraph text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:block {\"ref\":5034} /--><!-- wp:heading {\"level\":2} --><h2><strong>Your Heading</strong></h2><!-- /wp:heading -->
- Lists: <!-- wp:list --><ul><li>Item text here</li><li>Item text here</li></ul><!-- /wp:list -->
- Call-to-action links: <a href=\"/contact/\">contact us today</a> or <a href=\"/contact/\">get started</a>

IMPORTANT:
- All headings (h2, h3) must be wrapped in <strong> tags to ensure they appear bold.
- Include 2-3 call-to-action links throughout the content that link to /contact/ using phrases like \"contact us today\", \"get started\", \"reach out\", \"discuss your project\", etc.
- Make the call-to-action links natural and contextual within PARAGRAPH content (not within list items).
- Insert this exact CTA block at the very end:

<!-- wp:block {\"ref\":1219} /-->

- Insert this exact HTML BEFORE every H2 heading and before the final paragraph:

<!-- wp:block {\"ref\":5034} /-->

Do NOT use markdown syntax or plain HTML. Use proper WordPress block markup for all content.

CRITICAL: do not add a link to a substring within a word.  for example AI is a service we offer but retail should not have the AI linked.";

        return $prompt;
    }

    /**
     * Generate state page URL
     *
     * @param  string  $state  State name
     *
     * @return string State URL
     */
    private function generateStateUrl( string $state ): string {
        $slug = sanitize_title( $state );
        return home_url( "/wordpress-development-services-{$slug}/" );
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
        $desired_slug = "wordpress-development-services-{$slug}";

        // Update post slug if needed
        $current_post = get_post( $post_id );
        if ( $current_post && $current_post->post_name !== $desired_slug ) {
            wp_update_post( [
                'ID'        => $post_id,
                'post_name' => $desired_slug,
            ] );
        }
    }
}
