<?php
/**
 * Schema.org JSON-LD Generator
 *
 * @package EightyFourEM\LocalPages\Schema
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Schema;

use EightyFourEM\LocalPages\Contracts\SchemaGeneratorInterface;

/**
 * Generates Schema.org structured data for local pages
 */
class SchemaGenerator implements SchemaGeneratorInterface {

    /**
     * Generate Schema.org JSON-LD structured data
     *
     * @param  array  $data  Data for schema generation
     *
     * @return string JSON-LD schema
     */
    public function generate( array $data ): string {
        $type = $data['type'] ?? 'state';

        if ( 'state' === $type ) {
            return $this->generateStateSchemaInternal( $data );
        }
        elseif ( 'city' === $type ) {
            return $this->generateCitySchemaInternal( $data );
        }
        elseif ( 'index' === $type ) {
            return $this->generateIndexSchema( $data );
        }

        return '';
    }

    /**
     * Generate schema for a state page
     *
     * @param  string  $state  State name
     *
     * @return string JSON-LD schema
     */
    public function generateStateSchema( string $state ): string {
        return $this->generateStateSchemaInternal( [ 'state' => $state ] );
    }

    /**
     * Generate schema for a state page (internal)
     *
     * @param  array  $data  State data
     *
     * @return string
     */
    private function generateStateSchemaInternal( array $data ): string {
        $state   = $data['state'] ?? '';
        $cities  = $data['cities'] ?? [];
        $post_id = $data['post_id'] ?? null;

        $url = $post_id ? get_permalink( $post_id ) : site_url( '/wordpress-development-services-usa/' . sanitize_title( $state ) . '/' );

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            'name'        => "Web Development & WordPress Services in {$state}",
            'description' => "Professional WordPress development, custom plugins, AI integration, and white-label agency services in {$state}. Cedar Rapids, Iowa-based agency with over 30 years of web development experience serving " . implode( ', ', array_slice( $cities, 0, 3 ) ) . " and businesses across {$state}.",
            'url'         => $url,
            'isPartOf'    => [
                '@type' => 'WebSite',
                'name'  => '84EM - WordPress Development Agency',
                'url'   => site_url( '/' ),
            ],
            'about'       => [
                '@type'       => 'Service',
                'name'        => "WordPress Development in {$state}",
                'description' => "Custom WordPress plugin development, AI integration, enterprise API solutions, and white-label development for agencies in {$state}. Founded 2012, serving clients nationwide.",
                'provider'    => $this->getOrganizationSchema(),
                'areaServed'  => [
                    '@type'         => 'State',
                    'name'          => $state,
                    'containsPlace' => $this->getCitiesSchema( $cities ),
                ],
                'serviceType' => [
                    'Custom WordPress Plugin Development',
                    'AI Integration & Development',
                    'WordPress API Integration',
                    'White-Label WordPress Development',
                    'WooCommerce Development',
                    'WordPress Security Hardening',
                    'WordPress Maintenance & Support',
                    'LearnDash LMS Development',
                ],
                'offers'      => [
                    '@type'         => 'Offer',
                    'priceSpecification' => [
                        '@type'       => 'PriceSpecification',
                        'minPrice'    => '150',
                        'priceCurrency' => 'USD',
                        'unitText'    => 'per hour',
                    ],
                ],
            ],
            'mainEntity'  => [
                '@type'       => 'LocalBusiness',
                'name'        => '84EM WordPress Development',
                'description' => "WordPress development agency founded in 2012, specializing in custom plugin development, AI integration, and agency partnerships. Over 30 years of web development experience since 1995. Serving {$state} businesses from Cedar Rapids, Iowa headquarters.",
                'url'         => site_url( '/' ),
                'priceRange'  => '$$$',
                'areaServed'  => [
                    '@type' => 'State',
                    'name'  => $state,
                ],
                'knowsAbout'  => [
                    'WordPress Plugin Development',
                    'AI Integration',
                    'OpenAI API Integration',
                    'Anthropic Claude API',
                    'WooCommerce Customization',
                    'REST API Development',
                    'WordPress Security',
                    'LearnDash LMS',
                    'Enterprise WordPress Solutions',
                    'White-Label Development',
                ],
            ],
        ];

