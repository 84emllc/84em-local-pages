<?php
/**
 * Testimonial Block ID Finder
 *
 * WP-CLI command to find and display testimonial block IDs.
 * Run: wp local-pages find-testimonial-ids
 *
 * @package EightyFourEM\LocalPages\Cli
 * @license MIT License
 */

namespace EightyFourEM\LocalPages\Cli;

use WP_CLI;

/**
 * Helper command to find testimonial block IDs
 */
class TestimonialIdFinder {

    /**
     * Pattern title to key mapping
     *
     * @var array<string, string>
     */
    private const PATTERN_MAPPINGS = [
        'Testimonial: CQ Concepts 2'           => 'cq-concepts-2',
        'Testimonial: Red Lab'                 => 'red-lab',
        'Testimonial: Followbright'            => 'followbright',
        'Testimonial: The Pinnacle Group (short)' => 'pinnacle-short',
        'Testimonial: Panacea'                 => 'panacea',
        'Testimonial: Mike Hedding (short)'    => 'mike-hedding-short',
        'Testimonial: Equilibria'              => 'equilibria',
        'Testimonial: Red Lab 2'               => 'red-lab-2',
        'Testimonial: Followbright 2 (short)'  => 'followbright-2-short',
    ];

    /**
     * Find testimonial block IDs and output as PHP config
     *
     * ## EXAMPLES
     *
     *     wp local-pages find-testimonial-ids
     *
     * @when after_wp_load
     */
    public function __invoke(): void {
        // Get all published wp_block posts and filter by title
        $all_blocks = get_posts( [
            'post_type'   => 'wp_block',
            'post_status' => 'publish',
            'numberposts' => 200,
        ] );

        // Filter to only testimonial patterns
        $patterns = array_filter( $all_blocks, function( $block ) {
            return str_starts_with( $block->post_title, 'Testimonial:' );
        } );

        if ( empty( $patterns ) ) {
            WP_CLI::warning( 'No testimonial patterns found. Make sure they are imported as synced patterns.' );
            return;
        }

        WP_CLI::log( "\n=== Found Testimonial Patterns ===" );
        WP_CLI::log( '' );

        $found_ids = [];

        foreach ( $patterns as $pattern ) {
            $title = $pattern->post_title;
            $id    = $pattern->ID;

            // Check if this matches one of our expected patterns
            $key = self::PATTERN_MAPPINGS[ $title ] ?? null;

            if ( $key ) {
                $found_ids[ $key ] = $id;
                WP_CLI::log( "✓ {$title}" );
                WP_CLI::log( "  Key: '{$key}' => {$id}" );
            }
            else {
                WP_CLI::log( "? {$title} (ID: {$id}) - not in mapping" );
            }
            WP_CLI::log( '' );
        }

        // Output as PHP code
        WP_CLI::log( "\n=== Copy this to TestimonialBlockIds.php ===" );
        WP_CLI::log( '' );
        WP_CLI::log( 'public const IDS = [' );

        foreach ( self::PATTERN_MAPPINGS as $title => $key ) {
            $id = $found_ids[ $key ] ?? 0;
            $status = $id ? '' : ' // NOT FOUND';
            WP_CLI::log( "    '{$key}' => {$id},{$status}" );
        }

        WP_CLI::log( '];' );
        WP_CLI::log( '' );

        // Summary
        $found_count = count( array_filter( $found_ids ) );
        $total_count = count( self::PATTERN_MAPPINGS );

        if ( $found_count === $total_count ) {
            WP_CLI::success( "Found all {$total_count} testimonial patterns!" );
        }
        else {
            WP_CLI::warning( "Found {$found_count} of {$total_count} patterns. Missing patterns need to be imported." );
        }
    }
}
