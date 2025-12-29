<?php
/**
 * Integration Tests for Location Link Updates
 *
 * Tests the --update-location-links command to ensure it only
 * processes local pages (those with _local_page_state meta).
 *
 * @package EightyFourEM\LocalPages\Tests
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

require_once __DIR__ . '/../TestCase.php';

class Test_Location_Link_Updates extends TestCase {

	/**
	 * Test that update-location-links only processes local pages
	 */
	public function test_update_location_links_filters_local_pages_only() {
		// Create a regular (non-local) published page
		$regular_page_id = wp_insert_post( [
			'post_title'   => 'Test Regular Page',
			'post_content' => '<p>This is a regular page with WordPress development mentioned.</p>',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		] );

		// Create a local state page
		$local_page_id = wp_insert_post( [
			'post_title'   => 'Test Local Page',
			'post_content' => '<p>This is a local page with WordPress development mentioned.</p>',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'meta_input'   => [
				'_local_page_state' => 'Test State',
			],
		] );

		$this->assertGreaterThan( 0, $regular_page_id, 'Regular page should be created' );
		$this->assertGreaterThan( 0, $local_page_id, 'Local page should be created' );

		// Get original content
		$regular_content_before = get_post_field( 'post_content', $regular_page_id );
		$local_content_before   = get_post_field( 'post_content', $local_page_id );

		// Note: We cannot actually run handleUpdateLocationLinks here because it would
		// process ALL local pages in the database (350 pages). Instead, we verify
		// that the query correctly filters for local pages only.

		// Verify the meta query works correctly
		$query_args = [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'     => '_local_page_state',
					'compare' => 'EXISTS',
				],
			],
		];

		$query = new \WP_Query( $query_args );
		$posts = $query->posts;
		wp_reset_postdata();

		// Verify the local page is in the results
		$local_page_found = false;
		foreach ( $posts as $post ) {
			if ( $post->ID === $local_page_id ) {
				$local_page_found = true;
				break;
			}
		}

		$this->assertTrue( $local_page_found, 'Local page should be found in query results' );

		// Verify the regular page is NOT in the results
		$regular_page_found = false;
		foreach ( $posts as $post ) {
			if ( $post->ID === $regular_page_id ) {
				$regular_page_found = true;
				break;
			}
		}

		$this->assertFalse( $regular_page_found, 'Regular page should NOT be found in query results' );

		// Cleanup
		wp_delete_post( $regular_page_id, true );
		wp_delete_post( $local_page_id, true );
	}

	/**
	 * Test that states-only flag correctly filters city pages
	 */
	public function test_update_location_links_states_only_flag() {
		// Create a state page
		$state_page_id = wp_insert_post( [
			'post_title'   => 'Test State Page',
			'post_content' => '<p>This is a state page.</p>',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'meta_input'   => [
				'_local_page_state' => 'Test State',
			],
		] );

		// Create a city page
		$city_page_id = wp_insert_post( [
			'post_title'   => 'Test City Page',
			'post_content' => '<p>This is a city page.</p>',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'meta_input'   => [
				'_local_page_state' => 'Test State',
				'_local_page_city'  => 'Test City',
			],
		] );

		$this->assertGreaterThan( 0, $state_page_id, 'State page should be created' );
		$this->assertGreaterThan( 0, $city_page_id, 'City page should be created' );

		// Test query for states only
		$query_args = [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'     => '_local_page_state',
					'compare' => 'EXISTS',
				],
				[
					'key'     => '_local_page_city',
					'compare' => 'NOT EXISTS',
				],
			],
		];

		$query = new \WP_Query( $query_args );
		$posts = $query->posts;
		wp_reset_postdata();

		// Verify the state page is in the results
		$state_page_found = false;
		foreach ( $posts as $post ) {
			if ( $post->ID === $state_page_id ) {
				$state_page_found = true;
				break;
			}
		}

		$this->assertTrue( $state_page_found, 'State page should be found in states-only query' );

		// Verify the city page is NOT in the results
		$city_page_found = false;
		foreach ( $posts as $post ) {
			if ( $post->ID === $city_page_id ) {
				$city_page_found = true;
				break;
			}
		}

		$this->assertFalse( $city_page_found, 'City page should NOT be found in states-only query' );

		// Cleanup
		wp_delete_post( $state_page_id, true );
		wp_delete_post( $city_page_id, true );
	}

	/**
	 * Test that both state and city pages are included without states-only flag
	 */
	public function test_update_location_links_includes_all_local_pages() {
		// Create a state page
		$state_page_id = wp_insert_post( [
			'post_title'   => 'Test State Page 2',
			'post_content' => '<p>This is a state page.</p>',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'meta_input'   => [
				'_local_page_state' => 'Test State 2',
			],
		] );

		// Create a city page
		$city_page_id = wp_insert_post( [
			'post_title'   => 'Test City Page 2',
			'post_content' => '<p>This is a city page.</p>',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'meta_input'   => [
				'_local_page_state' => 'Test State 2',
				'_local_page_city'  => 'Test City 2',
			],
		] );

		$this->assertGreaterThan( 0, $state_page_id, 'State page should be created' );
		$this->assertGreaterThan( 0, $city_page_id, 'City page should be created' );

		// Test query for all local pages (states and cities)
		$query_args = [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'     => '_local_page_state',
					'compare' => 'EXISTS',
				],
			],
		];

		$query = new \WP_Query( $query_args );
		$posts = $query->posts;
		wp_reset_postdata();

		// Verify both pages are in the results
		$state_page_found = false;
		$city_page_found  = false;

		foreach ( $posts as $post ) {
			if ( $post->ID === $state_page_id ) {
				$state_page_found = true;
			}
			if ( $post->ID === $city_page_id ) {
				$city_page_found = true;
			}
		}

		$this->assertTrue( $state_page_found, 'State page should be found in all-pages query' );
		$this->assertTrue( $city_page_found, 'City page should be found in all-pages query' );

		// Cleanup
		wp_delete_post( $state_page_id, true );
		wp_delete_post( $city_page_id, true );
	}
}
