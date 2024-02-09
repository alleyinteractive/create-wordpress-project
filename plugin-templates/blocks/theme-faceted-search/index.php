<?php
/**
 * Block Name: Faceted Search.
 *
 * @package create-wordpress-plugin
 */

/**
 * Registers the create-wordpress-plugin/theme-faceted-search block using the metadata loaded from the `block.json` file.
 */
function theme_faceted_search_theme_faceted_search_block_init(): void {
	// Register the block by passing the location of block.json.
	register_block_type(
		__DIR__
	);
}
add_action( 'init', 'theme_faceted_search_theme_faceted_search_block_init' );
