<?php
/**
 * Location Context Provider
 *
 * Provides contextual information about states and cities for content generation.
 * Data is pre-generated to avoid runtime API calls.
 *
 * @package EightyFourEM\LocalPages\Data
 * @license MIT License
 */

namespace EightyFourEM\LocalPages\Data;

/**
 * Provides location-specific context for content generation
 */
class LocationContextProvider {

    /**
     * State context data
     * Generated once, stored statically
     *
     * @var array<string, array{
     *     industries: array<string>,
     *     context: string,
     *     business_angle: string,
     *     is_home_state: bool
     * }>
     */
    private const STATE_CONTEXT = [
        'Alabama' => [
            'industries'     => [ 'automotive', 'aerospace', 'healthcare', 'manufacturing' ],
            'context'        => 'Growing tech sector alongside traditional manufacturing',
            'business_angle' => 'manufacturing and aerospace companies modernizing their web presence',
            'is_home_state'  => false,
        ],
        'Alaska' => [
            'industries'     => [ 'oil and gas', 'fishing', 'tourism', 'healthcare' ],
            'context'        => 'Remote businesses needing reliable digital infrastructure',
            'business_angle' => 'businesses serving remote communities and tourism operators',
            'is_home_state'  => false,
        ],
        'Arizona' => [
            'industries'     => [ 'tech', 'healthcare', 'real estate', 'tourism', 'aerospace' ],
            'context'        => 'Fast-growing tech hub with major corporate relocations',
            'business_angle' => 'tech startups and growing businesses in the Phoenix corridor',
            'is_home_state'  => false,
        ],
        'Arkansas' => [
            'industries'     => [ 'retail', 'agriculture', 'healthcare', 'logistics' ],
            'context'        => 'Home to major retail headquarters with growing tech needs',
            'business_angle' => 'businesses in retail, logistics, and agriculture technology',
            'is_home_state'  => false,
        ],
        'California' => [
            'industries'     => [ 'tech', 'entertainment', 'healthcare', 'finance', 'agriculture' ],
            'context'        => 'Global tech center with high expectations for web performance',
            'business_angle' => 'startups, agencies, and enterprises expecting top-tier development',
            'is_home_state'  => false,
        ],
        'Colorado' => [
            'industries'     => [ 'tech', 'aerospace', 'outdoor recreation', 'healthcare', 'cannabis' ],
            'context'        => 'Booming tech scene with outdoor industry growth',
            'business_angle' => 'tech companies and outdoor brands building digital presence',
            'is_home_state'  => false,
        ],
        'Connecticut' => [
            'industries'     => [ 'finance', 'insurance', 'healthcare', 'manufacturing', 'biotech' ],
            'context'        => 'Insurance and finance hub with pharmaceutical presence',
            'business_angle' => 'financial services and insurance companies with compliance needs',
            'is_home_state'  => false,
        ],
        'Delaware' => [
            'industries'     => [ 'finance', 'healthcare', 'chemicals', 'legal services' ],
            'context'        => 'Corporate headquarters state with finance focus',
            'business_angle' => 'corporate entities and financial services firms',
            'is_home_state'  => false,
        ],
        'Florida' => [
            'industries'     => [ 'tourism', 'healthcare', 'real estate', 'finance', 'aerospace' ],
            'context'        => 'Major tourism and healthcare market with growing tech scene',
            'business_angle' => 'tourism operators, healthcare providers, and real estate firms',
            'is_home_state'  => false,
        ],
        'Georgia' => [
            'industries'     => [ 'logistics', 'film', 'fintech', 'healthcare', 'agriculture' ],
            'context'        => 'Major logistics hub with growing fintech sector',
            'business_angle' => 'logistics companies and fintech startups in the Atlanta metro',
            'is_home_state'  => false,
        ],
        'Hawaii' => [
            'industries'     => [ 'tourism', 'military', 'healthcare', 'agriculture' ],
            'context'        => 'Tourism-dependent economy needing reliable web infrastructure',
            'business_angle' => 'tourism businesses and hospitality operators',
            'is_home_state'  => false,
        ],
        'Idaho' => [
            'industries'     => [ 'tech', 'agriculture', 'manufacturing', 'healthcare' ],
            'context'        => 'Growing tech presence in Boise with agricultural roots',
            'business_angle' => 'tech companies and agribusinesses modernizing operations',
            'is_home_state'  => false,
        ],
        'Illinois' => [
            'industries'     => [ 'finance', 'manufacturing', 'healthcare', 'logistics', 'tech' ],
            'context'        => 'Major financial center with diverse business base',
            'business_angle' => 'financial services, manufacturing, and logistics companies',
            'is_home_state'  => false,
        ],
        'Indiana' => [
            'industries'     => [ 'manufacturing', 'healthcare', 'logistics', 'agriculture', 'life sciences' ],
            'context'        => 'Manufacturing hub with growing life sciences sector',
            'business_angle' => 'manufacturers and life sciences companies',
            'is_home_state'  => false,
        ],
        'Iowa' => [
            'industries'     => [ 'agriculture', 'insurance', 'manufacturing', 'healthcare', 'fintech' ],
            'context'        => 'Insurance hub with strong agriculture and growing fintech',
            'business_angle' => 'insurance companies, agribusinesses, and fintech startups',
            'is_home_state'  => true,
        ],
        'Kansas' => [
            'industries'     => [ 'agriculture', 'aerospace', 'healthcare', 'manufacturing' ],
            'context'        => 'Aerospace manufacturing center with agricultural base',
            'business_angle' => 'aerospace manufacturers and agricultural technology companies',
            'is_home_state'  => false,
        ],
        'Kentucky' => [
            'industries'     => [ 'automotive', 'logistics', 'healthcare', 'bourbon', 'manufacturing' ],
            'context'        => 'Automotive and logistics hub with bourbon industry',
            'business_angle' => 'automotive suppliers and logistics operators',
            'is_home_state'  => false,
        ],
        'Louisiana' => [
            'industries'     => [ 'oil and gas', 'tourism', 'shipping', 'healthcare', 'petrochemicals' ],
            'context'        => 'Energy sector and port commerce with tourism',
            'business_angle' => 'energy companies and tourism operators',
            'is_home_state'  => false,
        ],
        'Maine' => [
            'industries'     => [ 'tourism', 'healthcare', 'fishing', 'manufacturing', 'technology' ],
            'context'        => 'Tourism and outdoor recreation with craft manufacturing',
            'business_angle' => 'tourism operators and small manufacturers',
            'is_home_state'  => false,
        ],
        'Maryland' => [
            'industries'     => [ 'biotech', 'cybersecurity', 'healthcare', 'government', 'defense' ],
            'context'        => 'Government contracting and biotech corridor',
            'business_angle' => 'government contractors and biotech companies',
            'is_home_state'  => false,
        ],
        'Massachusetts' => [
            'industries'     => [ 'biotech', 'education', 'healthcare', 'finance', 'tech' ],
            'context'        => 'Biotech and education hub with venture capital presence',
            'business_angle' => 'biotech startups and educational institutions',
            'is_home_state'  => false,
        ],
        'Michigan' => [
            'industries'     => [ 'automotive', 'manufacturing', 'healthcare', 'tech', 'agriculture' ],
            'context'        => 'Automotive industry transitioning to EV and tech',
            'business_angle' => 'automotive companies and tech startups in Detroit and Ann Arbor',
            'is_home_state'  => false,
        ],
        'Minnesota' => [
            'industries'     => [ 'healthcare', 'finance', 'retail', 'manufacturing', 'tech' ],
            'context'        => 'Fortune 500 headquarters with strong healthcare sector',
            'business_angle' => 'healthcare companies and corporate headquarters',
            'is_home_state'  => false,
        ],
        'Mississippi' => [
            'industries'     => [ 'manufacturing', 'agriculture', 'healthcare', 'shipbuilding' ],
            'context'        => 'Manufacturing and shipbuilding with agricultural base',
            'business_angle' => 'manufacturers and agricultural businesses',
            'is_home_state'  => false,
        ],
        'Missouri' => [
            'industries'     => [ 'healthcare', 'finance', 'manufacturing', 'agriculture', 'logistics' ],
            'context'        => 'Central hub for healthcare and financial services',
            'business_angle' => 'healthcare systems and financial services firms',
            'is_home_state'  => false,
        ],
        'Montana' => [
            'industries'     => [ 'tourism', 'agriculture', 'healthcare', 'tech', 'mining' ],
            'context'        => 'Remote work destination with tourism focus',
            'business_angle' => 'tourism operators and remote-friendly businesses',
            'is_home_state'  => false,
        ],
        'Nebraska' => [
            'industries'     => [ 'agriculture', 'insurance', 'finance', 'healthcare', 'logistics' ],
            'context'        => 'Insurance and agribusiness hub with Warren Buffett presence',
            'business_angle' => 'insurance companies and agribusinesses',
            'is_home_state'  => false,
        ],
        'Nevada' => [
            'industries'     => [ 'gaming', 'tourism', 'logistics', 'tech', 'mining' ],
            'context'        => 'Gaming and tourism with growing tech relocations',
            'business_angle' => 'hospitality operators and tech companies relocating from California',
            'is_home_state'  => false,
        ],
        'New Hampshire' => [
            'industries'     => [ 'tech', 'healthcare', 'manufacturing', 'tourism', 'finance' ],
            'context'        => 'No income tax state attracting business headquarters',
            'business_angle' => 'tech companies and businesses relocating for tax advantages',
            'is_home_state'  => false,
        ],
        'New Jersey' => [
            'industries'     => [ 'pharma', 'finance', 'healthcare', 'logistics', 'tech' ],
            'context'        => 'Pharmaceutical corridor with NYC metro business access',
            'business_angle' => 'pharmaceutical companies and financial services firms',
            'is_home_state'  => false,
        ],
        'New Mexico' => [
            'industries'     => [ 'aerospace', 'government', 'healthcare', 'tourism', 'energy' ],
            'context'        => 'National labs and aerospace with tourism',
            'business_angle' => 'government contractors and aerospace companies',
            'is_home_state'  => false,
        ],
        'New York' => [
            'industries'     => [ 'finance', 'media', 'tech', 'healthcare', 'fashion' ],
            'context'        => 'Global financial and media capital',
            'business_angle' => 'financial services, media companies, and tech startups',
            'is_home_state'  => false,
        ],
        'North Carolina' => [
            'industries'     => [ 'banking', 'tech', 'healthcare', 'biotech', 'manufacturing' ],
            'context'        => 'Banking center with Research Triangle tech hub',
            'business_angle' => 'banks, tech companies, and biotech firms',
            'is_home_state'  => false,
        ],
        'North Dakota' => [
            'industries'     => [ 'energy', 'agriculture', 'healthcare', 'technology' ],
            'context'        => 'Energy boom state with agricultural base',
            'business_angle' => 'energy companies and agricultural technology',
            'is_home_state'  => false,
        ],
        'Ohio' => [
            'industries'     => [ 'manufacturing', 'healthcare', 'finance', 'logistics', 'tech' ],
            'context'        => 'Diverse manufacturing with growing tech scenes',
            'business_angle' => 'manufacturers and healthcare systems',
            'is_home_state'  => false,
        ],
        'Oklahoma' => [
            'industries'     => [ 'energy', 'aerospace', 'agriculture', 'healthcare' ],
            'context'        => 'Energy sector with aerospace manufacturing',
            'business_angle' => 'energy companies and aerospace manufacturers',
            'is_home_state'  => false,
        ],
        'Oregon' => [
            'industries'     => [ 'tech', 'outdoor recreation', 'manufacturing', 'healthcare', 'agriculture' ],
            'context'        => 'Tech hub with outdoor brand headquarters',
            'business_angle' => 'tech companies and outdoor recreation brands',
            'is_home_state'  => false,
        ],
        'Pennsylvania' => [
            'industries'     => [ 'healthcare', 'finance', 'manufacturing', 'education', 'tech' ],
            'context'        => 'Healthcare and education hub with pharma presence',
            'business_angle' => 'healthcare systems, universities, and pharmaceutical companies',
            'is_home_state'  => false,
        ],
        'Rhode Island' => [
            'industries'     => [ 'healthcare', 'education', 'defense', 'tourism', 'manufacturing' ],
            'context'        => 'Healthcare and defense with education institutions',
            'business_angle' => 'healthcare providers and defense contractors',
            'is_home_state'  => false,
        ],
        'South Carolina' => [
            'industries'     => [ 'automotive', 'aerospace', 'tourism', 'manufacturing', 'healthcare' ],
            'context'        => 'Automotive manufacturing hub with coastal tourism',
            'business_angle' => 'automotive manufacturers and tourism operators',
            'is_home_state'  => false,
        ],
        'South Dakota' => [
            'industries'     => [ 'finance', 'healthcare', 'agriculture', 'tourism' ],
            'context'        => 'Business-friendly tax environment with credit card industry',
            'business_angle' => 'financial services companies and agricultural businesses',
            'is_home_state'  => false,
        ],
        'Tennessee' => [
            'industries'     => [ 'healthcare', 'music', 'automotive', 'logistics', 'tech' ],
            'context'        => 'Healthcare capital with music industry and automotive growth',
            'business_angle' => 'healthcare companies and entertainment businesses',
            'is_home_state'  => false,
        ],
        'Texas' => [
            'industries'     => [ 'energy', 'tech', 'healthcare', 'aerospace', 'finance' ],
            'context'        => 'Major tech relocations and energy industry hub',
            'business_angle' => 'tech companies, energy firms, and corporate headquarters',
            'is_home_state'  => false,
        ],
        'Utah' => [
            'industries'     => [ 'tech', 'outdoor recreation', 'healthcare', 'finance', 'tourism' ],
            'context'        => 'Silicon Slopes tech hub with outdoor industry',
            'business_angle' => 'tech startups and outdoor recreation companies',
            'is_home_state'  => false,
        ],
        'Vermont' => [
            'industries'     => [ 'tourism', 'agriculture', 'healthcare', 'manufacturing', 'tech' ],
            'context'        => 'Small business focus with tourism and craft industries',
            'business_angle' => 'tourism operators and craft manufacturers',
            'is_home_state'  => false,
        ],
        'Virginia' => [
            'industries'     => [ 'government', 'defense', 'tech', 'healthcare', 'agriculture' ],
            'context'        => 'Government contracting and tech corridor in Northern Virginia',
            'business_angle' => 'government contractors and tech companies',
            'is_home_state'  => false,
        ],
        'Washington' => [
            'industries'     => [ 'tech', 'aerospace', 'healthcare', 'retail', 'agriculture' ],
            'context'        => 'Tech giant headquarters with aerospace manufacturing',
            'business_angle' => 'tech companies and aerospace manufacturers',
            'is_home_state'  => false,
        ],
        'West Virginia' => [
            'industries'     => [ 'energy', 'healthcare', 'tourism', 'manufacturing' ],
            'context'        => 'Energy transition state with healthcare focus',
            'business_angle' => 'healthcare providers and energy companies',
            'is_home_state'  => false,
        ],
        'Wisconsin' => [
            'industries'     => [ 'manufacturing', 'healthcare', 'agriculture', 'insurance', 'tech' ],
            'context'        => 'Manufacturing and dairy with growing tech presence',
            'business_angle' => 'manufacturers and agricultural technology companies',
            'is_home_state'  => false,
        ],
        'Wyoming' => [
            'industries'     => [ 'energy', 'tourism', 'agriculture', 'cryptocurrency' ],
            'context'        => 'Energy and tourism with crypto-friendly regulations',
            'business_angle' => 'energy companies and cryptocurrency businesses',
            'is_home_state'  => false,
        ],
    ];

