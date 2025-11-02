<?php
/**
 * Generate Command Handler
 *
 * @package EightyFourEM\LocalPages\Cli\Commands
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Cli\Commands;

use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Data\KeywordsProvider;
use EightyFourEM\LocalPages\Content\StateContentGenerator;
use EightyFourEM\LocalPages\Content\CityContentGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use WP_CLI;
use Exception;

/**
 * Handles all generation-related CLI commands
 */
class GenerateCommand {

    /**
     * API key manager
     *
     * @var ApiKeyManager
     */
    private ApiKeyManager $apiKeyManager;

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
     * State content generator
     *
     * @var StateContentGenerator
     */
    private StateContentGenerator $stateContentGenerator;

    /**
     * City content generator
     *
     * @var CityContentGenerator
     */
    private CityContentGenerator $cityContentGenerator;

    /**
     * Content processor
     *
     * @var ContentProcessor
     */
    private ContentProcessor $contentProcessor;

    /**
     * Schema generator
     *
     * @var SchemaGenerator
     */
    private SchemaGenerator $schemaGenerator;

    /**
     * Constructor
     *
     * @param  ApiKeyManager  $apiKeyManager
     * @param  StatesProvider  $statesProvider
     * @param  KeywordsProvider  $keywordsProvider
     * @param  StateContentGenerator  $stateContentGenerator
     * @param  CityContentGenerator  $cityContentGenerator
     * @param  ContentProcessor  $contentProcessor
     * @param  SchemaGenerator  $schemaGenerator
     */
    public function __construct(
        ApiKeyManager $apiKeyManager,
        StatesProvider $statesProvider,
        KeywordsProvider $keywordsProvider,
        StateContentGenerator $stateContentGenerator,
        CityContentGenerator $cityContentGenerator,
        ContentProcessor $contentProcessor,
        SchemaGenerator $schemaGenerator
    ) {
        $this->apiKeyManager         = $apiKeyManager;
        $this->statesProvider        = $statesProvider;
        $this->keywordsProvider      = $keywordsProvider;
        $this->stateContentGenerator = $stateContentGenerator;
        $this->cityContentGenerator  = $cityContentGenerator;
        $this->contentProcessor      = $contentProcessor;
        $this->schemaGenerator       = $schemaGenerator;
    }

