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
use EightyFourEM\LocalPages\Data\StatesProvider;

/**
 * Generates Schema.org structured data for local pages
 */
class SchemaGenerator implements SchemaGeneratorInterface {
    /**
     * States data provider
     *
     * @var StatesProvider
     */
    private StatesProvider $statesProvider;

    /**
     * Constructor
     *
     * @param  StatesProvider  $statesProvider
     */
    public function __construct( StatesProvider $statesProvider ) {
        $this->statesProvider = $statesProvider;
    }

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
            'name'        => "WordPress Development Services in {$state}",
            'description' => "Professional WordPress development, custom plugins, and agency services in {$state}. Expert developers serving " . implode( ', ', array_slice( $cities, 0, 3 ) ) . " and beyond.",
            'url'         => $url,
            'isPartOf'    => [
                '@type' => 'WebSite',
                'name'  => '84EM - WordPress Development Agency',
                'url'   => site_url( '/' ),
            ],
            'about'       => [
                '@type'       => 'Service',
                'name'        => "WordPress Development in {$state}",
                'provider'    => $this->getOrganizationSchema(),
                'areaServed'  => [
                    '@type'         => 'State',
                    'name'          => $state,
                    'containsPlace' => $this->getCitiesSchema( $cities ),
                ],
                'serviceType' => [
                    'WordPress Development',
                    'Custom Plugin Development',
                    'WordPress Maintenance',
                    'Agency Services',
                    'WordPress Security',
                ],
            ],
            'mainEntity'  => [
                '@type'       => 'LocalBusiness',
                'name'        => '84EM WordPress Development',
                'description' => "WordPress development agency serving businesses in {$state}",
                'url'         => site_url( '/' ),
                'areaServed'  => [
                    '@type' => 'State',
                    'name'  => $state,
                ],
                'knowsAbout'  => [
                    'WordPress Development',
                    'PHP Programming',
                    'JavaScript Development',
                    'API Integration',
                    'eCommerce Development',
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
            'name'        => "WordPress Development Services in {$city}, {$state}",
            'description' => "Professional WordPress development and custom plugin services in {$city}, {$state}.",
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
                    'WordPress Development',
                    'Custom Plugin Development',
                    'WordPress Maintenance',
                    'Agency Services',
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
            '@type'        => 'Organization',
            'name'         => '84EM',
            'url'          => 'https://84em.com',
            'logo'         => 'https://84em.com/wp-content/uploads/84em-logo.png',
            'sameAs'       => [
                'https://github.com/84em',
                'https://www.linkedin.com/company/84em',
            ],
            'contactPoint' => [
                '@type'       => 'ContactPoint',
                'contactType' => 'sales',
                'email'       => 'hello@84em.com',
                'url'         => 'https://84em.com/contact/',
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
