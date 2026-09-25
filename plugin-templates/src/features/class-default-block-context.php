<?php
/**
 * Create WordPress Plugin Features: Default_Block_Context class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Applies the given default block context to rendered blocks.
 */
final readonly class Default_Block_Context implements Feature {
	/**
	 * Constructor.
	 *
	 * @param mixed[] $context The block context to apply.
	 */
	public function __construct(
		private array $context,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'render_block_context', $this->filter_render_block_context( ... ) );
	}

	/**
	 * Filters the default context provided to a rendered block.
	 *
	 * @param mixed $context Default context.
	 * @return mixed Updated context.
	 */
	public function filter_render_block_context( $context ) {
		if ( is_array( $context ) ) {
			$context = array_merge( $context, $this->context );
		}

		return $context;
	}
}
