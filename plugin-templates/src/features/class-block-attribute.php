<?php
/**
 * Create WordPress Plugin Features: Block_Attribute class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Add an attribute to a block type.
 */
final readonly class Block_Attribute implements Feature {
	/**
	 * Constructor.
	 *
	 * @param string[] $block_types The block types to add the attribute to.
	 * @param string   $attribute   The attribute to add to the block type.
	 * @param mixed[]  $schema      Schema for the attribute.
	 */
	public function __construct(
		private array $block_types,
		private string $attribute,
		private array $schema,
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
		if ( in_array( $block_type, $this->block_types, true ) ) {
			if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
				$args['attributes'] = [];
			}

			$args['attributes'][ $this->attribute ] = $this->schema;
		}

		return $args;
	}
}
