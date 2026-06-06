<?php
/**
 * Create WordPress Plugin Features: Featured_Image_Caption class
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_Block;

/**
 * Feature: Adds support for captions on featured images.
 *
 * @package create-wordpress-plugin
 */
final readonly class Featured_Image_Caption implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'render_block_core/post-featured-image', $this->filter_render_block_core_post_featured_image( ... ), 10, 3 );
		add_action( 'init', $this->on_init( ... ) );
	}

	/**
	 * Filters the content of a single block.
	 *
	 * Appends the featured image caption to the featured image block markup.
	 *
	 * @param string   $block_content The block content.
	 * @param mixed[]  $block         The full block, including name and attributes.
	 * @param WP_Block $instance      The block instance.
	 * @return string The filtered block content.
	 */
	public function filter_render_block_core_post_featured_image( $block_content, $block, $instance ) {
		$post_id = is_numeric( $instance->context['postId'] ?? null ) ? (int) $instance->context['postId'] : 0;
		if ( $post_id === 0 ) {
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
	 * Fires after WordPress has finished loading but before any headers are sent.
	 *
	 * Registers the featured image caption meta for post types that support featured images.
	 */
	public function on_init(): void {
		foreach ( get_post_types_by_support( 'thumbnail' ) as $post_type ) {
			register_post_meta(
				$post_type,
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
}
