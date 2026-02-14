<?php
/**
 * State Content Generator (Updated)
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

use EightyFourEM\LocalPages\Contracts\ContentGeneratorInterface;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Config\BlockIds;
use EightyFourEM\LocalPages\Config\TestimonialBlockIds;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\LocationContextProvider;
use EightyFourEM\LocalPages\Data\TestimonialProvider;
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
     * @todo Move to config
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
        'AI-powered',
        'artificial intelligence',
        'machine learning',
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
     * Generate content based on provided data
     *
     * @param array $data Data for content generation
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
        $cities     = $state_data['cities'] ?? [];

        // Process the raw content with city context for interlinking
        $processed_content = $this->contentProcessor->processContent( $raw_content, [
            'state'  => $state,
            'cities' => $cities,
        ] );

        // Validate for banned phrases
        $this->validateBannedPhrases( $processed_content, $state );

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
        if ( ! isset( $data['state'] ) ) {
            return false;
        }

        $state = $data['state'];

        return $this->statesProvider->has( $state );
    }

    /**
     * Generate a complete state page
     *
     * @param string $state State name
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
            }
            catch ( Exception $e ) {
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

        }
        catch ( Exception $e ) {
            WP_CLI::error( "Failed to generate state page for {$state}: " . $e->getMessage() );

            return false;
        }
    }

    /**
     * Update an existing state page
     *
     * @param int    $post_id Post ID to update
     * @param string $state   State name
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
            }
            catch ( Exception $e ) {
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

        }
        catch ( Exception $e ) {
            WP_CLI::error( "Failed to update state page for {$state}  (ID: {$post_id}): " . $e->getMessage() );

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
        return "WordPress Development, Plugins, Consulting, Agency Services in {$data}, including {$cities}";
    }

    /**
     * Build the prompt for Claude API
     *
     * @param string $state State name
     *
     * @return string The prompt for API
     */
    private function buildPrompt( string $state ): string {
        // Get state data and cities
        $state_data = $this->statesProvider->get( $state );
        $cities     = $state_data['cities'] ?? [];
        $city_list  = implode( ', ', array_slice( $cities, 0, 10 ) );

        // Get location context
        $context       = $this->locationContext->getStateContext( $state );
        $state_context = $context['context'] ?? '';
        $is_home_state = $context['is_home_state'] ?? false;

        // Get testimonial block reference
        $testimonial_block = $this->testimonialProvider->getStateBlockReference( $state );

        // Block IDs
        $services_block = BlockIds::SERVICES;
        $cta_block      = BlockIds::CTA;

        // Build banned phrases string
        $banned_list = implode( "\n- ", self::BANNED_PHRASES );

        // Home state gets special treatment
        $home_state_note = $is_home_state
            ? "\nNOTE: 84EM is headquartered in Cedar Rapids, Iowa. Mention this naturally."
            : '';

        // State context as background, not a list to parrot
        $state_context_note = $state_context
            ? "Background (for tone, not to list verbatim): {$state_context}"
            : '';

        $prompt = "Write a landing page for 84EM's WordPress services targeting {$state} businesses.

ABOUT 84EM:
- WordPress development agency based in Cedar Rapids, Iowa (founded 2012)
- Partners with digital agencies (white-label or client-facing) and works directly with businesses
- Works across industries—no single vertical focus
- Positioned on expertise and reliability, not price
{$home_state_note}

EXPERIENCE SHORTCODES (CRITICAL):
When mentioning years of experience, you MUST use these shortcodes exactly as shown:
- '[dev_years] years' for programming experience (e.g., 'over [dev_years] years of programming')
- '[wp_years] years' for WordPress experience (e.g., '[wp_years] years of WordPress expertise')
NEVER write 'since [dev_years]' or 'since [wp_years]' - that produces nonsense like 'since 31'.
NEVER combine shortcodes with year dates like 'since 1995' - use ONLY the shortcode pattern above.

STATE CONTEXT:
{$state_context_note}
- Major cities: {$city_list}

VOICE:
- Direct, matter-of-fact, no marketing fluff
- Short sentences, one idea per paragraph
- Contractions are fine
- Helpful, not fear-based (don't imply other developers are bad)

STRUCTURE:
1. Opening hook (1 paragraph, 2-3 sentences): A specific observation about {$state} businesses and WordPress needs. Reference something real about the state—don't list industries generically.

2. Value proposition (1 paragraph, 2-3 sentences): Why working with a WordPress specialist makes sense. Mention experience using the shortcodes above. Be specific about what that means for the client.

3. Who we work with (1 paragraph, 2-3 sentences): We work with agencies and businesses across industries. If you mention a specific industry, make it genuinely relevant to {$state}—don't default to a generic list like 'fintech, healthcare, education, non-profits.'

4. H2: \"Web Development & WordPress Services in {$state}\" followed by exactly:
<!-- wp:block {\"ref\":{$services_block}} /-->

5. H2: \"Why {$state} Businesses Choose 84EM\" followed by a bullet list with these EXACT items (use wp:list with is-style-checkmark-list class):
- **[dev_years] years of web development experience** – Programming since 1995, WordPress since 2012
- **Deep WordPress architecture expertise** – Custom plugins, theme development, complex integrations, and performance optimization
- **Agency partnerships** – White-label or client-facing, your choice
- **Direct partnership with businesses** – From startups to established companies needing reliable WordPress support
- **Remote-first, nationwide service** – Based in Cedar Rapids, Iowa, serving clients across all 50 states

6. Insert a spacer after the bullet list: <!-- wp:spacer {\"height\":\"40px\"} --><div style=\"height:40px\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div><!-- /wp:spacer -->
Then testimonial: Insert exactly:
{$testimonial_block}

7. End with exactly:
<!-- wp:block {\"ref\":{$cta_block}} /-->

FORMATTING:
- Paragraphs: <!-- wp:paragraph {\"fontSize\":\"large\"} --><p class=\"has-large-font-size\">Text here.</p><!-- /wp:paragraph -->
- Headings: <!-- wp:heading {\"level\":2,\"fontSize\":\"large\"} --><h2 class=\"has-large-font-size\"><strong>Heading</strong></h2><!-- /wp:heading -->
- Lists: <!-- wp:list {\"className\":\"is-style-checkmark-list\",\"fontSize\":\"large\"} --><ul class=\"wp-block-list is-style-checkmark-list has-large-font-size\"><!-- wp:list-item --><li>Item</li><!-- /wp:list-item --></ul><!-- /wp:list -->

REQUIREMENTS:
- Total length: 200-300 words of unique content (excluding blocks)
- DO NOT start with 'Your WordPress site needs to work'
- DO NOT list industries generically (e.g., 'fintech, healthcare, education, and non-profits')
- DO NOT use these phrases:
- {$banned_list}
- Vary the opening approach based on the state's character

OUTPUT: Return only the WordPress block content, no preamble or explanation.";

        return $prompt;
    }

    /**
     * Validate content doesn't contain banned phrases
     *
     * @param string $content Generated content
     * @param string $state   State name for logging
     *
     * @return void
     */
    private function validateBannedPhrases( string $content, string $state ): void {
        $lower_content = strtolower( $content );

        foreach ( self::BANNED_PHRASES as $phrase ) {
            if ( str_contains( $lower_content, strtolower( $phrase ) ) ) {
                WP_CLI::warning( "Content for {$state} contains banned phrase: \"{$phrase}\"" );
            }
        }
    }

    /**
     * Set up URL structure for state page
     *
     * @param int    $post_id Post ID
     * @param string $state   State name
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
