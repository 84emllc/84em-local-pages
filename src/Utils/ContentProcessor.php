<?php
/**
 * Content Processor Utility
 *
 * @package EightyFourEM\LocalPages\Utils
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Utils;

use EightyFourEM\LocalPages\Data\KeywordsProvider;

/**
 * Processes and enhances content with links, formatting, and other enhancements
 */
class ContentProcessor {

    /**
     * Keywords data provider
     *
     * @var KeywordsProvider
     */
    private KeywordsProvider $keywordsProvider;

    /**
     * Constructor
     *
     * @param  KeywordsProvider  $keywordsProvider
     */
    public function __construct( KeywordsProvider $keywordsProvider ) {
        $this->keywordsProvider = $keywordsProvider;
    }

    /**
     * Process content by adding internal links and formatting
     *
     * @param  string  $content  Raw content to process
     * @param  array  $context  Context data (state, city, etc.)
     *
     * @return string Processed content
     */
    public function processContent( string $content, array $context = [] ): string {
        // Clean up the content first
        $processed_content = $this->cleanContent( $content );

        // Add location-specific internal links FIRST (cities/states)
        // This ensures city names get linked before longer keyword phrases that might contain them
        $processed_content = $this->addLocationLinks( $processed_content, $context );

        // Add internal links to service keywords AFTER location links
        $processed_content = $this->addServiceLinks( $processed_content );

        // Format headings properly
        $processed_content = $this->formatHeadings( $processed_content );

        // Add WordPress blocks structure
        $processed_content = $this->addBlockStructure( $processed_content );

        return $processed_content;
    }

    /**
     * Clean up raw content from API
     *
     * @param  string  $content  Raw content
     *
     * @return string Cleaned content
     */
    private function cleanContent( string $content ): string {
        // Remove markdown code fences (```html, ```, etc.)
        $content = preg_replace( '/^```[a-z]*\s*/i', '', $content );
        $content = preg_replace( '/\s*```\s*$/i', '', $content );

        // Remove excessive whitespace
        $content = preg_replace( '/\s+/', ' ', $content );

        // Normalize line breaks
        $content = str_replace( [ "\r\n", "\r" ], "\n", $content );

        // Remove multiple consecutive line breaks
        $content = preg_replace( '/\n{3,}/', "\n\n", $content );

        // Trim each line
        $lines   = explode( "\n", $content );
        $lines   = array_map( 'trim', $lines );
        $content = implode( "\n", $lines );

        return trim( $content );
    }

