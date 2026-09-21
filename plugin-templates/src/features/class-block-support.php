<?php
/**
 * Create WordPress Plugin Features: Block_Support class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Add support for a feature to a block type.
 */
final readonly class Block_Support implements Feature {
	/**
	 * Constructor.
	 *
	 * @param string[]            $block_type The block types to add support for.
	 * @param string              $feature    The feature to add support for, e.g. 'color'.
	 * @param bool|string|mixed[] $config     Configuration for the feature.
	 */
	public function __construct(
		private array $block_type,
		private string $feature,
		private bool|string|array $config,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'register_block_type_args', $this->filter_register_block_type_args( ... ), 10, 2 );
	}

	/**
	 * Filters the arguments for registering a block type.
	 *
	 * @param mixed[] $args       Array of arguments for registering a block type.
	 * @param string  $block_type Block type name including namespace.
	 * @return mixed[] Updated arguments.
	 */
	public function filter_register_block_type_args( $args, $block_type ) {
		if ( in_array( $block_type, $this->block_type, true ) ) {
			if ( ! isset( $args['supports'] ) || ! is_array( $args['supports'] ) ) {
				$args['supports'] = [];
			}

			$args['supports'][ $this->feature ] = $this->config;
		}

		return $args;
	}
}