        return wp_json_encode( $schema );
    }

    /**
     * Generate schema for a city page
     *
     * @param  string  $state  State name
     * @param  string  $city  City name
     *
     * @return string JSON-LD schema
     */
    public function generateCitySchema( string $state, string $city ): string {
        return $this->generateCitySchemaInternal( [ 'state' => $state, 'city' => $city ] );
    }

    /**
     * Generate schema for a city page (internal)
     *
     * @param  array  $data  City data
     *
     * @return string
     */
    private function generateCitySchemaInternal( array $data ): string {
        $state   = $data['state'] ?? '';
        $city    = $data['city'] ?? '';
        $post_id = $data['post_id'] ?? null;

        $state_slug = sanitize_title( $state );
        $city_slug  = sanitize_title( $city );
        $url        = $post_id ? get_permalink( $post_id ) : site_url( "/wordpress-development-services-usa/{$state_slug}/{$city_slug}/" );

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            'name'        => "Web Development & WordPress Services in {$city}, {$state}",
            'description' => "Professional WordPress development, custom plugins, AI integration, and white-label agency services in {$city}, {$state}. Cedar Rapids, Iowa-based agency with over 30 years of web development experience.",
            'url'         => $url,
            'isPartOf'    => [
                '@type' => 'WebSite',
                'name'  => '84EM - WordPress Development Agency',
                'url'   => site_url( '/' ),
            ],
            'breadcrumb'  => [
                '@type'           => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type'    => 'ListItem',
                        'position' => 1,
                        'name'     => 'Home',
                        'item'     => site_url( '/' ),
                    ],
                    [
                        '@type'    => 'ListItem',
                        'position' => 2,
                        'name'     => $state,
                        'item'     => site_url( "/wordpress-development-services-usa/{$state_slug}/" ),
                    ],
                    [
                        '@type'    => 'ListItem',
                        'position' => 3,
                        'name'     => $city,
                        'item'     => $url,
                    ],
                ],
            ],
            'about'       => [
                '@type'       => 'Service',
                'name'        => "WordPress Development in {$city}",
                'description' => "Custom WordPress plugin development, AI integration, API solutions, and white-label agency services for businesses in {$city}, {$state}. Founded 2012, serving clients nationwide.",
                'provider'    => $this->getOrganizationSchema(),
                'areaServed'  => [
                    '@type'            => 'City',
                    'name'             => $city,
                    'containedInPlace' => [
                        '@type' => 'State',
                        'name'  => $state,
                    ],
                ],
                'serviceType' => [
                    'Custom WordPress Plugin Development',
                    'AI Integration & Development',
                    'WordPress API Integration',
                    'White-Label WordPress Development',
                    'WooCommerce Development',
                    'WordPress Security Hardening',
                    'WordPress Maintenance & Support',
                ],
                'offers'      => [
                    '@type'         => 'Offer',
                    'priceSpecification' => [
                        '@type'       => 'PriceSpecification',
                        'minPrice'    => '150',
                        'priceCurrency' => 'USD',
                        'unitText'    => 'per hour',
                    ],
                ],
            ],
        ];

        return wp_json_encode( $schema );
    }

    /**
     * Generate schema for the index page
     *
     * @param  array  $data  Index page data
     *
     * @return string
     */
    private function generateIndexSchema( array $data ): string {
        $states_data = $data['states_data'] ?? [];

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => 'WordPress Development Services by Location',
            'description' => 'Professional WordPress development services available across all 50 US states. Find expert WordPress developers in your area.',
            'url'         => site_url( '/wordpress-development-services/' ),
            'isPartOf'    => [
                '@type' => 'WebSite',
                'name'  => '84EM - WordPress Development Agency',
                'url'   => site_url( '/' ),
            ],
            'mainEntity'  => [
                '@type'           => 'ItemList',
                'name'            => 'US States We Serve',
                'numberOfItems'   => count( $states_data ),
                'itemListElement' => $this->getStatesListSchema( $states_data ),
            ],
            'provider'    => $this->getOrganizationSchema(),
        ];

        return wp_json_encode( $schema );
    }

    /**
     * Get organization schema
     *
     * @return array
     */
    private function getOrganizationSchema(): array {
        return [
            '@type'            => 'Organization',
            'name'             => '84EM',
            'legalName'        => '84EM, LLC',
            'url'              => 'https://84em.com',
            'logo'             => 'https://84em.com/wp-content/uploads/2016/08/cropped-84em-cropped-250.png',
            'foundingDate'     => '2012',
            'foundingLocation' => [
                '@type'           => 'Place',
                'address'         => [
                    '@type'           => 'PostalAddress',
                    'addressLocality' => 'Cedar Rapids',
                    'addressRegion'   => 'Iowa',
                    'addressCountry'  => 'US',
                ],
            ],
            'description'      => 'WordPress development agency specializing in custom plugin development, AI integration, and agency partnerships. Founded in 2012 with over 30 years of web development experience since 1995.',
            'slogan'           => 'Senior-level WordPress development and AI integration',
            'areaServed'       => [
                '@type' => 'Country',
                'name'  => 'United States',
            ],
            'knowsAbout'       => [
                'WordPress Plugin Development',
                'Custom WordPress Development',
                'WordPress API Integration',
                'AI Integration',
                'WooCommerce Development',
                'LearnDash LMS',
                'White-Label WordPress Development',
                'WordPress Security',
                'WordPress Maintenance',
                'Enterprise WordPress Solutions',
            ],
            'sameAs'           => [
                'https://github.com/84emllc',
                'https://www.linkedin.com/company/84em',
                'https://www.linkedin.com/in/andrew84em/',
                'https://facebook.com/84emllc',
            ],
            'contactPoint'     => [
                '@type'             => 'ContactPoint',
                'contactType'       => 'sales',
                'email'             => 'hello@84em.com',
                'url'               => 'https://84em.com/contact/',
                'availableLanguage' => 'English',
                'hoursAvailable'    => [
                    '@type'     => 'OpeningHoursSpecification',
                    'dayOfWeek' => [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ],
                    'opens'     => '08:00',
                    'closes'    => '17:00',
                ],
            ],
            'founder'          => [
                '@type'  => 'Person',
                'name'   => 'Andrew Miller',
                'url'    => 'https://www.linkedin.com/in/andrew84em/',
                'sameAs' => 'https://www.linkedin.com/in/andrew84em/',
            ],
        ];
    }

    /**
     * Get cities schema array
     *
     * @param  array  $cities
     *
     * @return array
     */
    private function getCitiesSchema( array $cities ): array {
        return array_map( function ( $city ) {
            return [
                '@type' => 'City',
                'name'  => $city,
            ];
        }, $cities );
    }

    /**
     * Get states list schema
     *
     * @param  array  $states_data
     *
     * @return array
     */
    private function getStatesListSchema( array $states_data ): array {
        $items    = [];
        $position = 1;

        foreach ( $states_data as $state ) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position ++,
                'name'     => $state['name'] ?? '',
                'url'      => $state['url'] ?? '',
            ];
        }

        return $items;
    }

    /**
     * Validate schema data
     *
     * @param  string  $schema  JSON-LD schema to validate
     *
     * @return bool
     */
    public function validate( string $schema ): bool {
        // Remove script tags if present
        $schema = preg_replace( '/<script[^>]*>|<\/script>/i', '', $schema );

        // Try to decode JSON
        $decoded = json_decode( $schema, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return false;
        }

        // Check for required fields
        return isset( $decoded['@context'] ) && isset( $decoded['@type'] );
    }
}