    /**
     * Add internal links to service-related keywords
     *
     * @param  string  $content  Content to process
     *
     * @return string Content with service links added
     */
    private function addServiceLinks( string $content ): string {
        $service_keywords = $this->keywordsProvider->getAll();

        // Process list items first - link keywords at the start of each list item
        // This ensures service lists have all keywords linked
        $content = $this->addServiceLinksInListItems( $content, $service_keywords );

        // Extract list items that contain <strong> tags (these are service category lists)
        // We'll protect these from any keyword linking
        preg_match_all( '/(<li[^>]*>)(.*?)(<\/li>)/is', $content, $list_items_matches, PREG_SET_ORDER );
        $protected_list_items = [];
        foreach ( $list_items_matches as $list_match ) {
            $li_content = $list_match[2];
            // If this list item contains a <strong> tag, protect it from linking
            if ( strpos( $li_content, '<strong>' ) !== false || strpos( $li_content, '<strong ' ) !== false ) {
                $protected_list_items[] = $list_match[0];
            }
        }

        // Replace protected list items with placeholders
        $placeholders = [];
        foreach ( $protected_list_items as $i => $protected_item ) {
            $placeholder = "___PROTECTED_LIST_ITEM_{$i}___";
            $placeholders[$placeholder] = $protected_item;
            $content = str_replace( $protected_item, $placeholder, $content );
        }

        // Then process regular content - link first occurrence in paragraphs
        // But skip if already linked in a list item
        foreach ( $service_keywords as $keyword => $url ) {
            // Check if this keyword (case-insensitive) is already linked to this URL anywhere in the content
            // Use a case-insensitive regex to check for any variation of the keyword linked to this URL
            $escaped_keyword = preg_quote( $keyword, '/' );
            $link_check_pattern = '/<a\s+href=["\']' . preg_quote( $url, '/' ) . '["\']>\s*' . $escaped_keyword . '\s*<\/a>/i';
            if ( preg_match( $link_check_pattern, $content ) ) {
                continue;
            }

            // Split content by HTML tags to avoid matching text inside tags/attributes
            $parts = preg_split( '/(<[^>]+>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
            $replaced = false;

            foreach ( $parts as $index => $part ) {
                // Skip empty parts
                if ( empty( $part ) ) {
                    continue;
                }

                // Skip HTML tags (odd indices after split)
                if ( preg_match( '/^<[^>]+>$/', $part ) ) {
                    continue;
                }

                // Skip placeholders
                if ( strpos( $part, '___PROTECTED_LIST_ITEM_' ) !== false ) {
                    continue;
                }

                // Skip if we already replaced once
                if ( $replaced ) {
                    continue;
                }

                // Create a pattern that matches the keyword (case-insensitive)
                // but not if it's part of another word
                $pattern = '/\b(' . preg_quote( $keyword, '/' ) . ')\b/i';

                // Check if keyword exists in this text part
                if ( preg_match( $pattern, $part, $matches ) ) {
                    // Replace in this part only
                    $parts[$index] = preg_replace_callback(
                        $pattern,
                        function( $matches ) use ( $url ) {
                            return '<a href="' . esc_url( $url ) . '">' . $matches[1] . '</a>';
                        },
                        $part,
                        1 // Only replace first occurrence
                    );
                    $replaced = true;
                }
            }

            // Rejoin the parts
            $content = implode( '', $parts );
        }

        // Restore protected list items
        foreach ( $placeholders as $placeholder => $original ) {
            $content = str_replace( $placeholder, $original, $content );
        }

        return $content;
    }

    /**
     * Add service links specifically within list items
     * Uses fuzzy matching to find the best keyword match in each list item
     *
     * @param  string  $content  Content to process
     * @param  array  $keywords  Keywords to link
     *
     * @return string Content with links added to list items
     */
    private function addServiceLinksInListItems( string $content, array $keywords ): string {
        // Pattern to extract individual list items
        preg_match_all( '/(<li[^>]*>)(.*?)(<\/li>)/is', $content, $list_items, PREG_SET_ORDER );

        foreach ( $list_items as $list_item ) {
            $li_opening = $list_item[1];  // <li> or <li class="...">
            $li_content = $list_item[2];  // The content between <li> and </li>
            $li_closing = $list_item[3];  // </li>
            $original_full_item = $list_item[0];  // Full original list item

            // Skip if this list item already has a link
            if ( strpos( $li_content, '<a href=' ) !== false ) {
                continue;
            }

            // Skip if this list item starts with a <strong> tag (bolded service title)
            // These are the hardcoded service titles that should not be linked
            if ( preg_match( '/^\s*<strong>/i', $li_content ) ) {
                continue;
            }

            // Find the best matching keyword for this entire list item text
            $best_match = $this->findBestKeywordMatch( $li_content, $keywords );

            if ( $best_match ) {
                // Link the matched keyword within the list item content
                $new_li_content = $this->linkKeywordInText(
                    $li_content,
                    $best_match['keyword'],
                    $best_match['url']
                );

                // Reconstruct the list item with the link
                $new_full_item = $li_opening . $new_li_content . $li_closing;

                // Replace in content
                $content = str_replace( $original_full_item, $new_full_item, $content );
            }
        }

        return $content;
    }

    /**
     * Find the best matching keyword within a text string
     * Returns the longest matching keyword (most specific)
     *
     * @param  string  $text  Text to search within
     * @param  array  $keywords  Array of keywords to URLs
     *
     * @return array|null Array with 'keyword' and 'url' keys, or null if no match
     */
    private function findBestKeywordMatch( string $text, array $keywords ): ?array {
        $matches = [];
        $text_lower = strtolower( $text );

        // Find all keywords that appear in the text
        foreach ( $keywords as $keyword => $url ) {
            $keyword_lower = strtolower( $keyword );

            // Check if this keyword appears in the text (case-insensitive substring match)
            if ( strpos( $text_lower, $keyword_lower ) !== false ) {
                $matches[] = [
                    'keyword' => $keyword,
                    'url'     => $url,
                    'length'  => strlen( $keyword ),
                ];
            }
        }

        // If no matches found, return null
        if ( empty( $matches ) ) {
            return null;
        }

        // Sort by length (descending) to get the longest/most specific match
        usort( $matches, function( $a, $b ) {
            return $b['length'] - $a['length'];
        } );

        // Return the longest match
        return $matches[0];
    }

    /**
     * Link a keyword within text, preserving the original case from the text
     *
     * @param  string  $text  Text containing the keyword
     * @param  string  $keyword  Keyword to link
     * @param  string  $url  URL to link to
     *
     * @return string Text with keyword linked
     */
    private function linkKeywordInText( string $text, string $keyword, string $url ): string {
        // Case-insensitive search for the keyword in the text
        $pattern = '/(' . preg_quote( $keyword, '/' ) . ')/i';

        // Replace with link, preserving original case from text
        return preg_replace(
            $pattern,
            '<a href="' . esc_url( $url ) . '">$1</a>',
            $text,
            1  // Only replace first occurrence
        );
    }

    /**
     * Add location-specific internal links
     *
     * @param  string  $content  Content to process
     * @param  array  $context  Context with state, city info
     *
     * @return string Content with location links added
     */
    private function addLocationLinks( string $content, array $context = [] ): string {
        $state = $context['state'] ?? null;
        $city  = $context['city'] ?? null;
        $cities = $context['cities'] ?? null;

        if ( $state ) {
            // For state pages: Link city names to their respective city pages
            if ( ! $city && $cities ) {
                foreach ( $cities as $city_name ) {
                    $city_url = $this->generateCityUrl( $state, $city_name );

                    // Only link if this URL doesn't already exist in content
                    if ( strpos( $content, $city_url ) !== false ) {
                        continue;
                    }

                    // Check if city name is already inside ANY link (not just this specific URL)
                    $already_linked_pattern = '/<a\s+href=["\'][^"\']*["\'][^>]*>\s*' . preg_quote( $city_name, '/' ) . '\s*<\/a>/i';
                    if ( preg_match( $already_linked_pattern, $content ) ) {
                        continue;
                    }

                    // Create a pattern that matches the city name as a whole word
                    $pattern = '/\b(' . preg_quote( $city_name, '/' ) . ')\b/';

                    // Split content by HTML tags to avoid matching text inside tags
                    $parts = preg_split( '/(<[^>]+>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
                    $replaced = false;

                    foreach ( $parts as $index => $part ) {
                        // Skip HTML tags and empty parts
                        if ( empty( $part ) || preg_match( '/^<[^>]+>$/', $part ) ) {
                            continue;
                        }

                        // Skip if we already replaced once
                        if ( $replaced ) {
                            continue;
                        }

                        // Check if city name exists in this text part
                        if ( preg_match( $pattern, $part ) ) {
                            $parts[$index] = preg_replace(
                                $pattern,
                                '<a href="' . esc_url( $city_url ) . '">$1</a>',
                                $part,
                                1
                            );
                            $replaced = true;
                        }
                    }

                    // Rejoin the parts
                    $content = implode( '', $parts );
                }
            }
            // For city pages: Link to state page
            elseif ( $city ) {
                $state_url = $this->generateStateUrl( $state );

                // Only add link if it doesn't already exist
                if ( strpos( $content, $state_url ) !== false ) {
                    return $content;
                }

                // Check if state name is already inside ANY link
                $already_linked_pattern = '/<a\s+href=["\'][^"\']*["\'][^>]*>\s*' . preg_quote( $state, '/' ) . '\s*<\/a>/i';
                if ( preg_match( $already_linked_pattern, $content ) ) {
                    return $content;
                }

                $pattern = '/\b(' . preg_quote( $state, '/' ) . ')\b/';

                // Split content by HTML tags to avoid matching text inside tags
                $parts = preg_split( '/(<[^>]+>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
                $replaced = false;

                foreach ( $parts as $index => $part ) {
                    // Skip HTML tags and empty parts
                    if ( empty( $part ) || preg_match( '/^<[^>]+>$/', $part ) ) {
                        continue;
                    }

                    // Skip if we already replaced once
                    if ( $replaced ) {
                        continue;
                    }

                    // Check if state name exists in this text part
                    if ( preg_match( $pattern, $part ) ) {
                        $parts[$index] = preg_replace(
                            $pattern,
                            '<a href="' . esc_url( $state_url ) . '">$1</a>',
                            $part,
                            1
                        );
                        $replaced = true;
                    }
                }

                // Rejoin the parts
                $content = implode( '', $parts );
            }
        }

        return $content;
    }

    /**
     * Format headings with proper HTML structure
     *
     * @param  string  $content  Content to process
     *
     * @return string Content with formatted headings
     */
    private function formatHeadings( string $content ): string {
        // Convert markdown-style headings to HTML
        $content = preg_replace( '/^### (.+)$/m', '<h3>$1</h3>', $content );
        $content = preg_replace( '/^## (.+)$/m', '<h2>$1</h2>', $content );
        $content = preg_replace( '/^# (.+)$/m', '<h1>$1</h1>', $content );

        // Ensure proper spacing around headings
        $content = preg_replace( '/(<h[1-6]>.*?<\/h[1-6]>)/', "\n\n$1\n\n", $content );

        // Clean up extra whitespace created
        $content = preg_replace( '/\n{3,}/', "\n\n", $content );

        return $content;
    }

    /**
     * Add WordPress block structure to content
     *
     * @param  string  $content  Content to process
     *
     * @return string Content with block structure
     */
    private function addBlockStructure( string $content ): string {
        // Check if content already has WordPress block markup
        if ( strpos( $content, '<!-- wp:' ) !== false ) {
            // Content already has block markup, don't add it again
            return $content;
        }

        // Only add block structure if it doesn't already exist
        $blocks     = [];
        $paragraphs = explode( "\n\n", $content );

        foreach ( $paragraphs as $paragraph ) {
            $paragraph = trim( $paragraph );
            if ( empty( $paragraph ) ) {
                continue;
            }

            // Check if this is a heading
            if ( preg_match( '/^<h([1-6])>(.*?)<\/h[1-6]>$/', $paragraph, $matches ) ) {
                $level    = $matches[1];
                $text     = $matches[2];
                $blocks[] = '<!-- wp:heading {"level":' . $level . '} -->';
                $blocks[] = '<h' . $level . '>' . $text . '</h' . $level . '>';
                $blocks[] = '<!-- /wp:heading -->';
            }
            else {
                // This is a paragraph
                $blocks[] = '<!-- wp:paragraph -->';
                $blocks[] = '<p>' . $paragraph . '</p>';
                $blocks[] = '<!-- /wp:paragraph -->';
            }

            $blocks[] = ''; // Add spacing between blocks
        }

        return implode( "\n", $blocks );
    }

    /**
     * Generate state page URL
     *
     * @param  string  $state  State name
     *
     * @return string State page URL
     */
    private function generateStateUrl( string $state ): string {
        $slug = sanitize_title( $state );
        return home_url( "/wordpress-development-services-usa/{$slug}/" );
    }

    /**
     * Generate city page URL
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return string City page URL
     */
    public function generateCityUrl( string $state, string $city ): string {
        $state_slug = sanitize_title( $state );
        $city_slug  = sanitize_title( $city );
        return home_url( "/wordpress-development-services-usa/{$state_slug}/{$city_slug}/" );
    }

    /**
     * Extract and validate content sections
     *
     * @param  string  $content  Raw content
     *
     * @return array Sections array with title, meta_description, content
     */
    public function extractContentSections( string $content ): array {
        $sections = [
            'title'            => '',
            'meta_description' => '',
            'content'          => '',
            'excerpt'          => '',
        ];

        // Try to extract title from the first heading
        if ( preg_match( '/^#+ (.+)$/m', $content, $matches ) ) {
            $sections['title'] = trim( $matches[1] );
        }

        // Try to extract meta description from content
        $first_paragraph = $this->getFirstParagraph( $content );
        if ( $first_paragraph ) {
            $sections['meta_description'] = $this->createMetaDescription( $first_paragraph );
            $sections['excerpt']          = wp_trim_words( $first_paragraph, 30 );
        }

        $sections['content'] = $content;

        return $sections;
    }

    /**
     * Get the first paragraph from content
     *
     * @param  string  $content  Content to analyze
     *
     * @return string First paragraph
     */
    private function getFirstParagraph( string $content ): string {
        // Remove headings and get first substantial paragraph
        $content_lines = explode( "\n", $content );

        foreach ( $content_lines as $line ) {
            $line = trim( $line );

            // Skip headings and empty lines
            if ( empty( $line ) || preg_match( '/^#+/', $line ) ) {
                continue;
            }

            // Found first paragraph
            if ( strlen( $line ) > 50 ) {
                return $line;
            }
        }

        return '';
    }

    /**
     * Create SEO-friendly meta description
     *
     * @param  string  $text  Source text
     *
     * @return string Meta description
     */
    private function createMetaDescription( string $text ): string {
        // Strip HTML tags
        $text = strip_tags( $text );

        // Limit to 155 characters for SEO
        $meta_description = wp_trim_words( $text, 25 );

        if ( strlen( $meta_description ) > 155 ) {
            $meta_description = substr( $meta_description, 0, 152 ) . '...';
        }

        return $meta_description;
    }

    /**
     * Validate content quality
     *
     * @param  string  $content  Content to validate
     *
     * @return array Validation result with success boolean and issues array
     */
    public function validateContent( string $content ): array {
        $issues = [];

        // Check minimum length
        $word_count = str_word_count( strip_tags( $content ) );
        if ( $word_count < 300 ) {
            $issues[] = "Content too short: {$word_count} words (minimum 300)";
        }

        // Check for headings
        if ( ! preg_match( '/<h[1-6]>|^#+/', $content ) ) {
            $issues[] = 'No headings found in content';
        }

        // Check for paragraph structure
        $paragraph_count = substr_count( $content, '<p>' ) + substr_count( $content, "\n\n" );
        if ( $paragraph_count < 3 ) {
            $issues[] = 'Content lacks proper paragraph structure';
        }

        return [
            'success'    => empty( $issues ),
            'issues'     => $issues,
            'word_count' => $word_count,
        ];
    }

    /**
     * Clean and format text for display
     *
     * @param  string  $text  Text to clean
     *
     * @return string Cleaned text
     */
    public function cleanText( string $text ): string {
        // Remove HTML tags
        $text = strip_tags( $text );

        // Normalize whitespace
        $text = preg_replace( '/\s+/', ' ', $text );

        // Trim
        return trim( $text );
    }
}