    /**
     * Major city context data
     * Key format: "City, State"
     *
     * @var array<string, array{industries: array<string>, context: string}>
     */
    private const CITY_CONTEXT = [
        // California
        'Los Angeles, California' => [
            'industries' => [ 'entertainment', 'tech', 'aerospace', 'fashion', 'ports' ],
            'context'    => 'Entertainment and creative industry capital',
        ],
        'San Francisco, California' => [
            'industries' => [ 'tech', 'finance', 'biotech', 'tourism' ],
            'context'    => 'Global tech and startup hub',
        ],
        'San Diego, California' => [
            'industries' => [ 'biotech', 'defense', 'tourism', 'tech' ],
            'context'    => 'Biotech cluster with military presence',
        ],
        'San Jose, California' => [
            'industries' => [ 'tech', 'semiconductors', 'manufacturing' ],
            'context'    => 'Heart of Silicon Valley',
        ],

        // Texas
        'Houston, Texas' => [
            'industries' => [ 'energy', 'healthcare', 'aerospace', 'shipping' ],
            'context'    => 'Energy capital with major medical center',
        ],
        'Austin, Texas' => [
            'industries' => [ 'tech', 'government', 'music', 'film' ],
            'context'    => 'Fast-growing tech hub with startup culture',
        ],
        'Dallas, Texas' => [
            'industries' => [ 'finance', 'tech', 'telecommunications', 'healthcare' ],
            'context'    => 'Corporate headquarters hub',
        ],
        'San Antonio, Texas' => [
            'industries' => [ 'military', 'healthcare', 'tourism', 'cybersecurity' ],
            'context'    => 'Military city with growing cybersecurity sector',
        ],

        // Iowa (home state - more detail)
        'Des Moines, Iowa' => [
            'industries' => [ 'insurance', 'finance', 'healthcare', 'tech' ],
            'context'    => 'Insurance capital with fintech growth',
        ],
        'Cedar Rapids, Iowa' => [
            'industries' => [ 'manufacturing', 'healthcare', 'technology', 'food processing' ],
            'context'    => 'Manufacturing hub with diverse business base',
        ],
        'Davenport, Iowa' => [
            'industries' => [ 'manufacturing', 'healthcare', 'logistics' ],
            'context'    => 'Quad Cities manufacturing center',
        ],
        'Iowa City, Iowa' => [
            'industries' => [ 'education', 'healthcare', 'tech', 'research' ],
            'context'    => 'University town with healthcare and research',
        ],
        'West Des Moines, Iowa' => [
            'industries' => [ 'insurance', 'finance', 'tech', 'retail' ],
            'context'    => 'Major insurance and financial services hub',
        ],
        'Ames, Iowa' => [
            'industries' => [ 'education', 'research', 'agriculture', 'tech' ],
            'context'    => 'University research hub with ag-tech focus',
        ],

        // New York
        'New York City, New York' => [
            'industries' => [ 'finance', 'media', 'fashion', 'tech', 'healthcare' ],
            'context'    => 'Global business and financial center',
        ],
        'Buffalo, New York' => [
            'industries' => [ 'healthcare', 'education', 'manufacturing', 'tech' ],
            'context'    => 'Revitalizing economy with healthcare focus',
        ],

        // Florida
        'Miami, Florida' => [
            'industries' => [ 'finance', 'tourism', 'real estate', 'tech', 'international trade' ],
            'context'    => 'Latin American business gateway with growing tech scene',
        ],
        'Tampa, Florida' => [
            'industries' => [ 'finance', 'healthcare', 'tech', 'tourism' ],
            'context'    => 'Financial services hub with tech growth',
        ],
        'Orlando, Florida' => [
            'industries' => [ 'tourism', 'simulation', 'healthcare', 'tech' ],
            'context'    => 'Theme park capital with simulation tech industry',
        ],

        // Illinois
        'Chicago, Illinois' => [
            'industries' => [ 'finance', 'manufacturing', 'tech', 'healthcare', 'logistics' ],
            'context'    => 'Major financial and transportation hub',
        ],

        // Pennsylvania
        'Philadelphia, Pennsylvania' => [
            'industries' => [ 'healthcare', 'education', 'pharma', 'finance' ],
            'context'    => 'Healthcare and education center',
        ],
        'Pittsburgh, Pennsylvania' => [
            'industries' => [ 'tech', 'healthcare', 'education', 'robotics' ],
            'context'    => 'Tech renaissance with robotics and AI focus',
        ],

        // Georgia
        'Atlanta, Georgia' => [
            'industries' => [ 'logistics', 'film', 'fintech', 'healthcare', 'tech' ],
            'context'    => 'Major logistics hub with growing fintech',
        ],

        // Massachusetts
        'Boston, Massachusetts' => [
            'industries' => [ 'biotech', 'education', 'healthcare', 'finance', 'tech' ],
            'context'    => 'Biotech and education powerhouse',
        ],

        // Washington
        'Seattle, Washington' => [
            'industries' => [ 'tech', 'aerospace', 'retail', 'healthcare' ],
            'context'    => 'Tech giant headquarters and aerospace hub',
        ],

        // Colorado
        'Denver, Colorado' => [
            'industries' => [ 'tech', 'aerospace', 'healthcare', 'energy', 'cannabis' ],
            'context'    => 'Growing tech scene with outdoor lifestyle',
        ],

        // Arizona
        'Phoenix, Arizona' => [
            'industries' => [ 'tech', 'healthcare', 'real estate', 'finance', 'semiconductors' ],
            'context'    => 'Fast-growing tech and semiconductor hub',
        ],

        // North Carolina
        'Charlotte, North Carolina' => [
            'industries' => [ 'banking', 'finance', 'healthcare', 'tech' ],
            'context'    => 'Major banking center',
        ],
        'Raleigh, North Carolina' => [
            'industries' => [ 'tech', 'biotech', 'education', 'healthcare' ],
            'context'    => 'Research Triangle tech and biotech hub',
        ],

        // Michigan
        'Detroit, Michigan' => [
            'industries' => [ 'automotive', 'tech', 'healthcare', 'manufacturing' ],
            'context'    => 'Automotive industry transitioning to EV and mobility tech',
        ],

        // Minnesota
        'Minneapolis, Minnesota' => [
            'industries' => [ 'healthcare', 'retail', 'finance', 'tech', 'manufacturing' ],
            'context'    => 'Fortune 500 headquarters concentration',
        ],

        // Tennessee
        'Nashville, Tennessee' => [
            'industries' => [ 'healthcare', 'music', 'tech', 'tourism' ],
            'context'    => 'Healthcare capital with entertainment industry',
        ],

        // Oregon
        'Portland, Oregon' => [
            'industries' => [ 'tech', 'outdoor recreation', 'manufacturing', 'creative' ],
            'context'    => 'Tech hub with outdoor brand headquarters',
        ],

        // Nevada
        'Las Vegas, Nevada' => [
            'industries' => [ 'gaming', 'tourism', 'conventions', 'tech' ],
            'context'    => 'Entertainment and convention capital',
        ],

        // Utah
        'Salt Lake City, Utah' => [
            'industries' => [ 'tech', 'outdoor recreation', 'finance', 'healthcare' ],
            'context'    => 'Silicon Slopes tech hub',
        ],
    ];

