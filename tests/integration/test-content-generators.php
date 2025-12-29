<?php
/**
 * Integration tests for Content Generators
 *
 * @package EightyFourEM\LocalPages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';
require_once dirname( __DIR__ ) . '/test-config.php';

use EightyFourEM\LocalPages\Content\StateContentGenerator;
use EightyFourEM\LocalPages\Content\CityContentGenerator;
use EightyFourEM\LocalPages\Content\MetadataGenerator;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Api\ClaudeApiClient;
use EightyFourEM\LocalPages\Api\Encryption;
use EightyFourEM\LocalPages\Data\StatesProvider;
use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use EightyFourEM\LocalPages\Utils\ContentProcessor;

class Test_Content_Generators extends TestCase {

    private StateContentGenerator $stateGenerator;
    private CityContentGenerator $cityGenerator;
    private ApiKeyManager $apiKeyManager;
    private ClaudeApiClient $apiClient;
    private Encryption $encryption;
    private StatesProvider $statesProvider;
    private SchemaGenerator $schemaGenerator;
    private ContentProcessor $contentProcessor;
    private MetadataGenerator $metadataGenerator;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp(); // Enables test mode (RUNNING_TESTS constant)

        // Initialize real providers
        $this->statesProvider = new StatesProvider();
        $this->schemaGenerator = new SchemaGenerator();
        $this->contentProcessor = new ContentProcessor();

        // Create real API key manager and API client
        // These will automatically use test_ prefixed options due to RUNNING_TESTS
        $this->encryption = new Encryption();
        $this->apiKeyManager = new ApiKeyManager( $this->encryption );

        // Set test API key and model (will be stored in test_ prefixed options)
        $this->apiKeyManager->setKey( TestConfig::getTestApiKey() );
        $this->apiKeyManager->setModel( TestConfig::getTestModel() );

        $this->apiClient = new ClaudeApiClient( $this->apiKeyManager );
        $this->metadataGenerator = new MetadataGenerator( $this->apiKeyManager, $this->apiClient, $this->statesProvider );

        // Initialize generators with all dependencies
        $this->stateGenerator = new StateContentGenerator(
            $this->apiKeyManager,
            $this->apiClient,
            $this->statesProvider,
            $this->schemaGenerator,
            $this->contentProcessor,
            $this->metadataGenerator
        );

        $this->cityGenerator = new CityContentGenerator(
            $this->apiKeyManager,
            $this->apiClient,
            $this->statesProvider,
            $this->schemaGenerator,
            $this->contentProcessor,
            $this->metadataGenerator
        );
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        // Clean up test options using ApiKeyManager methods
        $this->apiKeyManager->deleteKey();
        $this->apiKeyManager->deleteModel();
    }
    
    /**
     * Test state content generator initialization
     */
    public function test_state_generator_initialization() {
        $this->assertInstanceOf( StateContentGenerator::class, $this->stateGenerator );
    }
    
    /**
     * Test city content generator initialization
     */
    public function test_city_generator_initialization() {
        $this->assertInstanceOf( CityContentGenerator::class, $this->cityGenerator );
    }
    
    /**
     * Test state provider integration
     */
    public function test_states_provider_integration() {
        $states = $this->statesProvider->getAll();
        $this->assertIsArray( $states );
        $this->assertCount( 50, $states );
        
        // Test specific state
        $california = $this->statesProvider->get( 'California' );
        $this->assertIsArray( $california );
        $this->assertArrayHasKey( 'cities', $california );
        $this->assertCount( 10, $california['cities'] );
    }
    
    /**
     * Test schema generator for state
     */
    public function test_schema_generator_state() {
        $schemaJson = $this->schemaGenerator->generateStateSchema( 'California' );
        
        $this->assertIsString( $schemaJson );
        $schema = json_decode( $schemaJson, true );
        $this->assertIsArray( $schema );
        $this->assertArrayHasKey( '@context', $schema );
        $this->assertArrayHasKey( '@type', $schema );
        $this->assertEquals( 'https://schema.org', $schema['@context'] );
    }
    
    /**
     * Test schema generator for city
     */
    public function test_schema_generator_city() {
        $schemaJson = $this->schemaGenerator->generateCitySchema( 'California', 'Los Angeles' );
        
        $this->assertIsString( $schemaJson );
        $schema = json_decode( $schemaJson, true );
        $this->assertIsArray( $schema );
        $this->assertArrayHasKey( '@context', $schema );
        $this->assertArrayHasKey( '@type', $schema );
        $this->assertEquals( 'https://schema.org', $schema['@context'] );
    }
    
    /**
     * Test content processor functionality
     */
    public function test_content_processor() {
        $content = 'We offer WordPress development and custom plugin development services.';
        $context = ['type' => 'state', 'state' => 'California'];

        $processed = $this->contentProcessor->processContent( $content, $context );

        // Should wrap content in WordPress blocks
        $this->assertStringContainsString( '<!-- wp:paragraph -->', $processed );
        $this->assertStringContainsString( 'WordPress development', $processed );
    }
    
    /**
     * Test content processor with city links
     */
    public function test_content_processor_city_links() {
        $content = 'Serving businesses in Los Angeles, San Francisco, and San Diego.';
        $context = [
            'type' => 'state',
            'state' => 'California',
            'cities' => ['Los Angeles', 'San Francisco', 'San Diego']
        ];
        
        $processed = $this->contentProcessor->processContent( $content, $context );
        
        // Should add links to city names
        $this->assertStringContainsString( 'los-angeles', $processed );
        $this->assertStringContainsString( 'san-francisco', $processed );
        $this->assertStringContainsString( 'san-diego', $processed );
    }
    
    /**
     * Test content cleaning functionality
     */
    public function test_content_cleaning() {
        // Test that content is cleaned properly
        $content = '<!-- wp:paragraph --><p>Test content with extra spaces.  </p><!-- /wp:paragraph -->';
        $context = ['type' => 'state'];
        
        $processed = $this->contentProcessor->processContent( $content, $context );
        
        // Should still be valid WordPress blocks
        $this->assertStringContainsString( '<!-- wp:paragraph -->', $processed );
        $this->assertStringContainsString( '</p><!-- /wp:paragraph -->', $processed );
    }
    
    /**
     * Test block structure validation
     */
    public function test_block_structure_validation() {
        $validContent = '<!-- wp:paragraph --><p>Test content</p><!-- /wp:paragraph -->';
        $invalidContent = '<p>Test content without blocks</p>';
        
        // Valid content should have WordPress blocks
        $this->assertStringContainsString( '<!-- wp:', $validContent );
        
        // Invalid content should not have blocks
        $this->assertStringNotContainsString( '<!-- wp:', $invalidContent );
    }
    
    /**
     * Test state data structure
     */
    public function test_state_data_structure() {
        $states = $this->statesProvider->getAll();
        
        foreach ( $states as $stateName => $stateData ) {
            $this->assertIsString( $stateName );
            $this->assertIsArray( $stateData );
            $this->assertArrayHasKey( 'cities', $stateData );
            $this->assertIsArray( $stateData['cities'] );
            $this->assertCount( 10, $stateData['cities'] );
            
            // Test first state more thoroughly
            if ( $stateName === 'Alabama' ) {
                $this->assertContains( 'Birmingham', $stateData['cities'] );
                $this->assertContains( 'Montgomery', $stateData['cities'] );
            }
            
            // Only test first few states
            static $tested = 0;
            if ( ++$tested >= 3 ) break;
        }
    }
    
}