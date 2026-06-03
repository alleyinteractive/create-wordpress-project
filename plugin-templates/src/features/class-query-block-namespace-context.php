<?php
/**
 * Create WordPress Plugin Features: Query_Block_Namespace_Context class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Makes the 'namespace' attribute of a core query block available as
 * block context to its inner blocks.
 */
final readonly class Query_Block_Namespace_Context implements Feature {
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
		if ( 'core/query' === $block_type ) {
			if ( ! isset( $args['provides_context'] ) || ! is_array( $args['provides_context'] ) ) {
				$args['provides_context'] = [];
			}

			$args['provides_context']['namespace'] = 'namespace';
		}

		return $args;
	}
}