    /**
     * Get context for a state
     *
     * @param string $state State name
     *
     * @return array{industries: array<string>, context: string, business_angle: string, is_home_state: bool}|null
     */
    public function getStateContext( string $state ): ?array {
        return self::STATE_CONTEXT[ $state ] ?? null;
    }

    /**
     * Get context for a city
     *
     * @param string $city  City name
     * @param string $state State name
     *
     * @return array{industries: array<string>, context: string}|null
     */
    public function getCityContext( string $city, string $state ): ?array {
        $key = "{$city}, {$state}";

        if ( isset( self::CITY_CONTEXT[ $key ] ) ) {
            return self::CITY_CONTEXT[ $key ];
        }

        // Fall back to state context for cities we don't have specific data for
        $state_context = $this->getStateContext( $state );

        if ( $state_context ) {
            return [
                'industries' => $state_context['industries'],
                'context'    => "Business hub in {$state}",
            ];
        }

        return null;
    }

    /**
     * Get industries as comma-separated string
     *
     * @param string      $state State name
     * @param string|null $city  Optional city name
     *
     * @return string
     */
    public function getIndustriesString( string $state, ?string $city = null ): string {
        if ( $city ) {
            $context = $this->getCityContext( $city, $state );
        }
        else {
            $context = $this->getStateContext( $state );
        }

        if ( ! $context || empty( $context['industries'] ) ) {
            return '';
        }

        return implode( ', ', $context['industries'] );
    }

    /**
     * Check if this is the home state (Iowa)
     *
     * @param string $state State name
     *
     * @return bool
     */
    public function isHomeState( string $state ): bool {
        $context = $this->getStateContext( $state );
        return $context['is_home_state'] ?? false;
    }

    /**
     * Get business angle for state
     *
     * @param string $state State name
     *
     * @return string
     */
    public function getBusinessAngle( string $state ): string {
        $context = $this->getStateContext( $state );
        return $context['business_angle'] ?? 'businesses';
    }

    /**
     * Check if city has specific context data
     *
     * @param string $city  City name
     * @param string $state State name
     *
     * @return bool
     */
    public function hasCityContext( string $city, string $state ): bool {
        $key = "{$city}, {$state}";
        return isset( self::CITY_CONTEXT[ $key ] );
    }

    /**
     * Get all states with context
     *
     * @return array<string>
     */
    public function getAllStates(): array {
        return array_keys( self::STATE_CONTEXT );
    }

    /**
     * Get all cities with specific context
     *
     * @return array<string>
     */
    public function getAllCitiesWithContext(): array {
        return array_keys( self::CITY_CONTEXT );
    }
}
