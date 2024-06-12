<?php
/**
 * Block Name: Faceted Search Meta.
 *
 * @package create-wordpress-plugin
 */

/**
 * Registers the create-wordpress-plugin/theme-search-meta block using the metadata loaded from the `block.json` file.
 */
function create_wordpress_plugin_theme_faceted_search_meta_block_init(): void {
	// Register the block by passing the location of block.json.
	register_block_type(
		__DIR__
	);
}
add_action( 'init', 'create_wordpress_plugin_theme_faceted_search_meta_block_init' );
