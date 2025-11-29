<?php
/**
 * Service Keywords Data Provider
 *
 * @package EightyFourEM\LocalPages\Data
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Data;

use EightyFourEM\LocalPages\Contracts\DataProviderInterface;

/**
 * Provides service keywords data
 */
class KeywordsProvider implements DataProviderInterface {
    /**
     * Keywords data cache
     *
     * @var array|null
     */
    private ?array $data = null;

    /**
     * Get all keywords data
     *
     * @return array
     */
    public function getAll(): array {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return $this->data;
    }

    /**
     * Get URL for a specific keyword
     *
     * @param  string  $key  Keyword
     *
     * @return mixed|null
     */
    public function get( string $key ): mixed {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return $this->data[ $key ] ?? null;
    }

    /**
     * Check if keyword exists
     *
     * @param  string  $key  Keyword
     *
     * @return bool
     */
    public function has( string $key ): bool {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return isset( $this->data[ $key ] );
    }

    /**
     * Get all keywords
     *
     * @return array
     */
    public function getKeys(): array {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return array_keys( $this->data );
    }

    /**
     * Load keywords data
     */
    private function loadData(): void {
        $work_page   = site_url( '/work/' );
        $services    = site_url( '/services/' );
        $plugins     = site_url( '/services/custom-wordpress-plugin-development/' );
        $whitelabel  = site_url( '/services/wordpress-development-for-agencies/' );
        $ai          = site_url( '/services/ai-enhanced-wordpress-development/' );
        $consulting  = site_url( '/services/wordpress-consulting-strategy/' );
        $maintenance = site_url( '/services/wordpress-maintenance-support/' );

        $this->data = [
            'AI Enhanced WordPress Development'         => $ai,
            'AI'                                        => $ai,
            'AI WordPress'                              => $ai,
            'API integrations'                          => $services,
            'AI WordPress development'                  => $ai,
            'AI Plugins'                                => $ai,
            'AI WordPress Plugins'                      => $ai,
            'Consulting'                                => $consulting,
            'WordPress Consulting'                      => $consulting,
            'Plugins'                                   => $plugins,
            'Custom Solutions'                          => $plugins,
            'Custom WordPress development'              => $plugins,
            'Data migration and platform transfers'     => $services,
            'Platform Migrations'                       => $services,
            'White label WordPress development'         => $whitelabel,
            'WordPress Maintenance and Support'         => $maintenance,
            'WordPress development'                     => $ai,
            'WordPress maintenance and ongoing support' => $maintenance,
            'WordPress maintenance'                     => $maintenance,
            'WordPress migrations'                      => $services,
            'WordPress plugin development services'     => $plugins,
            'WordPress plugin development'              => $plugins,
            'WordPress security audits and hardening'   => $services,
            'WordPress security audits'                 => $services,
            'WordPress security'                        => $services,
            'WordPress support'                         => $maintenance,
            'custom WordPress themes'                   => $services,
            'WordPress troubleshooting'                 => $maintenance,
            'custom plugin development'                 => $plugins,
            'data migration'                            => $services,
            'digital agency services'                   => $whitelabel,
            'platform transfers'                        => $services,
            'security audits'                           => $work_page,
            'theme development'                         => $services,
            'web development'                           => $work_page,
            'white-label development'                   => $whitelabel,
            'White Label Development'                   => $whitelabel,
            'Agency Partnership'                        => $whitelabel,
            'Agency Services'                           => $whitelabel,
        ];
    }
}
