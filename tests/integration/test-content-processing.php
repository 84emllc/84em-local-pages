<?php
/**
 * Integration tests for ContentProcessor class - actual plugin functionality
 *
 * @package EightyFourEM\LocalPages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

require_once dirname( __DIR__ ) . '/TestCase.php';
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

use EightyFourEM\LocalPages\Utils\ContentProcessor;

class Test_Content_Processing extends TestCase {

    private ContentProcessor $contentProcessor;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        $this->contentProcessor = new ContentProcessor();
    }

    /**
     * Test the main processContent method wraps in WordPress blocks
     */
    public function test_process_content_adds_block_structure() {
        $content = 'We offer WordPress development and API integrations for your business.';
        $result = $this->contentProcessor->processContent( $content, [] );

        // Should be wrapped in WordPress blocks
        $this->assertStringContainsString( '<!-- wp:paragraph -->', $result );
        $this->assertStringContainsString( '<!-- /wp:paragraph -->', $result );
    }

    /**
     * Test that existing WordPress blocks are not duplicated
     */
    public function test_process_content_preserves_existing_blocks() {
        // Content that already has WordPress block markup
        $content = '<!-- wp:paragraph -->
<p>This content already has WordPress development in blocks.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Existing Heading</h2>
<!-- /wp:heading -->';
        
        $result = $this->contentProcessor->processContent( $content, [] );
        
        // Should NOT duplicate the block markers
        $paragraph_count = substr_count( $result, '<!-- wp:paragraph -->' );
        $this->assertEquals( 1, $paragraph_count, 'Should not duplicate paragraph blocks' );
        
        // Should still have the heading block
        $this->assertStringContainsString( '<!-- wp:heading {"level":2} -->', $result );
    }

    /**
     * Test location linking for state pages (cities get linked)
     */
    public function test_process_content_links_cities_on_state_pages() {
        $content = 'We serve businesses in Los Angeles, San Francisco, and San Diego.';
        
        $context = [
            'state' => 'California',
            'cities' => ['Los Angeles', 'San Francisco', 'San Diego']
        ];
        
        $result = $this->contentProcessor->processContent( $content, $context );
        
        // Cities should be linked to their respective pages (new URL format)
        $this->assertStringContainsString( '/wordpress-development-services-usa/california/los-angeles/', $result );
        $this->assertStringContainsString( '/wordpress-development-services-usa/california/san-francisco/', $result );
        $this->assertStringContainsString( '/wordpress-development-services-usa/california/san-diego/', $result );

        // Each city should be wrapped in a link
        $this->assertStringContainsString( '<a href="' . home_url('/wordpress-development-services-usa/california/los-angeles/') . '">Los Angeles</a>', $result );
    }

    /**
     * Test location linking for city pages (state gets linked)
     */
    public function test_process_content_links_state_on_city_pages() {
        $content = 'We provide services throughout California from our Los Angeles base.';
        
        $context = [
            'state' => 'California',
            'city' => 'Los Angeles'
        ];
        
        $result = $this->contentProcessor->processContent( $content, $context );
        
        // State should be linked to state page (new URL format)
        $this->assertStringContainsString( '/wordpress-development-services-usa/california/', $result );
        $this->assertStringContainsString( '>California</a>', $result );
    }

    /**
     * Test that content is cleaned properly (whitespace normalization)
     */
    public function test_process_content_cleans_whitespace() {
        $content = "This    has     excessive     spaces.\n\n\n\nAnd too many\n\n\n\nline breaks.";
        
        $result = $this->contentProcessor->processContent( $content, [] );
        
        // Should normalize spaces
        $this->assertStringNotContainsString( '     ', $result );
        
        // Should have proper block structure
        $this->assertStringContainsString( '<!-- wp:paragraph -->', $result );
        
        // Should not have excessive line breaks in the output
        $this->assertStringNotContainsString( "\n\n\n\n", $result );
    }

    /**
     * Test markdown heading conversion to HTML
     */
    public function test_process_content_converts_markdown_headings() {
        $content = "## This is an H2 heading\n\nSome content here.\n\n### This is an H3 heading\n\nMore content.";
        
        $result = $this->contentProcessor->processContent( $content, [] );
        
        // Markdown headings should be converted to HTML and wrapped in blocks
        $this->assertStringContainsString( '<!-- wp:heading', $result );
        $this->assertStringContainsString( '<h2>', $result );
        $this->assertStringContainsString( 'This is an H2 heading', $result );
        
        // H3 headings are also converted
        $this->assertStringContainsString( 'This is an H3 heading', $result );
        
        // The content should still contain the text parts
        $this->assertStringContainsString( 'Some content here', $result );
        $this->assertStringContainsString( 'More content', $result );
    }

    /**
     * Test that only first occurrence of location names are linked
     */
    public function test_process_content_links_only_first_occurrence_of_locations() {
        $content = 'We serve California businesses. California has great opportunities. California is our focus.';

        $context = [
            'city' => 'Los Angeles',
            'state' => 'California',
        ];

        $result = $this->contentProcessor->processContent( $content, $context );

        // Count how many times the state link appears (should be only once)
        $link_count = substr_count( $result, '>California</a>' );
        $this->assertEquals( 1, $link_count, 'Should only link first occurrence of state name' );
    }

    /**
     * Test generateCityUrl method
     */
    public function test_generate_city_url() {
        $url = $this->contentProcessor->generateCityUrl( 'California', 'Los Angeles' );

        $expected = home_url( '/wordpress-development-services-usa/california/los-angeles/' );
        $this->assertEquals( $expected, $url );

        // Test with spaces and special characters
        $url = $this->contentProcessor->generateCityUrl( 'New York', 'New York City' );
        $expected = home_url( '/wordpress-development-services-usa/new-york/new-york-city/' );
        $this->assertEquals( $expected, $url );
    }

    /**
     * Test extractContentSections method
     */
    public function test_extract_content_sections() {
        $content = "# Main Title Here\n\nThis is the first paragraph with important information about our services.\n\nAnother paragraph with more details.";
        
        $sections = $this->contentProcessor->extractContentSections( $content );
        
        // Should extract title from markdown heading
        $this->assertEquals( 'Main Title Here', $sections['title'] );
        
        // Should have meta description from first paragraph
        $this->assertNotEmpty( $sections['meta_description'] );
        $this->assertStringContainsString( 'first paragraph', $sections['meta_description'] );
        
        // Should have excerpt
        $this->assertNotEmpty( $sections['excerpt'] );
        
        // Should have full content
        $this->assertEquals( $content, $sections['content'] );
    }

    /**
     * Test validateContent method
     */
    public function test_validate_content() {
        // Test content that's too short
        $short_content = 'This is too short.';
        $validation = $this->contentProcessor->validateContent( $short_content );
        
        $this->assertFalse( $validation['success'] );
        $this->assertNotEmpty( $validation['issues'] );
        $this->assertStringContainsString( 'too short', $validation['issues'][0] );
        
        // Test valid content with proper structure
        $valid_content = "## Comprehensive WordPress Development Services\n\n" . 
            str_repeat( 'This is a paragraph with lots of content about WordPress development services. ', 10 ) . 
            "\n\n" . 
            str_repeat( 'Another paragraph with more information about our expertise and capabilities. ', 10 ) . 
            "\n\n" . 
            str_repeat( 'A third paragraph discussing our approach and methodology for projects. ', 10 ) .
            "\n\n" .
            str_repeat( 'Additional content to ensure we have enough words and paragraphs. ', 10 );
        
        $validation = $this->contentProcessor->validateContent( $valid_content );
        
        $this->assertTrue( $validation['success'], 'Validation should succeed for content with 300+ words' );
        $this->assertEmpty( $validation['issues'] );
        $this->assertGreaterThan( 300, $validation['word_count'] );
    }

    /**
     * Test cleanText method
     */
    public function test_clean_text() {
        $dirty_text = '<p>This has <strong>HTML tags</strong> and     excessive     spaces.</p>';
        
        $clean = $this->contentProcessor->cleanText( $dirty_text );
        
        // Should remove HTML tags
        $this->assertStringNotContainsString( '<p>', $clean );
        $this->assertStringNotContainsString( '<strong>', $clean );
        
        // Should normalize spaces
        $this->assertEquals( 'This has HTML tags and excessive spaces.', $clean );
    }

    /**
     * Test that links are not added inside existing links
     */
    public function test_process_content_avoids_nested_links() {
        $content = 'Check our <a href="/services">WordPress development and API integrations</a> page.';
        
        $result = $this->contentProcessor->processContent( $content, [] );
        
        // Should preserve existing link
        $this->assertStringContainsString( '<a href="/services">', $result );
        
        // Should NOT create nested links for keywords inside existing links
        $this->assertStringNotContainsString( '<a href="/services"><a href=', $result );
        $this->assertStringNotContainsString( '</a></a>', $result );
    }

}