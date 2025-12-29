<?php
/**
 * Integration tests for LD-JSON schema generation
 *
 * @package EightyFourEM\LocalPages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Schema\SchemaGenerator;
use EightyFourEM\LocalPages\Data\StatesProvider;

class Test_LD_JSON_Schema extends TestCase {

    private $schemaGenerator;
    private $statesProvider;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        // Initialize dependencies
        $this->statesProvider = new StatesProvider();
        $this->schemaGenerator = new SchemaGenerator();
    }

    /**
     * Test generate method for state schema
     */
    public function test_generate_state_schema() {
        // Test state page schema
        $state_data = [
            'type' => 'state',
            'state' => 'California',
            'cities' => ['Los Angeles', 'San Francisco', 'San Diego', 'San Jose', 'Sacramento', 'Fresno'],
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $state_data );
        $decoded = json_decode( $schema, true );

        // Validate JSON structure
        $this->assertNotNull( $decoded );
        $this->assertIsArray( $decoded );
        $this->assertEquals( 'WebPage', $decoded['@type'] );
        $this->assertEquals( 'https://schema.org', $decoded['@context'] );

        // Test page name
        $this->assertEquals( 'WordPress Development Services in California', $decoded['name'] );

        // Test description
        $this->assertStringContainsString( 'California', $decoded['description'] );
        $this->assertStringContainsString( 'WordPress development', $decoded['description'] );

        // Test about section (Service)
        $this->assertIsArray( $decoded['about'] );
        $this->assertEquals( 'Service', $decoded['about']['@type'] );
        $this->assertEquals( 'WordPress Development in California', $decoded['about']['name'] );

        // Test provider
        $this->assertIsArray( $decoded['about']['provider'] );
        $this->assertEquals( 'Organization', $decoded['about']['provider']['@type'] );
        $this->assertEquals( '84EM', $decoded['about']['provider']['name'] );

        // Test area served
        $this->assertIsArray( $decoded['about']['areaServed'] );
        $this->assertEquals( 'State', $decoded['about']['areaServed']['@type'] );
        $this->assertEquals( 'California', $decoded['about']['areaServed']['name'] );

        // Test service types
        $this->assertIsArray( $decoded['about']['serviceType'] );
        $this->assertTrue( in_array( 'WordPress Development', $decoded['about']['serviceType'] ), 'WordPress Development should be in serviceType' );
        $this->assertTrue( in_array( 'Custom Plugin Development', $decoded['about']['serviceType'] ), 'Custom Plugin Development should be in serviceType' );
    }

    /**
     * Test generateStateSchema method directly
     */
    public function test_generate_state_schema_direct() {
        $schema = $this->schemaGenerator->generateStateSchema( 'Texas' );
        $decoded = json_decode( $schema, true );

        // Validate JSON structure
        $this->assertNotNull( $decoded );
        $this->assertEquals( 'WordPress Development Services in Texas', $decoded['name'] );
        $this->assertEquals( 'Texas', $decoded['about']['areaServed']['name'] );
    }

    /**
     * Test city page LD-JSON schema
     */
    public function test_city_page_ld_json_schema() {
        $city_data = [
            'type' => 'city',
            'state' => 'California',
            'city' => 'Los Angeles',
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $city_data );
        $decoded = json_decode( $schema, true );

        // Test service name includes city
        $this->assertEquals( 'WordPress Development Services in Los Angeles, California', $decoded['name'] );

        // Test city-specific area served
        $this->assertEquals( 'City', $decoded['about']['areaServed']['@type'] );
        $this->assertEquals( 'Los Angeles', $decoded['about']['areaServed']['name'] );

        // Test contained in place
        $this->assertArrayHasKey( 'containedInPlace', $decoded['about']['areaServed'] );
        $this->assertEquals( 'State', $decoded['about']['areaServed']['containedInPlace']['@type'] );
        $this->assertEquals( 'California', $decoded['about']['areaServed']['containedInPlace']['name'] );
    }

    /**
     * Test generateCitySchema method directly
     */
    public function test_generate_city_schema_direct() {
        $schema = $this->schemaGenerator->generateCitySchema( 'New York', 'New York City' );
        $decoded = json_decode( $schema, true );

        // Validate JSON structure
        $this->assertNotNull( $decoded );
        $this->assertEquals( 'WordPress Development Services in New York City, New York', $decoded['name'] );
        $this->assertEquals( 'New York City', $decoded['about']['areaServed']['name'] );
        $this->assertEquals( 'New York', $decoded['about']['areaServed']['containedInPlace']['name'] );
    }

    /**
     * Test schema with special characters in location names
     */
    public function test_schema_with_special_characters() {
        $data = [
            'type' => 'city',
            'state' => 'Illinois',
            'city' => "O'Fallon",
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $data );
        $decoded = json_decode( $schema, true );

        // Ensure special characters are properly encoded
        $this->assertNotNull( $decoded );
        $this->assertEquals( "WordPress Development Services in O'Fallon, Illinois", $decoded['name'] );
        $this->assertEquals( "O'Fallon", $decoded['about']['areaServed']['name'] );
    }

    /**
     * Test schema includes all required service types for state
     */
    public function test_complete_service_types() {
        $data = [
            'type' => 'state',
            'state' => 'Texas',
            'cities' => ['Houston', 'San Antonio', 'Dallas', 'Austin', 'Fort Worth', 'El Paso'],
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $data );
        $decoded = json_decode( $schema, true );

        $expected_services = [
            'WordPress Development',
            'Custom Plugin Development',
            'WordPress Maintenance',
            'Agency Services',
            'WordPress Security'
        ];

        foreach ( $expected_services as $service ) {
            $this->assertTrue( in_array( $service, $decoded['about']['serviceType'] ), "$service should be in serviceType" );
        }
    }

    /**
     * Test mainEntity section in schema
     */
    public function test_main_entity_section() {
        $data = [
            'type' => 'state',
            'state' => 'Florida',
            'cities' => ['Miami', 'Tampa', 'Jacksonville', 'Orlando', 'St. Petersburg', 'Tallahassee'],
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $data );
        $decoded = json_decode( $schema, true );

        // Check mainEntity structure
        $this->assertArrayHasKey( 'mainEntity', $decoded );
        $this->assertIsArray( $decoded['mainEntity'] );
        $this->assertEquals( 'LocalBusiness', $decoded['mainEntity']['@type'] );
        $this->assertEquals( '84EM WordPress Development', $decoded['mainEntity']['name'] );

        // Check knowsAbout
        $this->assertArrayHasKey( 'knowsAbout', $decoded['mainEntity'] );
        $this->assertTrue( in_array( 'WordPress Development', $decoded['mainEntity']['knowsAbout'] ), 'WordPress Development should be in knowsAbout' );
        $this->assertTrue( in_array( 'PHP Programming', $decoded['mainEntity']['knowsAbout'] ), 'PHP Programming should be in knowsAbout' );
    }

    /**
     * Test provider/organization structure
     */
    public function test_provider_organization_info() {
        $data = [
            'type' => 'state',
            'state' => 'New York',
            'cities' => ['New York City', 'Buffalo', 'Rochester', 'Yonkers', 'Syracuse', 'Albany'],
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $data );
        $decoded = json_decode( $schema, true );

        $provider = $decoded['about']['provider'];

        // Test provider structure
        $this->assertEquals( 'Organization', $provider['@type'] );
        $this->assertEquals( '84EM', $provider['name'] );
        $this->assertEquals( 'https://84em.com', $provider['url'] );

        // Test contact point instead of address (Organization schema doesn't have address)
        $this->assertArrayHasKey( 'contactPoint', $provider );
        $this->assertEquals( 'ContactPoint', $provider['contactPoint']['@type'] );
        $this->assertEquals( 'sales', $provider['contactPoint']['contactType'] );
    }

    /**
     * Test index page schema generation
     */
    public function test_index_page_schema() {
        $data = [
            'type' => 'index',
            'states_data' => [
                ['name' => 'California', 'url' => 'https://84em.com/wordpress-development-services-california/'],
                ['name' => 'Texas', 'url' => 'https://84em.com/wordpress-development-services-texas/'],
                ['name' => 'Florida', 'url' => 'https://84em.com/wordpress-development-services-florida/']
            ]
        ];

        $schema = $this->schemaGenerator->generate( $data );
        $decoded = json_decode( $schema, true );

        // Test basic structure
        $this->assertNotNull( $decoded );
        $this->assertEquals( 'CollectionPage', $decoded['@type'] );
        $this->assertEquals( 'WordPress Development Services by Location', $decoded['name'] );

        // Test mainEntity instead of hasPart
        $this->assertArrayHasKey( 'mainEntity', $decoded );
        $this->assertIsArray( $decoded['mainEntity'] );
        $this->assertEquals( 'ItemList', $decoded['mainEntity']['@type'] );
    }

    /**
     * Test multi-word state names
     */
    public function test_multi_word_state_names() {
        $data = [
            'type' => 'state',
            'state' => 'North Carolina',
            'cities' => ['Charlotte', 'Raleigh', 'Greensboro', 'Durham', 'Winston-Salem', 'Fayetteville'],
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $data );
        $decoded = json_decode( $schema, true );

        $this->assertEquals( 'WordPress Development Services in North Carolina', $decoded['name'] );
        $this->assertEquals( 'North Carolina', $decoded['about']['areaServed']['name'] );
    }

    /**
     * Test schema URL structure
     */
    public function test_schema_url_structure() {
        // Test without post_id - should use site_url with sanitized state
        $data = [
            'type' => 'state',
            'state' => 'California',
            'cities' => ['Los Angeles', 'San Francisco'],
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $data );
        $decoded = json_decode( $schema, true );

        $this->assertArrayHasKey( 'url', $decoded );
        // Without post_id, should use site_url pattern - just check URL exists
        $this->assertNotEmpty( $decoded['url'] );
        $this->assertTrue(
            filter_var( $decoded['url'], FILTER_VALIDATE_URL ) !== false,
            'URL should be valid'
        );

        // For city pages
        $city_data = [
            'type' => 'city',
            'state' => 'California',
            'city' => 'San Diego',
            'post_id' => null
        ];

        $city_schema = $this->schemaGenerator->generate( $city_data );
        $city_decoded = json_decode( $city_schema, true );

        $this->assertArrayHasKey( 'url', $city_decoded );
        $this->assertNotEmpty( $city_decoded['url'] );
        $this->assertTrue(
            filter_var( $city_decoded['url'], FILTER_VALIDATE_URL ) !== false,
            'City URL should be valid'
        );
    }

    /**
     * Test schema validation method
     */
    public function test_schema_validation() {
        // Valid schema
        $valid_schema = json_encode( [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Test Page'
        ] );

        $this->assertTrue( $this->schemaGenerator->validate( $valid_schema ) );

        // Invalid JSON
        $invalid_json = '{invalid json}';
        $this->assertFalse( $this->schemaGenerator->validate( $invalid_json ) );

        // Valid JSON but missing required fields
        $incomplete_schema = json_encode( [
            'name' => 'Test Page'
        ] );

        $this->assertFalse( $this->schemaGenerator->validate( $incomplete_schema ) );
    }

    /**
     * Test JSON encoding handles unicode properly
     */
    public function test_unicode_handling() {
        $data = [
            'type' => 'city',
            'state' => 'Hawaii',
            'city' => 'Kailua-Kona',
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $data );

        // Should be valid JSON
        $decoded = json_decode( $schema, true );
        $this->assertNotNull( $decoded );

        // Check that unicode is preserved
        $json_error = json_last_error();
        $this->assertEquals( JSON_ERROR_NONE, $json_error );
    }

    /**
     * Test containsPlace for state with cities
     */
    public function test_state_contains_place() {
        $cities = ['Phoenix', 'Tucson', 'Mesa', 'Chandler', 'Scottsdale', 'Glendale'];
        $data = [
            'type' => 'state',
            'state' => 'Arizona',
            'cities' => $cities,
            'post_id' => null
        ];

        $schema = $this->schemaGenerator->generate( $data );
        $decoded = json_decode( $schema, true );

        // Check containsPlace exists and has cities
        $this->assertArrayHasKey( 'containsPlace', $decoded['about']['areaServed'] );
        $this->assertIsArray( $decoded['about']['areaServed']['containsPlace'] );
        $this->assertEquals( count( $cities ), count( $decoded['about']['areaServed']['containsPlace'] ) );

        // Check first city structure
        $first_city = $decoded['about']['areaServed']['containsPlace'][0];
        $this->assertEquals( 'City', $first_city['@type'] );
        $this->assertEquals( 'Phoenix', $first_city['name'] );
    }
}
