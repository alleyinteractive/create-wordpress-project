<?php
/**
 * Block Name: Post Subheadline.
 *
 * @package create-wordpress-plugin
 */

/**
 * Registers the create-wordpress-plugin/theme-post-subheadline block using the metadata loaded from the `block.json` file.
 */
function create_wordpress_plugin_theme_post_subheadline_block_init(): void {
	// Register the block by passing the location of block.json.
	register_block_type(
		__DIR__
	);
}
add_action( 'init', 'create_wordpress_plugin_theme_post_subheadline_block_init' );
