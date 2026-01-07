<?php
/**
 * Testimonial Provider
 *
 * Provides deterministic randomized testimonial selection for local pages.
 * Uses location-based seeding so the same location always gets the same testimonial,
 * but different locations get different testimonials.
 *
 * @package EightyFourEM\LocalPages\Data
 * @license MIT License
 */

namespace EightyFourEM\LocalPages\Data;

/**
 * Provides testimonials for local pages with deterministic randomization
 */
class TestimonialProvider {

    /**
     * Block IDs for testimonial patterns (must be set after patterns are imported)
     * Map of testimonial key => WordPress block ID
     *
     * @var array<string, int>
     */
    private array $blockIds = [];

    /**
     * Available testimonials for rotation
     * Curated list excluding problematic phrases like "game changer"
     *
     * @var array<string, array{quote: string, source: string, type: string}>
     */
    private const TESTIMONIALS = [
        'cq-concepts-2' => [
            'quote'  => "They're super easy to work with—always quick to respond, on top of any issues, and really know their stuff.",
            'source' => 'President, CQ Concepts Inc.',
            'type'   => 'agency',
        ],
        'red-lab' => [
            'quote'  => "He's detailed, builds for extendability, and delivers projects on time.",
            'source' => 'Founder, Red Lab Technologies',
            'type'   => 'agency',
        ],
        'followbright' => [
            'quote'  => '84EM is nothing but a breath of fresh air in a world of unreliable vendors',
            'source' => 'Founder, Followbright Web Agency',
            'type'   => 'agency',
        ],
        'pinnacle-short' => [
            'quote'  => 'The difference a highly competent developer makes—especially one who is responsive, clear, and reliable in communication—cannot be overstated.',
            'source' => 'Joel Jones, Director of Digital Innovations and Marketing Solutions, The Pinnacle Group',
            'type'   => 'agency',
        ],
        'panacea' => [
            'quote'  => 'Any company looking for his services would be very lucky to have him on their team!',
            'source' => 'COO & Co-Founder, Panacea Financial',
            'type'   => 'business',
        ],
        'mike-hedding-short' => [
            'quote'  => 'He has great technical knowledge and his communication skills are excellent.',
            'source' => 'Mike Hedding Music',
            'type'   => 'business',
        ],
        'equilibria' => [
            'quote'  => 'I feel confident that their work has meaningfully contributed to our extreme growth.',
            'source' => 'Co-Founder, CPO & CTO, Equilibria Inc',
            'type'   => 'business',
        ],
        'red-lab-2' => [
            'quote'  => 'Deadlines were always met. When new challenges arose, 84EM was up for the challenge and worked diligently to make sure the project would still launch on time.',
            'source' => 'Founder, Red Lab Technologies',
            'type'   => 'agency',
        ],
        'followbright-2-short' => [
            'quote'  => '84EM has been maintaining the entire website without issues for years, making the client beyond satisfied.',
            'source' => 'Founder, Followbright Web Agency',
            'type'   => 'agency',
        ],
    ];

    /**
     * Testimonials to use for state pages (broader appeal)
     *
     * @var array<string>
     */
    private const STATE_TESTIMONIALS = [
        'cq-concepts-2',
        'pinnacle-short',
        'followbright',
        'equilibria',
        'panacea',
    ];

    /**
     * Testimonials to use for city pages
     *
     * @var array<string>
     */
    private const CITY_TESTIMONIALS = [
        'cq-concepts-2',
        'red-lab',
        'mike-hedding-short',
        'followbright-2-short',
        'red-lab-2',
        'panacea',
        'equilibria',
        'pinnacle-short',
        'followbright',
    ];

    /**
     * Constructor
     *
     * @param array<string, int> $blockIds Map of testimonial key => WordPress block ID
     */
    public function __construct( array $blockIds = [] ) {
        $this->blockIds = $blockIds;
    }

    /**
     * Set block IDs for testimonial patterns
     *
     * @param array<string, int> $blockIds Map of testimonial key => WordPress block ID
     *
     * @return void
     */
    public function setBlockIds( array $blockIds ): void {
        $this->blockIds = $blockIds;
    }

    /**
     * Get a testimonial for a state page
     *
     * @param string $state State name
     *
     * @return array{key: string, quote: string, source: string, type: string, block_id: int|null}
     */
    public function getForState( string $state ): array {
        return $this->getTestimonial( $state, self::STATE_TESTIMONIALS );
    }

    /**
     * Get a testimonial for a city page
     *
     * @param string $state State name
     * @param string $city  City name
     *
     * @return array{key: string, quote: string, source: string, type: string, block_id: int|null}
     */
    public function getForCity( string $state, string $city ): array {
        $location = "{$city}, {$state}";
        return $this->getTestimonial( $location, self::CITY_TESTIMONIALS );
    }

    /**
     * Get testimonial block reference for WordPress content
     *
     * @param string $state State name
     *
     * @return string WordPress block reference or empty string if no block ID
     */
    public function getStateBlockReference( string $state ): string {
        $testimonial = $this->getForState( $state );

        if ( ! empty( $testimonial['block_id'] ) ) {
            return sprintf( '<!-- wp:block {"ref":%d} /-->', $testimonial['block_id'] );
        }

        return '';
    }

    /**
     * Get testimonial block reference for city page
     *
     * @param string $state State name
     * @param string $city  City name
     *
     * @return string WordPress block reference or empty string if no block ID
     */
    public function getCityBlockReference( string $state, string $city ): string {
        $testimonial = $this->getForCity( $state, $city );

        if ( ! empty( $testimonial['block_id'] ) ) {
            return sprintf( '<!-- wp:block {"ref":%d} /-->', $testimonial['block_id'] );
        }

        return '';
    }

    /**
     * Get all available testimonials
     *
     * @return array<string, array{quote: string, source: string, type: string}>
     */
    public function getAll(): array {
        return self::TESTIMONIALS;
    }

    /**
     * Get testimonial by key
     *
     * @param string $key Testimonial key
     *
     * @return array{quote: string, source: string, type: string}|null
     */
    public function get( string $key ): ?array {
        return self::TESTIMONIALS[ $key ] ?? null;
    }

    /**
     * Select a testimonial using deterministic randomization
     *
     * @param string        $seed    Seed string (location name)
     * @param array<string> $pool    Pool of testimonial keys to choose from
     *
     * @return array{key: string, quote: string, source: string, type: string, block_id: int|null}
     */
    private function getTestimonial( string $seed, array $pool ): array {
        // Create deterministic index from seed
        $hash  = crc32( strtolower( $seed ) );
        $index = abs( $hash ) % count( $pool );
        $key   = $pool[ $index ];

        $testimonial             = self::TESTIMONIALS[ $key ];
        $testimonial['key']      = $key;
        $testimonial['block_id'] = $this->blockIds[ $key ] ?? null;

        return $testimonial;
    }

    /**
     * Preview testimonial distribution across all states
     * Useful for verifying good distribution
     *
     * @param array<string> $states List of state names
     *
     * @return array<string, int> Count of each testimonial used
     */
    public function previewDistribution( array $states ): array {
        $distribution = [];

        foreach ( $states as $state ) {
            $testimonial = $this->getForState( $state );
            $key         = $testimonial['key'];

            if ( ! isset( $distribution[ $key ] ) ) {
                $distribution[ $key ] = 0;
            }
            $distribution[ $key ]++;
        }

        return $distribution;
    }
}
