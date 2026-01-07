<?php
/**
 * Testimonial Block IDs Configuration
 *
 * Maps testimonial keys to WordPress synced pattern (reusable block) IDs.
 * Update these values after importing your testimonial patterns to WordPress.
 *
 * To find block IDs:
 * 1. Go to WordPress Admin → Appearance → Patterns (or Editor → Patterns)
 * 2. Find each testimonial pattern
 * 3. The ID is in the URL when editing: /wp-admin/post.php?post=XXX&action=edit
 *
 * @package EightyFourEM\LocalPages\Config
 * @license MIT License
 */

namespace EightyFourEM\LocalPages\Config;

/**
 * Testimonial block ID mappings
 */
class TestimonialBlockIds {

    /**
     * Map of testimonial key => WordPress block ID
     *
     * @var array<string, int>
     */
    public const IDS = [
        'cq-concepts-2'        => 3035,
        'red-lab'              => 3021,
        'followbright'         => 3018,
        'pinnacle-short'       => 10295,
        'panacea'              => 3024,
        'mike-hedding-short'   => 3945,
        'equilibria'           => 3022,
        'red-lab-2'            => 3025,
        'followbright-2-short' => 3946,
    ];

    /**
     * Get all block IDs
     *
     * @return array<string, int>
     */
    public static function getAll(): array {
        return self::IDS;
    }

    /**
     * Get block ID by testimonial key
     *
     * @param string $key Testimonial key
     *
     * @return int|null
     */
    public static function get( string $key ): ?int {
        return self::IDS[ $key ] ?? null;
    }
}
