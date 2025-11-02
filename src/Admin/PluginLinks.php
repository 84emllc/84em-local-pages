<?php
/**
 * Plugin Links
 *
 * @package EightyFourEM\LocalPages\Admin
 */

namespace EightyFourEM\LocalPages\Admin;

/**
 * Manages plugin action links on the plugins page
 */
class PluginLinks {
    /**
     * Initialize plugin links
     */
    public function init(): void {
        $plugin_basename = plugin_basename( dirname( __DIR__, 2 ) . '/84em-local-pages.php' );
        add_filter( 'plugin_action_links_' . $plugin_basename, [ $this, 'addActionLinks' ] );
    }

    /**
     * Add custom action links to the plugin on the plugins page
     *
     * @param array $links Existing plugin action links
     * @return array Modified plugin action links
     */
    public function addActionLinks( array $links ): array {
        $custom_links = [];

        // Changelog link (all users)
        $custom_links[] = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url( 'https://github.com/84emllc/84em-local-pages/blob/main/CHANGELOG.md' ),
            esc_html__( 'Changelog', '84em-local-pages' )
        );

        // View on GitHub link (all users)
        $custom_links[] = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url( 'https://github.com/84emllc/84em-local-pages/' ),
            esc_html__( 'View on GitHub', '84em-local-pages' )
        );

        return array_merge( $custom_links, $links );
    }
}