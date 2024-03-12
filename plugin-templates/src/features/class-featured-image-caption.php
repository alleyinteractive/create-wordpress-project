<?php
/**
 * Create WordPress Plugin Features: Featured_Image_Caption class
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_Block;

use function Create_WordPress_Plugin\register_meta_helper;

/**
 * Feature: Adds support for captions on featured images.
 *
 * @package create-wordpress-plugin
 */
final class Featured_Image_Caption implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'render_block_core/post-featured-image', [ $this, 'add_caption_to_featured_image' ], 10, 3 );
		add_action( 'init', [ $this, 'add_meta_field' ] );
	}

	/**
	 * Adds the featured image caption to the featured image block.
	 *
	 * @param string $block_content The existing block content.
	 * @param array $block The full block, including name and attributes.
	 * @param WP_Block $instance The block instance.
	 *
	 * @phpstan-param array<string, mixed> $block
	 *
	 * @return string Modified block content.
	 */
	public function add_caption_to_featured_image( string $block_content, array $block, WP_Block $instance  ): string {
		$post_id = $instance->context['postId'] ?? null;
		if ( empty( $post_id ) ) {
			return $block_content;
		}
		$featured_image_caption = get_post_meta( $post_id, 'create_wordpress_plugin_featured_image_caption', true );
		if ( empty( $featured_image_caption ) ) {
			$featured_image_id      = get_post_meta( $post_id, '_thumbnail_id', true );
			$featured_image_caption = wp_get_attachment_caption( is_numeric( $featured_image_id ) ? (int) $featured_image_id : 0 );
		}

		if ( ! empty( $featured_image_caption ) ) {
			$block_content = str_replace( '</figure>', '<figcaption class="wp-block-post-featured-image__caption">' . esc_html( is_string( $featured_image_caption ) ? $featured_image_caption : '' ) . '</figcaption></figure>', $block_content );
		}

		return $block_content;
	}

	/**
	 * Registers the meta field only for post types that support featured images.
	 */
	public function add_meta_field(): void {
		register_meta_helper(
			'post',
			get_post_types_by_support( 'thumbnail' ),
			'create_wordpress_plugin_featured_image_caption',
			[
				'sanitize_callback' => 'sanitize_text_field',
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => true,
			]
		);
	}
}