    /**
     * Handle generate-all command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleGenerateAll( array $args, array $assoc_args ): void {
        $include_cities = ! isset( $assoc_args['states-only'] );

        WP_CLI::line( '🚀 Starting comprehensive generation process...' );
        WP_CLI::line( '' );

        if ( $include_cities ) {
            WP_CLI::line( '📊 This will generate/update:' );
            WP_CLI::line( '   • 50 state pages' );
            WP_CLI::line( '   • 300 city pages (6 per state)' );
            WP_CLI::line( '   • Total: 350 pages' );
        }
        else {
            WP_CLI::line( '📊 This will generate/update 50 state pages only.' );
        }

        WP_CLI::line( '' );

        $states_data  = $this->statesProvider->getAll();
        $total_states = count( $states_data );

        $state_created_count = 0;
        $state_updated_count = 0;
        $city_created_count  = 0;
        $city_updated_count  = 0;

        // Initialize progress bar for states
        $progress = \WP_CLI\Utils\make_progress_bar( 'Processing all states and cities', $total_states );

        foreach ( $states_data as $state => $data ) {
            WP_CLI::log( "🏛️ Processing {$state}..." );

            // Generate/update state page first
            $existing_state_post = $this->findStatePage( $state );

            if ( $existing_state_post ) {
                if ( $this->stateContentGenerator->updateStatePage( $existing_state_post->ID, $state ) ) {
                    $state_updated_count ++;
                    WP_CLI::log( "  ✅ Updated state page: {$state} (ID: {$existing_state_post->ID})" );
                }
                else {
                    WP_CLI::warning( "  ❌ Failed to update state page: {$state} (ID: {$existing_state_post->ID})" );
                }
            }
            else {
                $post_id = $this->stateContentGenerator->generateStatePage( $state );
                if ( $post_id ) {
                    $state_created_count ++;
                    WP_CLI::log( "  ✅ Created state page: {$state}" );
                }
                else {
                    WP_CLI::warning( "  ❌ Failed to create state page: {$state}" );
                }
            }

            // Generate cities if requested
            if ( $include_cities ) {
                $cities = $data['cities'] ?? [];
                foreach ( $cities as $city ) {
                    // Check if city page exists
                    $existing_city_post = $this->findCityPage( $state, $city );

                    if ( $existing_city_post ) {
                        if ( $this->cityContentGenerator->updateCityPage( $existing_city_post->ID, $state, $city ) ) {
                            $city_updated_count ++;
                            WP_CLI::log( "    ✅ Updated city page: {$city}, {$state} (ID: {$existing_city_post->ID})" );;
                        }
                        else {
                            WP_CLI::warning( "    ❌ Failed to update city page: {$city}, {$state} (ID: {$existing_city_post->ID})" );
                        }
                    }
                    else {
                        $post_id = $this->cityContentGenerator->generateCityPage( $state, $city );
                        if ( $post_id ) {
                            $city_created_count ++;
                            WP_CLI::log( "    ✅ Created city page: {$city}, {$state} (ID: {$post_id})" );
                        }
                        else {
                            WP_CLI::warning( "    ❌ Failed to create city page: {$city}, {$state}" );
                        }
                    }

                    // Add delay between API requests to respect rate limits
                    sleep( 2 );
                }
            }

            $progress->tick();

            // Add delay between states to respect rate limits
            sleep( 2 );
        }

        $progress->finish();

        // Display final summary
        WP_CLI::line( '' );
        WP_CLI::line( '🎉 Generation Complete!' );
        WP_CLI::line( '======================' );
        WP_CLI::line( "States created: {$state_created_count}" );
        WP_CLI::line( "States updated: {$state_updated_count}" );

        if ( $include_cities ) {
            WP_CLI::line( "Cities created: {$city_created_count}" );
            WP_CLI::line( "Cities updated: {$city_updated_count}" );
            WP_CLI::line( "Total pages processed: " . ( $state_created_count + $state_updated_count + $city_created_count + $city_updated_count ) );
        }
        else {
            WP_CLI::line( "Total state pages processed: " . ( $state_created_count + $state_updated_count ) );
        }

        WP_CLI::success( 'All local pages have been generated/updated successfully!' );
    }

    /**
     * Handle update-all command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleUpdateAll( array $args, array $assoc_args ): void {
        $states_only = isset( $assoc_args['states-only'] );

        WP_CLI::line( '🔄 Starting update of all existing local pages...' );
        WP_CLI::line( '' );

        if ( $states_only ) {
            WP_CLI::line( '📊 Updating state pages only (--states-only flag set)' );
            WP_CLI::line( '' );
        }

        $query_args = [];

        // If states-only flag is set, exclude city pages from the query
        if ( $states_only ) {
            $query_args['meta_query'] = [
                [
                    'key'     => '_local_page_city',
                    'compare' => 'NOT EXISTS',
                ],
            ];
        }

        $all_local_posts = $this->findLocalPages( $query_args );

        if ( empty( $all_local_posts ) ) {
            WP_CLI::warning( 'No local pages found to update.' );
            return;
        }

        $updated_count = 0;
        $failed_count  = 0;

        $progress = \WP_CLI\Utils\make_progress_bar( 'Updating local pages', count( $all_local_posts ) );

        foreach ( $all_local_posts as $post ) {
            $state = get_post_meta( $post->ID, '_local_page_state', true );
            $city  = get_post_meta( $post->ID, '_local_page_city', true );

            try {
                if ( $city ) {
                    // This is a city page
                    if ( $this->cityContentGenerator->updateCityPage( $post->ID, $state, $city ) ) {
                        $updated_count ++;
                        WP_CLI::log( "✅ Updated: {$city}, {$state}" );
                    }
                    else {
                        $failed_count ++;
                        WP_CLI::warning( "❌ Failed to update: {$city}, {$state}" );
                    }
                }
                else {
                    // This is a state page
                    if ( $this->stateContentGenerator->updateStatePage( $post->ID, $state ) ) {
                        $updated_count ++;
                        WP_CLI::log( "✅ Updated: {$state}" );
                    }
                    else {
                        $failed_count ++;
                        WP_CLI::warning( "❌ Failed to update: {$state}" );
                    }
                }

                // Add delay between API requests
                sleep( 2 );

            } catch ( Exception $e ) {
                $failed_count ++;
                $location = $city ? "{$city}, {$state}" : $state;
                WP_CLI::warning( "❌ Error updating {$location}: " . $e->getMessage() );
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::line( '' );
        WP_CLI::line( '📊 Update Summary' );
        WP_CLI::line( '=================' );
        WP_CLI::line( "Successfully updated: {$updated_count}" );
        WP_CLI::line( "Failed to update: {$failed_count}" );
        WP_CLI::line( "Total processed: " . count( $all_local_posts ) );

        if ( $failed_count === 0 ) {
            WP_CLI::success( 'All local pages updated successfully!' );
        }
        else {
            WP_CLI::warning( "Update completed with {$failed_count} failures." );
        }
    }

    /**
     * Handle state-specific command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleState( array $args, array $assoc_args ): void {
        $state_arg = $assoc_args['state'];

        // Handle 'all' states
        if ( $state_arg === 'all' ) {
            $this->handleGenerateAll( $args, $assoc_args );
            return;
        }

        $state_names   = $this->parseStateNames( $state_arg );
        $created_count = 0;
        $updated_count = 0;

        foreach ( $state_names as $state_name ) {
            // Validate state name
            if ( ! $this->statesProvider->has( $state_name ) ) {
                WP_CLI::warning( "Invalid state name: {$state_name}. Skipping." );
                continue;
            }

            // Check if page already exists (state page, not city)
            $existing_post = $this->findStatePage( $state_name );

            if ( $existing_post ) {
                if ( $this->stateContentGenerator->updateStatePage( $existing_post->ID, $state_name ) ) {
                    $updated_count ++;
                    WP_CLI::success( "Updated state page: {$state_name} (ID: {$existing_post->ID})" );;
                }
                else {
                    WP_CLI::error( "Failed to update state page: {$state_name} (ID: {$existing_post->ID})" );
                }
            }
            else {
                $post_id = $this->stateContentGenerator->generateStatePage( $state_name );
                if ( $post_id ) {
                    $created_count ++;
                    WP_CLI::success( "Created state page: {$state_name} (ID: {$post_id})" );
                }
                else {
                    WP_CLI::error( "Failed to create state page: {$state_name}" );
                }
            }

            // Add delay between requests
            sleep( 2 );
        }

        WP_CLI::line( '' );
        WP_CLI::line( "📊 Summary: Created {$created_count}, Updated {$updated_count}" );
    }

    /**
     * Handle city-specific command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleCity( array $args, array $assoc_args ): void {
        $state_arg = $assoc_args['state'] ?? null;
        $city_arg  = $assoc_args['city'] ?? null;

        if ( empty( $state_arg ) ) {
            WP_CLI::error( 'State is required when working with cities. Use --state="State Name"' );
            return;
        }

        if ( empty( $city_arg ) ) {
            WP_CLI::error( 'City is required. Use --city="City Name" or --city=all' );
            return;
        }

        // Validate state name
        if ( ! $this->statesProvider->has( $state_arg ) ) {
            WP_CLI::error( "Invalid state name: {$state_arg}" );
            return;
        }

        // Handle 'all' cities for a state
        if ( $city_arg === 'all' ) {
            // Check if --complete flag is set to also update state page
            $complete = isset( $assoc_args['complete'] );
            $this->generateAllCitiesForState( $state_arg, $complete );
            return;
        }

        // Handle specific cities
        $city_names    = $this->parseCityNames( $city_arg );
        $created_count = 0;
        $updated_count = 0;

        foreach ( $city_names as $city_name ) {
            // Validate city is in state
            $state_data = $this->statesProvider->get( $state_arg );
            $cities     = $state_data['cities'] ?? [];

            if ( ! in_array( $city_name, $cities ) ) {
                WP_CLI::warning( "City '{$city_name}' not found in {$state_arg}. Skipping." );
                continue;
            }

            // Check if city page exists
            $existing_post = $this->findCityPage( $state_arg, $city_name );

            if ( $existing_post ) {
                if ( $this->cityContentGenerator->updateCityPage( $existing_post->ID, $state_arg, $city_name ) ) {
                    $updated_count ++;
                    WP_CLI::success( "Updated city page: {$city_name}, {$state_arg} (ID: {$existing_post->ID})" );
                }
                else {
                    WP_CLI::error( "Failed to update city page: {$city_name}, {$state_arg} (ID: {$existing_post->ID})" );
                }
            }
            else {
                $post_id = $this->cityContentGenerator->generateCityPage( $state_arg, $city_name );
                if ( $post_id ) {
                    $created_count ++;
                    WP_CLI::success( "Created city page: {$city_name}, {$state_arg} (ID: {$post_id})" );
                }
                else {
                    WP_CLI::error( "Failed to create city page: {$city_name}, {$state_arg}" );
                }
            }

            // Add delay between requests
            sleep( 2 );
        }

        WP_CLI::line( '' );
        WP_CLI::line( "📊 Summary: Created {$created_count}, Updated {$updated_count}" );
    }

    /**
     * Handle update command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleUpdate( array $args, array $assoc_args ): void {
        // This method can be extended to handle specific update scenarios
        // For now, delegate to update-all
        $this->handleUpdateAll( $args, $assoc_args );
    }

    /**
     * Handle delete command
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleDelete( array $args, array $assoc_args ): void {
        $state_arg = $assoc_args['state'] ?? null;
        $city_arg  = $assoc_args['city'] ?? null;

        if ( empty( $state_arg ) ) {
            WP_CLI::error( 'State is required for delete operations. Use --state="State Name"' );
            return;
        }

        if ( $city_arg ) {
            $this->deleteCityPage( $state_arg, $city_arg );
        }
        else {
            $this->deleteStatePage( $state_arg );
        }
    }

    /**
     * Handle sitemap generation
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleSitemapGeneration( array $args, array $assoc_args ): void {

        WP_CLI::log( '🗺️ Generating XML sitemap for Local Pages...' );

        // Initialize WordPress filesystem
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Get all published local pages
        $posts = $this->findLocalPages( [
            'post_status' => 'publish',
        ] );

        if ( empty( $posts ) ) {
            WP_CLI::warning( 'No published Local Pages found. Nothing to add to sitemap.' );
            return;
        }

        // Build XML sitemap content
        $xml_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $page_count = 0;
        foreach ( $posts as $post ) {
            $permalink     = get_permalink( $post->ID );
            $modified_date = get_the_modified_date( 'c', $post->ID ); // ISO 8601 format

            $xml_content .= '  <url>' . "\n";
            $xml_content .= '    <loc>' . esc_url( $permalink ) . '</loc>' . "\n";
            $xml_content .= '    <lastmod>' . $modified_date . '</lastmod>' . "\n";
            $xml_content .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml_content .= '    <priority>0.7</priority>' . "\n";
            $xml_content .= '  </url>' . "\n";

            $page_count ++;
        }

        $xml_content .= '</urlset>' . "\n";

        // Define sitemap file path (root directory)
        $sitemap_file = ABSPATH . 'sitemap-local.xml';

        // Write sitemap to file using WordPress filesystem
        if ( $wp_filesystem->put_contents( $sitemap_file, $xml_content, FS_CHMOD_FILE ) ) {
            WP_CLI::success( "✅ Sitemap generated successfully! Added {$page_count} pages to sitemap-local.xml" );
            WP_CLI::log( "📄 Sitemap saved to: {$sitemap_file}" );
        }
        else {
            WP_CLI::error( '❌ Failed to write sitemap file. Check file permissions.' );
        }

    }

    /**
     * Handle index page generation
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleIndexGeneration( array $args, array $assoc_args ): void {
        WP_CLI::line( '📄 Generating index page for WordPress Development Services in USA...' );

        $page_slug  = 'wordpress-development-services-usa';
        $page_title = 'WordPress Development Services in USA | 84EM';

        // Check if page already exists
        $existing_page = get_page_by_path( $page_slug );

        // Get all published state pages (not city pages)
        $posts = $this->findLocalPages( [
            'post_status' => 'publish',
            'meta_query'  => [
                [
                    'key'     => '_local_page_state',
                    'compare' => 'EXISTS',
                ],
                [
                    'key'     => '_local_page_city',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ] );

        if ( empty( $posts ) ) {
            WP_CLI::warning( 'No published Local Pages found. Cannot generate index page.' );
            return;
        }

        // Build content with alphabetized list of states
        $content_data = $this->buildIndexPageContent( $posts );
        $content      = $content_data['content'];
        $states_data  = $content_data['states_data'];

        // Generate LD-JSON schema
        $schema = $this->schemaGenerator->generate( [
            'type'        => 'index',
            'states_data' => $states_data,
        ] );

        if ( $existing_page ) {
            // Update existing page
            $post_data = [
                'ID'                => $existing_page->ID,
                'post_content'      => $content,
                'post_modified'     => current_time( 'mysql' ),
                'post_modified_gmt' => current_time( 'mysql', 1 ),
            ];

            $result = wp_update_post( $post_data );

            if ( $result && ! is_wp_error( $result ) ) {
                // Update meta fields including schema
                update_post_meta( $existing_page->ID, '_genesis_description', 'Professional WordPress development services across all 50 states in the USA. Expert custom plugins, API integrations, and web solutions for businesses nationwide.' );
                update_post_meta( $existing_page->ID, 'schema', $schema );

                WP_CLI::success( "✅ Updated index page '{$page_title}' (ID: {$existing_page->ID})" );
            } else {
                WP_CLI::error( '❌ Failed to update index page.' );
            }
        } else {
            // Create new page
            $post_data = [
                'post_title'   => $page_title,
                'post_name'    => $page_slug,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
                'meta_input'   => [
                    '_genesis_title'       => $page_title,
                    '_genesis_description' => 'Professional WordPress development services across all 50 states in the USA. Expert custom plugins, API integrations, and web solutions for businesses nationwide.',
                    'schema'               => $schema,
                ],
            ];

            $post_id = wp_insert_post( $post_data );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                WP_CLI::success( "✅ Created index page '{$page_title}' (ID: {$post_id})" );
            } else {
                WP_CLI::error( '❌ Failed to create index page.' );
            }
        }
    }

    /**
     * Handle schema regeneration
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleSchemaRegeneration( array $args, array $assoc_args ): void {
        $state_filter = $assoc_args['state'] ?? null;
        $city_filter  = $assoc_args['city'] ?? null;
        $states_only  = isset( $assoc_args['states-only'] ) || isset( $assoc_args['state-only'] );

        // Build query args
        $query_args = [
            'meta_query'  => [
                [
                    'key'     => '_local_page_state',
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        // Filter by specific state if provided
        if ( $state_filter ) {
            $query_args['meta_query'][] = [
                'key'   => '_local_page_state',
                'value' => $state_filter,
            ];
        }

        // Filter by specific city if provided
        if ( $city_filter ) {
            $query_args['meta_query'][] = [
                'key'   => '_local_page_city',
                'value' => $city_filter,
            ];
        }
        // If states-only flag is set, exclude city pages
        elseif ( $states_only ) {
            $query_args['meta_query'][] = [
                'key'     => '_local_page_city',
                'compare' => 'NOT EXISTS',
            ];
        }

        $posts = $this->findLocalPages( $query_args );

        if ( empty( $posts ) ) {
            WP_CLI::warning( 'No local pages found to regenerate schema for.' );
            return;
        }

        $total = count( $posts );
        WP_CLI::line( "🔧 Regenerating schema markup for {$total} pages..." );

        $progress = \WP_CLI\Utils\make_progress_bar( 'Regenerating schemas', $total );
        $success_count = 0;
        $error_count = 0;

        // Use injected schema generator
        $schemaGenerator = $this->schemaGenerator;

        foreach ( $posts as $post ) {
            $state = get_post_meta( $post->ID, '_local_page_state', true );
            $city  = get_post_meta( $post->ID, '_local_page_city', true );

            try {
                if ( $city ) {
                    // Generate city schema
                    $schema = $schemaGenerator->generateCitySchema( $state, $city );
                } else {
                    // Generate state schema
                    $schema = $schemaGenerator->generateStateSchema( $state );
                }

                // Update the schema meta
                update_post_meta( $post->ID, 'schema', $schema );
                $success_count++;

            } catch ( \Exception $e ) {
                WP_CLI::warning( "Failed to regenerate schema for {$post->post_title}: " . $e->getMessage() );
                $error_count++;
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::line( '' );
        WP_CLI::line( '📊 Schema Regeneration Summary' );
        WP_CLI::line( '==============================' );
        WP_CLI::line( "Successfully regenerated: {$success_count}" );
        WP_CLI::line( "Failed: {$error_count}" );
        WP_CLI::line( "Total processed: {$total}" );

        if ( $error_count === 0 ) {
            WP_CLI::success( 'All schemas regenerated successfully!' );
        } else {
            WP_CLI::warning( "Schema regeneration completed with {$error_count} errors." );
        }
    }

    /**
     * Find a state page by state name
     *
     * @param  string  $state  State name
     *
     * @return \WP_Post|null The state page post object or null if not found
     */
    private function findStatePage( string $state ): ?\WP_Post {
        $query = new \WP_Query( [
            'post_type'      => 'page',
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'meta_query'     => [
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
        ] );

        $post = $query->have_posts() ? $query->posts[0] : null;
        wp_reset_postdata();

        return $post;
    }

    /**
     * Find a city page by state and city name
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return \WP_Post|null The city page post object or null if not found
     */
    private function findCityPage( string $state, string $city ): ?\WP_Post {
        $query = new \WP_Query( [
            'post_type'      => 'page',
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'   => '_local_page_state',
                    'value' => $state,
                ],
                [
                    'key'   => '_local_page_city',
                    'value' => $city,
                ],
            ],
        ] );

