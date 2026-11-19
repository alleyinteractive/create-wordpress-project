<?php
/**
 * Create WordPress Plugin Features: Removed_Apply_Globally_Control class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Removes the "Apply globally" control from the block Advanced panel in the post editor.
 *
 * The control pushes a block's styles into global styles. It checks only for a
 * block theme, not whether the user can update global styles, so denying those
 * capabilities leaves a control that fails when used.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/packages/editor/src/hooks/push-changes-to-global-styles/index.js
 */
final readonly class Removed_Apply_Globally_Control implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'enqueue_block_editor_assets', $this->on_enqueue_block_editor_assets( ... ) );
	}

	/**
	 * Removes the filter that adds the control, after `wp-editor` has added it.
	 */
	public function on_enqueue_block_editor_assets(): void {
		wp_add_inline_script(
			'wp-editor',
			"wp.hooks.removeFilter( 'editor.BlockEdit', 'core/editor/push-changes-to-global-styles' );",
			'after',
		);
	}
}
