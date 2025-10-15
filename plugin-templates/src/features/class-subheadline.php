<?php
/**
 * Create WordPress Plugin Features: Subheadline class
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Adds support for subheadlines.
 *
 * @package create-wordpress-plugin
 */
final class Subheadline implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'init', [ $this, 'add_meta_field' ] );
	}

	/**
	 * Adds support for subheadlines to any posts that support them (default post, but filterable).
	 */
	public function add_meta_field(): void {
		/**
		 * Filter the post types that support subheadline.
		 * Defaults to `post`.
		 *
		 * @param array $post_types The post types that support subheadline.
		 * @return array The post types that support subheadline.
		 */
		$post_types = apply_filters( 'create_wordpress_plugin_subheadline_post_types', [ 'post' ] );
		foreach ( $post_types as $post_type ) {
			if ( is_string( $post_type ) ) {
				add_post_type_support( $post_type, 'subheadline' );
			}
		}

		foreach ( get_post_types_by_support( 'subheadline' ) as $post_type ) {
			register_post_meta(
				$post_type,
				'create_wordpress_plugin_subheadline',
				[
					'sanitize_callback' => 'wp_kses_post',
					'single'            => true,
					'type'              => 'string',
					'show_in_rest'      => true,
				]
			);
		}
	}
}
