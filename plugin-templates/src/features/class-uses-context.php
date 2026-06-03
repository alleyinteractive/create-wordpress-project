<?php
/**
 * Create WordPress Plugin Features: Uses_Context class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Declare that a block type uses a context property.
 */
final readonly class Uses_Context implements Feature {
	/**
	 * Constructor.
	 *
	 * @param string $block_name Block type name including namespace, e.g. 'core/query'.
	 * @param string $context    Context property name to use, e.g. 'postId'.
	 */
	public function __construct(
		private string $block_name,
		private string $context,
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
		if ( $this->block_name === $block_type ) {
			if ( ! isset( $args['uses_context'] ) || ! is_array( $args['uses_context'] ) ) {
				$args['uses_context'] = [];
			}

			$args['uses_context'][] = $this->context;
		}

		return $args;
	}
}
