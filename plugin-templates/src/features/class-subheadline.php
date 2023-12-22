<?php
/**
 * Subheadline class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;
use function Create_WordPress_Plugin\register_meta_helper;

use Alley\WP\Types\Feature;

final class Subheadline implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'init', [ $this, 'add_meta_field' ] );
	}

	/**
	 * Adds support for subheadline to the `post` post type.
	 * Registers the meta field only for post types that support subheadline.
	 */
	public function add_meta_field() {
		$post_types = apply_filters( 'create_wordpress_plugin_subheadline_post_types', [ 'post' ] );
		foreach ( $post_types as $post_type ) {
			add_post_type_support( $post_type, 'subheadline' );
		}

		register_meta_helper(
			'post',
			get_post_types_by_support( 'subheadline' ),
			'create_wordpress_plugin_subheadline',
			[
				'sanitize_callback' => 'sanitize_text_field',
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => true,
			]
		);
	}
}
