<?php
/**
 * Featured_Image_Caption class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin;

use Alley\WP\Types\Feature;

final class Featured_Image_Caption implements Feature {
	public function __construct() {
		add_filter( 'render_block_core/post-featured-image', [ $this, 'add_caption_to_featured_image' ], 10, 1 );
	}

	public function add_caption_to_featured_image( $block_content  ) {
		$featured_image_caption = get_post_meta( get_the_ID(), 'create_wordpress_plugin_featured_image_caption', true );
		if ( empty( $featured_image_caption ) ) {
			$featured_image_id      = get_post_meta( get_the_ID(), '_thumbnail_id', true );
			$featured_image_caption = wp_get_attachment_caption( $featured_image_id );
		}

		if ( ! empty( $featured_image_caption ) ) {
			$block_content = str_replace( '</figure>', '<figcaption class="wp-block-post-featured-image__caption">' . $featured_image_caption . '</figcaption></figure>', $block_content );
		}

		return $block_content;
	}
}
new Featured_Image_Caption();