        $post = $query->have_posts() ? $query->posts[0] : null;
        wp_reset_postdata();

        return $post;
    }

    /**
     * Find all local pages with optional filtering
     *
     * @param  array  $args  Query arguments
     *
     * @return array Array of WP_Post objects
     */
    private function findLocalPages( array $args = [] ): array {
        $defaults = [
            'post_type'      => 'page',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        $query_args = wp_parse_args( $args, $defaults );
        $query      = new \WP_Query( $query_args );
        $posts      = $query->posts;
        wp_reset_postdata();

        return $posts;
    }

    /**
     * Generate all cities for a specific state
     *
     * @param  string  $state  State name
     *
     * @return void
     */
    private function generateAllCitiesForState( string $state, bool $update_state_page = false ): void {
        $state_data = $this->statesProvider->get( $state );
        if ( ! $state_data ) {
            WP_CLI::error( "Invalid state: {$state}" );
            return;
        }

        $cities = $state_data['cities'] ?? [];
        if ( empty( $cities ) ) {
            WP_CLI::warning( "No cities defined for {$state}" );
            return;
        }

        WP_CLI::line( "🏙️ Generating all cities for {$state}..." );

        $created_count = 0;
        $updated_count = 0;

        $progress = \WP_CLI\Utils\make_progress_bar( "Processing {$state} cities", count( $cities ) );

        foreach ( $cities as $city ) {
            // Check if city page exists
            $existing_post = $this->findCityPage( $state, $city );

            if ( $existing_post ) {
                if ( $this->cityContentGenerator->updateCityPage( $existing_post->ID, $state, $city ) ) {
                    $updated_count ++;
                }
                else {
                    WP_CLI::warning( "Failed to update: {$city}, {$state}" );
                }
            }
            else {
                $post_id = $this->cityContentGenerator->generateCityPage( $state, $city );
                if ( $post_id ) {
                    $created_count ++;
                }
                else {
                    WP_CLI::warning( "Failed to create: {$city}, {$state}" );
                }
            }

            $progress->tick();
            sleep( 2 );
        }

        $progress->finish();

        WP_CLI::line( '' );
        WP_CLI::success( "Completed {$state} cities: Created {$created_count}, Updated {$updated_count}" );

        // If requested, also update the state page
        if ( $update_state_page ) {
            WP_CLI::line( '' );
            WP_CLI::line( "🏛️ Now updating {$state} state page..." );

            // Check if state page exists
            $existing_state_post = $this->findStatePage( $state );

            if ( $existing_state_post ) {
                if ( $this->stateContentGenerator->updateStatePage( $existing_state_post->ID, $state ) ) {
                    WP_CLI::success( "Updated state page: {$state} (ID: {$existing_state_post->ID})" );;
                }
                else {
                    WP_CLI::error( "Failed to update state page: {$state} (ID: {$existing_state_post->ID})" );;
                }
            }
            else {
                $post_id = $this->stateContentGenerator->generateStatePage( $state );
                if ( $post_id ) {
                    WP_CLI::success( "Created state page: {$state} (ID: {$post_id})" );
                }
                else {
                    WP_CLI::error( "Failed to create state page: {$state}" );
                }
            }

            sleep( 2 );
        }
    }

    /**
     * Delete a city page
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return void
     */
    private function deleteCityPage( string $state, string $city ): void {
        $post = $this->findCityPage( $state, $city );

        if ( ! $post ) {
            WP_CLI::warning( "City page not found: {$city}, {$state}" );
            return;
        }

        if ( wp_delete_post( $post->ID, true ) ) {
            WP_CLI::success( "Deleted city page: {$city}, {$state}" );
        }
        else {
            WP_CLI::error( "Failed to delete city page: {$city}, {$state}" );
        }
    }

    /**
     * Delete a state page and all its cities
     *
     * @param  string  $state  State name
     *
     * @return void
     */
    private function deleteStatePage( string $state ): void {
        // Find all pages for this state (state page + city pages)
        $posts = $this->findLocalPages( [
            'meta_query' => [
                [
                    'key'   => '_local_page_state',
                    'value' => $state,
                ],
            ],
        ] );

        if ( empty( $posts ) ) {
            WP_CLI::warning( "No pages found for state: {$state}" );
            return;
        }

        $deleted_count = 0;
        foreach ( $posts as $post ) {
            if ( wp_delete_post( $post->ID, true ) ) {
                $deleted_count ++;
            }
        }

        WP_CLI::success( "Deleted {$deleted_count} pages for state: {$state}" );
    }

    /**
     * Parse comma-separated state names
     *
     * @param  string  $states_string  Comma-separated state names
     *
     * @return array
     */
    private function parseStateNames( string $states_string ): array {
        return array_map( 'trim', explode( ',', $states_string ) );
    }

    /**
     * Parse comma-separated city names
     *
     * @param  string  $cities_string  Comma-separated city names
     *
     * @return array
     */
    private function parseCityNames( string $cities_string ): array {
        return array_map( 'trim', explode( ',', $cities_string ) );
    }

    /**
     * Builds the content for the index page with alphabetized state list
     *
     * @param  array  $posts  Array of WP_Post objects
     *
     * @return array Array with 'content' and 'states_data' keys
     */
    private function buildIndexPageContent( array $posts ): array {
        $states_data = [];

        // Extract state data from local pages
        foreach ( $posts as $post ) {
            $state     = get_post_meta( $post->ID, '_local_page_state', true );
            $permalink = get_permalink( $post->ID );

            if ( $state && $permalink ) {
                $states_data[] = [
                    'name' => $state,
                    'url'  => $permalink,
                ];
            }
        }

        // Sort states alphabetically
        usort( $states_data, function ( $a, $b ) {
            return strcmp( $a['name'], $b['name'] );
        } );

        // Build the content using WordPress block editor syntax
        $content = '<!-- wp:paragraph -->
<p>84EM provides professional WordPress development services across all 50 states in the USA. Our remote-first approach enables us to deliver expert WordPress solutions, custom plugins, API integrations, and comprehensive web development services to businesses nationwide.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2><strong>WordPress Development Services by State</strong></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Click on your state below to learn more about our WordPress development services in your area:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>';

        // Add each state as a list item
        foreach ( $states_data as $state ) {
            $content .= '<li><a href="' . esc_url( $state['url'] ) . '">' . esc_html( $state['name'] ) . '</a></li>';
        }

        $content .= '</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2} -->
<h2><strong>Why Choose 84EM for WordPress Development?</strong></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>As a fully remote WordPress development company, 84EM serves clients across the United States with:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Custom WordPress development and plugin creation</li>
<li>API integrations and third-party service connections</li>
<li>WordPress security audits and hardening</li>
<li>White-label development services for agencies</li>
<li>WordPress maintenance and ongoing support</li>
<li>Data migration and platform transfers</li>
<li>WordPress troubleshooting and optimization</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Our experienced team delivers reliable, scalable WordPress solutions regardless of your location. <a href="/contact/">Contact us today</a> to discuss your WordPress development needs.</p>
<!-- /wp:paragraph -->';

        return [
            'content'     => $content,
            'states_data' => $states_data,
        ];
    }

    /**
     * Handle updating keyword links in existing pages
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleUpdateKeywordLinks( array $args, array $assoc_args ): void {
        WP_CLI::line( '🔗 Updating Keyword Links in Existing Pages' );
        WP_CLI::line( '============================================' );
        WP_CLI::line( '' );

        // Check if we should update only states or all
        $states_only = isset( $assoc_args['states-only'] );

        // Get all local pages (MUST have _local_page_state meta key)
        $query_args = [
            'post_status' => 'publish',
            'meta_query'  => [
                [
                    'key'     => '_local_page_state',
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        // If states-only, exclude city pages
        if ( $states_only ) {
            $query_args['meta_query'][] = [
                'key'     => '_local_page_city',
                'compare' => 'NOT EXISTS',
            ];
            WP_CLI::line( '📊 Processing state pages only...' );
        } else {
            WP_CLI::line( '📊 Processing all state and city pages...' );
        }

        $posts = $this->findLocalPages( $query_args );
        
        if ( empty( $posts ) ) {
            WP_CLI::warning( 'No local pages found to update.' );
            return;
        }

        $total = count( $posts );
        WP_CLI::line( "Found {$total} pages to update." );
        WP_CLI::line( '' );

        // Initialize progress bar
        $progress = \WP_CLI\Utils\make_progress_bar( 'Updating keyword links', $total );

        $updated_count = 0;
        $skipped_count = 0;
        $error_count = 0;

        foreach ( $posts as $post ) {
            try {
                // Get the current content
                $content = $post->post_content;
                
                if ( empty( $content ) ) {
                    $skipped_count++;
                    $progress->tick();
                    continue;
                }

                // Determine the context (state or city page)
                $state = get_post_meta( $post->ID, '_local_page_state', true );
                $city = get_post_meta( $post->ID, '_local_page_city', true );
                
                $context = [
                    'type' => ! empty( $city ) ? 'city' : 'state',
                    'state' => $state,
                ];
                
                if ( ! empty( $city ) ) {
                    $context['city'] = $city;
                }

                // First, strip all existing links to keywords and locations
                // This ensures we're starting fresh with the latest keyword URLs
                $stripped_content = $this->stripExistingKeywordLinks( $content );

                // Reprocess the content with current keywords
                $processed_content = $this->contentProcessor->processContent( $stripped_content, $context );

                // Check if content actually changed (compare with original, not stripped)
                if ( $processed_content === $post->post_content ) {
                    $skipped_count++;
                    $progress->tick();
                    continue;
                }

                // Update the post
                $result = wp_update_post( [
                    'ID'           => $post->ID,
                    'post_content' => $processed_content,
                ], true );

                if ( is_wp_error( $result ) ) {
                    $error_count++;
                    WP_CLI::warning( "Failed to update {$post->post_title}: " . $result->get_error_message() );
                } else {
                    $updated_count++;
                }

            } catch ( \Exception $e ) {
                $error_count++;
                WP_CLI::warning( "Error processing {$post->post_title}: " . $e->getMessage() );
            }

            $progress->tick();
        }

        $progress->finish();

        // Display summary
        WP_CLI::line( '' );
        WP_CLI::line( '📊 Update Summary' );
        WP_CLI::line( '=================' );
        WP_CLI::line( "Total pages processed: {$total}" );
        WP_CLI::success( "Updated: {$updated_count}" );
        
        if ( $skipped_count > 0 ) {
            WP_CLI::line( "Skipped (no changes): {$skipped_count}" );
        }
        
        if ( $error_count > 0 ) {
            WP_CLI::warning( "Errors: {$error_count}" );
        }

        WP_CLI::line( '' );
        WP_CLI::success( 'Keyword link update complete!' );
    }

    /**
     * Migrate all local pages from old URL structure to new URL structure
     *
     * Old format: /wordpress-development-services-usa/wordpress-development-services-{state}/
     * New format: /wordpress-development-services-usa/{state}/
     *
     * @param  array  $args  Positional arguments
     * @param  array  $assoc_args  Associative arguments
     *
     * @return void
     */
    public function handleUrlMigration( array $args, array $assoc_args ): void {
        WP_CLI::line( '🔄 Starting URL Migration for All Local Pages' );
        WP_CLI::line( '==============================================' );
        WP_CLI::line( '' );

        // Get index page ID
        $index_page = get_page_by_path( 'wordpress-development-services-usa' );
        if ( ! $index_page ) {
            WP_CLI::error( 'Index page "wordpress-development-services-usa" not found. Please generate it first with: wp 84em local-pages --generate-index' );
            return;
        }
        $index_page_id = $index_page->ID;

        WP_CLI::log( "✅ Found index page (ID: {$index_page_id})" );
        WP_CLI::line( '' );

        // Get all state pages (currently have long slugs)
        // State pages have _local_page_state but NOT _local_page_city
        $state_pages = get_posts( [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => '_local_page_state',
                    'compare' => 'EXISTS',
                ],
                [
                    'key'     => '_local_page_city',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ] );

        $total_states = count( $state_pages );
        WP_CLI::log( "📍 Found {$total_states} state pages to migrate" );

        if ( $total_states === 0 ) {
            WP_CLI::warning( 'No state pages found to migrate.' );
            return;
        }

        WP_CLI::line( '' );
        $progress = \WP_CLI\Utils\make_progress_bar( 'Migrating state pages', $total_states );

        $migrated_count = 0;
        $skipped_count  = 0;

        foreach ( $state_pages as $post ) {
            $state = get_post_meta( $post->ID, '_local_page_state', true );
            if ( ! $state ) {
                $skipped_count++;
                $progress->tick();
                continue;
            }

            // New slug is just the state name (sanitized)
            $new_slug = sanitize_title( $state );

            // Only update if slug or parent needs changing
            if ( $post->post_name !== $new_slug || $post->post_parent !== $index_page_id ) {
                wp_update_post( [
                    'ID'          => $post->ID,
                    'post_name'   => $new_slug,
                    'post_parent' => $index_page_id,
                ] );
                $migrated_count++;
            } else {
                $skipped_count++;
            }

            $progress->tick();
        }

        $progress->finish();

        // Get all city pages (these will automatically update due to parent changes)
        // City pages have both _local_page_state AND _local_page_city
        $city_pages = get_posts( [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => '_local_page_state',
                    'compare' => 'EXISTS',
                ],
                [
                    'key'     => '_local_page_city',
                    'compare' => 'EXISTS',
                ],
            ],
        ] );

        $total_cities = count( $city_pages );

        // Flush rewrite rules to regenerate permalinks
        flush_rewrite_rules();

        WP_CLI::line( '' );
        WP_CLI::line( '📊 Migration Summary' );
        WP_CLI::line( '====================' );
        WP_CLI::line( "State pages migrated: {$migrated_count}" );
        WP_CLI::line( "State pages skipped: {$skipped_count}" );
        WP_CLI::line( "City pages (auto-updated): {$total_cities}" );
        WP_CLI::line( '' );
        WP_CLI::success( "Migrated {$migrated_count} state pages. City page URLs updated automatically based on parent." );
        WP_CLI::line( '' );
        WP_CLI::line( '📝 Next Steps:' );
        WP_CLI::line( '   1. wp 84em local-pages --update-keyword-links' );
        WP_CLI::line( '   2. wp 84em local-pages --generate-sitemap' );
        WP_CLI::line( '   3. wp rewrite flush' );
        WP_CLI::line( '' );
        WP_CLI::line( '✨ URL migration complete!' );
    }

    /**
     * Strip existing keyword and location links from content
     *
     * @param  string  $content  Content to strip links from
     *
     * @return string Content with links removed
     */
    private function stripExistingKeywordLinks( string $content ): string {
        // Get all keywords
        $keywords = $this->keywordsProvider->getAll();
        
        // Strip ANY links containing our keywords, regardless of URL
        // This ensures we remove links with old URLs too
        foreach ( $keywords as $keyword => $url ) {
            // Escape special regex characters in the keyword
            $escaped_keyword = preg_quote( $keyword, '/' );
            
            // Pattern to match: <a href="[any url]">[keyword]</a>
            // This removes any link where the link text exactly matches our keyword
            // Case-insensitive to catch variations
            $pattern = '/<a\s+href=["\'][^"\']*["\']>(' . $escaped_keyword . ')<\/a>/i';
            $content = preg_replace( $pattern, '$1', $content );
        }
        
        // Also strip links to known service/work pages that might have old URLs
        // This catches links to /services/, /work/, /contact/ etc with keyword text
        $known_paths = [
            '/work/',
            '/services/',
            '/services/custom-wordpress-plugin-development/',
            '/services/white-label-wordpress-development-for-agencies/',
            '/contact/',
        ];
        
        foreach ( $known_paths as $path ) {
            $escaped_path = preg_quote( $path, '/' );
            // Remove any link to these paths, preserving the text content
            $pattern = '/<a\s+href=["\'][^"\']*' . $escaped_path . '[^"\']*["\']>([^<]+)<\/a>/i';
            $content = preg_replace( $pattern, '$1', $content );
        }
        
        // Remove location links (state and city links)
        // Pattern supports both legacy and new URL formats during migration
        // Legacy: /wordpress-development-services-[state]/
        // New: /wordpress-development-services-usa/[state]/
        $pattern = '/<a\s+href=["\']\/?(wordpress-development-services-usa\/)?wordpress-development-services-[^"\']+["\']>([^<]+)<\/a>/i';
        $content = preg_replace( $pattern, '$2', $content );
        
        return $content;
    }
}
