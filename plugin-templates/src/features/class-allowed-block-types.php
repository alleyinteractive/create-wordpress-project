<?php
/**
 * Create WordPress Plugin Features: Allowed_Block_Types class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use Closure;
use WP_Block_Editor_Context;
use WP_Block_Type;
use WP_Block_Type_Registry;

/**
 * Feature: Remove block types from the inserter based on a callback.
 */
final readonly class Allowed_Block_Types implements Feature {
	/**
	 * Callback to determine whether a block type is allowed.
	 *
	 * @var Closure
	 */
	private Closure $allowed;

	/**
	 * Constructor.
	 *
	 * @param string|string[]        $context  The context or contexts in which to apply the callback.
	 * @param callable               $allowed  Callback to determine if a block type is allowed.
	 * @param WP_Block_Type_Registry $registry The block type registry used to retrieve registered blocks.
	 */
	public function __construct(
		private string|array $context,
		callable $allowed,
		private WP_Block_Type_Registry $registry,
	) {
		$this->allowed = $allowed( ... );
	}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'allowed_block_types_all', $this->filter_allowed_block_types_all( ... ), 10, 2 );
	}

	/**
	 * Filters the allowed block types for all editor types.
	 *
	 * @param bool|string[]           $allowed_block_types  Array of block type slugs, or boolean to enable/disable all.
	 * @param WP_Block_Editor_Context $block_editor_context The current block editor context.
	 * @return bool|string[] Updated allowed block types.
	 */
	public function filter_allowed_block_types_all( $allowed_block_types, $block_editor_context ) {
		// Only constrain the contexts this instance was configured for.
		if ( ! in_array( $block_editor_context->name, (array) $this->context, true ) ) {
			return $allowed_block_types;
		}

		// Start from the full set of registered blocks when not already limited.
		if ( ! is_array( $allowed_block_types ) ) {
			$allowed_block_types = array_values(
				array_map(
					fn ( WP_Block_Type $block_type ) => $block_type->name,
					$this->registry->get_all_registered(),
				),
			);
		}

		return array_values(
			array_filter(
				$allowed_block_types,
				function ( $block_type_name ) use ( $block_editor_context ): bool {
					$block_type = $this->registry->get_registered( $block_type_name );

					// Keep unknown blocks allowed to avoid unintended removals.
					if ( ! $block_type instanceof WP_Block_Type ) {
						return true;
					}

					return (bool) ( $this->allowed )( $block_type, $block_editor_context->post );
				}
			)
		);
	}
}
