<?php
/**
 * Integration tests for data structures
 *
 * @package EightyFourEM_Local_Pages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Data\StatesProvider;

class Test_Data_Structures extends TestCase {

    private StatesProvider $statesProvider;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        $this->statesProvider = new StatesProvider();
    }

    /**
     * Test US states data structure
     */
    public function test_us_states_data_structure() {
        $states = $this->statesProvider->getAll();

        // Should have exactly 50 states
        $this->assertCount( 50, $states );

        // Test each state structure
        foreach ( $states as $state => $data ) {
            $this->assertIsString( $state );
            $this->assertIsArray( $data );
            $this->assertArrayHasKey( 'cities', $data );
            $this->assertIsArray( $data['cities'] );
            $this->assertCount( 10, $data['cities'] );

            // Each city should be a non-empty string
            foreach ( $data['cities'] as $city ) {
                $this->assertIsString( $city );
                $this->assertNotEmpty( $city );
            }
        }

        // Test specific states
        $this->assertArrayHasKey( 'California', $states );
        $this->assertArrayHasKey( 'Texas', $states );
        $this->assertArrayHasKey( 'New York', $states );
        $this->assertArrayHasKey( 'Wyoming', $states );

        // Test California cities
        $california_cities = $states['California']['cities'];
        $this->assertContains( 'Los Angeles', $california_cities );
        $this->assertContains( 'San Francisco', $california_cities );
        $this->assertContains( 'San Diego', $california_cities );
    }




}
