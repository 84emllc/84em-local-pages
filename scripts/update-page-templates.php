<?php
/**
 * WP-CLI Script: Update Page Templates for Local Pages
 *
 * Updates the _wp_page_template meta value to 'wp-custom-template-local-pages-2'
 * for any page that has either a _local_page_cities OR _local_page_state meta key.
 *
 * Usage:
 *   wp eval-file wp-content/plugins/84em-local-pages/scripts/update-page-templates.php
 *
 * Or with specific template:
 *   wp eval-file wp-content/plugins/84em-local-pages/scripts/update-page-templates.php --template=wp-custom-template-local-pages-2
 *
 * Dry run mode:
 *   wp eval-file wp-content/plugins/84em-local-pages/scripts/update-page-templates.php --dry-run
 *
 * @package EightyFourEM\LocalPages\Scripts
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

defined( 'ABSPATH' ) or die;

// Ensure WP-CLI is available.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "This script must be run via WP-CLI.\n";
	exit( 1 );
}

/**
 * Update page templates for local pages.
 *
 * @param array $args       Positional arguments.
 * @param array $assoc_args Associative arguments.
 *
 * @return void
 */
function eightyfourem_update_local_page_templates( array $args, array $assoc_args ): void {
	// Configuration.
	$template_name = $assoc_args['template'] ?? 'wp-custom-template-local-pages-2';
	$dry_run       = isset( $assoc_args['dry-run'] );

	WP_CLI::line( '' );
	WP_CLI::line( 'Update Page Templates for Local Pages' );
	WP_CLI::line( '======================================' );
	WP_CLI::line( '' );

	if ( $dry_run ) {
		WP_CLI::warning( 'DRY RUN MODE - No changes will be made.' );
		WP_CLI::line( '' );
	}

	WP_CLI::line( "Target template: {$template_name}" );
	WP_CLI::line( '' );

	// Query for pages with either _local_page_cities OR _local_page_state meta key.
	// We use a meta_query with OR relation to find pages with either key.
	$query_args = [
		'post_type'      => 'page',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_query'     => [
			'relation' => 'OR',
			[
				'key'     => '_local_page_cities',
				'compare' => 'EXISTS',
			],
			[
				'key'     => '_local_page_state',
				'compare' => 'EXISTS',
			],
		],
	];

	$query = new WP_Query( $query_args );
	$posts = $query->posts;
	wp_reset_postdata();

	if ( empty( $posts ) ) {
		WP_CLI::warning( 'No local pages found (no pages with _local_page_cities or _local_page_state meta).' );
		return;
	}

	$total_found    = count( $posts );
	$updated_count  = 0;
	$skipped_count  = 0;
	$already_set    = 0;

	WP_CLI::line( "Found {$total_found} local pages to process." );
	WP_CLI::line( '' );

	// Create progress bar.
	$progress = \WP_CLI\Utils\make_progress_bar( 'Processing pages', $total_found );

	foreach ( $posts as $post ) {
		// Get current template value.
		$current_template = get_post_meta( $post->ID, '_wp_page_template', true );

		// Check if already set to target template.
		if ( $current_template === $template_name ) {
			$already_set++;
			$progress->tick();
			continue;
		}

		// Determine page type for logging.
		$state = get_post_meta( $post->ID, '_local_page_state', true );
		$city  = get_post_meta( $post->ID, '_local_page_city', true );
		$cities_meta = get_post_meta( $post->ID, '_local_page_cities', true );

		$location_label = '';
		if ( $city && $state ) {
			$location_label = "{$city}, {$state}";
		} elseif ( $state ) {
			$location_label = $state;
		} elseif ( $cities_meta ) {
			$location_label = 'Cities page';
		} else {
			$location_label = 'Unknown local page';
		}

		if ( $dry_run ) {
			$old_template = $current_template ?: '(default)';
			WP_CLI::log( "[DRY RUN] Would update: {$post->post_title} ({$location_label}) - Current: {$old_template}" );
			$updated_count++;
		} else {
			// Update the page template.
			$result = update_post_meta( $post->ID, '_wp_page_template', $template_name );

			if ( false !== $result ) {
				$updated_count++;
				WP_CLI::log( "Updated: {$post->post_title} ({$location_label}) - ID: {$post->ID}" );
			} else {
				$skipped_count++;
				WP_CLI::warning( "Failed to update: {$post->post_title} (ID: {$post->ID})" );
			}
		}

		$progress->tick();
	}

	$progress->finish();

	// Display summary.
	WP_CLI::line( '' );
	WP_CLI::line( 'Summary' );
	WP_CLI::line( '=======' );
	WP_CLI::line( "Total pages found: {$total_found}" );

	if ( $dry_run ) {
		WP_CLI::line( "Would update: {$updated_count}" );
	} else {
		WP_CLI::line( "Updated: {$updated_count}" );
	}

	WP_CLI::line( "Already set correctly: {$already_set}" );

	if ( $skipped_count > 0 ) {
		WP_CLI::line( "Failed to update: {$skipped_count}" );
	}

	WP_CLI::line( '' );

	if ( $dry_run ) {
		WP_CLI::warning( 'DRY RUN - No changes were made. Remove --dry-run to apply changes.' );
	} elseif ( $updated_count > 0 || $already_set > 0 ) {
		WP_CLI::success( 'Page template update complete!' );
	}
}

// Parse WP-CLI arguments from $args global (available in wp eval-file context).
$cli_args = [];
$assoc_args = [];

// Get arguments from WP-CLI context.
if ( isset( $args ) && is_array( $args ) ) {
	foreach ( $args as $arg ) {
		if ( strpos( $arg, '--' ) === 0 ) {
			$arg = substr( $arg, 2 );
			if ( strpos( $arg, '=' ) !== false ) {
				list( $key, $value ) = explode( '=', $arg, 2 );
				$assoc_args[ $key ] = $value;
			} else {
				$assoc_args[ $arg ] = true;
			}
		} else {
			$cli_args[] = $arg;
		}
	}
}

// Run the update function.
eightyfourem_update_local_page_templates( $cli_args, $assoc_args );
