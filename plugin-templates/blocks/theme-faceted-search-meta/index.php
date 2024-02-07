<?php
/**
 * Block Name: Faceted Search Meta.
 *
 * @package create-wordpress-project
 */

/**
 * Registers the create-wordpress-project/theme-search-meta block using the metadata loaded from the `block.json` file.
 */
function theme_faceted_search_meta_theme_faceted_search_meta_block_init(): void {
	// Register the block by passing the location of block.json.
	register_block_type(
		__DIR__
	);
}
add_action( 'init', 'theme_faceted_search_meta_theme_faceted_search_meta_block_init' );
