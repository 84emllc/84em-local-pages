<?php
/**
 * Checkpoint Manager for Bulk Operations
 *
 * Saves progress during bulk generation/update operations to allow resuming
 * after non-retryable errors.
 *
 * @package EightyFourEM\LocalPages\Utils
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Utils;

/**
 * Manages checkpoints for bulk operations
 */
class CheckpointManager {

	/**
	 * Option name prefix for checkpoints
	 */
	private const OPTION_PREFIX = '84em_local_pages_checkpoint_';

	/**
	 * Maximum checkpoint age in seconds (24 hours)
	 */
	private const MAX_CHECKPOINT_AGE = 86400;

	/**
	 * Save a checkpoint for a bulk operation
	 *
	 * @param  string  $operation_type  Type of operation (generate-all, update-all)
	 * @param  array   $data  Checkpoint data
	 *
	 * @return bool True on success, false on failure
	 */
	public function saveCheckpoint( string $operation_type, array $data ): bool {
		$option_name = self::OPTION_PREFIX . $operation_type;

		// Add timestamp to checkpoint data
		$data['timestamp'] = time();

		return update_option(
			option:    $option_name,
			value:     $data,
			autoload:  false
		);
	}

	/**
	 * Load a checkpoint for a bulk operation
	 *
	 * @param  string  $operation_type  Type of operation (generate-all, update-all)
	 *
	 * @return array|false Checkpoint data or false if not found/expired
	 */
	public function loadCheckpoint( string $operation_type ): array|false {
		$option_name = self::OPTION_PREFIX . $operation_type;
		$checkpoint  = get_option( $option_name, false );

		if ( false === $checkpoint ) {
			return false;
		}

		// Check if checkpoint is expired
		$timestamp = $checkpoint['timestamp'] ?? 0;
		if ( time() - $timestamp > self::MAX_CHECKPOINT_AGE ) {
			// Checkpoint is too old, delete it
			$this->deleteCheckpoint( $operation_type );
			return false;
		}

		return $checkpoint;
	}

	/**
	 * Delete a checkpoint for a bulk operation
	 *
	 * @param  string  $operation_type  Type of operation
	 *
	 * @return bool True on success, false on failure
	 */
	public function deleteCheckpoint( string $operation_type ): bool {
		$option_name = self::OPTION_PREFIX . $operation_type;
		return delete_option( $option_name );
	}

	/**
	 * Check if a checkpoint exists for an operation
	 *
	 * @param  string  $operation_type  Type of operation
	 *
	 * @return bool True if valid checkpoint exists, false otherwise
	 */
	public function hasCheckpoint( string $operation_type ): bool {
		$checkpoint = $this->loadCheckpoint( $operation_type );
		return false !== $checkpoint;
	}

	/**
	 * Get checkpoint age in seconds
	 *
	 * @param  string  $operation_type  Type of operation
	 *
	 * @return int|false Age in seconds or false if no checkpoint exists
	 */
	public function getCheckpointAge( string $operation_type ): int|false {
		$checkpoint = $this->loadCheckpoint( $operation_type );

		if ( false === $checkpoint ) {
			return false;
		}

		$timestamp = $checkpoint['timestamp'] ?? 0;
		return time() - $timestamp;
	}

	/**
	 * Format checkpoint age as human-readable string
	 *
	 * @param  int  $seconds  Age in seconds
	 *
	 * @return string Human-readable age string
	 */
	public function formatAge( int $seconds ): string {
		if ( $seconds < 60 ) {
			return $seconds . ' second' . ( $seconds !== 1 ? 's' : '' ) . ' ago';
		}

		$minutes = floor( $seconds / 60 );
		if ( $minutes < 60 ) {
			return $minutes . ' minute' . ( $minutes !== 1 ? 's' : '' ) . ' ago';
		}

		$hours = floor( $minutes / 60 );
		return $hours . ' hour' . ( $hours !== 1 ? 's' : '' ) . ' ago';
	}

	/**
	 * Clear all checkpoints (useful for cleanup)
	 *
	 * @return int Number of checkpoints deleted
	 */
	public function clearAllCheckpoints(): int {
		global $wpdb;

		$pattern = self::OPTION_PREFIX . '%';
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$pattern
			)
		);

		return (int) $deleted;
	}
}