<?php
/**
 * Create WordPress Plugin Features: Disable_Block_Patterns class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Disables core and remotely fetched block patterns.
 */
final readonly class Disable_Block_Patterns implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		// Run before core registers theme block patterns on `init` (priority 10).
		add_action( 'init', $this->on_init( ... ), 9 );
		add_filter( 'should_load_remote_block_patterns', '__return_false' );
	}

	/**
	 * Fires after WordPress has finished loading but before any headers are sent.
	 *
	 * Removes support for core block patterns.
	 */
	public function on_init(): void {
		remove_theme_support( 'core-block-patterns' );
	}
}
