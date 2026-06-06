<?php
/**
 * Create WordPress Plugin Features: Post_Type_Support class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Add support for a feature to a post type.
 */
final readonly class Post_Type_Support implements Feature {
	/**
	 * Constructor.
	 *
	 * @param string $post_type Post type to add support to.
	 * @param string $feature   Feature to add support for.
	 */
	public function __construct(
		private string $post_type,
		private string $feature,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		if ( post_type_exists( $this->post_type ) ) {
			$this->add_support();
		} else {
			add_action( "registered_post_type_{$this->post_type}", $this->add_support( ... ) );
		}
	}

	/**
	 * Register the post type support.
	 */
	public function add_support(): void {
		add_post_type_support( $this->post_type, $this->feature );
	}
}
