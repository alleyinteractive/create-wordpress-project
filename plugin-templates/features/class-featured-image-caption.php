<?php
/**
 * Featured_Image_Caption class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

final class Featured_Image_Caption implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'render_block_core/post-featured-image', [ $this, 'add_caption_to_featured_image' ], 10, 3 );
	}

	/**
	 * Adds the featured image caption to the featured image block.
	 *
	 * @param string   $block_content The existing block content.
	 * @param array    $block         The full block, including name and attributes.
	 * @param \WP_Block $instance      The block instance.
	 * @return void
	 */
	public function add_caption_to_featured_image( string $block_content, array $block, \WP_Block $instance  ) {
		$post_id = isset( $instance->context['postId'] ) ? $instance->context['postId'] : null;
		if ( empty( $post_id ) ) {
			return $block_content;
		}
		$featured_image_caption = get_post_meta( $post_id, 'create_wordpress_plugin_featured_image_caption', true );
		if ( empty( $featured_image_caption ) ) {
			$featured_image_id      = get_post_meta( $post_id, '_thumbnail_id', true );
			$featured_image_caption = wp_get_attachment_caption( $featured_image_id );
		}

		if ( ! empty( $featured_image_caption ) ) {
			$block_content = str_replace( '</figure>', '<figcaption class="wp-block-post-featured-image__caption">' . esc_html( $featured_image_caption ) . '</figcaption></figure>', $block_content );
		}

		return $block_content;
	}
}
